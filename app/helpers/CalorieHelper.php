<?php

class CalorieHelper {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Get user's daily calorie target
     */
    public function getUserDailyCalorieTarget($userId) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT calorie_target 
                FROM user_diet_profiles 
                WHERE user_id = ? AND active = TRUE 
                ORDER BY created_at DESC 
                LIMIT 1
            ");
            $stmt->execute([$userId]);
            $result = $stmt->fetch();
            
            return $result ? $result['calorie_target'] : 2000; // Default to 2000 if no profile
        } catch (Exception $e) {
            error_log("Error getting user calorie target: " . $e->getMessage());
            return 2000; // Default fallback
        }
    }
    
    /**
     * Calculate weekly calorie target
     */
    public function getWeeklyCalorieTarget($userId) {
        $dailyTarget = $this->getUserDailyCalorieTarget($userId);
        return $dailyTarget * 7;
    }
    
    /**
     * Get cart total calories
     */
    public function getCartTotalCalories($userId) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    SUM(ci.quantity * COALESCE(p.calories_per_unit, 0)) as total_calories,
                    COUNT(ci.id) as item_count
                FROM cart_items ci
                LEFT JOIN products p ON ci.product_id = p.id
                WHERE ci.user_id = ?
            ");
            $stmt->execute([$userId]);
            $result = $stmt->fetch();
            
            return [
                'total_calories' => intval($result['total_calories'] ?? 0),
                'item_count' => intval($result['item_count'] ?? 0)
            ];
        } catch (Exception $e) {
            error_log("Error getting cart calories: " . $e->getMessage());
            return ['total_calories' => 0, 'item_count' => 0];
        }
    }
    
    /**
     * Get cart items with calorie information
     */
    public function getCartItemsWithCalories($userId) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    ci.*,
                    p.name,
                    p.price,
                    p.unit,
                    p.image,
                    p.calories_per_unit,
                    p.protein_per_unit,
                    p.carbs_per_unit,
                    p.fat_per_unit,
                    p.fiber_per_unit,
                    p.sodium_per_unit,
                    (ci.quantity * COALESCE(p.calories_per_unit, 0)) as item_calories
                FROM cart_items ci
                LEFT JOIN products p ON ci.product_id = p.id
                WHERE ci.user_id = ?
                ORDER BY ci.id DESC
            ");
            $stmt->execute([$userId]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting cart items with calories: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get combined calorie target for user and all family members
     */
    public function getCombinedCalorieTarget($userId) {
        // Get user's calorie target
        $userTarget = $this->getUserDailyCalorieTarget($userId);
        
        // Get family members' combined calorie target
        require_once __DIR__ . '/FamilyMemberHelper.php';
        $familyMemberHelper = new FamilyMemberHelper($this->pdo);
        $familyData = $familyMemberHelper->getCombinedCalorieTarget($userId);
        
        $familyTarget = intval($familyData['total_calories'] ?? 0);
        $familyMemberCount = intval($familyData['total_members'] ?? 0);
        
        // Combined target = user + family members
        $combinedTarget = $userTarget + $familyTarget;
        
        return [
            'user_target' => $userTarget,
            'family_target' => $familyTarget,
            'family_member_count' => $familyMemberCount,
            'combined_target' => $combinedTarget,
            'total_persons' => 1 + $familyMemberCount // User + family members
        ];
    }
    
    /**
     * Get calorie recommendation for cart (includes user + family members)
     */
    public function getCalorieRecommendation($userId) {
        // Get combined calorie target (user + family members)
        $combinedData = $this->getCombinedCalorieTarget($userId);
        $dailyTarget = $combinedData['combined_target'];
        $weeklyTarget = $dailyTarget * 7;
        
        $cartData = $this->getCartTotalCalories($userId);
        $cartCalories = $cartData['total_calories'];
        $itemCount = $cartData['item_count'];
        
        // Calculate percentage of daily target
        $dailyPercentage = $dailyTarget > 0 ? ($cartCalories / $dailyTarget) * 100 : 0;
        
        // Calculate percentage of weekly target
        $weeklyPercentage = $weeklyTarget > 0 ? ($cartCalories / $weeklyTarget) * 100 : 0;
        
        // Determine recommendation
        $recommendation = 'recommended';
        $message = 'Recommended for your diet';
        $color = 'green';
        $icon = 'check-circle';
        
        if ($cartCalories > $dailyTarget) {
            $recommendation = 'avoid';
            $message = 'Avoid this - exceeds daily calorie target';
            $color = 'red';
            $icon = 'exclamation-triangle';
        } elseif ($cartCalories > ($dailyTarget * 0.8)) {
            $recommendation = 'caution';
            $message = 'Use caution - approaching daily limit';
            $color = 'yellow';
            $icon = 'exclamation-circle';
        } elseif ($cartCalories > ($weeklyTarget * 0.1)) {
            $recommendation = 'moderate';
            $message = 'Moderate consumption recommended';
            $color = 'blue';
            $icon = 'info-circle';
        }
        
        return [
            'recommendation' => $recommendation,
            'message' => $message,
            'color' => $color,
            'icon' => $icon,
            'cart_calories' => $cartCalories,
            'daily_target' => $dailyTarget,
            'weekly_target' => $weeklyTarget,
            'daily_percentage' => round($dailyPercentage, 1),
            'weekly_percentage' => round($weeklyPercentage, 1),
            'item_count' => $itemCount,
            'user_target' => $combinedData['user_target'],
            'family_target' => $combinedData['family_target'],
            'family_member_count' => $combinedData['family_member_count'],
            'total_persons' => $combinedData['total_persons']
        ];
    }
    
    /**
     * Get individual product calorie recommendation
     */
    public function getProductCalorieRecommendation($productId, $quantity = 1, $userId = null) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT calories_per_unit, name, unit
                FROM products 
                WHERE id = ?
            ");
            $stmt->execute([$productId]);
            $product = $stmt->fetch();
            
            if (!$product || !$product['calories_per_unit']) {
                return [
                    'recommendation' => 'neutral',
                    'message' => 'No calorie information available',
                    'color' => 'gray',
                    'icon' => 'question-circle',
                    'product_calories' => 0
                ];
            }
            
            $productCalories = $product['calories_per_unit'] * $quantity;
            
            // Get user's daily target for comparison
            $dailyTarget = $userId ? $this->getUserDailyCalorieTarget($userId) : 2000;
            $percentage = $dailyTarget > 0 ? ($productCalories / $dailyTarget) * 100 : 0;
            
            $recommendation = 'recommended';
            $message = 'Good choice for your diet';
            $color = 'green';
            $icon = 'check-circle';
            
            if ($productCalories > ($dailyTarget * 0.3)) {
                $recommendation = 'avoid';
                $message = 'High calorie - consider smaller portion';
                $color = 'red';
                $icon = 'exclamation-triangle';
            } elseif ($productCalories > ($dailyTarget * 0.15)) {
                $recommendation = 'caution';
                $message = 'Moderate calorie content';
                $color = 'yellow';
                $icon = 'exclamation-circle';
            }
            
            return [
                'recommendation' => $recommendation,
                'message' => $message,
                'color' => $color,
                'icon' => $icon,
                'product_calories' => $productCalories,
                'daily_percentage' => round($percentage, 1),
                'product_name' => $product['name'],
                'unit' => $product['unit']
            ];
            
        } catch (Exception $e) {
            error_log("Error getting product calorie recommendation: " . $e->getMessage());
            return [
                'recommendation' => 'neutral',
                'message' => 'Unable to calculate recommendation',
                'color' => 'gray',
                'icon' => 'question-circle',
                'product_calories' => 0
            ];
        }
    }
}
?>
