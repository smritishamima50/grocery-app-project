<?php
require_once 'BaseController.php';
require_once 'app/middleware/AdminMiddleware.php';

class AdminController extends BaseController {
    private $adminMiddleware;
    
    public function __construct() {
        parent::__construct();
        $this->adminMiddleware = new AdminMiddleware();
        $this->adminMiddleware->requireAdmin();
    }

    public function dashboard() {
        // Get dashboard statistics
        $stats = [];

        // Revenue Today
        $stmt = $this->pdo->prepare("SELECT SUM(total_amount) as revenue_today FROM orders WHERE DATE(created_at) = CURDATE() AND status != 'cancelled'");
        $stmt->execute();
        $stats['revenue_today'] = $stmt->fetch()['revenue_today'] ?? 0;

        // Orders Today
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as orders_today FROM orders WHERE DATE(created_at) = CURDATE()");
        $stmt->execute();
        $stats['orders_today'] = $stmt->fetch()['orders_today'];

        // Low Stock Items (less than 10 units)
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as low_stock_items FROM products WHERE stock_quantity < 10");
        $stmt->execute();
        $stats['low_stock_items'] = $stmt->fetch()['low_stock_items'];

        // Active Users Today (users who placed orders today)
        $stmt = $this->pdo->prepare("SELECT COUNT(DISTINCT user_id) as active_users_today FROM orders WHERE DATE(created_at) = CURDATE()");
        $stmt->execute();
        $stats['active_users_today'] = $stmt->fetch()['active_users_today'];

        // Top Category This Week
        $stmt = $this->pdo->prepare("
            SELECT c.name as top_category, COUNT(oi.id) as order_count
            FROM order_items oi
            JOIN products p ON oi.product_id = p.id
            JOIN categories c ON p.category_id = c.id
            JOIN orders o ON oi.order_id = o.id
            WHERE o.created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
            GROUP BY c.id, c.name
            ORDER BY order_count DESC
            LIMIT 1
        ");
        $stmt->execute();
        $topCategory = $stmt->fetch();
        $stats['top_category'] = $topCategory ? $topCategory['top_category'] : 'N/A';

        // Order Status Distribution
        $stmt = $this->pdo->prepare("SELECT status, COUNT(*) as count FROM orders WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) GROUP BY status");
        $stmt->execute();
        $orderStatuses = $stmt->fetchAll();
        foreach ($orderStatuses as $status) {
            $stats['orders_' . $status['status']] = $status['count'];
        }

        // Recent orders
        $stmt = $this->pdo->prepare("
            SELECT o.*, u.first_name, u.last_name
            FROM orders o
            JOIN users u ON o.user_id = u.id
            ORDER BY o.created_at DESC LIMIT 10
        ");
        $stmt->execute();
        $recentOrders = $stmt->fetchAll();

        // Get admin data for layout
        $adminData = $this->adminMiddleware->getAdminData();
        $adminFullName = $this->adminMiddleware->getAdminFullName();
        $adminInitials = $this->adminMiddleware->getAdminInitials();

        $this->render('admin/dashboard', [
            'stats' => $stats,
            'recentOrders' => $recentOrders,
            'adminData' => $adminData,
            'adminFullName' => $adminFullName,
            'adminInitials' => $adminInitials
        ]);
    }

    public function products() {
        $this->requireAdmin();
        
        // Get categories for filters
        $stmt = $this->pdo->prepare("SELECT id, name FROM categories ORDER BY name");
        $stmt->execute();
        $categories = $stmt->fetchAll();
        
        $this->render('admin/products', [
            'categories' => $categories
        ]);
    }

    public function createProduct() {
        $this->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = htmlspecialchars(trim($_POST['name'] ?? ''));
            $brand = htmlspecialchars(trim($_POST['brand'] ?? ''));
            $description = htmlspecialchars(trim($_POST['description'] ?? ''));
            $price = floatval($_POST['price'] ?? 0);
            $unitSize = htmlspecialchars(trim($_POST['unit_size'] ?? ''));
            $stockQuantity = intval($_POST['stock_quantity'] ?? 0);
            $lowStockThreshold = intval($_POST['low_stock_threshold'] ?? 10);
            $unit = htmlspecialchars(trim($_POST['unit'] ?? ''));
            $categoryId = intval($_POST['category_id'] ?? 0);
            $image = htmlspecialchars(trim($_POST['image'] ?? ''));
            $nutritionInfo = htmlspecialchars(trim($_POST['nutrition_info'] ?? ''));
            $dietTags = $_POST['diet_tags'] ?? [];
            $isEcoFriendly = isset($_POST['is_eco_friendly']);
            $isFrozen = isset($_POST['is_frozen']);
            $isActive = isset($_POST['is_active']);

            if (empty($name) || $price <= 0 || $categoryId <= 0) {
                $_SESSION['error'] = 'Please fill in all required fields';
            } else {
                try {
                    $this->pdo->beginTransaction();
                    
                    $sql = "INSERT INTO products (name, brand, description, price, unit_size, stock_quantity, low_stock_threshold, unit, category_id, image, nutrition_info, diet_tags, is_eco_friendly, is_frozen, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    
                    $stmt = $this->pdo->prepare($sql);
                    $stmt->execute([
                        $name, $brand, $description, $price, $unitSize, $stockQuantity, $lowStockThreshold, 
                        $unit, $categoryId, $image, $nutritionInfo, json_encode($dietTags), 
                        $isEcoFriendly, $isFrozen, $isActive
                    ]);
                    
                    $this->pdo->commit();
                    $_SESSION['success'] = 'Product created successfully';
                    $this->redirect('/admin/products');
                } catch (Exception $e) {
                    $this->pdo->rollback();
                    error_log("Create product error: " . $e->getMessage());
                    $_SESSION['error'] = 'Failed to create product';
                }
            }
        }
        
        // Get categories for dropdown
        $stmt = $this->pdo->prepare("SELECT id, name FROM categories ORDER BY name");
        $stmt->execute();
        $categories = $stmt->fetchAll();
        
        $this->render('admin/create-product', [
            'categories' => $categories
        ]);
    }

    /**
     * Bulk import products from JSON
     */
    public function bulkImportProducts() {
        $this->requireAdmin();
        
        // Get categories for reference
        $stmt = $this->pdo->prepare("SELECT id, name FROM categories ORDER BY name");
        $stmt->execute();
        $categories = $stmt->fetchAll();
        
        $this->render('admin/bulk-import-products', [
            'categories' => $categories
        ]);
    }

    public function orders() {
        // Get filter parameters from GET request
        $status = trim($_GET['status'] ?? 'all');
        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = 20;
        $offset = ($page - 1) * $limit;
        $search = trim($_GET['search'] ?? '');
        $dateFrom = trim($_GET['date_from'] ?? '');
        $dateTo = trim($_GET['date_to'] ?? '');
        $driver = trim($_GET['driver'] ?? '');
        
        error_log("Orders page loaded - Filters: status=$status, search=$search, date_from=$dateFrom, date_to=$dateTo, driver=$driver, page=$page");

        $where = "WHERE 1=1";
        $params = [];

        // Apply status filter
        if (!empty($status) && $status !== 'all') {
            $where .= " AND o.status = ?";
            $params[] = $status;
            error_log("Applied status filter: $status");
        }

        // Apply search filter
        if (!empty($search)) {
            $where .= " AND (CAST(o.id AS CHAR) LIKE ? OR COALESCE(u.phone, '') LIKE ? OR CONCAT(COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) LIKE ?)";
            $searchTerm = "%$search%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
            error_log("Applied search filter: $search");
        }

        // Apply date range filter
        if (!empty($dateFrom)) {
            $where .= " AND DATE(o.created_at) >= ?";
            $params[] = $dateFrom;
            error_log("Applied date_from filter: $dateFrom");
        }
        if (!empty($dateTo)) {
            $where .= " AND DATE(o.created_at) <= ?";
            $params[] = $dateTo;
            error_log("Applied date_to filter: $dateTo");
        }

        // Apply driver filter
        if (!empty($driver)) {
            $where .= " AND o.assigned_driver = ?";
            $params[] = $driver;
            error_log("Applied driver filter: $driver");
        }

        // Get orders with customer and address information
        // Use LEFT JOIN for users to ensure we get all orders even if user data is missing
        $sql = "
            SELECT o.*, 
                   COALESCE(u.first_name, '') as first_name, 
                   COALESCE(u.last_name, '') as last_name, 
                   COALESCE(u.phone, '') as phone, 
                   COALESCE(u.email, '') as email,
                   ua.address_line1, ua.address_line2, ua.city, ua.state, ua.zip_code
            FROM orders o
            LEFT JOIN users u ON o.user_id = u.id
            LEFT JOIN user_addresses ua ON o.delivery_address_id = ua.id
            $where
            ORDER BY 
                CASE 
                    WHEN o.is_urgent = 1 THEN 1
                    WHEN o.status = 'pending' THEN 2
                    WHEN o.status = 'confirmed' THEN 3
                    WHEN o.status = 'packed' THEN 4
                    WHEN o.status = 'out_for_delivery' THEN 5
                    ELSE 6
                END,
                o.created_at DESC
            LIMIT $limit OFFSET $offset
        ";
        
        error_log("Orders query: " . $sql);
        error_log("Orders params: " . print_r($params, true));
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $orders = $stmt->fetchAll();
        
        error_log("Orders found: " . count($orders));

        // Get order items for each order
        foreach ($orders as &$order) {
            $stmt = $this->pdo->prepare("
                SELECT oi.*, 
                       COALESCE(oi.product_name_snapshot, p.name) as product_name,
                       COALESCE(oi.product_image_snapshot, p.image) as product_image
                FROM order_items oi
                LEFT JOIN products p ON oi.product_id = p.id
                WHERE oi.order_id = ?
                ORDER BY oi.id
            ");
            $stmt->execute([$order['id']]);
            $order['items'] = $stmt->fetchAll();
        }

        // Get total count for pagination - use LEFT JOIN to match the main query
        $countSql = "SELECT COUNT(*) as total FROM orders o LEFT JOIN users u ON o.user_id = u.id $where";
        error_log("Count query: " . $countSql);
        $stmt = $this->pdo->prepare($countSql);
        $stmt->execute($params);
        $total = $stmt->fetch()['total'];
        $totalPages = ceil($total / $limit);
        
        error_log("Total orders: $total, Total pages: $totalPages");

        // Get drivers for filter dropdown
        $stmt = $this->pdo->prepare("SELECT name FROM drivers WHERE is_active = 1 ORDER BY name");
        $stmt->execute();
        $drivers = $stmt->fetchAll();

        // Get order statistics
        $stats = $this->getOrderStats();

        $this->render('admin/orders', [
            'orders' => $orders,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'total' => $total, // Total number of orders for pagination display
            'status' => $status,
            'search' => $search,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'driver' => $driver,
            'drivers' => $drivers,
            'stats' => $stats
        ]);
    }

    public function updateOrderStatus() {
        if ($_SERVER['REQUEST_METHOD'] === 'PATCH') {
            // Get JSON input
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid JSON input']);
                return;
            }

            $orderId = $input['order_id'] ?? 0;
            $newStatus = $input['status'] ?? '';
            $assignedDriver = $input['assigned_driver'] ?? null;
            $adminNotes = $input['admin_notes'] ?? null;

            if (!$orderId || !$newStatus) {
                http_response_code(400);
                echo json_encode(['error' => 'Order ID and status are required']);
                return;
            }

            try {
                $this->pdo->beginTransaction();

                // Get current order status
                $stmt = $this->pdo->prepare("SELECT status FROM orders WHERE id = ?");
                $stmt->execute([$orderId]);
                $currentOrder = $stmt->fetch();
                
                if (!$currentOrder) {
                    throw new Exception('Order not found');
                }

                $oldStatus = $currentOrder['status'];

                // Update order
                $updateFields = ['status = ?'];
                $params = [$newStatus];

                if ($assignedDriver !== null) {
                    $updateFields[] = 'assigned_driver = ?';
                    $params[] = $assignedDriver ?: null;
                }

                if ($adminNotes !== null) {
                    $updateFields[] = 'admin_notes = ?';
                    $params[] = $adminNotes ?: null;
                }

                $params[] = $orderId;
                $sql = "UPDATE orders SET " . implode(', ', $updateFields) . " WHERE id = ?";
                
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute($params);

                // Record status change in history
                $stmt = $this->pdo->prepare("
                    INSERT INTO order_status_history (order_id, old_status, new_status, changed_by_admin_id, admin_name, notes)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $orderId,
                    $oldStatus,
                    $newStatus,
                    $_SESSION['user_id'],
                    $_SESSION['first_name'] . ' ' . $_SESSION['last_name'],
                    $adminNotes
                ]);

            // Add delivery update
                $stmt = $this->pdo->prepare("
                    INSERT INTO delivery_updates (order_id, status, message) 
                    VALUES (?, ?, ?)
                ");
                $stmt->execute([
                    $orderId, 
                    $newStatus, 
                    "Order status updated from $oldStatus to $newStatus" . 
                    ($assignedDriver ? " and assigned to $assignedDriver" : "")
                ]);

                $this->pdo->commit();

                echo json_encode([
                    'success' => true, 
                    'message' => 'Order updated successfully',
                    'new_status' => $newStatus,
                    'assigned_driver' => $assignedDriver
                ]);

            } catch (Exception $e) {
                $this->pdo->rollback();
                error_log("Order update error: " . $e->getMessage());
                http_response_code(500);
                echo json_encode(['error' => 'Failed to update order']);
            }
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
        }
    }

    public function getOrderDetails($orderId) {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $stmt = $this->pdo->prepare("
                SELECT o.*, 
                       u.first_name, u.last_name, u.phone, u.email,
                       ua.address_line1, ua.address_line2, ua.city, ua.state, ua.zip_code
                FROM orders o
                JOIN users u ON o.user_id = u.id
                LEFT JOIN user_addresses ua ON o.delivery_address_id = ua.id
                WHERE o.id = ?
            ");
            $stmt->execute([$orderId]);
            $order = $stmt->fetch();

            if (!$order) {
                http_response_code(404);
                echo json_encode(['error' => 'Order not found']);
                return;
            }

            // Get order items
            $stmt = $this->pdo->prepare("
                SELECT oi.*, 
                       COALESCE(oi.product_name_snapshot, p.name) as product_name,
                       COALESCE(oi.product_image_snapshot, p.image) as product_image
                FROM order_items oi
                LEFT JOIN products p ON oi.product_id = p.id
                WHERE oi.order_id = ?
                ORDER BY oi.id
            ");
            $stmt->execute([$orderId]);
            $order['items'] = $stmt->fetchAll();

            // Get status history
            $stmt = $this->pdo->prepare("
                SELECT * FROM order_status_history 
                WHERE order_id = ? 
                ORDER BY created_at DESC
            ");
            $stmt->execute([$orderId]);
            $order['status_history'] = $stmt->fetchAll();

            echo json_encode(['success' => true, 'order' => $order]);
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
        }
    }

    public function markAsDelivered($orderId) {
        if ($_SERVER['REQUEST_METHOD'] === 'PATCH') {
            try {
                $this->pdo->beginTransaction();

                // Get current order status
                $stmt = $this->pdo->prepare("SELECT status FROM orders WHERE id = ?");
                $stmt->execute([$orderId]);
                $currentOrder = $stmt->fetch();
                
                if (!$currentOrder) {
                    throw new Exception('Order not found');
                }

                $oldStatus = $currentOrder['status'];

                // Update order to delivered
                $stmt = $this->pdo->prepare("UPDATE orders SET status = 'delivered' WHERE id = ?");
                $stmt->execute([$orderId]);

                // Record status change
                $stmt = $this->pdo->prepare("
                    INSERT INTO order_status_history (order_id, old_status, new_status, changed_by_admin_id, admin_name, notes)
                    VALUES (?, ?, 'delivered', ?, ?, 'Order marked as delivered')
                ");
                $stmt->execute([
                    $orderId,
                    $oldStatus,
                    $_SESSION['user_id'],
                    $_SESSION['first_name'] . ' ' . $_SESSION['last_name']
                ]);

                // Add delivery update
                $stmt = $this->pdo->prepare("
                    INSERT INTO delivery_updates (order_id, status, message) 
                    VALUES (?, 'delivered', 'Order has been delivered successfully')
                ");
                $stmt->execute([$orderId]);

                $this->pdo->commit();

                echo json_encode(['success' => true, 'message' => 'Order marked as delivered']);

            } catch (Exception $e) {
                $this->pdo->rollback();
                error_log("Mark as delivered error: " . $e->getMessage());
                http_response_code(500);
                echo json_encode(['error' => 'Failed to mark order as delivered']);
            }
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
        }
    }

    public function cancelOrder($orderId) {
        if ($_SERVER['REQUEST_METHOD'] === 'PATCH') {
            $input = json_decode(file_get_contents('php://input'), true);
            $reason = $input['reason'] ?? 'Order cancelled by admin';

            try {
                $this->pdo->beginTransaction();

                // Get current order status
                $stmt = $this->pdo->prepare("SELECT status FROM orders WHERE id = ?");
                $stmt->execute([$orderId]);
                $currentOrder = $stmt->fetch();
                
                if (!$currentOrder) {
                    throw new Exception('Order not found');
                }

                $oldStatus = $currentOrder['status'];

                // Update order to cancelled
                $stmt = $this->pdo->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ?");
                $stmt->execute([$orderId]);

                // Record status change
                $stmt = $this->pdo->prepare("
                    INSERT INTO order_status_history (order_id, old_status, new_status, changed_by_admin_id, admin_name, notes)
                    VALUES (?, ?, 'cancelled', ?, ?, ?)
                ");
                $stmt->execute([
                    $orderId,
                    $oldStatus,
                    $_SESSION['user_id'],
                    $_SESSION['first_name'] . ' ' . $_SESSION['last_name'],
                    $reason
                ]);

                // Add delivery update
                $stmt = $this->pdo->prepare("
                    INSERT INTO delivery_updates (order_id, status, message) 
                    VALUES (?, 'cancelled', ?)
                ");
                $stmt->execute([$orderId, $reason]);

                $this->pdo->commit();

                echo json_encode(['success' => true, 'message' => 'Order cancelled successfully']);

            } catch (Exception $e) {
                $this->pdo->rollback();
                error_log("Cancel order error: " . $e->getMessage());
                http_response_code(500);
                echo json_encode(['error' => 'Failed to cancel order']);
            }
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
        }
    }

    private function getOrderStats() {
        $stats = [];

        // Total orders
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as total FROM orders");
        $stmt->execute();
        $stats['total_orders'] = $stmt->fetch()['total'];

        // Orders by status
        $stmt = $this->pdo->prepare("
            SELECT status, COUNT(*) as count 
            FROM orders 
            GROUP BY status
        ");
        $stmt->execute();
        $statusCounts = $stmt->fetchAll();
        
        foreach ($statusCounts as $status) {
            $stats['orders_' . $status['status']] = $status['count'];
        }

        // Urgent orders
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as urgent FROM orders WHERE is_urgent = 1");
        $stmt->execute();
        $stats['urgent_orders'] = $stmt->fetch()['urgent'];

        // Eco-friendly orders
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as eco_friendly FROM orders WHERE eco_friendly_delivery = 1");
        $stmt->execute();
        $stats['eco_friendly_orders'] = $stmt->fetch()['eco_friendly'];

        // Today's revenue
        $stmt = $this->pdo->prepare("
            SELECT COALESCE(SUM(total_amount), 0) as revenue_today 
            FROM orders 
            WHERE DATE(created_at) = CURDATE() AND status = 'delivered'
        ");
        $stmt->execute();
        $stats['revenue_today'] = $stmt->fetch()['revenue_today'];

        return $stats;
    }

    // Subscription Management
    public function surpriseGifts() {
        // Get all surprise gifts
        $stmt = $this->pdo->query("
            SELECT sg.*, p.name as product_name, p.price
            FROM surprise_gifts sg
            JOIN products p ON sg.product_id = p.id
            ORDER BY sg.created_at DESC
        ");
        $surpriseGifts = $stmt->fetchAll();
        
        // Get all products for the dropdown
        $stmt = $this->pdo->query("SELECT id, name, price FROM products ORDER BY name");
        $products = $stmt->fetchAll();
        
        // Calculate success rate (simplified)
        $totalOrders = $this->pdo->query("SELECT COUNT(*) as count FROM orders")->fetch()['count'];
        $totalGiftUses = array_sum(array_column($surpriseGifts, 'current_uses'));
        $successRate = $totalOrders > 0 ? round(($totalGiftUses / $totalOrders) * 100, 1) : 0;
        
        $this->render('admin/surprise-gifts', [
            'surpriseGifts' => $surpriseGifts,
            'products' => $products,
            'successRate' => $successRate
        ]);
    }
    
    public function saveSurpriseGift() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $giftId = $_POST['gift_id'] ?? null;
            $name = $_POST['name'];
            $description = $_POST['description'];
            $productId = $_POST['product_id'];
            $quantity = $_POST['quantity'];
            $triggerType = $_POST['trigger_type'];
            $triggerValue = $_POST['trigger_value'];
            $probabilityPercentage = $_POST['probability_percentage'];
            $isActive = isset($_POST['is_active']) ? 1 : 0;
            
            if ($giftId) {
                // Update existing gift
                $stmt = $this->pdo->prepare("
                    UPDATE surprise_gifts 
                    SET name = ?, description = ?, product_id = ?, quantity = ?, 
                        trigger_type = ?, trigger_value = ?, probability_percentage = ?, is_active = ?
                    WHERE id = ?
                ");
                $stmt->execute([$name, $description, $productId, $quantity, $triggerType, $triggerValue, $probabilityPercentage, $isActive, $giftId]);
            } else {
                // Create new gift
                $stmt = $this->pdo->prepare("
                    INSERT INTO surprise_gifts (name, description, product_id, quantity, trigger_type, trigger_value, probability_percentage, is_active)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$name, $description, $productId, $quantity, $triggerType, $triggerValue, $probabilityPercentage, $isActive]);
            }
            
            $this->redirect('/admin/surprise-gifts');
        }
    }

    public function subscriptions() {
        $stmt = $this->pdo->prepare("
            SELECT s.*, u.first_name, u.last_name, u.email,
                   ua.address_line1, ua.city, ua.state
            FROM subscriptions s
            JOIN users u ON s.user_id = u.id
            LEFT JOIN user_addresses ua ON s.delivery_address_id = ua.id
            ORDER BY s.created_at DESC
        ");
        $stmt->execute();
        $subscriptions = $stmt->fetchAll();

        // Parse product IDs for each subscription
        foreach ($subscriptions as &$subscription) {
            $subscription['product_ids_array'] = json_decode($subscription['product_ids'], true) ?? [];
        }

        $this->render('admin/subscriptions', [
            'subscriptions' => $subscriptions
        ]);
    }

    public function usersForSubscription() {
        // Get all users (customers)
        $stmt = $this->pdo->prepare("SELECT id, first_name, last_name, email FROM users WHERE role = 'customer' ORDER BY first_name, last_name");
        $stmt->execute();
        $users = $stmt->fetchAll();

        // Get all products
        $stmt = $this->pdo->prepare("SELECT id, name, price, image, unit FROM products ORDER BY name");
        $stmt->execute();
        $products = $stmt->fetchAll();

        $this->render('admin/create-subscription', [
            'users' => $users,
            'products' => $products
        ]);
    }

    public function createSubscription() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_POST['user_id'] ?? 0;
            $frequency = $_POST['frequency'] ?? 'monthly';
            $amount = floatval($_POST['amount'] ?? 0); // 200, 500, or 1000 tk
            $deliveryAddressId = $_POST['delivery_address_id'] ?? null;
            $deliverySlot = $_POST['delivery_slot'] ?? '';

            // Calculate next delivery date based on frequency
            $nextDeliveryDate = $this->calculateNextDeliveryDate($frequency);
            
            // Store product IDs as empty JSON since this is an amount-based subscription
            $productIds = [];

            try {
                $stmt = $this->pdo->prepare("
                    INSERT INTO subscriptions 
                    (user_id, frequency, payment_method, delivery_address_id, delivery_slot_preference, 
                     product_ids, next_delivery_date, amount, status, start_date)
                    VALUES (?, ?, 'cash_on_delivery', ?, ?, ?, ?, ?, 'active', CURDATE())
                ");
                
                $stmt->execute([
                    $userId,
                    $frequency,
                    $deliveryAddressId,
                    $deliverySlot,
                    json_encode($productIds),
                    $nextDeliveryDate,
                    $amount
                ]);

                echo json_encode([
                    'success' => true, 
                    'message' => 'Subscription created successfully',
                    'redirect' => '/admin/subscriptions'
                ]);
            } catch (PDOException $e) {
                error_log("Subscription creation error: " . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'Failed to create subscription']);
            }
        }
    }

    public function categories() {
        $stmt = $this->pdo->prepare("SELECT * FROM categories ORDER BY name");
        $stmt->execute();
        $categories = $stmt->fetchAll();

        $this->render('admin/categories', [
            'categories' => $categories
        ]);
    }

    public function createCategory() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'] ?? '';
            $description = $_POST['description'] ?? '';
            $image = $_POST['image'] ?? '';

            if ($name) {
                $stmt = $this->pdo->prepare("INSERT INTO categories (name, description, image) VALUES (?, ?, ?)");
                $stmt->execute([$name, $description, $image]);
                $this->redirect('/admin/categories');
            }
        }

        $this->render('admin/create-category');
    }

    public function editCategory($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM categories WHERE id = ?");
        $stmt->execute([$id]);
        $category = $stmt->fetch();

        if (!$category) {
            $this->redirect('/admin/categories');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'] ?? '';
            $description = $_POST['description'] ?? '';
            $image = $_POST['image'] ?? '';

            if ($name) {
                $stmt = $this->pdo->prepare("UPDATE categories SET name = ?, description = ?, image = ? WHERE id = ?");
                $stmt->execute([$name, $description, $image, $id]);
                $this->redirect('/admin/categories');
            }
        }

        $this->render('admin/edit-category', [
            'category' => $category
        ]);
    }

    public function deleteCategory($id) {
        // Check if category has products
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM products WHERE category_id = ?");
        $stmt->execute([$id]);
        $productCount = $stmt->fetch()['count'];

        if ($productCount > 0) {
            $_SESSION['error'] = "Cannot delete category with existing products. Please move or delete products first.";
        } else {
            $stmt = $this->pdo->prepare("DELETE FROM categories WHERE id = ?");
            $stmt->execute([$id]);
            $_SESSION['success'] = "Category deleted successfully.";
        }

        $this->redirect('/admin/categories');
    }

    public function editProduct($id) {
        $this->requireAdmin();
        
        $stmt = $this->pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $product = $stmt->fetch();

        if (!$product) {
            $_SESSION['error'] = 'Product not found';
            $this->redirect('/admin/products');
        }

        // Parse diet_tags JSON if it exists
        if ($product['diet_tags']) {
            $product['diet_tags'] = json_decode($product['diet_tags'], true) ?: [];
        } else {
            $product['diet_tags'] = [];
        }

        $stmt = $this->pdo->prepare("SELECT id, name FROM categories ORDER BY name");
        $stmt->execute();
        $categories = $stmt->fetchAll();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = htmlspecialchars(trim($_POST['name'] ?? ''));
            $brand = htmlspecialchars(trim($_POST['brand'] ?? ''));
            $description = htmlspecialchars(trim($_POST['description'] ?? ''));
            $price = floatval($_POST['price'] ?? 0);
            $unitSize = htmlspecialchars(trim($_POST['unit_size'] ?? ''));
            $stockQuantity = intval($_POST['stock_quantity'] ?? 0);
            $lowStockThreshold = intval($_POST['low_stock_threshold'] ?? 10);
            $unit = htmlspecialchars(trim($_POST['unit'] ?? ''));
            $categoryId = intval($_POST['category_id'] ?? 0);
            $image = htmlspecialchars(trim($_POST['image'] ?? ''));
            $nutritionInfo = htmlspecialchars(trim($_POST['nutrition_info'] ?? ''));
            $dietTags = $_POST['diet_tags'] ?? [];
            $isEcoFriendly = isset($_POST['is_eco_friendly']);
            $isFrozen = isset($_POST['is_frozen']);
            $isActive = isset($_POST['is_active']);

            if (empty($name) || $price <= 0 || $categoryId <= 0) {
                $_SESSION['error'] = 'Please fill in all required fields';
            } else {
                try {
                    $this->pdo->beginTransaction();
                    
                    $sql = "UPDATE products SET name = ?, brand = ?, description = ?, price = ?, unit_size = ?, stock_quantity = ?, low_stock_threshold = ?, unit = ?, category_id = ?, image = ?, nutrition_info = ?, diet_tags = ?, is_eco_friendly = ?, is_frozen = ?, is_active = ? WHERE id = ?";
                    
                    $stmt = $this->pdo->prepare($sql);
                    $stmt->execute([
                        $name, $brand, $description, $price, $unitSize, $stockQuantity, $lowStockThreshold, 
                        $unit, $categoryId, $image, $nutritionInfo, json_encode($dietTags), 
                        $isEcoFriendly, $isFrozen, $isActive, $id
                    ]);
                    
                    $this->pdo->commit();
                    $_SESSION['success'] = 'Product updated successfully';
                    $this->redirect('/admin/products');
                } catch (Exception $e) {
                    $this->pdo->rollback();
                    error_log("Update product error: " . $e->getMessage());
                    $_SESSION['error'] = 'Failed to update product';
                }
            }
        }

        $this->render('admin/edit-product', [
            'product' => $product,
            'categories' => $categories
        ]);
    }

    public function deleteProduct($id) {
        $this->requireAdmin();
        
        try {
            // Soft delete by setting is_active to false
            $stmt = $this->pdo->prepare("UPDATE products SET is_active = 0 WHERE id = ?");
            $stmt->execute([$id]);
            
            if ($stmt->rowCount() > 0) {
                $_SESSION['success'] = "Product deleted successfully.";
            } else {
                $_SESSION['error'] = "Product not found.";
            }
        } catch (Exception $e) {
            error_log("Delete product error: " . $e->getMessage());
            $_SESSION['error'] = "Failed to delete product.";
        }
        
        $this->redirect('/admin/products');
    }

    // Users Management
    public function users() {
        // This method now just renders the view - the actual data loading is handled by JavaScript/AJAX
        $this->render('admin/users', []);
    }

    public function updateUserRole() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_POST['user_id'] ?? 0;
            $role = $_POST['role'] ?? '';

            if (in_array($role, ['customer', 'admin'])) {
                $stmt = $this->pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
                $stmt->execute([$role, $userId]);
                $_SESSION['success'] = "User role updated successfully.";
            } else {
                $_SESSION['error'] = "Invalid role selected.";
            }

            $this->redirect('/admin/users');
        }
    }

    public function deleteUser($id) {
        // Prevent admin from deleting themselves
        if ($id == $_SESSION['user_id']) {
            $_SESSION['error'] = "You cannot delete your own account.";
            $this->redirect('/admin/users');
            return;
        }

        $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $_SESSION['success'] = "User deleted successfully.";
        $this->redirect('/admin/users');
    }

    public function createUser() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $firstName = $_POST['first_name'] ?? '';
            $lastName = $_POST['last_name'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $role = $_POST['role'] ?? 'customer';

            if (empty($firstName) || empty($lastName) || empty($email) || empty($password)) {
                $_SESSION['error'] = "All fields are required.";
                $this->redirect('/admin/users/create');
                return;
            }

            // Check if email already exists
            $stmt = $this->pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $_SESSION['error'] = "Email already exists.";
                $this->redirect('/admin/users/create');
                return;
            }

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $this->pdo->prepare("INSERT INTO users (first_name, last_name, email, password, role, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$firstName, $lastName, $email, $hashedPassword, $role]);
            
            $_SESSION['success'] = "User created successfully.";
            $this->redirect('/admin/users');
        } else {
            $this->render('admin/create-user');
        }
    }

    public function showUser($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch();

        if (!$user) {
            $_SESSION['error'] = "User not found.";
            $this->redirect('/admin/users');
            return;
        }

        $this->render('admin/show-user', [
            'user' => $user
        ]);
    }

    public function editUser($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $firstName = $_POST['first_name'] ?? '';
            $lastName = $_POST['last_name'] ?? '';
            $email = $_POST['email'] ?? '';
            $role = $_POST['role'] ?? 'customer';

            if (empty($firstName) || empty($lastName) || empty($email)) {
                $_SESSION['error'] = "All fields are required.";
                $this->redirect('/admin/users/' . $id . '/edit');
                return;
            }

            // Check if email already exists (excluding current user)
            $stmt = $this->pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $id]);
            if ($stmt->fetch()) {
                $_SESSION['error'] = "Email already exists.";
                $this->redirect('/admin/users/' . $id . '/edit');
                return;
            }

