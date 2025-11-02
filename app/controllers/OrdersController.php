<?php
require_once 'BaseController.php';

class OrdersController extends BaseController {
    public function __construct() {
        parent::__construct();
        $this->requireLogin();
    }

    public function index() {
        $userId = $_SESSION['user_id'];
        $page = $_GET['page'] ?? 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;

        // Get orders with pagination
        $stmt = $this->pdo->prepare("
            SELECT o.*, ua.address_line1, ua.city, ua.state, ua.zip_code
            FROM orders o
            LEFT JOIN user_addresses ua ON o.delivery_address_id = ua.id
            WHERE o.user_id = ?
            ORDER BY o.created_at DESC
            LIMIT $limit OFFSET $offset
        ");
        $stmt->execute([$userId]);
        $orders = $stmt->fetchAll();

        // Get order items for each order for the smart shopping list
        foreach ($orders as &$order) {
            // Prepare formatted delivery slot for UI
            $order['delivery_slot_display'] = $this->formatDeliverySlot($order['delivery_slot'] ?? '', $order['created_at'] ?? null);
            $stmt = $this->pdo->prepare("
                SELECT oi.*, p.name, p.image, p.unit
                FROM order_items oi
                JOIN products p ON oi.product_id = p.id
                WHERE oi.order_id = ?
                ORDER BY oi.id
            ");
            $stmt->execute([$order['id']]);
            $order['items'] = $stmt->fetchAll();
        }

        // Get total count for pagination
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as total FROM orders WHERE user_id = ?");
        $stmt->execute([$userId]);
        $total = $stmt->fetch()['total'];
        $totalPages = ceil($total / $limit);

        // Get smart shopping list data (frequently ordered items)
        $smartList = $this->getSmartShoppingList($userId);

        $this->render('orders/index', [
            'orders' => $orders,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'smartList' => $smartList
        ]);
    }

    private function getSmartShoppingList($userId) {
        // Get frequently ordered items from recent orders
        $stmt = $this->pdo->prepare("
            SELECT 
                oi.product_id,
                p.name,
                p.image,
                p.price,
                p.unit,
                SUM(oi.quantity) as total_quantity,
                COUNT(DISTINCT oi.order_id) as order_count,
                AVG(oi.quantity) as avg_quantity
            FROM order_items oi
            JOIN products p ON oi.product_id = p.id
            JOIN orders o ON oi.order_id = o.id
            WHERE o.user_id = ? 
            AND o.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY oi.product_id, p.name, p.image, p.price, p.unit
            HAVING order_count >= 2
            ORDER BY total_quantity DESC, order_count DESC
            LIMIT 8
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function show($orderId) {
        $userId = $_SESSION['user_id'];

        // Get order details
        $stmt = $this->pdo->prepare("
            SELECT o.*, ua.address_line1, ua.address_line2, ua.city, ua.state, ua.zip_code, ua.country
            FROM orders o
            LEFT JOIN user_addresses ua ON o.delivery_address_id = ua.id
            WHERE o.id = ? AND o.user_id = ?
        ");
        $stmt->execute([$orderId, $userId]);
        $order = $stmt->fetch();

        if (!$order) {
            http_response_code(404);
            echo "Order not found";
            return;
        }

        // Get order items
        $stmt = $this->pdo->prepare("
            SELECT oi.*, p.name, p.image, p.unit
            FROM order_items oi
            JOIN products p ON oi.product_id = p.id
            WHERE oi.order_id = ?
        ");
        $stmt->execute([$orderId]);
        $orderItems = $stmt->fetchAll();

        // Get delivery updates
        $stmt = $this->pdo->prepare("
            SELECT * FROM delivery_updates
            WHERE order_id = ?
            ORDER BY updated_at ASC
        ");
        $stmt->execute([$orderId]);
        $deliveryUpdates = $stmt->fetchAll();

        // Get surprise gifts for this order
        $surpriseGifts = [];
        if ($order['has_surprise_gift']) {
            require_once 'app/helpers/SurpriseGiftHelper.php';
            $surpriseGiftHelper = new SurpriseGiftHelper($this->pdo);
            $surpriseGifts = $surpriseGiftHelper->getOrderSurpriseGifts($orderId);
        }

        // Inject formatted delivery slot for UI
        $order['delivery_slot_display'] = $this->formatDeliverySlot($order['delivery_slot'] ?? '', $order['created_at'] ?? null);

        $this->render('orders/show', [
            'order' => $order,
            'orderItems' => $orderItems,
            'deliveryUpdates' => $deliveryUpdates,
            'surpriseGifts' => $surpriseGifts
        ]);
    }

    public function cancel() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_SESSION['user_id'];
            $orderId = $_POST['order_id'] ?? 0;

            // Verify order belongs to user and can be cancelled
            $stmt = $this->pdo->prepare("SELECT status FROM orders WHERE id = ? AND user_id = ?");
            $stmt->execute([$orderId, $userId]);
            $order = $stmt->fetch();

            if (!$order) {
                echo json_encode(['success' => false, 'message' => 'Order not found']);
                return;
            }

            // Only allow cancellation for placed and confirmed orders
            if (!in_array($order['status'], ['placed', 'confirmed'])) {
                echo json_encode(['success' => false, 'message' => 'Order cannot be cancelled at this stage']);
                return;
            }

            // Update order status
            $stmt = $this->pdo->prepare("UPDATE orders SET status = 'cancelled', updated_at = NOW() WHERE id = ?");
            $stmt->execute([$orderId]);

            // Add delivery update
            $stmt = $this->pdo->prepare("INSERT INTO delivery_updates (order_id, status, message) VALUES (?, 'cancelled', 'Order cancelled by customer')");
            $stmt->execute([$orderId]);

            // TODO: Implement refund logic if payment was completed

            echo json_encode(['success' => true, 'message' => 'Order cancelled successfully']);
        }
    }

    public function reorder() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_SESSION['user_id'];
            $orderId = $_POST['order_id'] ?? 0;

            // Get order items
            $stmt = $this->pdo->prepare("
                SELECT oi.product_id, oi.quantity, p.stock_quantity
                FROM order_items oi
                JOIN products p ON oi.product_id = p.id
                WHERE oi.order_id = ? AND EXISTS (
                    SELECT 1 FROM orders o WHERE o.id = oi.order_id AND o.user_id = ?
                )
            ");
            $stmt->execute([$orderId, $userId]);
            $orderItems = $stmt->fetchAll();

            if (empty($orderItems)) {
                echo json_encode(['success' => false, 'message' => 'Order not found or no items available']);
                return;
            }

            // Check stock availability and add to cart
            $addedItems = 0;
            foreach ($orderItems as $item) {
                if ($item['stock_quantity'] >= $item['quantity']) {
                    // Check if item already in cart
                    $stmt = $this->pdo->prepare("SELECT id, quantity FROM cart_items WHERE user_id = ? AND product_id = ?");
                    $stmt->execute([$userId, $item['product_id']]);
                    $existingItem = $stmt->fetch();

                    if ($existingItem) {
                        $newQuantity = $existingItem['quantity'] + $item['quantity'];
                        $stmt = $this->pdo->prepare("UPDATE cart_items SET quantity = ? WHERE id = ?");
                        $stmt->execute([$newQuantity, $existingItem['id']]);
                    } else {
                        $stmt = $this->pdo->prepare("INSERT INTO cart_items (user_id, product_id, quantity) VALUES (?, ?, ?)");
                        $stmt->execute([$userId, $item['product_id'], $item['quantity']]);
                    }
                    $addedItems++;
                }
            }

            if ($addedItems > 0) {
                echo json_encode(['success' => true, 'message' => "Added $addedItems item(s) to cart"]);
            } else {
                echo json_encode(['success' => false, 'message' => 'No items could be added to cart (out of stock)']);
            }
        }
    }

    public function reorderAll() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_SESSION['user_id'];

            // Get all recent order items from the last 5 orders
            $stmt = $this->pdo->prepare("
                SELECT oi.product_id, oi.quantity, p.stock_quantity
                FROM order_items oi
                JOIN products p ON oi.product_id = p.id
                WHERE oi.order_id IN (
                    SELECT id FROM orders 
                    WHERE user_id = ? 
                    ORDER BY created_at DESC 
                    LIMIT 5
                )
                GROUP BY oi.product_id
                ORDER BY MAX(oi.order_id) DESC
            ");
            $stmt->execute([$userId]);
            $orderItems = $stmt->fetchAll();

            if (empty($orderItems)) {
                echo json_encode(['success' => false, 'message' => 'No previous orders found']);
                return;
            }

            // Check stock availability and add to cart
            $addedItems = 0;
            $skippedItems = 0;
            
            foreach ($orderItems as $item) {
                if ($item['stock_quantity'] >= $item['quantity']) {
                    // Check if item already in cart
                    $stmt = $this->pdo->prepare("SELECT id, quantity FROM cart_items WHERE user_id = ? AND product_id = ?");
                    $stmt->execute([$userId, $item['product_id']]);
                    $existingItem = $stmt->fetch();

                    if ($existingItem) {
                        $newQuantity = min($existingItem['quantity'] + $item['quantity'], $item['stock_quantity']);
                        $stmt = $this->pdo->prepare("UPDATE cart_items SET quantity = ? WHERE id = ?");
                        $stmt->execute([$newQuantity, $existingItem['id']]);
                    } else {
                        $stmt = $this->pdo->prepare("INSERT INTO cart_items (user_id, product_id, quantity) VALUES (?, ?, ?)");
                        $stmt->execute([$userId, $item['product_id'], $item['quantity']]);
                    }
                    $addedItems++;
                } else {
                    $skippedItems++;
                }
            }

            $message = "Successfully added $addedItems item(s) to cart";
            if ($skippedItems > 0) {
                $message .= " ($skippedItems item(s) out of stock)";
            }

            echo json_encode([
                'success' => true, 
                'message' => $message,
                'itemCount' => $addedItems
            ]);
        }
    }

    public function track($orderId) {
        $userId = $_SESSION['user_id'];

        // Get order complete info including all necessary fields
        $stmt = $this->pdo->prepare("
            SELECT o.*, ua.address_line1, ua.address_line2, ua.city, ua.state, ua.zip_code, ua.country
            FROM orders o
            LEFT JOIN user_addresses ua ON o.delivery_address_id = ua.id
            WHERE o.id = ? AND o.user_id = ?
        ");
        $stmt->execute([$orderId, $userId]);
        $order = $stmt->fetch();

        if (!$order) {
            http_response_code(404);
            echo "Order not found";
            return;
        }

        // Get delivery updates
        $stmt = $this->pdo->prepare("
            SELECT * FROM delivery_updates
            WHERE order_id = ?
            ORDER BY updated_at ASC
        ");
        $stmt->execute([$orderId]);
        $deliveryUpdates = $stmt->fetchAll();

        // Get surprise gifts for this order
        $surpriseGifts = [];
        if ($order['has_surprise_gift']) {
            require_once 'app/helpers/SurpriseGiftHelper.php';
            $surpriseGiftHelper = new SurpriseGiftHelper($this->pdo);
            $surpriseGifts = $surpriseGiftHelper->getOrderSurpriseGifts($orderId);
        }

        // Inject formatted delivery slot for UI
        $order['delivery_slot_display'] = $this->formatDeliverySlot($order['delivery_slot'] ?? '', $order['created_at'] ?? null);

        $this->render('orders/track', [
            'order' => $order,
            'deliveryUpdates' => $deliveryUpdates,
            'surpriseGifts' => $surpriseGifts
        ]);
    }

    private function formatDeliverySlot($slot, $createdAt) {
        $slot = trim((string)$slot);
        if ($slot === '') { return 'Not scheduled'; }

        // Expected format: YYYY-MM-DD HH:MM-HH:MM
        if (preg_match('/^(\d{4}-\d{2}-\d{2})\s+(\d{2}:\d{2})-(\d{2}:\d{2})$/', $slot, $m)) {
            $date = $m[1];
            $start = $m[2];
            $end = $m[3];
            // If the date looks stale (e.g., older than order created date minus 2 days), treat as time-window only
            $createdTs = $createdAt ? strtotime($createdAt) : time();
            $slotDateTs = strtotime($date . ' 00:00:00');
            if ($slotDateTs < strtotime('-2 days', $createdTs)) {
                return sprintf('Time window: %s - %s', date('g:i A', strtotime($start)), date('g:i A', strtotime($end)));
            }
            return sprintf('%s, %s - %s', date('F j, Y', $slotDateTs), date('g:i A', strtotime($start)), date('g:i A', strtotime($end)));
        }

        // If it doesn't match expected format, just return sanitized
        return htmlspecialchars($slot);
    }
}
?>