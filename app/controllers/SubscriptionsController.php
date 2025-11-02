<?php
require_once 'BaseController.php';

class SubscriptionsController extends BaseController {
    public function __construct() {
        parent::__construct();
        $this->requireLogin();
    }

    public function index() {
        $userId = $_SESSION['user_id'];

        // Get user subscriptions
        $stmt = $this->pdo->prepare("
            SELECT s.*, ua.address_line1, ua.city, ua.state 
            FROM subscriptions s
            LEFT JOIN user_addresses ua ON s.delivery_address_id = ua.id
            WHERE s.user_id = ?
            ORDER BY s.created_at DESC
        ");
        $stmt->execute([$userId]);
        $subscriptions = $stmt->fetchAll();

        // Parse product IDs from JSON for each subscription
        foreach ($subscriptions as &$subscription) {
            $subscription['product_ids_array'] = json_decode($subscription['product_ids'], true) ?? [];
            
            // Get product details
            if (!empty($subscription['product_ids_array'])) {
                $placeholders = implode(',', array_fill(0, count($subscription['product_ids_array']), '?'));
                $stmt = $this->pdo->prepare("SELECT id, name, price, image, unit FROM products WHERE id IN ($placeholders)");
                $stmt->execute($subscription['product_ids_array']);
                $subscription['products'] = $stmt->fetchAll();
            } else {
                $subscription['products'] = [];
            }
        }

        $this->render('subscriptions/index', [
            'subscriptions' => $subscriptions
        ]);
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            // Get user addresses
            $userId = $_SESSION['user_id'];
            
            // Get cart items to convert to subscription
            $stmt = $this->pdo->prepare("
                SELECT ci.*, p.name, p.price, p.image, p.unit
                FROM cart_items ci
                JOIN products p ON ci.product_id = p.id
                WHERE ci.user_id = ?
            ");
            $stmt->execute([$userId]);
            $cartItems = $stmt->fetchAll();

            // Get user addresses
            $stmt = $this->pdo->prepare("SELECT * FROM user_addresses WHERE user_id = ?");
            $stmt->execute([$userId]);
            $addresses = $stmt->fetchAll();

            $this->render('subscriptions/create', [
                'cartItems' => $cartItems,
                'addresses' => $addresses
            ]);
        }
    }

    public function store() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_SESSION['user_id'];
            
            $frequency = $_POST['frequency'] ?? 'monthly';
            $paymentMethod = $_POST['payment_method'] ?? 'cash_on_delivery';
            $deliveryAddressId = $_POST['delivery_address_id'] ?? null;
            $deliverySlot = $_POST['delivery_slot'] ?? '';
            $productIds = json_decode($_POST['product_ids'] ?? '[]', true);
            
            if (empty($productIds)) {
                echo json_encode(['success' => false, 'message' => 'No products selected']);
                return;
            }

            // Calculate next delivery date
            $nextDeliveryDate = $this->calculateNextDeliveryDate($frequency);
            
            try {
                $stmt = $this->pdo->prepare("
                    INSERT INTO subscriptions 
                    (user_id, frequency, payment_method, delivery_address_id, delivery_slot_preference, 
                     product_ids, next_delivery_date, status, start_date)
                    VALUES (?, ?, ?, ?, ?, ?, ?, 'active', CURDATE())
                ");
                
                $stmt->execute([
                    $userId,
                    $frequency,
                    $paymentMethod,
                    $deliveryAddressId,
                    $deliverySlot,
                    json_encode($productIds),
                    $nextDeliveryDate
                ]);

                echo json_encode([
                    'success' => true, 
                    'message' => 'Subscription created successfully',
                    'redirect' => '/subscriptions'
                ]);
            } catch (PDOException $e) {
                error_log("Subscription creation error: " . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'Failed to create subscription']);
            }
        }
    }

    public function pause($id) {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_SESSION['user_id'];
            
            $stmt = $this->pdo->prepare("UPDATE subscriptions SET status = 'paused' WHERE id = ? AND user_id = ?");
            $stmt->execute([$id, $userId]);
            
            echo json_encode(['success' => true, 'message' => 'Subscription paused']);
        }
    }

    public function resume($id) {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_SESSION['user_id'];
            
            // Recalculate next delivery date based on frequency
            $stmt = $this->pdo->prepare("SELECT frequency FROM subscriptions WHERE id = ? AND user_id = ?");
            $stmt->execute([$id, $userId]);
            $subscription = $stmt->fetch();
            
            if ($subscription) {
                $nextDeliveryDate = $this->calculateNextDeliveryDate($subscription['frequency']);
                $stmt = $this->pdo->prepare("UPDATE subscriptions SET status = 'active', next_delivery_date = ? WHERE id = ? AND user_id = ?");
                $stmt->execute([$nextDeliveryDate, $id, $userId]);
            }
            
            echo json_encode(['success' => true, 'message' => 'Subscription resumed']);
        }
    }

    public function cancel($id) {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_SESSION['user_id'];
            
            $stmt = $this->pdo->prepare("UPDATE subscriptions SET status = 'cancelled' WHERE id = ? AND user_id = ?");
            $stmt->execute([$id, $userId]);
            
            echo json_encode(['success' => true, 'message' => 'Subscription cancelled']);
        }
    }

    private function calculateNextDeliveryDate($frequency) {
        $date = new DateTime();
        
        switch ($frequency) {
            case 'weekly':
                $date->modify('+7 days');
                break;
            case 'bi_weekly':
                $date->modify('+14 days');
                break;
            case 'monthly':
                $date->modify('+30 days');
                break;
            default:
                $date->modify('+30 days');
        }
        
        return $date->format('Y-m-d');
    }
}
?>