            $stmt = $this->pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ?, role = ? WHERE id = ?");
            $stmt->execute([$firstName, $lastName, $email, $role, $id]);
            
            $_SESSION['success'] = "User updated successfully.";
            $this->redirect('/admin/users');
        } else {
            $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$id]);
            $user = $stmt->fetch();

            if (!$user) {
                $_SESSION['error'] = "User not found.";
                $this->redirect('/admin/users');
                return;
            }

            $this->render('admin/edit-user', [
                'user' => $user
            ]);
        }
    }

    // Coupons Management
    public function coupons() {
        $this->requireAdmin();
        
        $this->render('admin/coupons', []);
    }

    public function createCoupon() {
        $this->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $code = strtoupper(trim($_POST['code'] ?? ''));
            $discountType = $_POST['discount_type'] ?? '';
            $discountValue = floatval($_POST['discount_value'] ?? 0);
            $minOrderAmount = floatval($_POST['min_order_amount'] ?? 0);
            $startDate = $_POST['start_date'] ?? null;
            $expiryDate = $_POST['expiry_date'] ?? null;
            $usageLimit = !empty($_POST['usage_limit']) ? intval($_POST['usage_limit']) : null;
            $maxUsesPerUser = intval($_POST['max_uses_per_user'] ?? 1);
            $isActive = isset($_POST['is_active']);

            if ($code && $discountType && $discountValue > 0) {
                // Validate discount value
                if ($discountType === 'percentage' && $discountValue > 100) {
                    $_SESSION['error'] = "Percentage discount cannot exceed 100%.";
                } elseif ($startDate && $expiryDate && $startDate > $expiryDate) {
                    $_SESSION['error'] = "Start date cannot be after expiry date.";
                } else {
                    try {
                        $stmt = $this->pdo->prepare("
                            INSERT INTO coupons (code, discount_type, discount_value, min_order_amount, start_date, expiry_date, usage_limit, max_uses_per_user, is_active)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                        ");
                        $stmt->execute([
                            $code, $discountType, $discountValue, $minOrderAmount, 
                            $startDate ?: null, $expiryDate ?: null, $usageLimit, 
                            $maxUsesPerUser, $isActive
                        ]);
                        $_SESSION['success'] = "Coupon created successfully.";
                    } catch (PDOException $e) {
                        if ($e->getCode() == 23000) {
                            $_SESSION['error'] = "Coupon code already exists.";
                        } else {
                            error_log("Create coupon error: " . $e->getMessage());
                            $_SESSION['error'] = "Failed to create coupon.";
                        }
                    }
                }
            } else {
                $_SESSION['error'] = "Please fill in all required fields.";
            }

            $this->redirect('/admin/coupons');
        }

        $this->render('admin/create-coupon');
    }

    public function editCoupon($id) {
        $this->requireAdmin();
        
        $stmt = $this->pdo->prepare("SELECT * FROM coupons WHERE id = ?");
        $stmt->execute([$id]);
        $coupon = $stmt->fetch();

        if (!$coupon) {
            $_SESSION['error'] = 'Coupon not found';
            $this->redirect('/admin/coupons');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $code = strtoupper(trim($_POST['code'] ?? ''));
            $discountType = $_POST['discount_type'] ?? '';
            $discountValue = floatval($_POST['discount_value'] ?? 0);
            $minOrderAmount = floatval($_POST['min_order_amount'] ?? 0);
            $startDate = $_POST['start_date'] ?? null;
            $expiryDate = $_POST['expiry_date'] ?? null;
            $usageLimit = !empty($_POST['usage_limit']) ? intval($_POST['usage_limit']) : null;
            $maxUsesPerUser = intval($_POST['max_uses_per_user'] ?? 1);
            $isActive = isset($_POST['is_active']);

            if ($code && $discountType && $discountValue > 0) {
                // Validate discount value
                if ($discountType === 'percentage' && $discountValue > 100) {
                    $_SESSION['error'] = "Percentage discount cannot exceed 100%.";
                } elseif ($startDate && $expiryDate && $startDate > $expiryDate) {
                    $_SESSION['error'] = "Start date cannot be after expiry date.";
                } else {
                    try {
                        $stmt = $this->pdo->prepare("
                            UPDATE coupons 
                            SET code = ?, discount_type = ?, discount_value = ?, min_order_amount = ?, start_date = ?, expiry_date = ?, usage_limit = ?, max_uses_per_user = ?, is_active = ?
                            WHERE id = ?
                        ");
                        $stmt->execute([
                            $code, $discountType, $discountValue, $minOrderAmount, 
                            $startDate ?: null, $expiryDate ?: null, $usageLimit, 
                            $maxUsesPerUser, $isActive, $id
                        ]);
                        $_SESSION['success'] = "Coupon updated successfully.";
                    } catch (PDOException $e) {
                        if ($e->getCode() == 23000) {
                            $_SESSION['error'] = "Coupon code already exists.";
                        } else {
                            error_log("Update coupon error: " . $e->getMessage());
                            $_SESSION['error'] = "Failed to update coupon.";
                        }
                    }
                }
            } else {
                $_SESSION['error'] = "Please fill in all required fields.";
            }

            $this->redirect('/admin/coupons');
        }

        $this->render('admin/edit-coupon', [
            'coupon' => $coupon
        ]);
    }

    public function deleteCoupon($id) {
        $this->requireAdmin();
        
        try {
            // Soft delete by setting is_active to false
            $stmt = $this->pdo->prepare("UPDATE coupons SET is_active = 0 WHERE id = ?");
            $stmt->execute([$id]);
            
            if ($stmt->rowCount() > 0) {
                $_SESSION['success'] = "Coupon deleted successfully.";
            } else {
                $_SESSION['error'] = "Coupon not found.";
            }
        } catch (Exception $e) {
            error_log("Delete coupon error: " . $e->getMessage());
            $_SESSION['error'] = "Failed to delete coupon.";
        }
        
        $this->redirect('/admin/coupons');
    }

    // Analytics
    public function analytics() {
        $period = $_GET['period'] ?? '7'; // Default to 7 days
        $days = intval($period);
        
        // Get comprehensive analytics data
        $analyticsData = $this->getComprehensiveAnalytics($days);
        
        // Get admin data for layout
        $adminData = $this->adminMiddleware->getAdminData();
        $adminFullName = $this->adminMiddleware->getAdminFullName();
        $adminInitials = $this->adminMiddleware->getAdminInitials();

        $this->render('admin/analytics', [
            'analyticsData' => $analyticsData,
            'period' => $days,
            'adminData' => $adminData,
            'adminFullName' => $adminFullName,
            'adminInitials' => $adminInitials
        ]);
    }
    
    /**
     * Get comprehensive analytics data
     */
    private function getComprehensiveAnalytics($days) {
        $startDate = date('Y-m-d', strtotime("-$days days"));
        $today = date('Y-m-d');
        
        // Revenue data
        $revenueData = $this->getRevenueAnalytics($startDate, $today);
        
        // Orders data
        $ordersData = $this->getOrdersAnalytics($startDate, $today);
        
        // Customer insights
        $customerInsights = $this->getCustomerInsights($startDate);
        
        // Top categories
        $topCategories = $this->getTopCategoriesAnalytics($startDate);
        
        // Top products
        $topProducts = $this->getTopProductsAnalytics($startDate);
        
        // Daily trend data
        $trendData = $this->getTrendAnalytics($startDate, $today);
        
        return [
            'revenue' => $revenueData,
            'orders' => $ordersData,
            'customers' => $customerInsights,
            'categories' => $topCategories,
            'products' => $topProducts,
            'trends' => $trendData,
            'period' => $days
        ];
    }
    
    /**
     * Get revenue analytics
     * Revenue includes all orders except cancelled ones
     */
    public function getRevenueAnalytics($startDate, $today) {
        // Revenue Today - All orders created today (except cancelled)
        // This represents all sales/revenue generated today, not just delivered
        $stmt = $this->pdo->prepare("
            SELECT COALESCE(SUM(total_amount), 0) as revenue_today 
            FROM orders 
            WHERE DATE(created_at) = ? AND status != 'cancelled'
        ");
        $stmt->execute([$today]);
        $revenueToday = $stmt->fetch()['revenue_today'];
        
        // Also get delivered revenue today for comparison
        $stmt = $this->pdo->prepare("
            SELECT COALESCE(SUM(total_amount), 0) as delivered_revenue_today 
            FROM orders 
            WHERE DATE(created_at) = ? AND status = 'delivered'
        ");
        $stmt->execute([$today]);
        $deliveredRevenueToday = $stmt->fetch()['delivered_revenue_today'];
        
        // Revenue This Week - All orders from start of week (except cancelled)
        $weekStart = date('Y-m-d', strtotime('monday this week'));
        $stmt = $this->pdo->prepare("
            SELECT COALESCE(SUM(total_amount), 0) as revenue_week 
            FROM orders 
            WHERE DATE(created_at) >= ? AND status != 'cancelled'
        ");
        $stmt->execute([$weekStart]);
        $revenueWeek = $stmt->fetch()['revenue_week'];
        
        // Revenue This Month - All orders from start of month (except cancelled)
        $monthStart = date('Y-m-01');
        $stmt = $this->pdo->prepare("
            SELECT COALESCE(SUM(total_amount), 0) as revenue_month 
            FROM orders 
            WHERE DATE(created_at) >= ? AND status != 'cancelled'
        ");
        $stmt->execute([$monthStart]);
        $revenueMonth = $stmt->fetch()['revenue_month'];
        
        // Previous week for comparison
        $prevWeekStart = date('Y-m-d', strtotime('monday last week'));
        $prevWeekEnd = date('Y-m-d', strtotime('sunday last week'));
        $stmt = $this->pdo->prepare("
            SELECT COALESCE(SUM(total_amount), 0) as revenue_previous 
            FROM orders 
            WHERE DATE(created_at) >= ? AND DATE(created_at) <= ? AND status != 'cancelled'
        ");
        $stmt->execute([$prevWeekStart, $prevWeekEnd]);
        $revenuePrevious = $stmt->fetch()['revenue_previous'];
        
        // Calculate growth percentage
        $growthPercentage = $revenuePrevious > 0 ? 
            round((($revenueWeek - $revenuePrevious) / $revenuePrevious) * 100, 1) : 0;
        
        return [
            'today' => floatval($revenueToday),
            'delivered_today' => floatval($deliveredRevenueToday),
            'week' => floatval($revenueWeek),
            'month' => floatval($revenueMonth),
            'growth_percentage' => $growthPercentage
        ];
    }
    
    /**
     * Get orders analytics
     */
    public function getOrdersAnalytics($startDate, $today) {
        // Orders Today
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) as orders_today 
            FROM orders 
            WHERE DATE(created_at) = ?
        ");
        $stmt->execute([$today]);
        $ordersToday = $stmt->fetch()['orders_today'];
        
        // Orders in period
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) as total_orders 
            FROM orders 
            WHERE DATE(created_at) >= ?
        ");
        $stmt->execute([$startDate]);
        $totalOrders = $stmt->fetch()['total_orders'];
        
        // Orders by status
        $stmt = $this->pdo->prepare("
            SELECT 
                status,
                COUNT(*) as count
            FROM orders 
            WHERE DATE(created_at) >= ?
            GROUP BY status
        ");
        $stmt->execute([$startDate]);
        $ordersByStatus = $stmt->fetchAll();
        
        $statusCounts = [];
        foreach ($ordersByStatus as $status) {
            $statusCounts[$status['status']] = intval($status['count']);
        }
        
        return [
            'today' => intval($ordersToday),
            'total' => intval($totalOrders),
            'pending' => $statusCounts['placed'] ?? 0,
            'delivered' => $statusCounts['delivered'] ?? 0,
            'cancelled' => $statusCounts['cancelled'] ?? 0,
            'by_status' => $statusCounts
        ];
    }
    
    /**
     * Get customer insights
     */
    public function getCustomerInsights($startDate) {
        // Total customers in period
        $stmt = $this->pdo->prepare("
            SELECT COUNT(DISTINCT user_id) as total_customers 
            FROM orders 
            WHERE DATE(created_at) >= ?
        ");
        $stmt->execute([$startDate]);
        $totalCustomers = $stmt->fetch()['total_customers'];
        
        // New customers (first-time buyers in period)
        $stmt = $this->pdo->prepare("
            SELECT COUNT(DISTINCT user_id) as new_customers
            FROM orders o1
            WHERE DATE(o1.created_at) >= ?
            AND NOT EXISTS (
                SELECT 1 FROM orders o2 
                WHERE o2.user_id = o1.user_id 
                AND DATE(o2.created_at) < ?
            )
        ");
        $stmt->execute([$startDate, $startDate]);
        $newCustomers = $stmt->fetch()['new_customers'];
        
        // Returning customers
        $returningCustomers = $totalCustomers - $newCustomers;
        
        // Customer retention rate
        $retentionRate = $totalCustomers > 0 ? round(($returningCustomers / $totalCustomers) * 100, 1) : 0;
        
        return [
            'total' => intval($totalCustomers),
            'new' => intval($newCustomers),
            'returning' => intval($returningCustomers),
            'retention_rate' => $retentionRate
        ];
    }
    
    /**
     * Get top categories analytics
     */
    public function getTopCategoriesAnalytics($startDate) {
        $stmt = $this->pdo->prepare("
            SELECT 
                c.name as category_name,
                COALESCE(SUM(oi.total_price), 0) as total_sales,
                COUNT(DISTINCT o.id) as order_count
            FROM categories c
            LEFT JOIN products p ON c.id = p.category_id
            LEFT JOIN order_items oi ON p.id = oi.product_id
            LEFT JOIN orders o ON oi.order_id = o.id AND DATE(o.created_at) >= ? AND o.status != 'cancelled'
            GROUP BY c.id, c.name
            HAVING total_sales > 0
            ORDER BY total_sales DESC
            LIMIT 5
        ");
        $stmt->execute([$startDate]);
        $categories = $stmt->fetchAll();
        
        // Calculate total sales for percentage calculation
        $totalSales = array_sum(array_column($categories, 'total_sales'));
        
        // Add percentage to each category
        foreach ($categories as &$category) {
            $category['percentage'] = $totalSales > 0 ? 
                round(($category['total_sales'] / $totalSales) * 100, 1) : 0;
            $category['total_sales'] = floatval($category['total_sales']);
            $category['order_count'] = intval($category['order_count']);
        }
        
        return $categories;
    }
    
    /**
     * Get top products analytics
     * Includes all orders (except cancelled) for accurate sales tracking
     */
    private function getTopProductsAnalytics($startDate) {
        $stmt = $this->pdo->prepare("
            SELECT p.name, p.price, SUM(oi.quantity) as total_sold, SUM(oi.total_price) as total_revenue
            FROM order_items oi
            JOIN products p ON oi.product_id = p.id
            JOIN orders o ON oi.order_id = o.id
            WHERE DATE(o.created_at) >= ? AND o.status != 'cancelled'
            GROUP BY p.id, p.name, p.price
            ORDER BY total_sold DESC
            LIMIT 10
        ");
        $stmt->execute([$startDate]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get trend analytics
     * Revenue trends include all orders (except cancelled) for accurate revenue tracking
     */
    public function getTrendAnalytics($startDate, $today) {
        $stmt = $this->pdo->prepare("
            SELECT 
                DATE(created_at) as date,
                COALESCE(SUM(total_amount), 0) as revenue,
                COUNT(*) as orders
            FROM orders 
            WHERE DATE(created_at) >= ? AND status != 'cancelled'
            GROUP BY DATE(created_at)
            ORDER BY date ASC
        ");
        $stmt->execute([$startDate]);
        $trendData = $stmt->fetchAll();
        
        // Fill missing dates with zero values
        $result = [];
        $currentDate = strtotime($startDate);
        $endDate = strtotime($today);
        
        while ($currentDate <= $endDate) {
            $dateStr = date('Y-m-d', $currentDate);
            $found = false;
            
            foreach ($trendData as $data) {
                if ($data['date'] === $dateStr) {
                    $result[] = [
                        'date' => $dateStr,
                        'revenue' => floatval($data['revenue']),
                        'orders' => intval($data['orders'])
                    ];
                    $found = true;
                    break;
                }
            }
            
            if (!$found) {
                $result[] = [
                    'date' => $dateStr,
                    'revenue' => 0.0,
                    'orders' => 0
                ];
            }
            
            $currentDate = strtotime('+1 day', $currentDate);
        }
        
        return $result;
    }

    // Inventory Management
    public function inventory() {
        $page = intval($_GET['page'] ?? 1);
        $limit = 20;
        $offset = ($page - 1) * $limit;
        $filter = $_GET['filter'] ?? 'all';
        $search = $_GET['search'] ?? '';
        $category = $_GET['category'] ?? '';

        $where = "WHERE 1=1";
        $params = [];

        // Apply filters
        switch ($filter) {
            case 'low_stock':
                $where .= " AND p.stock_quantity <= p.low_stock_threshold AND p.stock_quantity > 0";
                break;
            case 'out_of_stock':
                $where .= " AND p.stock_quantity = 0";
                break;
            case 'high_stock':
                $where .= " AND p.stock_quantity > 50";
                break;
            case 'frozen':
                $where .= " AND p.is_frozen = 1";
                break;
            case 'eco_friendly':
                $where .= " AND p.is_eco_friendly = 1";
                break;
            case 'inactive':
                $where .= " AND p.is_active = 0";
                break;
            case 'active':
                $where .= " AND p.is_active = 1";
                break;
            case 'all':
            default:
                // No additional filter - show all products
                break;
        }

        // Apply search
        if (!empty($search)) {
            $where .= " AND p.name LIKE ?";
            $params[] = "%$search%";
        }

        // Apply category filter
        if (!empty($category)) {
            $where .= " AND p.category_id = ?";
            $params[] = $category;
        }

        $stmt = $this->pdo->prepare("
            SELECT DISTINCT p.*, c.name as category_name
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            $where
            ORDER BY 
                CASE 
                    WHEN p.stock_quantity = 0 THEN 1
                    WHEN p.stock_quantity <= p.low_stock_threshold THEN 2
                    ELSE 3
                END,
                p.stock_quantity ASC, 
                p.name ASC,
                p.id ASC
            LIMIT $limit OFFSET $offset
        ");
        $stmt->execute($params);
        $products = $stmt->fetchAll();

        $stmt = $this->pdo->prepare("SELECT COUNT(DISTINCT p.id) as total FROM products p $where");
        $stmt->execute($params);
        $total = $stmt->fetch()['total'];
        $totalPages = ceil($total / $limit);

        // Get categories for filter dropdown
        $stmt = $this->pdo->prepare("SELECT id, name FROM categories ORDER BY name");
        $stmt->execute();
        $categories = $stmt->fetchAll();

        // Get inventory statistics
        $stats = $this->getInventoryStats();

        $this->render('admin/inventory', [
            'products' => $products,
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
            'offset' => $offset,
            'limit' => $limit,
            'filter' => $filter,
            'search' => $search,
            'category' => $category,
            'categories' => $categories,
            'stats' => $stats
        ]);
    }

    public function updateStock() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $productId = $_POST['product_id'] ?? 0;
            $newStock = intval($_POST['stock_quantity'] ?? 0);

            if ($productId && $newStock >= 0) {
                $stmt = $this->pdo->prepare("UPDATE products SET stock_quantity = ? WHERE id = ?");
                $stmt->execute([$newStock, $productId]);
                $_SESSION['success'] = "Stock updated successfully.";
            } else {
                $_SESSION['error'] = "Invalid stock quantity.";
            }

            $this->redirect('/admin/inventory');
        }
    }

    public function updateInventory($productId = null) {
        if ($_SERVER['REQUEST_METHOD'] === 'PATCH') {
            // Get JSON input
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid JSON input']);
                return;
            }

            // Use parameter if provided, otherwise get from input
            $productId = $productId ?? ($input['product_id'] ?? 0);
            $stockCount = intval($input['stock_count'] ?? -1);
            $lowStockThreshold = intval($input['low_stock_threshold'] ?? -1);
            $restockEta = $input['restock_eta'] ?? null;
            $isActive = isset($input['is_active']) ? (bool)$input['is_active'] : null;

            if (!$productId) {
                http_response_code(400);
                echo json_encode(['error' => 'Product ID is required']);
                return;
            }

            $updates = [];
            $params = [];

            if ($stockCount >= 0) {
                $updates[] = "stock_quantity = ?";
                $params[] = $stockCount;
            }

            if ($lowStockThreshold >= 0) {
                $updates[] = "low_stock_threshold = ?";
                $params[] = $lowStockThreshold;
            }

            if ($restockEta !== null) {
                $updates[] = "restock_eta = ?";
                $params[] = $restockEta ?: null;
            }

            if ($isActive !== null) {
                $updates[] = "is_active = ?";
                $params[] = $isActive ? 1 : 0;
            }

            if (empty($updates)) {
                http_response_code(400);
                echo json_encode(['error' => 'No valid fields to update']);
                return;
            }

            $params[] = $productId;
            $sql = "UPDATE products SET " . implode(', ', $updates) . " WHERE id = ?";
            
            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute($params);

            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Inventory updated successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to update inventory']);
            }
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
        }
    }

    private function getInventoryStats() {
        $stats = [];

        // Total products
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as total FROM products");
        $stmt->execute();
        $stats['total_products'] = $stmt->fetch()['total'];

        // Low stock items
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as low_stock FROM products WHERE stock_quantity <= low_stock_threshold AND stock_quantity > 0");
        $stmt->execute();
        $stats['low_stock'] = $stmt->fetch()['low_stock'];

        // Out of stock items
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as out_of_stock FROM products WHERE stock_quantity = 0");
        $stmt->execute();
        $stats['out_of_stock'] = $stmt->fetch()['out_of_stock'];

        // Frozen products
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as frozen FROM products WHERE is_frozen = 1");
        $stmt->execute();
        $stats['frozen'] = $stmt->fetch()['frozen'];

        // Eco-friendly products
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as eco_friendly FROM products WHERE is_eco_friendly = 1");
        $stmt->execute();
        $stats['eco_friendly'] = $stmt->fetch()['eco_friendly'];

        // Inactive products
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as inactive FROM products WHERE is_active = 0");
        $stmt->execute();
        $stats['inactive'] = $stmt->fetch()['inactive'];

        return $stats;
    }

    private function calculateNextDeliveryDate($frequency) {
        $date = new DateTime();
        
        switch ($frequency) {
            case 'weekly':
                $date->modify('+1 week');
                break;
            case 'bi_weekly':
                $date->modify('+2 weeks');
                break;
            case 'monthly':
                $date->modify('+1 month');
                break;
            default:
                $date->modify('+1 month');
        }
        
        return $date->format('Y-m-d');
    }

    public function drivers() {
        $this->requireAdmin();
        
        // Render the drivers management page
        $this->render('admin/drivers', []);
    }
}
?>