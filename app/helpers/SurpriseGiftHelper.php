<?php
class SurpriseGiftHelper {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Check if user is eligible for a surprise gift and select one
     */
    public function selectSurpriseGift($userId, $orderAmount, $orderCount = 0) {
        // Get user's order count if not provided
        if ($orderCount === 0) {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM orders WHERE user_id = ?");
            $stmt->execute([$userId]);
            $orderCount = $stmt->fetch()['count'];
        }
        
        // Get eligible surprise gifts
        $eligibleGifts = $this->getEligibleGifts($userId, $orderAmount, $orderCount);
        
        if (empty($eligibleGifts)) {
            return null;
        }
        
        // Select a gift based on probability
        $selectedGift = $this->selectGiftByProbability($eligibleGifts);
        
        if ($selectedGift) {
            // Check if user has already received this gift
            if ($this->hasUserReceivedGift($userId, $selectedGift['id'])) {
                return null;
            }
            
            // Check if gift has reached max uses
            if ($this->hasGiftReachedMaxUses($selectedGift)) {
                return null;
            }
            
            return $selectedGift;
        }
        
        return null;
    }
    
    /**
     * Get eligible surprise gifts based on order criteria
     */
    private function getEligibleGifts($userId, $orderAmount, $orderCount) {
        $stmt = $this->pdo->prepare("
            SELECT sg.*, p.name as product_name, p.price, p.image, p.unit
            FROM surprise_gifts sg
            JOIN products p ON sg.product_id = p.id
            WHERE sg.is_active = 1
            AND (sg.start_date IS NULL OR sg.start_date <= CURDATE())
            AND (sg.end_date IS NULL OR sg.end_date >= CURDATE())
            AND (
                (sg.trigger_type = 'order_amount' AND ? >= sg.trigger_value) OR
                (sg.trigger_type = 'order_count' AND ? >= sg.trigger_value) OR
                (sg.trigger_type = 'random') OR
                (sg.trigger_type = 'special_occasion')
            )
            AND (sg.max_total_uses IS NULL OR sg.current_uses < sg.max_total_uses)
            ORDER BY sg.probability_percentage DESC
        ");
        $stmt->execute([$orderAmount, $orderCount]);
        return $stmt->fetchAll();
    }
    
    /**
     * Select a gift based on probability
     */
    private function selectGiftByProbability($gifts) {
        $totalProbability = array_sum(array_column($gifts, 'probability_percentage'));
        
        if ($totalProbability === 0) {
            return null;
        }
        
        $randomNumber = mt_rand(1, 100);
        $cumulativeProbability = 0;
        
        foreach ($gifts as $gift) {
            $cumulativeProbability += $gift['probability_percentage'];
            if ($randomNumber <= $cumulativeProbability) {
                return $gift;
            }
        }
        
        return null;
    }
    
    /**
     * Check if user has already received this gift
     */
    public function hasUserReceivedGift($userId, $giftId) {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) as count 
            FROM user_surprise_gifts usg
            JOIN surprise_gifts sg ON usg.surprise_gift_id = sg.id
            WHERE usg.user_id = ? AND sg.id = ?
        ");
        $stmt->execute([$userId, $giftId]);
        $count = $stmt->fetch()['count'];
        
        // Check max uses per user
        $stmt = $this->pdo->prepare("SELECT max_uses_per_user FROM surprise_gifts WHERE id = ?");
        $stmt->execute([$giftId]);
        $maxUsesPerUser = $stmt->fetch()['max_uses_per_user'];
        
        return $count >= $maxUsesPerUser;
    }
    
    /**
     * Check if gift has reached maximum total uses
     */
    public function hasGiftReachedMaxUses($gift) {
        if ($gift['max_total_uses'] === null) {
            return false;
        }
        
        return $gift['current_uses'] >= $gift['max_total_uses'];
    }
    
    /**
     * Check if user is eligible for a specific gift
     */
    public function isUserEligibleForGift($userId, $giftId) {
        // Get gift details
        $stmt = $this->pdo->prepare("
            SELECT * FROM surprise_gifts 
            WHERE id = ? AND is_active = 1
            AND (start_date IS NULL OR start_date <= CURDATE())
            AND (end_date IS NULL OR end_date >= CURDATE())
        ");
        $stmt->execute([$giftId]);
        $gift = $stmt->fetch();
        
        if (!$gift) {
            return false;
        }
        
        // Check if user has already received this gift
        if ($this->hasUserReceivedGift($userId, $giftId)) {
            return false;
        }
        
        // Check if gift has reached max uses
        if ($this->hasGiftReachedMaxUses($gift)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Add surprise gift to order (without starting new transaction)
     */
    public function addSurpriseGiftToOrder($userId, $orderId, $gift) {
        try {
            // Add gift to order items
            $stmt = $this->pdo->prepare("
                INSERT INTO order_items (order_id, product_id, quantity, unit_price, total_price)
                VALUES (?, ?, ?, 0.00, 0.00)
            ");
            $stmt->execute([$orderId, $gift['product_id'], $gift['quantity']]);
            
            // Record user received gift
            $stmt = $this->pdo->prepare("
                INSERT INTO user_surprise_gifts (user_id, order_id, surprise_gift_id, quantity)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$userId, $orderId, $gift['id'], $gift['quantity']]);
            
            // Update gift usage count
            $stmt = $this->pdo->prepare("
                UPDATE surprise_gifts 
                SET current_uses = current_uses + 1 
                WHERE id = ?
            ");
            $stmt->execute([$gift['id']]);
            
            // Update order to mark it has surprise gift
            $stmt = $this->pdo->prepare("
                UPDATE orders 
                SET has_surprise_gift = 1, 
                    surprise_gift_message = ?
                WHERE id = ?
            ");
            $giftMessage = "🎁 You unlocked a surprise gift! " . $gift['name'] . " added to your order";
            $stmt->execute([$giftMessage, $orderId]);
            
            return true;
            
        } catch (Exception $e) {
            error_log("Error adding surprise gift: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get surprise gift message for display
     */
    public function getSurpriseGiftMessage($orderId) {
        $stmt = $this->pdo->prepare("SELECT surprise_gift_message FROM orders WHERE id = ?");
        $stmt->execute([$orderId]);
        $result = $stmt->fetch();
        
        return $result ? $result['surprise_gift_message'] : null;
    }
    
    /**
     * Get surprise gifts for an order
     */
    public function getOrderSurpriseGifts($orderId) {
        $stmt = $this->pdo->prepare("
            SELECT usg.*, sg.name, sg.description, p.name as product_name, p.image, p.unit
            FROM user_surprise_gifts usg
            JOIN surprise_gifts sg ON usg.surprise_gift_id = sg.id
            JOIN products p ON sg.product_id = p.id
            WHERE usg.order_id = ?
        ");
        $stmt->execute([$orderId]);
        return $stmt->fetchAll();
    }
}
?>
