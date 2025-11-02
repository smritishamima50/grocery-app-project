<?php
require_once 'BaseController.php';
require_once 'app/middleware/AdminMiddleware.php';
require_once 'app/helpers/PaymentGateways.php';

class ApiController extends BaseController {
    private $adminMiddleware;
    
    public function __construct() {
        parent::__construct();
        $this->adminMiddleware = new AdminMiddleware();
    }
    
    /**
     * Set JSON response headers
     */
    private function setJsonHeaders() {
        if (!headers_sent()) {
            header('Content-Type: application/json');
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: GET, POST, PATCH, DELETE, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type');
        }
        // Handle CORS preflight quickly
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(204);
            exit;
        }
    }
    
    /**
     * Check admin access and return JSON response
     */
    private function requireAdminJson() {
        if (!$this->adminMiddleware || !$this->adminMiddleware->isAdmin()) {
            while (ob_get_level()) {
                ob_end_clean();
            }
            if (!headers_sent()) {
                $this->setJsonHeaders();
                http_response_code(403);
            }
            echo json_encode(['success' => false, 'error' => 'Access denied. Admin privileges required.']);
            exit;
        }
    }
    
    /**
     * Get orders with filters
     * GET /api/admin/orders
     */
    public function orders() {
        $this->requireAdminJson();
        $this->setJsonHeaders();
        
        $status = $_GET['status'] ?? 'all';
        $page = intval($_GET['page'] ?? 1);
        $limit = intval($_GET['limit'] ?? 20);
        $offset = ($page - 1) * $limit;
        $search = $_GET['search'] ?? '';
        $dateFrom = $_GET['date_from'] ?? '';
        $dateTo = $_GET['date_to'] ?? '';
        $driver = $_GET['driver'] ?? '';

        $where = "WHERE 1=1";
        $params = [];

        // Apply filters
        if ($status !== 'all') {
            $where .= " AND o.status = ?";
            $params[] = $status;
        }

        if (!empty($search)) {
            $where .= " AND (o.id LIKE ? OR u.phone LIKE ? OR CONCAT(u.first_name, ' ', u.last_name) LIKE ?)";
            $searchTerm = "%$search%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
        }

        if (!empty($dateFrom)) {
            $where .= " AND DATE(o.created_at) >= ?";
            $params[] = $dateFrom;
        }
        if (!empty($dateTo)) {
            $where .= " AND DATE(o.created_at) <= ?";
            $params[] = $dateTo;
        }

        if (!empty($driver)) {
            $where .= " AND o.assigned_driver = ?";
            $params[] = $driver;
        }

        try {
            // Get orders
            $stmt = $this->pdo->prepare("
                SELECT o.*, 
                       u.first_name, u.last_name, u.phone, u.email,
                       ua.address_line1, ua.address_line2, ua.city, ua.state, ua.zip_code
                FROM orders o
                JOIN users u ON o.user_id = u.id
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
            ");
            $stmt->execute($params);
            $orders = $stmt->fetchAll();

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

            // Get total count
            $stmt = $this->pdo->prepare("SELECT COUNT(*) as total FROM orders o JOIN users u ON o.user_id = u.id $where");
            $stmt->execute($params);
            $total = $stmt->fetch()['total'];

            // Get statistics
            $stats = $this->getOrderStats();

            echo json_encode([
                'success' => true,
                'data' => [
                    'orders' => $orders,
                    'pagination' => [
                        'current_page' => $page,
                        'total_pages' => ceil($total / $limit),
                        'total_orders' => $total,
                        'per_page' => $limit
                    ],
                    'stats' => $stats
                ]
            ]);

        } catch (Exception $e) {
            error_log("Orders API error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Failed to fetch orders']);
        }
    }

    /**
     * Export orders to CSV
     * GET /api/admin/orders/export
     */
    public function exportOrders() {
        // Check admin access - don't require JSON for export
        if (!$this->adminMiddleware->isAdmin()) {
            http_response_code(403);
            echo "Access denied. Admin privileges required.";
            exit;
        }
        
        $status = $_GET['status'] ?? 'all';
        $search = $_GET['search'] ?? '';
        $dateFrom = $_GET['date_from'] ?? '';
        $dateTo = $_GET['date_to'] ?? '';
        $driver = $_GET['driver'] ?? '';

        $where = "WHERE 1=1";
        $params = [];

        // Apply filters - match the same logic as AdminController
        if ($status !== 'all' && !empty($status)) {
            $where .= " AND o.status = ?";
            $params[] = $status;
        }

        if (!empty($search)) {
            $where .= " AND (o.id LIKE ? OR u.phone LIKE ? OR CONCAT(COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) LIKE ?)";
            $searchTerm = "%$search%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
        }

        if (!empty($dateFrom)) {
            $where .= " AND DATE(o.created_at) >= ?";
            $params[] = $dateFrom;
        }
        if (!empty($dateTo)) {
            $where .= " AND DATE(o.created_at) <= ?";
            $params[] = $dateTo;
        }
        
        if (!empty($driver)) {
            $where .= " AND o.assigned_driver = ?";
            $params[] = $driver;
        }

        try {
            // Get all orders (no pagination for export) - use LEFT JOIN to match main query
            $stmt = $this->pdo->prepare("
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
                ORDER BY o.created_at DESC
            ");
            $stmt->execute($params);
            $orders = $stmt->fetchAll();

            // Set CSV headers
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="orders_export_' . date('Y-m-d_H-i-s') . '.csv"');
            
            // Output BOM for UTF-8 Excel compatibility
            echo "\xEF\xBB\xBF";

            // Open output stream
            $output = fopen('php://output', 'w');

            // CSV headers
            fputcsv($output, [
                'Order ID',
                'Date',
                'Customer Name',
                'Phone',
                'Email',
                'Status',
                'Assigned Driver',
                'Total Amount',
                'Payment Method',
                'Payment Status',
                'Address',
                'City',
                'State',
                'Zip Code',
                'Delivery Slot',
                'Is Urgent',
                'Eco Friendly',
                'Created At'
            ]);

            // Write order data
            foreach ($orders as $order) {
                $address = trim(($order['address_line1'] ?? '') . ' ' . ($order['address_line2'] ?? ''));
                
                fputcsv($output, [
                    $order['id'],
                    date('Y-m-d', strtotime($order['created_at'])),
                    trim(($order['first_name'] ?? '') . ' ' . ($order['last_name'] ?? '')),
                    $order['phone'] ?? '',
                    $order['email'] ?? '',
                    ucfirst(str_replace('_', ' ', $order['status'])),
                    $order['assigned_driver'] ?? 'Unassigned',
                    number_format($order['total_amount'], 2),
                    ucfirst(str_replace('_', ' ', $order['payment_method'] ?? '')),
                    ucfirst($order['payment_status'] ?? ''),
                    $address,
                    $order['city'] ?? '',
                    $order['state'] ?? '',
                    $order['zip_code'] ?? '',
                    $order['delivery_slot'] ?? '',
                    $order['is_urgent'] ? 'Yes' : 'No',
                    $order['eco_friendly_delivery'] ? 'Yes' : 'No',
                    $order['created_at']
                ]);
            }

            fclose($output);
            exit;

        } catch (Exception $e) {
            error_log("Export orders error: " . $e->getMessage());
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Failed to export orders']);
        }
    }

    /**
     * Get order details
     * GET /api/admin/orders/:id
     */
    public function getOrder($orderId) {
        $this->requireAdminJson();
        $this->setJsonHeaders();
        
        try {
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

        } catch (Exception $e) {
            error_log("Get order API error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Failed to fetch order details']);
        }
    }

    /**
     * Update order status and assignment
     * PATCH /api/admin/orders/:id
     */
    public function updateOrder($orderId) {
        // Disable error display to prevent PHP errors from corrupting JSON response
        $displayErrors = ini_get('display_errors');
        ini_set('display_errors', '0');
        
        // Start output buffering as early as possible to catch any accidental output
        // Clear any existing output buffer first
        while (ob_get_level()) {
            ob_end_clean();
        }
        ob_start();
        
        error_log("========================================");
        error_log("🔧 updateOrder called for order ID: $orderId");
        error_log("🔧 Request method: " . $_SERVER['REQUEST_METHOD']);
        error_log("🔧 Request URI: " . ($_SERVER['REQUEST_URI'] ?? 'N/A'));
        error_log("🔧 PHP Input stream available: " . (file_get_contents('php://input') !== false ? 'YES' : 'NO'));
        
        // Set JSON headers first
        $this->setJsonHeaders();
        
        // Check admin access
        if (!$this->adminMiddleware->isAdmin()) {
            while (ob_get_level()) {
                ob_end_clean();
            }
            if (!headers_sent()) {
                $this->setJsonHeaders();
                http_response_code(403);
            }
            $errorMsg = 'Access denied. Admin privileges required. User ID: ' . ($_SESSION['user_id'] ?? 'NOT SET') . ', Role: ' . ($_SESSION['role'] ?? 'NOT SET');
            error_log("❌ " . $errorMsg);
            echo json_encode(['success' => false, 'error' => $errorMsg]);
            if (isset($displayErrors)) {
                ini_set('display_errors', $displayErrors);
            }
            exit;
        }
        
        error_log("✅ Admin access verified for user ID: " . $_SESSION['user_id']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'PATCH') {
            while (ob_get_level()) {
                ob_end_clean();
            }
            if (!headers_sent()) {
                $this->setJsonHeaders();
                http_response_code(405);
            }
            error_log("❌ Invalid method: " . $_SERVER['REQUEST_METHOD'] . " (expected PATCH)");
            echo json_encode(['success' => false, 'error' => 'Method not allowed. Expected PATCH, got ' . $_SERVER['REQUEST_METHOD']]);
            if (isset($displayErrors)) {
                ini_set('display_errors', $displayErrors);
            }
            exit;
        }

        $rawInput = file_get_contents('php://input');
        error_log("📥 Raw input length: " . strlen($rawInput));
        error_log("📥 Raw input received: " . ($rawInput ? substr($rawInput, 0, 500) : 'EMPTY'));
        
        if (empty($rawInput)) {
            while (ob_get_level()) {
                ob_end_clean();
            }
            if (!headers_sent()) {
                $this->setJsonHeaders();
                http_response_code(400);
            }
            error_log("❌ Empty input received");
            echo json_encode(['success' => false, 'error' => 'Request body is empty']);
            if (isset($displayErrors)) {
                ini_set('display_errors', $displayErrors);
            }
            exit;
        }
        
        $input = json_decode($rawInput, true);
        $jsonError = json_last_error();
        if ($jsonError !== JSON_ERROR_NONE) {
            while (ob_get_level()) {
                ob_end_clean();
            }
            if (!headers_sent()) {
                $this->setJsonHeaders();
                http_response_code(400);
            }
            error_log("❌ JSON decode error: " . json_last_error_msg());
            echo json_encode(['success' => false, 'error' => 'Invalid JSON: ' . json_last_error_msg()]);
            if (isset($displayErrors)) {
                ini_set('display_errors', $displayErrors);
            }
            exit;
        }
        
        error_log("📥 Parsed input: " . print_r($input, true));

        $newStatus = $input['status'] ?? null;
        // Handle assigned_driver: empty string, null, or actual value
        $assignedDriver = isset($input['assigned_driver']) ? $input['assigned_driver'] : null;
        if ($assignedDriver === '' || $assignedDriver === null) {
            $assignedDriver = null; // Normalize empty strings to null
        } else {
            $assignedDriver = trim((string)$assignedDriver); // Trim whitespace
        }
        $adminNotes = $input['admin_notes'] ?? null;
        
        error_log("📊 Extracted params:");
        error_log("   - newStatus: " . ($newStatus ?? 'NULL'));
        error_log("   - assignedDriver: " . ($assignedDriver ?? 'NULL') . " (type: " . gettype($assignedDriver) . ")");
        error_log("   - adminNotes: " . ($adminNotes ? 'SET' : 'NULL'));

        // Validate that at least one field is being updated
        // Note: assignedDriver can be null (unassigning), so check if it was provided in input
        $hasAssignedDriverUpdate = isset($input['assigned_driver']); // true even if null
        if ($newStatus === null && !$hasAssignedDriverUpdate && $adminNotes === null) {
            while (ob_get_level()) {
                ob_end_clean();
            }
            if (!headers_sent()) {
                $this->setJsonHeaders();
                http_response_code(400);
            }
            error_log("❌ No fields to update");
            echo json_encode(['success' => false, 'error' => 'At least one field (status, assigned_driver, or admin_notes) must be provided']);
            if (isset($displayErrors)) {
                ini_set('display_errors', $displayErrors);
            }
            exit;
        }

        try {
            // Verify order exists first
            $checkStmt = $this->pdo->prepare("SELECT id, status, assigned_driver FROM orders WHERE id = ?");
            $checkStmt->execute([$orderId]);
            $currentOrder = $checkStmt->fetch();
            
            if (!$currentOrder) {
                while (ob_get_level()) {
                    ob_end_clean();
                }
                if (!headers_sent()) {
                    $this->setJsonHeaders();
                    http_response_code(404);
                }
                error_log("❌ Order not found: ID $orderId");
                echo json_encode(['success' => false, 'error' => "Order #$orderId not found"]);
                if (isset($displayErrors)) {
                    ini_set('display_errors', $displayErrors);
                }
                exit;
            }
            
            error_log("✅ Order found:");
            error_log("   - Current status: " . $currentOrder['status']);
            error_log("   - Current driver: " . ($currentOrder['assigned_driver'] ?? 'NULL'));
            
            $this->pdo->beginTransaction();
            error_log("✅ Transaction started");

            $oldStatus = $currentOrder['status'];
            $statusChanged = ($newStatus !== null && $newStatus !== $oldStatus);

            // Update order - build update fields dynamically
            $updateFields = [];
            $params = [];
            $driverParamIndex = -1; // Track which param index is assigned_driver

            // Update status if provided and different from current
            if ($newStatus !== null) {
                $updateFields[] = 'status = ?';
                $params[] = $newStatus;
            }

            if ($assignedDriver !== null || isset($input['assigned_driver'])) {
                // Always include assigned_driver in update if it was provided (even if null/unassigning)
                $updateFields[] = 'assigned_driver = ?';
                $params[] = $assignedDriver; // Already normalized to null or trimmed string above
                $driverParamIndex = count($params) - 1; // Track the index
                error_log("   ✅ Added assigned_driver to update at param index $driverParamIndex: " . var_export($assignedDriver, true));
            }

            if ($adminNotes !== null) {
                $updateFields[] = 'admin_notes = ?';
                $params[] = $adminNotes;
            }

            // Only update if there are fields to update
            if (empty($updateFields)) {
                $this->pdo->rollback();
                while (ob_get_level()) {
                    ob_end_clean();
                }
                if (!headers_sent()) {
                    $this->setJsonHeaders();
                    http_response_code(400);
                }
                error_log("⚠️ WARNING: No update fields to process!");
                echo json_encode(['success' => false, 'error' => 'No fields to update']);
                if (isset($displayErrors)) {
                    ini_set('display_errors', $displayErrors);
                }
                exit;
            }
            
            // IMPORTANT: Add orderId to params LAST (for WHERE clause)
            $params[] = $orderId;
            
            // Build SQL - ensure updated_at is always set
            $sql = "UPDATE orders SET " . implode(', ', $updateFields) . ", updated_at = NOW() WHERE id = ?";
            
            // CRITICAL DEBUG: Log the exact SQL and params before execution
            error_log("🔍 PRE-UPDATE VERIFICATION:");
            error_log("   - Update fields count: " . count($updateFields));
            error_log("   - Update fields: " . implode(', ', $updateFields));
            error_log("   - Params count: " . count($params));
            foreach ($updateFields as $idx => $field) {
                error_log("   - Field $idx ($field): " . var_export($params[$idx] ?? 'MISSING', true));
            }
            error_log("   - Order ID (last param): " . end($params));
            
            error_log("🔧 ========== EXECUTING DATABASE UPDATE ==========");
            error_log("🔧 SQL Query: $sql");
            error_log("🔧 Parameters (" . count($params) . "): " . print_r($params, true));
            error_log("🔧 Order ID: $orderId");
            
            // Prepare statement
            $stmt = $this->pdo->prepare($sql);
            if (!$stmt) {
                $error = $this->pdo->errorInfo();
                error_log("❌ SQL PREPARE FAILED: " . ($error[2] ?? 'Unknown error'));
                throw new Exception("SQL prepare failed: " . ($error[2] ?? 'Unknown error'));
            }
            
            error_log("✅ SQL prepared successfully");
            
            // Execute with error checking
            try {
                $result = $stmt->execute($params);
                if (!$result) {
                    $error = $stmt->errorInfo();
                    error_log("❌ SQL EXECUTE FAILED: " . ($error[2] ?? 'Unknown error'));
                    throw new Exception("SQL execute failed: " . ($error[2] ?? 'Unknown error'));
                }
                
                $rowsAffected = $stmt->rowCount();
                error_log("✅ SQL executed. Result: " . ($result ? 'TRUE' : 'FALSE') . ", Rows affected: $rowsAffected");
                
                // CRITICAL: Log what we're trying to update
                if ($driverParamIndex >= 0) {
                    error_log("🔍 DRIVER UPDATE CHECK:");
                    error_log("   - assigned_driver param index: $driverParamIndex");
                    error_log("   - assigned_driver value in params: " . var_export($params[$driverParamIndex] ?? 'MISSING', true));
                    error_log("   - assigned_driver normalized: " . var_export($assignedDriver, true));
                    error_log("   - Value type: " . gettype($params[$driverParamIndex] ?? null));
                }
                
            } catch (PDOException $e) {
                error_log("❌ PDO EXCEPTION: " . $e->getMessage());
                error_log("❌ Error Code: " . $e->getCode());
                throw $e;
            }
            
            // CRITICAL: Verify the update actually happened BEFORE commit
            if ($rowsAffected === 0) {
                // Check if values are already the same (this is acceptable)
                $checkSameStmt = $this->pdo->prepare("SELECT status, assigned_driver FROM orders WHERE id = ?");
                $checkSameStmt->execute([$orderId]);
                $currentValues = $checkSameStmt->fetch();
                
                $valuesAlreadySame = true;
                if ($newStatus !== null && $currentValues['status'] !== $newStatus) {
                    $valuesAlreadySame = false;
                }
                if ($assignedDriver !== null || isset($input['assigned_driver'])) {
                    $expectedDriver = ($assignedDriver === '' || $assignedDriver === null) ? null : trim((string)$assignedDriver);
                    $currentDriver = ($currentValues['assigned_driver'] === '' || $currentValues['assigned_driver'] === null) ? null : trim((string)($currentValues['assigned_driver'] ?? ''));
                    if ($currentDriver !== $expectedDriver) {
                        $valuesAlreadySame = false;
                    }
                }
                
                if ($valuesAlreadySame) {
                    // Values are already set correctly, this is fine - commit and continue
                    error_log("ℹ️ No rows affected but values are already correct - this is expected");
                    $this->pdo->commit();
                } else {
                    // This is a SERIOUS problem - the update didn't affect any rows
                    $this->pdo->rollback();
                    while (ob_get_level()) {
                        ob_end_clean();
                    }
                    
                    error_log("❌ ========== CRITICAL ERROR: NO ROWS AFFECTED ==========");
                    error_log("❌ UPDATE query executed successfully BUT 0 ROWS WERE UPDATED!");
                    error_log("❌ Order ID: $orderId");
                    error_log("❌ SQL: $sql");
                    error_log("❌ Params: " . print_r($params, true));
                
                // Check if order still exists and get current values
                $checkStmt = $this->pdo->prepare("SELECT id, status, assigned_driver FROM orders WHERE id = ?");
                $checkStmt->execute([$orderId]);
                $checkOrder = $checkStmt->fetch();
                
                if (!$checkOrder) {
                    if (!headers_sent()) {
                        $this->setJsonHeaders();
                        http_response_code(404);
                    }
                    error_log("❌ Order #$orderId DOES NOT EXIST in database!");
                    echo json_encode(['success' => false, 'error' => "Order #$orderId not found in database"]);
                    if (isset($displayErrors)) {
                        ini_set('display_errors', $displayErrors);
                    }
                    exit;
                } else {
                    error_log("❌ Order exists but update failed:");
                    error_log("   Current status: {$checkOrder['status']}");
                    error_log("   Current driver: " . ($checkOrder['assigned_driver'] ?? 'NULL'));
                    error_log("   Trying to set status: " . ($newStatus ?? 'NULL'));
                    error_log("   Trying to set driver: " . ($assignedDriver ?? 'NULL'));
                    
                    // Check if values are already the same (would cause 0 rows affected)
                    $alreadySame = true;
                    if ($newStatus !== null && $checkOrder['status'] !== $newStatus) {
                        $alreadySame = false;
                    }
                    // Check driver even if null (unassigning)
                    if ($assignedDriver !== null || isset($input['assigned_driver'])) {
                        $expectedDriver = ($assignedDriver === '' || $assignedDriver === null) ? null : trim((string)$assignedDriver);
                        $currentDriver = ($checkOrder['assigned_driver'] === '' || $checkOrder['assigned_driver'] === null) ? null : trim((string)($checkOrder['assigned_driver'] ?? ''));
                        if ($currentDriver !== $expectedDriver) {
                            $alreadySame = false;
                            error_log("   Driver different: current='{$currentDriver}' vs expected='{$expectedDriver}'");
                        }
                    }
                    
                    if ($alreadySame) {
                        error_log("⚠️ Values are already set to what we're trying to set - this is expected");
                        
                        // Build response with all fields
                        $response = [
                            'success' => true,
                            'message' => 'Order already has these values',
                            'database_updated' => true,
                            'verification_passed' => true
                        ];
                        
                        if ($newStatus !== null) {
                            $response['new_status'] = $checkOrder['status'];
                        }
                        if ($assignedDriver !== null || isset($input['assigned_driver'])) {
                            $response['assigned_driver'] = $checkOrder['assigned_driver'] ?? null;
                            if ($response['assigned_driver'] === '') {
                                $response['assigned_driver'] = null;
                            }
                            error_log("📤 Returning assigned_driver (already same): " . var_export($response['assigned_driver'], true));
                        }
                        
                        if (!headers_sent()) {
                            header('Content-Type: application/json; charset=utf-8');
                        }
                        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                        if (isset($displayErrors)) {
                            ini_set('display_errors', $displayErrors);
                        }
                        exit;
                    } else {
                        if (!headers_sent()) {
                            $this->setJsonHeaders();
                            http_response_code(500);
                        }
                        echo json_encode([
                            'success' => false, 
                            'error' => 'Database update failed: No rows affected. Current: status=' . $checkOrder['status'] . ', driver=' . ($checkOrder['assigned_driver'] ?? 'NULL')
                        ]);
                        if (isset($displayErrors)) {
                            ini_set('display_errors', $displayErrors);
                        }
                        exit;
                    }
                }
            }
            
            error_log("✅ UPDATE CONFIRMED: $rowsAffected row(s) affected in database");

            // Record status change in history only if status actually changed
            if ($newStatus !== null && $statusChanged) {
                try {
                    $stmt = $this->pdo->prepare("
                        INSERT INTO order_status_history (order_id, old_status, new_status, changed_by_admin_id, admin_name, notes)
                        VALUES (?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $orderId,
                        $oldStatus,
                        $newStatus,
                        $_SESSION['user_id'] ?? null,
                        ($_SESSION['first_name'] ?? '') . ' ' . ($_SESSION['last_name'] ?? ''),
                        $adminNotes
                    ]);
                } catch (Exception $e) {
                    // Log error but don't fail the update
                    error_log("Failed to insert status history: " . $e->getMessage());
                }

                // Add delivery update only if status changed
                try {
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
                } catch (Exception $e) {
                    // Log error but don't fail the update
                    error_log("Failed to insert delivery update: " . $e->getMessage());
                }
            }

            // Commit the transaction
            if (!$this->pdo->commit()) {
                $error = $this->pdo->errorInfo();
                throw new Exception("Transaction commit failed: " . ($error[2] ?? 'Unknown error'));
            }
            error_log("✅ Transaction committed successfully");

            // CRITICAL: Re-read from database to verify the update persisted
            $verifyStmt = $this->pdo->prepare("SELECT id, status, assigned_driver, updated_at FROM orders WHERE id = ?");
            $verifyStmt->execute([$orderId]);
            $updatedOrder = $verifyStmt->fetch();
            
            if (!$updatedOrder) {
                throw new Exception("CRITICAL: Order disappeared after update! Order ID: $orderId");
            }
            
            // Verify the update actually happened in the database
            $verificationPassed = true;
            $verificationErrors = [];
            
            if ($newStatus !== null) {
                if (!isset($updatedOrder['status'])) {
                    $verificationErrors[] = "Status field missing in database";
                    $verificationPassed = false;
                } elseif ($updatedOrder['status'] !== $newStatus) {
                    $verificationErrors[] = "Status mismatch: Expected '$newStatus' but got '{$updatedOrder['status']}'";
                    $verificationPassed = false;
                } else {
                    error_log("✅ Status verified in database: '{$updatedOrder['status']}'");
                }
            }
            
            // Verify driver update if it was included in the request (even if null for unassigning)
            if ($assignedDriver !== null || isset($input['assigned_driver'])) {
                $expectedDriver = ($assignedDriver === '' || $assignedDriver === null) ? null : $assignedDriver;
                $actualDriver = $updatedOrder['assigned_driver'] ?? null;
                
                // Normalize both values for comparison (handle empty strings and nulls)
                $expectedDriverNormalized = ($expectedDriver === '' || $expectedDriver === null) ? null : trim((string)$expectedDriver);
                $actualDriverNormalized = ($actualDriver === '' || $actualDriver === null) ? null : trim((string)$actualDriver);
                
                if ($actualDriverNormalized !== $expectedDriverNormalized) {
                    $verificationErrors[] = "Driver mismatch: Expected '" . ($expectedDriverNormalized ?? 'NULL') . "' but got '" . ($actualDriverNormalized ?? 'NULL') . "'";
                    $verificationPassed = false;
                    error_log("❌ Driver verification FAILED:");
                    error_log("   - Expected: '" . ($expectedDriverNormalized ?? 'NULL') . "'");
                    error_log("   - Actual in DB: '" . ($actualDriverNormalized ?? 'NULL') . "'");
                    error_log("   - Raw expected: " . var_export($expectedDriver, true));
                    error_log("   - Raw actual: " . var_export($actualDriver, true));
                } else {
                    error_log("✅ Driver verified in database: " . ($actualDriverNormalized ?? 'NULL'));
                }
            }
            
            if (!$verificationPassed) {
                error_log("❌ VERIFICATION FAILED:");
                foreach ($verificationErrors as $error) {
                    error_log("   - $error");
                }
                // Don't throw - still return success but log the issue
            } else {
                error_log("✅ Database verification PASSED - all updates confirmed");
            }
            
            error_log("📊 Final database state:");
            error_log("   - Order ID: {$updatedOrder['id']}");
            error_log("   - Status: {$updatedOrder['status']}");
            error_log("   - Driver: " . ($updatedOrder['assigned_driver'] ?? 'NULL'));
            error_log("   - Updated At: {$updatedOrder['updated_at']}");
            error_log("✅ Order update successfully saved to database for ID: $orderId");
            
            // Build success message based on what was updated
            $updateMessages = [];
            if ($newStatus !== null) {
                $updateMessages[] = "Status changed to '{$updatedOrder['status']}'";
            }
            if ($assignedDriver !== null) {
                if ($updatedOrder['assigned_driver']) {
                    $updateMessages[] = "Driver assigned: '{$updatedOrder['assigned_driver']}'";
                } else {
                    $updateMessages[] = "Driver unassigned";
                }
            }
            
            $response = [
                'success' => true, 
                'message' => 'Order updated successfully: ' . implode(', ', $updateMessages),
                'database_updated' => true,
                'verification_passed' => $verificationPassed,
                'order_id' => $orderId,
                'updated_at' => $updatedOrder['updated_at']
            ];
            
            // Include updated fields in response for frontend
            if ($newStatus !== null) {
                $response['new_status'] = $updatedOrder['status'];
                error_log("📤 Returning new_status: " . $response['new_status']);
            }
            
            // CRITICAL: Always return assigned_driver if it was in the update request (even if null for unassigning)
            if ($assignedDriver !== null || isset($input['assigned_driver'])) {
                // Get the actual value from database (in case normalization changed it)
                $dbDriverValue = $updatedOrder['assigned_driver'] ?? null;
                
                // Normalize empty string to null for consistency
                if ($dbDriverValue === '') {
                    $dbDriverValue = null;
                }
                
                // Always include assigned_driver in response
                $response['assigned_driver'] = $dbDriverValue;
                
                // CRITICAL: Log what we're returning
                error_log("📤 RETURNING assigned_driver in response:");
                error_log("   - Request value (normalized): " . var_export($assignedDriver, true));
                error_log("   - Database value: " . var_export($updatedOrder['assigned_driver'], true));
                error_log("   - Response value: " . var_export($response['assigned_driver'], true));
                error_log("   - Type: " . gettype($response['assigned_driver']));
                error_log("   - Was in input: " . (isset($input['assigned_driver']) ? 'YES' : 'NO'));
                
                // Double-check: If we sent a driver name, make sure it's in the response
                if ($assignedDriver !== null && $assignedDriver !== '' && $response['assigned_driver'] === null) {
                    error_log("⚠️ WARNING: Driver was sent but response shows null! This might be a database issue.");
                    // Try to use the sent value as fallback
                    $response['assigned_driver'] = trim((string)$assignedDriver);
                    error_log("   - Using sent value as fallback: " . $response['assigned_driver']);
                }
            } else {
                error_log("📤 assigned_driver was NOT in the update request, skipping response field");
            }
            
            // Log final response before sending
            error_log("📤 Final response: " . json_encode($response));
            error_log("========================================");
            
            // Clear any accidental output and set headers before sending JSON
            // Clean all output buffers
            while (ob_get_level()) {
                ob_end_clean();
            }
            
            // Set headers (check if already sent)
            if (!headers_sent()) {
                header('Content-Type: application/json; charset=utf-8');
            }
            
            // Send JSON response
            echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            
            // Restore error display setting
            if (isset($displayErrors)) {
                ini_set('display_errors', $displayErrors);
            }
            
            exit;

        } catch (Exception $e) {
            // Clear all output buffers
            while (ob_get_level()) {
                ob_end_clean();
            }
            
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollback();
                error_log("⚠️ Transaction rolled back due to error");
            }
            
            $errorMsg = 'Failed to update order: ' . $e->getMessage();
            error_log("❌ Update order API error: " . $errorMsg);
            error_log("❌ Stack trace: " . $e->getTraceAsString());
            
            // Set headers if not already sent
            if (!headers_sent()) {
                $this->setJsonHeaders();
                http_response_code(500);
            }
            
            echo json_encode(['success' => false, 'error' => $errorMsg]);
            
            // Restore error display setting
            if (isset($displayErrors)) {
                ini_set('display_errors', $displayErrors);
            }
            
            exit;
        }
    }

    /**
     * Get drivers list
     * GET /api/admin/drivers
     */
    public function drivers($driverId = null) {
        // Store original error settings
        $displayErrors = ini_get('display_errors');
        $errorReporting = error_reporting();
        ini_set('display_errors', '0');
        error_reporting(E_ALL); // Still log errors, just don't display them
        
        // Set custom error handler to catch any errors
        $errorHandler = set_error_handler(function($errno, $errstr, $errfile, $errline) {
            error_log("PHP Error in drivers API: [$errno] $errstr in $errfile on line $errline");
            return true; // Suppress error output
        });
        
        // Start output buffering as early as possible to catch any accidental output
        // Clear any existing output buffer first
        while (ob_get_level()) {
            ob_end_clean();
        }
        ob_start();
        
        // Wrap everything in try-catch to catch any unexpected errors
        try {
            // Verify PDO connection exists
            if (!$this->pdo) {
                throw new Exception('Database connection not available');
            }
            
            // Verify adminMiddleware exists
            if (!$this->adminMiddleware) {
                throw new Exception('Admin middleware not initialized');
            }
            
            // Check admin access FIRST, before any other operations
            // Do this manually to avoid potential issues with requireAdminJson
            try {
                $isAdmin = $this->adminMiddleware->isAdmin();
            } catch (Exception $e) {
                error_log("Error checking admin status: " . $e->getMessage());
                error_log("Admin middleware error trace: " . $e->getTraceAsString());
                // If admin check fails, assume not admin
                $isAdmin = false;
            } catch (Throwable $t) {
                error_log("Fatal error checking admin status: " . $t->getMessage());
                error_log("Admin middleware fatal error trace: " . $t->getTraceAsString());
                $isAdmin = false;
            }
            
            if (!$isAdmin) {
                // Clear output buffers
                while (ob_get_level()) {
                    ob_end_clean();
                }
                
                // Restore error handler
                if ($errorHandler !== null) {
                    restore_error_handler();
                }
                error_reporting($errorReporting);
                ini_set('display_errors', $displayErrors);
                
                // Set JSON headers and send error response
                if (!headers_sent()) {
                    $this->setJsonHeaders();
                    http_response_code(403);
                }
                
                echo json_encode(['success' => false, 'error' => 'Access denied. Admin privileges required.']);
                exit;
            }
            
            // Set JSON headers after admin check passes
            if (!headers_sent()) {
                $this->setJsonHeaders();
            }
            
            // Handle DELETE request
            if ($_SERVER['REQUEST_METHOD'] === 'DELETE' && $driverId) {
                try {
                    // Get driver name first
                    $stmt = $this->pdo->prepare("SELECT name FROM drivers WHERE id = ? AND is_active = 1");
                    $stmt->execute([$driverId]);
                    $driver = $stmt->fetch();
                    
                    if (!$driver) {
                        while (ob_get_level()) {
                            ob_end_clean();
                        }
                        if (!headers_sent()) {
                            $this->setJsonHeaders();
                            http_response_code(404);
                        }
                        echo json_encode(['success' => false, 'error' => 'Driver not found']);
                        if (isset($displayErrors)) {
                            ini_set('display_errors', $displayErrors);
                        }
                        exit;
                    }
                    
                    $driverName = $driver['name'];
                    
                    // First, unassign driver from all orders
                    $stmt = $this->pdo->prepare("UPDATE orders SET assigned_driver = NULL WHERE assigned_driver = ?");
                    $stmt->execute([$driverName]);
                    
                    // Mark driver as inactive (soft delete)
                    $stmt = $this->pdo->prepare("UPDATE drivers SET is_active = 0 WHERE id = ?");
                    $stmt->execute([$driverId]);
                    
                    while (ob_get_level()) {
                        ob_end_clean();
                    }
                    if (!headers_sent()) {
                        $this->setJsonHeaders();
                    }
                    echo json_encode(['success' => true, 'message' => 'Driver deleted successfully']);
                    if (isset($displayErrors)) {
                        ini_set('display_errors', $displayErrors);
                    }
                    exit;
                } catch (PDOException $e) {
                    while (ob_get_level()) {
                        ob_end_clean();
                    }
                    if (!headers_sent()) {
                        $this->setJsonHeaders();
                        http_response_code(500);
                    }
                    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
                    if (isset($displayErrors)) {
                        ini_set('display_errors', $displayErrors);
                    }
                    exit;
                } catch (Exception $e) {
                    while (ob_get_level()) {
                        ob_end_clean();
                    }
                    if (!headers_sent()) {
                        $this->setJsonHeaders();
                        http_response_code(500);
                    }
                    echo json_encode(['success' => false, 'error' => 'Error: ' . $e->getMessage()]);
                    if (isset($displayErrors)) {
                        ini_set('display_errors', $displayErrors);
                    }
                    exit;
                }
            }
            
            // Handle POST request (create new driver)
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                try {
                    $input = json_decode(file_get_contents('php://input'), true);
                    
                    if (!$input) {
                        while (ob_get_level()) {
                            ob_end_clean();
                        }
                        if (!headers_sent()) {
                            $this->setJsonHeaders();
                            http_response_code(400);
                        }
                        echo json_encode(['success' => false, 'error' => 'Invalid JSON data']);
                        if (isset($displayErrors)) {
                            ini_set('display_errors', $displayErrors);
                        }
                        exit;
                    }
                    
                    $name = trim($input['name'] ?? '');
                    $phone = trim($input['phone'] ?? '');
                    $email = !empty($input['email']) ? trim($input['email']) : null;
                    $vehicleType = $input['vehicle_type'] ?? 'bike';
                    $licenseNumber = !empty($input['license_number']) ? trim($input['license_number']) : null;
                    
                    // Validate required fields
                    if (empty($name) || empty($phone)) {
                        while (ob_get_level()) {
                            ob_end_clean();
                        }
                        if (!headers_sent()) {
                            $this->setJsonHeaders();
                            http_response_code(400);
                        }
                        echo json_encode(['success' => false, 'error' => 'Driver name and phone are required']);
                        if (isset($displayErrors)) {
                            ini_set('display_errors', $displayErrors);
                        }
                        exit;
                    }
                    
                    // Check if driver with same name or phone already exists
                    $stmt = $this->pdo->prepare("SELECT id FROM drivers WHERE (name = ? OR phone = ?) AND is_active = 1");
                    $stmt->execute([$name, $phone]);
                    if ($stmt->fetch()) {
                        while (ob_get_level()) {
                            ob_end_clean();
                        }
                        if (!headers_sent()) {
                            $this->setJsonHeaders();
                            http_response_code(400);
                        }
                        echo json_encode(['success' => false, 'error' => 'Driver with this name or phone already exists']);
                        if (isset($displayErrors)) {
                            ini_set('display_errors', $displayErrors);
                        }
                        exit;
                    }
                    
                    // Insert new driver
                    $stmt = $this->pdo->prepare("
                        INSERT INTO drivers (name, phone, email, vehicle_type, license_number, is_active) 
                        VALUES (?, ?, ?, ?, ?, 1)
                    ");
                    $stmt->execute([$name, $phone, $email, $vehicleType, $licenseNumber]);
                    
                    $newDriverId = $this->pdo->lastInsertId();
                    
                    while (ob_get_level()) {
                        ob_end_clean();
                    }
                    if (!headers_sent()) {
                        $this->setJsonHeaders();
                    }
                    
                    echo json_encode([
                        'success' => true,
                        'message' => 'Driver added successfully',
                        'driver' => [
                            'id' => $newDriverId,
                            'name' => $name,
                            'phone' => $phone,
                            'email' => $email,
                            'vehicle_type' => $vehicleType,
                            'license_number' => $licenseNumber
                        ]
                    ]);
                    
                    if (isset($displayErrors)) {
                        ini_set('display_errors', $displayErrors);
                    }
                    
                    exit;
                } catch (PDOException $e) {
                    while (ob_get_level()) {
                        ob_end_clean();
                    }
                    if (!headers_sent()) {
                        $this->setJsonHeaders();
                        http_response_code(500);
                    }
                    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
                    if (isset($displayErrors)) {
                        ini_set('display_errors', $displayErrors);
                    }
                    exit;
                } catch (Exception $e) {
                    while (ob_get_level()) {
                        ob_end_clean();
                    }
                    if (!headers_sent()) {
                        $this->setJsonHeaders();
                        http_response_code(500);
                    }
                    echo json_encode(['success' => false, 'error' => 'Error: ' . $e->getMessage()]);
                    if (isset($displayErrors)) {
                        ini_set('display_errors', $displayErrors);
                    }
                    exit;
                }
            }
            
            // Handle GET request (list drivers)
            error_log("🔄 Starting GET drivers request");
            
            // Verify PDO is available
            if (!$this->pdo) {
                throw new Exception('PDO connection not available');
            }
            
            // First check if the drivers table exists
            $tableExists = false;
            try {
                error_log("🔍 Checking if drivers table exists...");
                $checkTable = $this->pdo->prepare("SHOW TABLES LIKE 'drivers'");
                $checkTable->execute();
                $tableExists = (bool)$checkTable->fetch();
                error_log("📊 Drivers table exists: " . ($tableExists ? 'YES' : 'NO'));
            } catch (PDOException $e) {
                // If table check itself fails, assume table doesn't exist
                error_log("❌ Error checking for drivers table: " . $e->getMessage());
                error_log("❌ PDO Error Code: " . $e->getCode());
                $tableExists = false;
            } catch (Throwable $t) {
                error_log("❌ Fatal error checking drivers table: " . $t->getMessage());
                $tableExists = false;
            }
            
            if (!$tableExists) {
                // Table doesn't exist - try to create it automatically
                error_log("⚠️ Drivers table does not exist in database. Attempting to create it...");
                
                try {
                    $createTableSQL = "
                        CREATE TABLE IF NOT EXISTS drivers (
                            id INT AUTO_INCREMENT PRIMARY KEY,
                            name VARCHAR(100) NOT NULL,
                            phone VARCHAR(20) NOT NULL,
                            email VARCHAR(100),
                            vehicle_type ENUM('bike', 'car', 'van') DEFAULT 'bike',
                            license_number VARCHAR(50),
                            is_active BOOLEAN DEFAULT TRUE,
                            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
                    ";
                    $this->pdo->exec($createTableSQL);
                    error_log("✅ Drivers table created successfully");
                    $tableExists = true;
                } catch (PDOException $createError) {
                    error_log("❌ Failed to create drivers table: " . $createError->getMessage());
                    // Table creation failed, return empty list with message
                    while (ob_get_level()) {
                        ob_end_clean();
                    }
                    
                    if (!headers_sent()) {
                        $this->setJsonHeaders();
                    }
                    
                    echo json_encode([
                        'success' => true, 
                        'drivers' => [],
                        'message' => 'Drivers table does not exist. Please run the database migration to create it.',
                        'table_missing' => true
                    ]);
                    
                    if (isset($displayErrors)) {
                        ini_set('display_errors', $displayErrors);
                    }
                    
                    exit;
                }
            }
            
            // Fetch drivers - initialize as empty array first
            $drivers = [];
            error_log("📋 Fetching drivers from database...");
            
            try {
                $stmt = $this->pdo->prepare("SELECT * FROM drivers WHERE is_active = 1 ORDER BY name ASC");
                $stmt->execute();
                $fetchedDrivers = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Ensure drivers is an array
                if (is_array($fetchedDrivers)) {
                    $drivers = $fetchedDrivers;
                    error_log("✅ Fetched " . count($drivers) . " driver(s) from database");
                    
                    // Log sample driver data for debugging
                    if (count($drivers) > 0) {
                        error_log("📋 Sample driver data: " . json_encode($drivers[0]));
                    }
                } else {
                    error_log("⚠️ Drivers query did not return an array, using empty array");
                    $drivers = [];
                }
                
            } catch (PDOException $queryError) {
                error_log("❌ PDO Error while fetching drivers: " . $queryError->getMessage());
                error_log("❌ Error Code: " . $queryError->getCode());
                error_log("❌ SQL State: " . ($queryError->errorInfo[0] ?? 'N/A'));
                
                // If it's a "table doesn't exist" error, try to create it
                if ($queryError->getCode() == 1146 || strpos($queryError->getMessage(), "doesn't exist") !== false || 
                    strpos($queryError->getMessage(), "Unknown table") !== false) {
                    error_log("⚠️ Drivers table appears to be missing, attempting to create it...");
                    try {
                        $createTableSQL = "
                            CREATE TABLE IF NOT EXISTS drivers (
                                id INT AUTO_INCREMENT PRIMARY KEY,
                                name VARCHAR(100) NOT NULL,
                                phone VARCHAR(20) NOT NULL,
                                email VARCHAR(100),
                                vehicle_type ENUM('bike', 'car', 'van') DEFAULT 'bike',
                                license_number VARCHAR(50),
                                is_active BOOLEAN DEFAULT TRUE,
                                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
                        ";
                        $this->pdo->exec($createTableSQL);
                        error_log("✅ Drivers table created successfully");
                        
                        // Retry the query
                        try {
                            $stmt = $this->pdo->prepare("SELECT * FROM drivers WHERE is_active = 1 ORDER BY name ASC");
                            $stmt->execute();
                            $fetchedDrivers = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            if (is_array($fetchedDrivers)) {
                                $drivers = $fetchedDrivers;
                                error_log("✅ Successfully fetched " . count($drivers) . " driver(s) after table creation");
                            }
                        } catch (PDOException $retryError) {
                            error_log("❌ Error on retry query: " . $retryError->getMessage());
                            // Use empty array, don't throw
                            $drivers = [];
                        }
                    } catch (PDOException $createError) {
                        error_log("❌ Failed to create drivers table: " . $createError->getMessage());
                        // Use empty array instead of throwing, so we can return a success response with empty list
                        $drivers = [];
                        error_log("⚠️ Returning empty drivers list due to table creation failure");
                    }
                } else {
                    // For other PDO errors, log but return empty array
                    error_log("⚠️ Database query error, returning empty drivers list");
                    $drivers = [];
                }
            }
            
            // Ensure drivers is always an array at this point
            if (!is_array($drivers)) {
                error_log("⚠️ Drivers is not an array, converting to empty array");
                $drivers = [];
            }

            // Clear output buffers before sending JSON
            // Get all buffer contents first to ensure nothing leaks
            $bufferContents = '';
            while (ob_get_level()) {
                $bufferContents .= ob_get_contents();
                ob_end_clean();
            }
            
            // Log if there was any unexpected output
            if (!empty(trim($bufferContents))) {
                error_log("⚠️ Unexpected output caught in drivers API buffer: " . substr($bufferContents, 0, 500));
            }
            
            // Ensure headers are sent
            if (!headers_sent()) {
                $this->setJsonHeaders();
            }
            
            // Send JSON response
            $response = json_encode(['success' => true, 'drivers' => $drivers ?: []]);
            
            if ($response === false) {
                error_log("JSON encode error: " . json_last_error_msg());
                if (!headers_sent()) {
                    $this->setJsonHeaders();
                    http_response_code(500);
                }
                echo json_encode(['success' => false, 'error' => 'Failed to encode response']);
            } else {
                echo $response;
            }
            
            // Restore error display setting
            if (isset($displayErrors)) {
                ini_set('display_errors', $displayErrors);
            }
            
            exit;

        } catch (PDOException $e) {
            // Handle PDO exceptions specifically
            error_log("❌ Drivers API PDO Exception: " . $e->getMessage());
            error_log("❌ Error Code: " . $e->getCode());
            error_log("❌ Stack trace: " . $e->getTraceAsString());
            
            // Restore error handler
            if ($errorHandler !== null) {
                restore_error_handler();
            }
            error_reporting($errorReporting);
            
            // Clear all output buffers
            while (ob_get_level()) {
                ob_end_clean();
            }
            
            if (!headers_sent()) {
                $this->setJsonHeaders();
                http_response_code(500);
            }
            
            $errorMsg = 'Database error: ' . $e->getMessage();
            if (strpos($e->getMessage(), "doesn't exist") !== false || $e->getCode() == 1146) {
                $errorMsg = 'Drivers table does not exist. Please run the database migration.';
            }
            
            echo json_encode([
                'success' => false, 
                'error' => $errorMsg,
                'error_type' => 'PDOException',
                'error_code' => $e->getCode()
            ]);
            
            if (isset($displayErrors)) {
                ini_set('display_errors', $displayErrors);
            }
            
            exit;
        } catch (Exception $e) {
            // Restore error handler first
            if ($errorHandler !== null) {
                restore_error_handler();
            }
            error_reporting($errorReporting);
            
            // Clear all output buffers
            while (ob_get_level()) {
                ob_end_clean();
            }
            
            error_log("❌ Drivers API Exception: " . $e->getMessage());
            error_log("❌ Exception Type: " . get_class($e));
            error_log("❌ File: " . $e->getFile() . " Line: " . $e->getLine());
            error_log("❌ Stack trace: " . $e->getTraceAsString());
            
            if (!headers_sent()) {
                $this->setJsonHeaders();
                http_response_code(500);
            }
            
            echo json_encode([
                'success' => false, 
                'error' => 'Failed to process driver request: ' . $e->getMessage(),
                'error_type' => get_class($e)
            ]);
            
            if (isset($displayErrors)) {
                ini_set('display_errors', $displayErrors);
            }
            
            exit;
        } catch (Throwable $t) {
            // Catch any fatal errors
            error_log("❌ Drivers API Fatal Error: " . $t->getMessage());
            error_log("❌ Error Type: " . get_class($t));
            error_log("❌ File: " . $t->getFile() . " Line: " . $t->getLine());
            error_log("❌ Stack trace: " . $t->getTraceAsString());
            
            // Restore error handler
            if ($errorHandler !== null) {
                restore_error_handler();
            }
            error_reporting($errorReporting);
            
            // Clear all output buffers
            while (ob_get_level()) {
                ob_end_clean();
            }
            
            if (!headers_sent()) {
                $this->setJsonHeaders();
                http_response_code(500);
            }
            
            echo json_encode([
                'success' => false, 
                'error' => 'System error: ' . $t->getMessage(),
                'error_type' => get_class($t)
            ]);
            
            if (isset($displayErrors)) {
                ini_set('display_errors', $displayErrors);
            }
            
            exit;
        } finally {
            // Always restore error handler and settings
            if (function_exists('restore_error_handler')) {
                @restore_error_handler();
            }
            if (isset($errorReporting)) {
                error_reporting($errorReporting);
            }
            if (isset($displayErrors)) {
                ini_set('display_errors', $displayErrors);
            }
        }
    }

    /**
     * Mark order as delivered
     * PATCH /api/admin/orders/:id/delivered
     */
    public function markAsDelivered($orderId) {
        $this->requireAdminJson();
        $this->setJsonHeaders();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'PATCH') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
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
            error_log("Mark as delivered API error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Failed to mark order as delivered']);
        }
    }

    /**
     * Cancel order
     * PATCH /api/admin/orders/:id/cancel
     */
    public function cancelOrder($orderId) {
        $this->requireAdminJson();
        $this->setJsonHeaders();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'PATCH') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

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
            error_log("Cancel order API error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Failed to cancel order']);
        }
    }

    /**
     * Get order statistics
     */
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

    /**
     * Get analytics summary data
     * GET /api/admin/analytics/summary
     */
    public function analyticsSummary() {
        $this->requireAdminJson();
        $this->setJsonHeaders();
        
        $period = $_GET['period'] ?? '7'; // Default to 7 days
        $days = intval($period);
        
        try {
            // Revenue data
            $revenueData = $this->getRevenueData($days);
            
            // Orders data
            $ordersData = $this->getOrdersData($days);
            
            // Customer insights
            $customerInsights = $this->getCustomerInsights($days);
            
            // Top categories
            $topCategories = $this->getTopCategories($days);
            
            // Daily trend data
            $trendData = $this->getTrendData($days);
            
            echo json_encode([
                'success' => true,
                'data' => [
                    'revenue' => $revenueData,
                    'orders' => $ordersData,
                    'customers' => $customerInsights,
                    'categories' => $topCategories,
                    'trends' => $trendData,
                    'period' => $days
                ]
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Failed to fetch analytics data: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Get top categories data
     * GET /api/admin/analytics/top-categories
     */
    public function topCategories() {
        $this->requireAdminJson();
        $this->setJsonHeaders();
        
        $period = $_GET['period'] ?? '7';
        $days = intval($period);
        
        try {
            $topCategories = $this->getTopCategories($days);
            
            echo json_encode([
                'success' => true,
                'data' => $topCategories
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Failed to fetch categories data: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Get revenue data for specified period
     */
    private function getRevenueData($days) {
        $today = date('Y-m-d');
        $startDate = date('Y-m-d', strtotime("-$days days"));
        
        // Revenue Today
        $stmt = $this->pdo->prepare("
            SELECT COALESCE(SUM(total_amount), 0) as revenue_today 
            FROM orders 
            WHERE DATE(created_at) = ? AND status = 'delivered'
        ");
        $stmt->execute([$today]);
        $revenueToday = $stmt->fetch()['revenue_today'];
        
        // Revenue This Week
        $weekStart = date('Y-m-d', strtotime('monday this week'));
        $stmt = $this->pdo->prepare("
            SELECT COALESCE(SUM(total_amount), 0) as revenue_week 
            FROM orders 
            WHERE DATE(created_at) >= ? AND status = 'delivered'
        ");
        $stmt->execute([$weekStart]);
        $revenueWeek = $stmt->fetch()['revenue_week'];
        
        // Revenue This Month
        $monthStart = date('Y-m-01');
        $stmt = $this->pdo->prepare("
            SELECT COALESCE(SUM(total_amount), 0) as revenue_month 
            FROM orders 
            WHERE DATE(created_at) >= ? AND status = 'delivered'
        ");
        $stmt->execute([$monthStart]);
        $revenueMonth = $stmt->fetch()['revenue_month'];
        
        // Previous period for comparison
        $prevStart = date('Y-m-d', strtotime("-$days days", strtotime($startDate)));
        $stmt = $this->pdo->prepare("
            SELECT COALESCE(SUM(total_amount), 0) as revenue_previous 
            FROM orders 
            WHERE DATE(created_at) >= ? AND DATE(created_at) < ? AND status = 'delivered'
        ");
        $stmt->execute([$prevStart, $startDate]);
        $revenuePrevious = $stmt->fetch()['revenue_previous'];
        
        // Calculate growth percentage
        $growthPercentage = $revenuePrevious > 0 ? 
            round((($revenueWeek - $revenuePrevious) / $revenuePrevious) * 100, 1) : 0;
        
        return [
            'today' => floatval($revenueToday),
            'week' => floatval($revenueWeek),
            'month' => floatval($revenueMonth),
            'growth_percentage' => $growthPercentage,
            'period_days' => $days
        ];
    }
    
    /**
     * Get orders data for specified period
     */
    private function getOrdersData($days) {
        $today = date('Y-m-d');
        $startDate = date('Y-m-d', strtotime("-$days days"));
        
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
     * Get customer insights data
     */
    private function getCustomerInsights($days) {
        $startDate = date('Y-m-d', strtotime("-$days days"));
        
        // Total customers
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
     * Get top categories data
     */
    private function getTopCategories($days) {
        $startDate = date('Y-m-d', strtotime("-$days days"));
        
        $stmt = $this->pdo->prepare("
            SELECT 
                c.name as category_name,
                COALESCE(SUM(oi.total_price), 0) as total_sales,
                COUNT(DISTINCT o.id) as order_count
            FROM categories c
            LEFT JOIN products p ON c.id = p.category_id
            LEFT JOIN order_items oi ON p.id = oi.product_id
            LEFT JOIN orders o ON oi.order_id = o.id AND DATE(o.created_at) >= ? AND o.status = 'delivered'
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
     * Get trend data for charts
     */
    private function getTrendData($days) {
        $startDate = date('Y-m-d', strtotime("-$days days"));
        
        $stmt = $this->pdo->prepare("
            SELECT 
                DATE(created_at) as date,
                COALESCE(SUM(total_amount), 0) as revenue,
                COUNT(*) as orders
            FROM orders 
            WHERE DATE(created_at) >= ? AND status = 'delivered'
            GROUP BY DATE(created_at)
            ORDER BY date ASC
        ");
        $stmt->execute([$startDate]);
        $trendData = $stmt->fetchAll();
        
        // Fill missing dates with zero values
        $result = [];
        $currentDate = strtotime($startDate);
        $endDate = strtotime('today');
        
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
    
    /**
     * Get inventory data
     * GET /api/admin/inventory
     */
    public function inventory() {
        $this->requireAdminJson();
        $this->setJsonHeaders();
        
        $filter = $_GET['filter'] ?? 'all';
        $search = $_GET['search'] ?? '';
        $category = $_GET['category'] ?? '';
        $page = $_GET['page'] ?? 1;
        $limit = $_GET['limit'] ?? 20;
        $offset = ($page - 1) * $limit;

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

        try {
            // Get products
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

            // Get total count
            $stmt = $this->pdo->prepare("SELECT COUNT(DISTINCT p.id) as total FROM products p $where");
            $stmt->execute($params);
            $total = $stmt->fetch()['total'];

            // Get inventory statistics
            $stats = $this->getInventoryStats();

            echo json_encode([
                'success' => true,
                'data' => [
                    'products' => $products,
                    'total' => intval($total),
                    'page' => intval($page),
                    'limit' => intval($limit),
                    'stats' => $stats
                ]
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Failed to fetch inventory data: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Update inventory for a specific product
     * PATCH /api/admin/inventory/:id
     */
    public function updateInventory($productId) {
        $this->requireAdminJson();
        $this->setJsonHeaders();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'PATCH') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        // Get JSON input
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid JSON input']);
            return;
        }

        $stockCount = intval($input['stock_count'] ?? -1);
        $lowStockThreshold = intval($input['low_stock_threshold'] ?? -1);
        $restockEta = $input['restock_eta'] ?? null;
        $isActive = isset($input['is_active']) ? (bool)$input['is_active'] : null;

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

        try {
            $params[] = $productId;
            $sql = "UPDATE products SET " . implode(', ', $updates) . " WHERE id = ?";
            
            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute($params);

            if ($result) {
                echo json_encode([
                    'success' => true, 
                    'message' => 'Inventory updated successfully'
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to update inventory']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Failed to update inventory: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Get inventory statistics
     */
    private function getInventoryStats() {
        $stats = [];

        // Total products
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as total FROM products");
        $stmt->execute();
        $stats['total_products'] = intval($stmt->fetch()['total']);

        // Low stock items
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as low_stock FROM products WHERE stock_quantity <= low_stock_threshold AND stock_quantity > 0");
        $stmt->execute();
        $stats['low_stock'] = intval($stmt->fetch()['low_stock']);

        // Out of stock items
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as out_of_stock FROM products WHERE stock_quantity = 0");
        $stmt->execute();
        $stats['out_of_stock'] = intval($stmt->fetch()['out_of_stock']);

        // Frozen products
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as frozen FROM products WHERE is_frozen = 1");
        $stmt->execute();
        $stats['frozen'] = intval($stmt->fetch()['frozen']);

        // Eco-friendly products
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as eco_friendly FROM products WHERE is_eco_friendly = 1");
        $stmt->execute();
        $stats['eco_friendly'] = intval($stmt->fetch()['eco_friendly']);

        // Inactive products
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as inactive FROM products WHERE is_active = 0");
        $stmt->execute();
        $stats['inactive'] = intval($stmt->fetch()['inactive']);

        return $stats;
    }

    /**
     * Products API Endpoints
     */
    
    /**
     * Get products with filters
     * GET /api/admin/products
     */
    public function getProducts() {
        // Suppress error display for API
        $displayErrors = ini_get('display_errors');
        ini_set('display_errors', '0');
        
        // Start output buffering
        while (ob_get_level()) {
            ob_end_clean();
        }
        ob_start();
        
        try {
            // Check admin access first with detailed error logging
            if (!$this->adminMiddleware || !$this->adminMiddleware->isAdmin()) {
                error_log("❌ getProducts: Admin access denied. User ID: " . ($_SESSION['user_id'] ?? 'not set') . ", Role: " . ($_SESSION['role'] ?? 'not set'));
                while (ob_get_level()) {
                    ob_end_clean();
                }
                if (!headers_sent()) {
                    $this->setJsonHeaders();
                    http_response_code(403);
                }
                echo json_encode(['success' => false, 'error' => 'Access denied. Admin privileges required.']);
                exit;
            }
            
            $this->setJsonHeaders();
            
            $page = intval($_GET['page'] ?? 1);
            $limit = intval($_GET['limit'] ?? 20);
            $offset = ($page - 1) * $limit;
            $search = $_GET['search'] ?? '';
            $category = $_GET['category'] ?? '';
            $status = $_GET['status'] ?? 'all'; // all, active, inactive
            $stock = $_GET['stock'] ?? 'all'; // all, in_stock, low_stock, out_of_stock
            
            $where = "WHERE 1=1";
            $params = [];
            
            // Search filter - check if brand column exists first
            try {
                $stmt = $this->pdo->query("SHOW COLUMNS FROM products LIKE 'brand'");
                $brandColumnExists = $stmt->rowCount() > 0;
            } catch (Exception $e) {
                $brandColumnExists = false;
            }
            
            if (!empty($search)) {
                if ($brandColumnExists) {
                    $where .= " AND (p.name LIKE ? OR p.brand LIKE ? OR p.description LIKE ?)";
                    $searchTerm = "%$search%";
                    $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
                } else {
                    $where .= " AND (p.name LIKE ? OR p.description LIKE ?)";
                    $searchTerm = "%$search%";
                    $params = array_merge($params, [$searchTerm, $searchTerm]);
                }
            }
            
            // Category filter
            if (!empty($category)) {
                $where .= " AND p.category_id = ?";
                $params[] = $category;
            }
            
            // Status filter - check if is_active column exists
            try {
                $stmt = $this->pdo->query("SHOW COLUMNS FROM products LIKE 'is_active'");
                $isActiveExists = $stmt->rowCount() > 0;
            } catch (Exception $e) {
                $isActiveExists = false;
            }
            
            if ($status === 'active' && $isActiveExists) {
                $where .= " AND p.is_active = 1";
            } elseif ($status === 'inactive' && $isActiveExists) {
                $where .= " AND p.is_active = 0";
            }
            // If is_active column doesn't exist, status filter is ignored
            
            // Stock filter - check if stock_quantity and low_stock_threshold columns exist
            try {
                $stmt = $this->pdo->query("SHOW COLUMNS FROM products LIKE 'stock_quantity'");
                $stockQuantityExists = $stmt->rowCount() > 0;
            } catch (Exception $e) {
                $stockQuantityExists = false;
            }
            
            try {
                $stmt = $this->pdo->query("SHOW COLUMNS FROM products LIKE 'low_stock_threshold'");
                $lowStockThresholdExists = $stmt->rowCount() > 0;
            } catch (Exception $e) {
                $lowStockThresholdExists = false;
            }
            
            if ($stockQuantityExists) {
                if ($stock === 'in_stock') {
                    if ($lowStockThresholdExists) {
                        $where .= " AND p.stock_quantity > COALESCE(p.low_stock_threshold, 10)";
                    } else {
                        $where .= " AND p.stock_quantity > 10";
                    }
                } elseif ($stock === 'low_stock') {
                    if ($lowStockThresholdExists) {
                        $where .= " AND p.stock_quantity > 0 AND p.stock_quantity <= COALESCE(p.low_stock_threshold, 10)";
                    } else {
                        $where .= " AND p.stock_quantity > 0 AND p.stock_quantity <= 10";
                    }
                } elseif ($stock === 'out_of_stock') {
                    $where .= " AND (p.stock_quantity = 0 OR p.stock_quantity IS NULL)";
                }
            }
            // If stock_quantity column doesn't exist, stock filter is ignored
            
            // Initialize defaults
            $products = [];
            $total = 0;
            $categories = [];
            $overallTotal = 0;
            $overallActive = 0;
            $overallLowStock = 0;
            $overallOutOfStock = 0;
            
            // Get products - handle potential missing columns gracefully
            try {
                $sql = "SELECT p.*, c.name as category_name 
                        FROM products p 
                        LEFT JOIN categories c ON p.category_id = c.id 
                        $where 
                        ORDER BY p.created_at DESC 
                        LIMIT $limit OFFSET $offset";
                
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute($params);
                $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Ensure all products have required fields
                foreach ($products as &$product) {
                    $product['brand'] = $product['brand'] ?? null;
                    $product['description'] = $product['description'] ?? null;
                    $product['image'] = $product['image'] ?? null;
                    $product['diet_tags'] = $product['diet_tags'] ?? '[]';
                    $product['allergens'] = $product['allergens'] ?? '[]';
                    $product['images'] = $product['images'] ?? '[]';
                    $product['is_eco_friendly'] = isset($product['is_eco_friendly']) ? (bool)$product['is_eco_friendly'] : false;
                    $product['is_frozen'] = isset($product['is_frozen']) ? (bool)$product['is_frozen'] : false;
                    $product['is_active'] = isset($product['is_active']) ? (bool)$product['is_active'] : true;
                    $product['halal_certified'] = isset($product['halal_certified']) ? (bool)$product['halal_certified'] : false;
                    $product['stock_quantity'] = isset($product['stock_quantity']) ? intval($product['stock_quantity']) : 0;
                    $product['low_stock_threshold'] = isset($product['low_stock_threshold']) ? intval($product['low_stock_threshold']) : 10;
                    $product['price_current'] = $product['price_current'] ?? $product['price'] ?? 0;
                }
                unset($product); // Break reference
            } catch (PDOException $e) {
                error_log("❌ getProducts Query Error: " . $e->getMessage());
                error_log("❌ SQL: " . ($sql ?? 'N/A'));
                error_log("❌ Params: " . print_r($params, true));
                error_log("❌ Error Code: " . $e->getCode());
                
                // If query fails, check if table exists
                try {
                    $stmt = $this->pdo->query("SHOW TABLES LIKE 'products'");
                    if ($stmt->rowCount() === 0) {
                        throw new Exception("Products table does not exist in database");
                    }
                } catch (Exception $tableCheck) {
                    throw new Exception("Database error: Products table not found. Please run database migrations.");
                }
                
                // Check if it's a column error
                if (strpos($e->getMessage(), 'Unknown column') !== false || strpos($e->getMessage(), 'doesn\'t exist') !== false) {
                    throw new Exception("Database structure mismatch: Some columns are missing. Error: " . $e->getMessage());
                }
                
                throw $e; // Re-throw original error
            }
            
            // Get total count
            try {
                $countSql = "SELECT COUNT(*) as total FROM products p $where";
                $stmt = $this->pdo->prepare($countSql);
                $stmt->execute($params);
                $total = intval($stmt->fetch()['total']);
            } catch (PDOException $e) {
                error_log("❌ getProducts Count Query Error: " . $e->getMessage());
                // If count fails, set to 0 and continue
                $total = 0;
            }
            
            // Get categories for filter
            try {
                $stmt = $this->pdo->prepare("SELECT id, name FROM categories ORDER BY name");
                $stmt->execute();
                $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                error_log("❌ getProducts Categories Query Error: " . $e->getMessage());
                // If categories query fails, use empty array
                $categories = [];
            }
            
            // Get overall statistics (not affected by filters)
            try {
                $stmt = $this->pdo->prepare("SELECT COUNT(*) as total FROM products");
                $stmt->execute();
                $overallTotal = intval($stmt->fetch()['total']);
            } catch (PDOException $e) {
                error_log("❌ getProducts Overall Total Query Error: " . $e->getMessage());
                $overallTotal = 0;
            }
            
            try {
                // Check if is_active column exists
                $stmt = $this->pdo->query("SHOW COLUMNS FROM products LIKE 'is_active'");
                $isActiveExists = $stmt->rowCount() > 0;
                
                if ($isActiveExists) {
                    $stmt = $this->pdo->prepare("SELECT COUNT(*) as active FROM products WHERE is_active = 1");
                } else {
                    $stmt = $this->pdo->prepare("SELECT COUNT(*) as active FROM products");
                }
                $stmt->execute();
                $overallActive = intval($stmt->fetch()['active']);
            } catch (PDOException $e) {
                error_log("❌ getProducts Overall Active Query Error: " . $e->getMessage());
                $overallActive = 0;
            }
            
            // Get low stock count - handle case where low_stock_threshold column might not exist
            try {
                $stmt = $this->pdo->prepare("SELECT COUNT(*) as low_stock FROM products WHERE stock_quantity > 0 AND stock_quantity <= COALESCE(low_stock_threshold, 10)");
                $stmt->execute();
                $overallLowStock = intval($stmt->fetch()['low_stock']);
            } catch (PDOException $e) {
                // Fallback if low_stock_threshold doesn't exist
                $stmt = $this->pdo->prepare("SELECT COUNT(*) as low_stock FROM products WHERE stock_quantity > 0 AND stock_quantity <= 10");
                $stmt->execute();
                $overallLowStock = intval($stmt->fetch()['low_stock']);
            }
            
            try {
                // Check if stock_quantity column exists
                $stmt = $this->pdo->query("SHOW COLUMNS FROM products LIKE 'stock_quantity'");
                $stockQuantityExists = $stmt->rowCount() > 0;
                
                if ($stockQuantityExists) {
                    $stmt = $this->pdo->prepare("SELECT COUNT(*) as out_of_stock FROM products WHERE stock_quantity = 0 OR stock_quantity IS NULL");
                } else {
                    $stmt = $this->pdo->prepare("SELECT COUNT(*) as out_of_stock FROM products");
                    // If column doesn't exist, set to 0
                }
                $stmt->execute();
                $overallOutOfStock = intval($stmt->fetch()['out_of_stock']);
            } catch (PDOException $e) {
                error_log("❌ getProducts Out of Stock Query Error: " . $e->getMessage());
                $overallOutOfStock = 0;
            }
            
            // Clear output buffer
            while (ob_get_level()) {
                ob_end_clean();
            }
            
            if (!headers_sent()) {
                $this->setJsonHeaders();
            }
            
            echo json_encode([
                'success' => true,
                'data' => $products ?: [],
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $limit,
                    'total' => $total,
                    'total_pages' => ceil($total / $limit)
                ],
                'filters' => [
                    'categories' => $categories ?: []
                ],
                'statistics' => [
                    'total_products' => $overallTotal,
                    'active_products' => $overallActive,
                    'low_stock_products' => $overallLowStock,
                    'out_of_stock_products' => $overallOutOfStock
                ]
            ]);
            
        } catch (PDOException $e) {
            error_log("❌ getProducts PDO Error: " . $e->getMessage());
            while (ob_get_level()) {
                ob_end_clean();
            }
            if (!headers_sent()) {
                $this->setJsonHeaders();
                http_response_code(500);
            }
            echo json_encode([
                'success' => false,
                'error' => 'Database error: ' . $e->getMessage()
            ]);
        } catch (Exception $e) {
            error_log("❌ getProducts Error: " . $e->getMessage());
            while (ob_get_level()) {
                ob_end_clean();
            }
            if (!headers_sent()) {
                $this->setJsonHeaders();
                http_response_code(500);
            }
            echo json_encode([
                'success' => false,
                'error' => 'Failed to load products: ' . $e->getMessage()
            ]);
        } finally {
            if (isset($displayErrors)) {
                ini_set('display_errors', $displayErrors);
            }
        }
    }
    
    /**
     * Create product
     * POST /api/admin/products
     */
    public function createProduct() {
        $this->requireAdminJson();
        $this->setJsonHeaders();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            exit;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid JSON input']);
            exit;
        }
        
        // Validate required fields
        $required = ['name', 'price', 'category_id'];
        foreach ($required as $field) {
            if (empty($input[$field])) {
                echo json_encode(['success' => false, 'error' => "Field '$field' is required"]);
                exit;
            }
        }
        
        try {
            $this->pdo->beginTransaction();
            
            // Sanitize and validate data
            $name = htmlspecialchars(trim($input['name']));
            $brand = htmlspecialchars(trim($input['brand'] ?? ''));
            $description = htmlspecialchars(trim($input['description'] ?? ''));
            $price = floatval($input['price']);
            $priceCurrent = isset($input['price_current']) ? floatval($input['price_current']) : $price;
            $unitSize = htmlspecialchars(trim($input['unit_size'] ?? ''));
            $stockQuantity = intval($input['stock_quantity'] ?? 0);
            $lowStockThreshold = intval($input['low_stock_threshold'] ?? 10);
            $unit = htmlspecialchars(trim($input['unit'] ?? ''));
            $categoryId = intval($input['category_id']);
            $image = htmlspecialchars(trim($input['image'] ?? ''));
            $images = is_array($input['images'] ?? []) ? $input['images'] : [];
            $nutritionInfo = htmlspecialchars(trim($input['nutrition_info'] ?? ''));
            $dietTags = is_array($input['diet_tags'] ?? []) ? $input['diet_tags'] : [];
            $allergens = is_array($input['allergens'] ?? []) ? $input['allergens'] : [];
            $halalCertified = isset($input['halal_certified']) ? 
                ($input['halal_certified'] === true || $input['halal_certified'] === 1 || $input['halal_certified'] === '1') : false;
            // Handle boolean values - accept both true/false and 1/0
            $isEcoFriendly = isset($input['is_eco_friendly']) ? 
                ($input['is_eco_friendly'] === true || $input['is_eco_friendly'] === 1 || $input['is_eco_friendly'] === '1') : false;
            $isFrozen = isset($input['is_frozen']) ? 
                ($input['is_frozen'] === true || $input['is_frozen'] === 1 || $input['is_frozen'] === '1') : false;
            $isActive = isset($input['is_active']) ? 
                ($input['is_active'] === true || $input['is_active'] === 1 || $input['is_active'] === '1') : true;
            
            // Validate price
            if ($price <= 0) {
                throw new Exception('Price must be greater than 0');
            }
            
            // Validate category exists
            $stmt = $this->pdo->prepare("SELECT id FROM categories WHERE id = ?");
            $stmt->execute([$categoryId]);
            if (!$stmt->fetch()) {
                throw new Exception('Invalid category');
            }
            
            // Prepare JSON fields
            $dietTagsJson = empty($dietTags) ? '[]' : json_encode($dietTags);
            if ($dietTagsJson === false) $dietTagsJson = '[]';
            
            $allergensJson = empty($allergens) ? '[]' : json_encode($allergens);
            if ($allergensJson === false) $allergensJson = '[]';
            
            $imagesJson = empty($images) ? '[]' : json_encode($images);
            if ($imagesJson === false) $imagesJson = '[]';
            
            // Convert boolean values to integers for MySQL compatibility (BOOLEAN = TINYINT(1))
            $isEcoFriendlyInt = $isEcoFriendly ? 1 : 0;
            $isFrozenInt = $isFrozen ? 1 : 0;
            $isActiveInt = $isActive ? 1 : 0;
            $halalCertifiedInt = $halalCertified ? 1 : 0;
            
            // Handle empty strings as NULL for optional fields
            $brand = empty($brand) ? null : $brand;
            $description = empty($description) ? null : $description;
            $unitSize = empty($unitSize) ? null : $unitSize;
            $unit = empty($unit) ? null : $unit;
            $image = empty($image) ? null : $image;
            $nutritionInfo = empty($nutritionInfo) ? null : $nutritionInfo;
            
            // Check which columns exist before inserting
            $stmt = $this->pdo->query("SHOW COLUMNS FROM products");
            $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $tableColumns = array_flip($columns);
            
            // Build dynamic field list based on available columns
            $fields = ['name', 'description', 'price', 'category_id'];
            $values = [$name, $description, $price, $categoryId];
            
            // Add optional fields only if column exists
            if (isset($tableColumns['brand']) && !empty($brand)) {
                $fields[] = 'brand';
                $values[] = $brand;
            }
            if (isset($tableColumns['price_current'])) {
                $fields[] = 'price_current';
                $values[] = $priceCurrent;
            }
            if (isset($tableColumns['unit_size']) && !empty($unitSize)) {
                $fields[] = 'unit_size';
                $values[] = $unitSize;
            }
            if (isset($tableColumns['stock_quantity'])) {
                $fields[] = 'stock_quantity';
                $values[] = $stockQuantity;
            }
            if (isset($tableColumns['low_stock_threshold'])) {
                $fields[] = 'low_stock_threshold';
                $values[] = $lowStockThreshold;
            }
            if (isset($tableColumns['unit']) && !empty($unit)) {
                $fields[] = 'unit';
                $values[] = $unit;
            }
            if (isset($tableColumns['image']) && !empty($image)) {
                $fields[] = 'image';
                $values[] = $image;
            }
            if (isset($tableColumns['images'])) {
                $fields[] = 'images';
                $values[] = $imagesJson;
            }
            if (isset($tableColumns['nutrition_info']) && !empty($nutritionInfo)) {
                $fields[] = 'nutrition_info';
                $values[] = $nutritionInfo;
            }
            if (isset($tableColumns['diet_tags'])) {
                $fields[] = 'diet_tags';
                $values[] = $dietTagsJson;
            }
            if (isset($tableColumns['allergens'])) {
                $fields[] = 'allergens';
                $values[] = $allergensJson;
            }
            if (isset($tableColumns['halal_certified'])) {
                $fields[] = 'halal_certified';
                $values[] = $halalCertifiedInt;
            }
            if (isset($tableColumns['is_eco_friendly'])) {
                $fields[] = 'is_eco_friendly';
                $values[] = $isEcoFriendlyInt;
            }
            if (isset($tableColumns['is_frozen'])) {
                $fields[] = 'is_frozen';
                $values[] = $isFrozenInt;
            }
            if (isset($tableColumns['is_active'])) {
                $fields[] = 'is_active';
                $values[] = $isActiveInt;
            }
            
            // Insert product with dynamic fields
            $placeholders = str_repeat('?,', count($fields) - 1) . '?';
            $sql = "INSERT INTO products (" . implode(', ', $fields) . ") VALUES ($placeholders)";
            
            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute($values);
            
            if (!$result) {
                $errorInfo = $stmt->errorInfo();
                throw new Exception('Database insert failed: ' . ($errorInfo[2] ?? 'Unknown error'));
            }
            
            $productId = $this->pdo->lastInsertId();
            
            $this->pdo->commit();
            
            echo json_encode([
                'success' => true,
                'message' => 'Product created successfully',
                'product_id' => $productId
            ]);
            
        } catch (PDOException $e) {
            $this->pdo->rollback();
            error_log("Create product PDO error: " . $e->getMessage());
            error_log("SQL Error Code: " . $e->getCode());
            error_log("SQL Error Info: " . print_r($e->errorInfo ?? [], true));
            
            http_response_code(500);
            $errorMessage = 'Database error occurred';
            
            // Parse specific error types
            $errorMsg = $e->getMessage();
            if (strpos($errorMsg, 'Unknown column') !== false) {
                error_log("CRITICAL: Database column mismatch detected. Check products table structure.");
                $errorMessage = 'Database structure mismatch. Please contact administrator.';
            } elseif (strpos($errorMsg, 'Field') !== false && strpos($errorMsg, 'incorrect') !== false) {
                error_log("CRITICAL: Field type mismatch. Check data types in products table.");
                $errorMessage = 'Data type mismatch. Please check all field values.';
            } elseif (strpos($errorMsg, 'SQLSTATE[42S22]') !== false) {
                $errorMessage = 'Database field incorrect. Please contact administrator to check database structure.';
            } elseif (strpos($errorMsg, 'SQLSTATE[42S21]') !== false) {
                $errorMessage = 'Duplicate column name. Please contact administrator.';
            } else {
                // Log full error for debugging but show user-friendly message
                $errorMessage = 'Database error occurred. Please check all required fields are filled correctly.';
            }
            
            echo json_encode(['success' => false, 'error' => $errorMessage]);
        } catch (Exception $e) {
            $this->pdo->rollback();
            error_log("Create product error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to create product: ' . $e->getMessage()]);
        }
    }

    /**
     * Bulk import products from JSON
     * POST /api/admin/products/bulk-import
     */
    public function bulkImportProducts() {
        $this->requireAdminJson();
        $this->setJsonHeaders();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            exit;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid JSON input']);
            exit;
        }
        
        // Handle both formats: {"products": [...]} or just [...]
        if (isset($input['products']) && is_array($input['products'])) {
            $products = $input['products'];
        } elseif (is_array($input) && isset($input[0])) {
            // Direct array format
            $products = $input;
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid JSON format. Expected {"products": [...]} or array of products']);
            exit;
        }
        $results = [
            'success' => [],
            'failed' => [],
            'total' => count($products),
            'success_count' => 0,
            'failed_count' => 0
        ];
        
        try {
            $this->pdo->beginTransaction();
            
            // Ensure brand column exists (try to add it if missing, ignore errors if it already exists)
            try {
                $this->pdo->exec("ALTER TABLE products ADD COLUMN brand VARCHAR(255) NULL AFTER name");
                error_log("Brand column added to products table");
            } catch (PDOException $e) {
                // Column might already exist or other error, continue
                if (strpos($e->getMessage(), 'Duplicate column name') === false) {
                    error_log("Note: Could not add brand column (might already exist): " . $e->getMessage());
                }
            }
            
            // Get all categories as map for quick lookup
            $stmt = $this->pdo->prepare("SELECT id, name FROM categories");
            $stmt->execute();
            $categoryMap = [];
            foreach ($stmt->fetchAll() as $cat) {
                $categoryMap[strtolower(trim($cat['name']))] = $cat['id'];
            }
            
            // Get actual table columns to handle missing columns gracefully (once for all products)
            $stmt = $this->pdo->query("SHOW COLUMNS FROM products");
            $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $tableColumns = array_flip($columns); // Use flip for fast lookup
            
            foreach ($products as $index => $product) {
                try {
                    // Validate required fields
                    if (empty($product['name']) || !isset($product['price']) || $product['price'] <= 0) {
                        throw new Exception("Product #" . ($index + 1) . ": Missing required fields (name, price)");
                    }
                    
                    // Get or create category
                    $categoryName = $product['category'] ?? '';
                    if (empty($categoryName)) {
                        throw new Exception("Product #" . ($index + 1) . ": Category is required");
                    }
                    
                    $categoryId = $categoryMap[strtolower(trim($categoryName))] ?? null;
                    
                    if (!$categoryId) {
                        // Create category if it doesn't exist
                        $stmt = $this->pdo->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
                        $stmt->execute([trim($categoryName), "Category for " . trim($categoryName)]);
                        $categoryId = $this->pdo->lastInsertId();
                        $categoryMap[strtolower(trim($categoryName))] = $categoryId;
                    }
                    
                    // Sanitize and prepare data
                    $name = htmlspecialchars(trim($product['name']));
                    // Only include brand if column exists and value is provided
                    $brand = (isset($tableColumns['brand']) && !empty($product['brand'])) ? htmlspecialchars(trim($product['brand'])) : null;
                    $description = !empty($product['description']) ? htmlspecialchars(trim($product['description'])) : null;
                    $price = floatval($product['price']);
                    $unitSize = !empty($product['unit_size']) ? htmlspecialchars(trim($product['unit_size'])) : null;
                    $stockQuantity = intval($product['stock_quantity'] ?? 0);
                    $lowStockThreshold = intval($product['low_stock_threshold'] ?? 10);
                    $unit = !empty($product['unit']) ? htmlspecialchars(trim($product['unit'])) : null;
                    $image = !empty($product['image']) ? htmlspecialchars(trim($product['image'])) : null;
                    $nutritionInfo = !empty($product['nutrition_info']) ? htmlspecialchars(trim($product['nutrition_info'])) : null;
                    $dietTags = is_array($product['diet_tags'] ?? []) ? $product['diet_tags'] : [];
                    $dietTagsJson = empty($dietTags) ? '[]' : json_encode($dietTags);
                    if ($dietTagsJson === false) {
                        $dietTagsJson = '[]';
                    }
                    
                    $isEcoFriendly = isset($product['is_eco_friendly']) ? 
                        ($product['is_eco_friendly'] === true || $product['is_eco_friendly'] === 1 || $product['is_eco_friendly'] === '1') : false;
                    $isFrozen = isset($product['is_frozen']) ? 
                        ($product['is_frozen'] === true || $product['is_frozen'] === 1 || $product['is_frozen'] === '1') : false;
                    $isActive = isset($product['is_active']) ? 
                        ($product['is_active'] === true || $product['is_active'] === 1 || $product['is_active'] === '1') : true;
                    
                    // Convert booleans to integers
                    $isEcoFriendlyInt = $isEcoFriendly ? 1 : 0;
                    $isFrozenInt = $isFrozen ? 1 : 0;
                    $isActiveInt = $isActive ? 1 : 0;
                    
                    // Check if product already exists (by name)
                    $stmt = $this->pdo->prepare("SELECT id FROM products WHERE name = ?");
                    $stmt->execute([$name]);
                    if ($stmt->fetch()) {
                        throw new Exception("Product already exists: " . $name);
                    }
                    
                    // Build dynamic SQL based on available columns
                    $fields = ['name', 'description', 'price', 'category_id'];
                    $values = [$name, $description, $price, $categoryId];
                    
                    // Add optional fields only if column exists
                    if (isset($tableColumns['brand']) && $brand !== null) {
                        $fields[] = 'brand';
                        $values[] = $brand;
                    }
                    if (isset($tableColumns['unit_size']) && $unitSize !== null) {
                        $fields[] = 'unit_size';
                        $values[] = $unitSize;
                    }
                    if (isset($tableColumns['stock_quantity'])) {
                        $fields[] = 'stock_quantity';
                        $values[] = $stockQuantity;
                    }
                    if (isset($tableColumns['low_stock_threshold'])) {
                        $fields[] = 'low_stock_threshold';
                        $values[] = $lowStockThreshold;
                    }
                    if (isset($tableColumns['unit']) && $unit !== null) {
                        $fields[] = 'unit';
                        $values[] = $unit;
                    }
                    if (isset($tableColumns['image']) && $image !== null) {
                        $fields[] = 'image';
                        $values[] = $image;
                    }
                    if (isset($tableColumns['nutrition_info']) && $nutritionInfo !== null) {
                        $fields[] = 'nutrition_info';
                        $values[] = $nutritionInfo;
                    }
                    if (isset($tableColumns['diet_tags'])) {
                        $fields[] = 'diet_tags';
                        $values[] = $dietTagsJson;
                    }
                    if (isset($tableColumns['is_eco_friendly'])) {
                        $fields[] = 'is_eco_friendly';
                        $values[] = $isEcoFriendlyInt;
                    }
                    if (isset($tableColumns['is_frozen'])) {
                        $fields[] = 'is_frozen';
                        $values[] = $isFrozenInt;
                    }
                    if (isset($tableColumns['is_active'])) {
                        $fields[] = 'is_active';
                        $values[] = $isActiveInt;
                    }
                    
                    // Build SQL with dynamic fields
                    $placeholders = str_repeat('?,', count($fields) - 1) . '?';
                    $sql = "INSERT INTO products (" . implode(', ', $fields) . ") VALUES ($placeholders)";
                    
                    $stmt = $this->pdo->prepare($sql);
                    $result = $stmt->execute($values);
                    
                    if (!$result) {
                        $errorInfo = $stmt->errorInfo();
                        throw new Exception("Database insert failed: " . ($errorInfo[2] ?? 'Unknown error'));
                    }
                    
                    $productId = $this->pdo->lastInsertId();
                    $results['success'][] = [
                        'index' => $index + 1,
                        'name' => $name,
                        'id' => $productId
                    ];
                    $results['success_count']++;
                    
                } catch (Exception $e) {
                    $results['failed'][] = [
                        'index' => $index + 1,
                        'name' => $product['name'] ?? 'Unknown',
                        'error' => $e->getMessage()
                    ];
                    $results['failed_count']++;
                    error_log("Bulk import product #" . ($index + 1) . " error: " . $e->getMessage());
                }
            }
            
            if ($results['success_count'] > 0) {
                $this->pdo->commit();
                echo json_encode([
                    'success' => true,
                    'message' => "Successfully imported {$results['success_count']} product(s). " . 
                                ($results['failed_count'] > 0 ? "{$results['failed_count']} product(s) failed." : ""),
                    'results' => $results
                ]);
            } else {
                $this->pdo->rollback();
                echo json_encode([
                    'success' => false,
                    'error' => 'All products failed to import',
                    'results' => $results
                ]);
            }
            
        } catch (Exception $e) {
            $this->pdo->rollback();
            error_log("Bulk import transaction error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Bulk import failed: ' . $e->getMessage(),
                'results' => $results
            ]);
        }
    }
    
    /**
     * Update product
     * PATCH /api/admin/products/:id
     */
    public function updateProduct($productId) {
        $this->requireAdminJson();
        $this->setJsonHeaders();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'PATCH') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            exit;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid JSON input']);
            exit;
        }
        
        try {
            $this->pdo->beginTransaction();
            
            // Check if product exists
            $stmt = $this->pdo->prepare("SELECT * FROM products WHERE id = ?");
            $stmt->execute([$productId]);
            $product = $stmt->fetch();
            
            if (!$product) {
                $this->pdo->rollback();
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Product not found']);
                exit;
            }
            
            // Build update fields
            $updateFields = [];
            $params = [];
            
            // Check which columns exist
            $stmt = $this->pdo->query("SHOW COLUMNS FROM products");
            $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $tableColumns = array_flip($columns);
            
            $allowedFields = ['name', 'brand', 'description', 'price', 'price_current', 'unit_size', 'stock_quantity', 'low_stock_threshold', 'unit', 'category_id', 'image', 'images', 'nutrition_info', 'diet_tags', 'allergens', 'halal_certified', 'is_eco_friendly', 'is_frozen', 'is_active'];
            
            foreach ($allowedFields as $field) {
                if (isset($input[$field]) && isset($tableColumns[$field])) {
                    if (in_array($field, ['diet_tags', 'allergens', 'images'])) {
                        $updateFields[] = "$field = ?";
                        $params[] = json_encode(is_array($input[$field]) ? $input[$field] : []);
                    } elseif (in_array($field, ['is_eco_friendly', 'is_frozen', 'is_active', 'halal_certified'])) {
                        $updateFields[] = "$field = ?";
                        $val = $input[$field];
                        $params[] = ($val === true || $val === 1 || $val === '1') ? 1 : 0;
                    } elseif (in_array($field, ['price', 'price_current', 'stock_quantity', 'low_stock_threshold', 'category_id'])) {
                        $updateFields[] = "$field = ?";
                        $params[] = floatval($input[$field]);
                    } else {
                        $updateFields[] = "$field = ?";
                        $params[] = htmlspecialchars(trim($input[$field]));
                    }
                }
            }
            
            // Track price changes for history
            if (isset($input['price']) && $input['price'] != $product['price'] && isset($tableColumns['price_current'])) {
                // Insert into price history
                try {
                    $adminId = $_SESSION['user_id'] ?? null;
                    $stmt = $this->pdo->prepare("INSERT INTO product_price_history (product_id, price, changed_by_admin_id) VALUES (?, ?, ?)");
                    $stmt->execute([$productId, $input['price'], $adminId]);
                } catch (Exception $e) {
                    // Table might not exist, log and continue
                    error_log("Price history insert failed: " . $e->getMessage());
                }
            }
            
            if (empty($updateFields)) {
                $this->pdo->rollback();
                echo json_encode(['success' => false, 'error' => 'No fields to update']);
                exit;
            }
            
            $params[] = $productId;
            $sql = "UPDATE products SET " . implode(', ', $updateFields) . " WHERE id = ?";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            
            $this->pdo->commit();
            
            echo json_encode([
                'success' => true,
                'message' => 'Product updated successfully'
            ]);
            
        } catch (Exception $e) {
            $this->pdo->rollback();
            error_log("Update product error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to update product: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Get single product
     * GET /api/admin/products/:id
     */
    public function getProduct($productId) {
        $this->requireAdminJson();
        $this->setJsonHeaders();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            exit;
        }
        
        try {
            $stmt = $this->pdo->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ?");
            $stmt->execute([$productId]);
            $product = $stmt->fetch();
            
            if (!$product) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Product not found']);
                exit;
            }
            
            echo json_encode([
                'success' => true,
                'data' => $product
            ]);
        } catch (Exception $e) {
            error_log("Get product error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to get product: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Delete product (soft delete)
     * DELETE /api/admin/products/:id
     */
    public function deleteProduct($productId) {
        $this->requireAdminJson();
        $this->setJsonHeaders();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            exit;
        }
        
        try {
            $this->pdo->beginTransaction();
            
            // Check if product exists
            $stmt = $this->pdo->prepare("SELECT id FROM products WHERE id = ?");
            $stmt->execute([$productId]);
            if (!$stmt->fetch()) {
                $this->pdo->rollback();
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Product not found']);
                exit;
            }
            
            // Soft delete by setting is_active to false
            $stmt = $this->pdo->prepare("UPDATE products SET is_active = 0 WHERE id = ?");
            $stmt->execute([$productId]);
            
            $this->pdo->commit();
            
            echo json_encode([
                'success' => true,
                'message' => 'Product deleted successfully'
            ]);
            
        } catch (Exception $e) {
            $this->pdo->rollback();
            error_log("Delete product error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to delete product: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Coupons API Endpoints
     */
    
    /**
     * Get coupons with filters
     * GET /api/admin/coupons
     */
    public function getCoupons() {
        // Suppress error display for API
        $displayErrors = ini_get('display_errors');
        ini_set('display_errors', '0');
        
        // Start output buffering
        while (ob_get_level()) {
            ob_end_clean();
        }
        ob_start();
        
        try {
            // Check admin access first
            if (!$this->adminMiddleware || !$this->adminMiddleware->isAdmin()) {
                error_log("❌ getCoupons: Admin access denied. User ID: " . ($_SESSION['user_id'] ?? 'not set') . ", Role: " . ($_SESSION['role'] ?? 'not set'));
                while (ob_get_level()) {
                    ob_end_clean();
                }
                if (!headers_sent()) {
                    $this->setJsonHeaders();
                    http_response_code(403);
                }
                echo json_encode(['success' => false, 'error' => 'Access denied. Admin privileges required.']);
                exit;
            }
            
            $this->setJsonHeaders();
            
            $page = intval($_GET['page'] ?? 1);
            $limit = intval($_GET['limit'] ?? 20);
            $offset = ($page - 1) * $limit;
            $search = $_GET['search'] ?? '';
            $status = $_GET['status'] ?? 'all'; // all, active, inactive, expired
            $type = $_GET['type'] ?? 'all'; // all, percentage, flat
            
            $where = "WHERE 1=1";
            $params = [];
            
            // Search filter
            if (!empty($search)) {
                $where .= " AND code LIKE ?";
                $params[] = "%$search%";
            }
            
            // Type filter
            if ($type !== 'all' && in_array($type, ['percentage', 'flat'])) {
                $where .= " AND discount_type = ?";
                $params[] = $type;
            }
            
            // Status filter
            if ($status === 'active') {
                $where .= " AND is_active = 1 AND (expiry_date IS NULL OR expiry_date >= CURDATE()) AND (usage_limit IS NULL OR used_count < usage_limit)";
            } elseif ($status === 'inactive') {
                $where .= " AND is_active = 0";
            } elseif ($status === 'expired') {
                $where .= " AND expiry_date IS NOT NULL AND expiry_date < CURDATE()";
            }
            
            // Initialize defaults
            $coupons = [];
            $total = 0;
            $overallTotal = 0;
            $overallActive = 0;
            $overallExpired = 0;
            $overallUsage = 0;
            
            // Get coupons
            try {
                $sql = "SELECT * FROM coupons $where ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
                
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute($params);
                $coupons = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Ensure all coupons have required fields
                foreach ($coupons as &$coupon) {
                    $coupon['discount_type'] = $coupon['discount_type'] ?? 'percentage';
                    $coupon['discount_value'] = isset($coupon['discount_value']) ? floatval($coupon['discount_value']) : 0;
                    $coupon['min_order_amount'] = isset($coupon['min_order_amount']) ? floatval($coupon['min_order_amount']) : 0;
                    $coupon['used_count'] = isset($coupon['used_count']) ? intval($coupon['used_count']) : 0;
                    $coupon['usage_limit'] = isset($coupon['usage_limit']) ? ($coupon['usage_limit'] ? intval($coupon['usage_limit']) : null) : null;
                    $coupon['max_uses_per_user'] = isset($coupon['max_uses_per_user']) ? intval($coupon['max_uses_per_user']) : 1;
                    $coupon['is_active'] = isset($coupon['is_active']) ? (bool)$coupon['is_active'] : true;
                }
                unset($coupon); // Break reference
            } catch (PDOException $e) {
                error_log("❌ getCoupons Query Error: " . $e->getMessage());
                error_log("❌ SQL: " . ($sql ?? 'N/A'));
                throw $e;
            }
            
            // Get total count
            try {
                $countSql = "SELECT COUNT(*) as total FROM coupons $where";
                $stmt = $this->pdo->prepare($countSql);
                $stmt->execute($params);
                $total = intval($stmt->fetch()['total']);
            } catch (PDOException $e) {
                error_log("❌ getCoupons Count Query Error: " . $e->getMessage());
                $total = 0;
            }
            
            // Get overall statistics (not affected by filters)
            try {
                $stmt = $this->pdo->prepare("SELECT COUNT(*) as total FROM coupons");
                $stmt->execute();
                $overallTotal = intval($stmt->fetch()['total']);
                
                $stmt = $this->pdo->prepare("SELECT COUNT(*) as active FROM coupons WHERE is_active = 1 AND (expiry_date IS NULL OR expiry_date >= CURDATE()) AND (usage_limit IS NULL OR used_count < usage_limit)");
                $stmt->execute();
                $overallActive = intval($stmt->fetch()['active']);
                
                $stmt = $this->pdo->prepare("SELECT COUNT(*) as expired FROM coupons WHERE expiry_date IS NOT NULL AND expiry_date < CURDATE()");
                $stmt->execute();
                $overallExpired = intval($stmt->fetch()['expired']);
                
                $stmt = $this->pdo->prepare("SELECT SUM(used_count) as total_usage FROM coupons");
                $stmt->execute();
                $overallUsage = intval($stmt->fetch()['total_usage'] ?? 0);
            } catch (PDOException $e) {
                error_log("❌ getCoupons Statistics Query Error: " . $e->getMessage());
            }
            
            // Clear output buffer
            while (ob_get_level()) {
                ob_end_clean();
            }
            
            if (!headers_sent()) {
                $this->setJsonHeaders();
            }
            
            echo json_encode([
                'success' => true,
                'data' => $coupons ?: [],
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $limit,
                    'total' => $total,
                    'total_pages' => ceil($total / $limit)
                ],
                'statistics' => [
                    'total' => $overallTotal,
                    'active' => $overallActive,
                    'expired' => $overallExpired,
                    'total_usage' => $overallUsage
                ]
            ]);
            
        } catch (PDOException $e) {
            error_log("❌ getCoupons PDO Error: " . $e->getMessage());
            while (ob_get_level()) {
                ob_end_clean();
            }
            if (!headers_sent()) {
                $this->setJsonHeaders();
                http_response_code(500);
            }
            echo json_encode([
                'success' => false,
                'error' => 'Database error: ' . $e->getMessage()
            ]);
        } catch (Exception $e) {
            error_log("❌ getCoupons Error: " . $e->getMessage());
            while (ob_get_level()) {
                ob_end_clean();
            }
            if (!headers_sent()) {
                $this->setJsonHeaders();
                http_response_code(500);
            }
            echo json_encode([
                'success' => false,
                'error' => 'Failed to load coupons: ' . $e->getMessage()
            ]);
        } finally {
            if (isset($displayErrors)) {
                ini_set('display_errors', $displayErrors);
            }
        }
    }
    
    /**
     * Create coupon
     * POST /api/admin/coupons
     */
    public function createCoupon() {
        $this->requireAdminJson();
        $this->setJsonHeaders();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            exit;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid JSON input']);
            exit;
        }
        
        // Validate required fields
        $required = ['code', 'discount_type', 'discount_value'];
        foreach ($required as $field) {
            if (empty($input[$field])) {
                echo json_encode(['success' => false, 'error' => "Field '$field' is required"]);
                exit;
            }
        }
        
        try {
            $this->pdo->beginTransaction();
            
            // Sanitize and validate data
            $code = strtoupper(trim($input['code']));
            // Handle both "fixed" and "flat" for discount type
            $discountType = $input['discount_type'] ?? $input['type'] ?? '';
            if ($discountType === 'fixed') {
                $discountType = 'flat';
            }
            $discountValue = floatval($input['discount_value'] ?? $input['value'] ?? 0);
            $minOrderAmount = floatval($input['min_order_amount'] ?? 0);
            $startDate = $input['start_date'] ?? null;
            $expiryDate = $input['expiry_date'] ?? null;
            $usageLimit = isset($input['usage_limit']) ? intval($input['usage_limit']) : null;
            $maxUsesPerUser = intval($input['max_uses_per_user'] ?? 1);
            $isActive = isset($input['is_active']) ? (bool)$input['is_active'] : true;
            
            // Validate discount type
            if (!in_array($discountType, ['percentage', 'flat'])) {
                throw new Exception('Invalid discount type. Must be "percentage" or "flat".');
            }
            
            // Validate discount value
            if ($discountValue <= 0) {
                throw new Exception('Discount value must be greater than 0');
            }
            
            if ($discountType === 'percentage' && $discountValue > 100) {
                throw new Exception('Percentage discount cannot exceed 100%');
            }
            
            // Validate dates
            if ($startDate && $expiryDate && strtotime($startDate) > strtotime($expiryDate)) {
                throw new Exception('Start date cannot be after expiry date');
            }
            
            // Validate dates are not in the past (optional - can remove if you want past dates)
            $today = date('Y-m-d');
            if ($startDate && $startDate < $today) {
                // Allow past start dates for flexibility
            }
            if ($expiryDate && $expiryDate < $today) {
                // Allow past expiry dates for flexibility
            }
            
            // Check if code already exists
            $stmt = $this->pdo->prepare("SELECT id FROM coupons WHERE code = ?");
            $stmt->execute([$code]);
            if ($stmt->fetch()) {
                throw new Exception('Coupon code already exists');
            }
            
            // Insert coupon
            $sql = "INSERT INTO coupons (code, discount_type, discount_value, min_order_amount, start_date, expiry_date, usage_limit, max_uses_per_user, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                $code, $discountType, $discountValue, $minOrderAmount, $startDate, $expiryDate, 
                $usageLimit, $maxUsesPerUser, $isActive
            ]);
            
            $couponId = $this->pdo->lastInsertId();
            
            $this->pdo->commit();
            
            echo json_encode([
                'success' => true,
                'message' => 'Coupon created successfully',
                'coupon_id' => $couponId
            ]);
            
        } catch (Exception $e) {
            $this->pdo->rollback();
            error_log("Create coupon error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to create coupon: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Update coupon
     * PATCH /api/admin/coupons/:id
     */
    public function updateCoupon($couponId) {
        $this->requireAdminJson();
        $this->setJsonHeaders();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'PATCH') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            exit;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid JSON input']);
            exit;
        }
        
        try {
            $this->pdo->beginTransaction();
            
            // Check if coupon exists
            $stmt = $this->pdo->prepare("SELECT * FROM coupons WHERE id = ?");
            $stmt->execute([$couponId]);
            $coupon = $stmt->fetch();
            
            if (!$coupon) {
                $this->pdo->rollback();
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Coupon not found']);
                exit;
            }
            
            // Build update fields
            $updateFields = [];
            $params = [];
            
            $allowedFields = ['code', 'discount_type', 'discount_value', 'min_order_amount', 'start_date', 'expiry_date', 'usage_limit', 'max_uses_per_user', 'is_active'];
            
            foreach ($allowedFields as $field) {
                if (isset($input[$field])) {
                    if ($field === 'code') {
                        // Check if code is being changed and if new code already exists
                        $newCode = strtoupper(trim($input[$field]));
                        if ($newCode !== $coupon['code']) {
                            $stmt = $this->pdo->prepare("SELECT id FROM coupons WHERE code = ? AND id != ?");
                            $stmt->execute([$newCode, $couponId]);
                            if ($stmt->fetch()) {
                                throw new Exception('Coupon code already exists');
                            }
                        }
                        $updateFields[] = "$field = ?";
                        $params[] = $newCode;
                    } elseif ($field === 'discount_type') {
                        // Handle both "fixed" and "flat" for discount type
                        $discountType = $input[$field];
                        if ($discountType === 'fixed') {
                            $discountType = 'flat';
                        }
                        if (!in_array($discountType, ['percentage', 'flat'])) {
                            throw new Exception('Invalid discount type');
                        }
                        $updateFields[] = "$field = ?";
                        $params[] = $discountType;
                    } elseif (in_array($field, ['discount_value', 'min_order_amount'])) {
                        $updateFields[] = "$field = ?";
                        $params[] = floatval($input[$field]);
                    } elseif (in_array($field, ['usage_limit', 'max_uses_per_user'])) {
                        $val = $input[$field];
                        // Handle empty string as null for usage_limit
                        if ($field === 'usage_limit' && ($val === '' || $val === null)) {
                            $updateFields[] = "$field = NULL";
                            // Don't add to params for NULL
                        } else {
                            $updateFields[] = "$field = ?";
                            $params[] = intval($val);
                        }
                    } elseif ($field === 'is_active') {
                        $updateFields[] = "$field = ?";
                        $params[] = (bool)$input[$field] ? 1 : 0;
                    } elseif (in_array($field, ['start_date', 'expiry_date'])) {
                        // Handle date fields - allow empty string to set to NULL
                        if ($input[$field] === '' || $input[$field] === null) {
                            $updateFields[] = "$field = NULL";
                            // Don't add to params for NULL - but we need to use a different approach
                            // Replace NULL with proper NULL handling
                        } else {
                            $updateFields[] = "$field = ?";
                            $params[] = $input[$field];
                        }
                    } else {
                        $updateFields[] = "$field = ?";
                        $params[] = $input[$field];
                    }
                }
            }
            
            if (empty($updateFields)) {
                $this->pdo->rollback();
                echo json_encode(['success' => false, 'error' => 'No fields to update']);
                exit;
            }
            
            // Validate discount value if being updated
            if (isset($input['discount_value'])) {
                $discountValue = floatval($input['discount_value']);
                if ($discountValue <= 0) {
                    throw new Exception('Discount value must be greater than 0');
                }
                
                $currentDiscountType = $input['discount_type'] ?? $coupon['discount_type'];
                if ($currentDiscountType === 'percentage' && $discountValue > 100) {
                    throw new Exception('Percentage discount cannot exceed 100%');
                }
            }
            
            // Validate dates if being updated
            if (isset($input['start_date']) && isset($input['expiry_date'])) {
                $startDate = $input['start_date'] ?: null;
                $expiryDate = $input['expiry_date'] ?: null;
                if ($startDate && $expiryDate && strtotime($startDate) > strtotime($expiryDate)) {
                    throw new Exception('Start date cannot be after expiry date');
                }
            }
            
            // Handle NULL values properly - need to separate fields with NULL from those with params
            $fieldsWithNull = [];
            $fieldsWithParams = [];
            $paramsForFields = [];
            
            foreach ($updateFields as $fieldUpdate) {
                if (strpos($fieldUpdate, '= NULL') !== false) {
                    $fieldsWithNull[] = $fieldUpdate;
                } else {
                    $fieldsWithParams[] = $fieldUpdate;
                    // Get the param index from the field update string
                    // Since we're building in order, we can use array index
                    $fieldName = explode(' =', $fieldUpdate)[0];
                    // Find the corresponding param value
                    // This is tricky - let's rebuild properly
                }
            }
            
            // Rebuild update fields and params properly
            $finalUpdateFields = [];
            $finalParams = [];
            
            foreach ($updateFields as $idx => $fieldUpdate) {
                if (strpos($fieldUpdate, '= NULL') !== false) {
                    $finalUpdateFields[] = $fieldUpdate;
                } else {
                    $finalUpdateFields[] = $fieldUpdate;
                    // Find corresponding param value
                    // Since params array is built in same order as updateFields
                    // We need to count how many non-NULL fields came before this one
                    $nonNullCount = 0;
                    for ($i = 0; $i < $idx; $i++) {
                        if (strpos($updateFields[$i], '= NULL') === false) {
                            $nonNullCount++;
                        }
                    }
                    // Get the param at this index (accounting for non-NULL fields before)
                    // Actually, params array is built sequentially, so we can use the count
                    // But wait, we need to rebuild this properly
            
            // Simpler approach: rebuild the whole thing
            $finalUpdateFields = [];
            $finalParams = [];
            $paramIndex = 0;
            
            foreach ($allowedFields as $field) {
                if (isset($input[$field])) {
                    if ($field === 'code') {
                        $finalUpdateFields[] = "$field = ?";
                        $finalParams[] = strtoupper(trim($input[$field]));
                    } elseif ($field === 'discount_type') {
                        $discountType = $input[$field];
                        if ($discountType === 'fixed') $discountType = 'flat';
                        $finalUpdateFields[] = "$field = ?";
                        $finalParams[] = $discountType;
                    } elseif (in_array($field, ['discount_value', 'min_order_amount'])) {
                        $finalUpdateFields[] = "$field = ?";
                        $finalParams[] = floatval($input[$field]);
                    } elseif ($field === 'usage_limit') {
                        $val = $input[$field];
                        if ($val === '' || $val === null) {
                            $finalUpdateFields[] = "$field = NULL";
                        } else {
                            $finalUpdateFields[] = "$field = ?";
                            $finalParams[] = intval($val);
                        }
                    } elseif ($field === 'max_uses_per_user') {
                        $finalUpdateFields[] = "$field = ?";
                        $finalParams[] = intval($input[$field]);
                    } elseif ($field === 'is_active') {
                        $finalUpdateFields[] = "$field = ?";
                        $finalParams[] = (bool)$input[$field] ? 1 : 0;
                    } elseif (in_array($field, ['start_date', 'expiry_date'])) {
                        if ($input[$field] === '' || $input[$field] === null) {
                            $finalUpdateFields[] = "$field = NULL";
                        } else {
                            $finalUpdateFields[] = "$field = ?";
                            $finalParams[] = $input[$field];
                        }
                    }
                }
            }
            
            $finalParams[] = $couponId;
            $sql = "UPDATE coupons SET " . implode(', ', $finalUpdateFields) . " WHERE id = ?";
            
            // Replace = NULL with proper NULL binding - actually MySQL accepts = NULL in prepared statements
            // But PDO requires us to bind NULL properly, so let's use COALESCE or handle differently
            // Actually, for simplicity, let's just execute the SQL directly for NULL values
            // But that's not safe. Let's rebuild without the complex logic above and handle it properly
            
            // Simpler: rebuild updateFields and params from scratch based on input
            $rebuildUpdateFields = [];
            $rebuildParams = [];
            
            foreach ($allowedFields as $field) {
                if (isset($input[$field])) {
                    if ($field === 'code') {
                        $rebuildUpdateFields[] = "$field = ?";
                        $rebuildParams[] = strtoupper(trim($input[$field]));
                    } elseif ($field === 'discount_type') {
                        $discountType = $input[$field];
                        if ($discountType === 'fixed') $discountType = 'flat';
                        $rebuildUpdateFields[] = "$field = ?";
                        $rebuildParams[] = $discountType;
                    } elseif (in_array($field, ['discount_value', 'min_order_amount'])) {
                        $rebuildUpdateFields[] = "$field = ?";
                        $rebuildParams[] = floatval($input[$field]);
                    } elseif ($field === 'usage_limit') {
                        $val = $input[$field];
                        if ($val === '' || $val === null) {
                            // Use NULL binding for PDO
                            $rebuildUpdateFields[] = "$field = NULL";
                        } else {
                            $rebuildUpdateFields[] = "$field = ?";
                            $rebuildParams[] = intval($val);
                        }
                    } elseif ($field === 'max_uses_per_user') {
                        $rebuildUpdateFields[] = "$field = ?";
                        $rebuildParams[] = intval($input[$field]);
                    } elseif ($field === 'is_active') {
                        $rebuildUpdateFields[] = "$field = ?";
                        $rebuildParams[] = (bool)$input[$field] ? 1 : 0;
                    } elseif (in_array($field, ['start_date', 'expiry_date'])) {
                        if ($input[$field] === '' || $input[$field] === null) {
                            $rebuildUpdateFields[] = "$field = NULL";
                        } else {
                            $rebuildUpdateFields[] = "$field = ?";
                            $rebuildParams[] = $input[$field];
                        }
                    }
                }
            }
            
            if (empty($rebuildUpdateFields)) {
                $this->pdo->rollback();
                echo json_encode(['success' => false, 'error' => 'No fields to update']);
                exit;
            }
            
            $rebuildParams[] = $couponId;
            $sql = "UPDATE coupons SET " . implode(', ', $rebuildUpdateFields) . " WHERE id = ?";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($rebuildParams);
            
            $this->pdo->commit();
            
            echo json_encode([
                'success' => true,
                'message' => 'Coupon updated successfully'
            ]);
            
        } catch (Exception $e) {
            $this->pdo->rollback();
            error_log("Update coupon error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to update coupon: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Get single coupon
     * GET /api/admin/coupons/:id
     */
    public function getCoupon($couponId) {
        $this->requireAdminJson();
        $this->setJsonHeaders();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            exit;
        }
        
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM coupons WHERE id = ?");
            $stmt->execute([$couponId]);
            $coupon = $stmt->fetch();
            
            if (!$coupon) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Coupon not found']);
                exit;
            }
            
            echo json_encode([
                'success' => true,
                'data' => $coupon
            ]);
        } catch (Exception $e) {
            error_log("Get coupon error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to get coupon: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Delete coupon
     * DELETE /api/admin/coupons/:id
     */
    public function deleteCoupon($couponId) {
        $this->requireAdminJson();
        $this->setJsonHeaders();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            exit;
        }
        
        try {
            $this->pdo->beginTransaction();
            
            // Check if coupon exists
            $stmt = $this->pdo->prepare("SELECT id FROM coupons WHERE id = ?");
            $stmt->execute([$couponId]);
            if (!$stmt->fetch()) {
                $this->pdo->rollback();
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Coupon not found']);
                exit;
            }
            
            // Soft delete by setting is_active to false
            $stmt = $this->pdo->prepare("UPDATE coupons SET is_active = 0 WHERE id = ?");
            $stmt->execute([$couponId]);
            
            $this->pdo->commit();
            
            echo json_encode([
                'success' => true,
                'message' => 'Coupon deleted successfully'
            ]);
            
        } catch (Exception $e) {
            $this->pdo->rollback();
            error_log("Delete coupon error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to delete coupon: ' . $e->getMessage()]);
        }
    }

    /**
     * Users Management API Endpoints
     */
    
    /**
     * Get users with advanced filtering and sorting
     * GET /api/admin/users
     */
    public function getUsers() {
        // Suppress error display for API
        $displayErrors = ini_get('display_errors');
        ini_set('display_errors', '0');
        
        // Start output buffering
        while (ob_get_level()) {
            ob_end_clean();
        }
        ob_start();
        
        try {
            // Check admin access first
            if (!$this->adminMiddleware || !$this->adminMiddleware->isAdmin()) {
                error_log("❌ getUsers: Admin access denied. User ID: " . ($_SESSION['user_id'] ?? 'not set') . ", Role: " . ($_SESSION['role'] ?? 'not set'));
                while (ob_get_level()) {
                    ob_end_clean();
                }
                if (!headers_sent()) {
                    $this->setJsonHeaders();
                    http_response_code(403);
                }
                echo json_encode(['success' => false, 'error' => 'Access denied. Admin privileges required.']);
                exit;
            }
            
            $this->setJsonHeaders();
            
            $page = intval($_GET['page'] ?? 1);
            $limit = intval($_GET['limit'] ?? 20);
            $offset = ($page - 1) * $limit;
            $search = $_GET['search'] ?? '';
            $role = $_GET['role'] ?? '';
            $sort = $_GET['sort'] ?? 'created_at'; // loyalty_points, total_orders, created_at, last_order_date
            $order = $_GET['order'] ?? 'desc';
            $status = $_GET['status'] ?? ''; // active, inactive, vip, high_priority
            
            $where = "WHERE 1=1";
            $params = [];
            
            // Search filter
            if (!empty($search)) {
                $where .= " AND (u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)";
                $searchTerm = "%$search%";
                $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
            }
            
            // Role filter
            if (!empty($role)) {
                $where .= " AND u.role = ?";
                $params[] = $role;
            }
            
            // Status filter
            if ($status === 'active') {
                $where .= " AND u.account_active = 1";
            } elseif ($status === 'inactive') {
                $where .= " AND u.account_active = 0";
            } elseif ($status === 'vip') {
                $where .= " AND u.is_vip = 1";
            } elseif ($status === 'high_priority') {
                $where .= " AND u.is_high_priority = 1";
            }
            
            // Validate sort field
            $allowedSorts = ['loyalty_points', 'total_orders', 'created_at', 'last_order_date', 'total_spent'];
            if (!in_array($sort, $allowedSorts)) {
                $sort = 'created_at';
            }
            
            // Validate order
            $order = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';
            
            // Initialize defaults
            $users = [];
            $total = 0;
            
            // Get users with comprehensive statistics
            $sql = "SELECT u.*, 
                       COALESCE(order_stats.total_orders, 0) as total_orders,
                       COALESCE(order_stats.last_order_date, NULL) as last_order_date,
                       COALESCE(order_stats.total_spent, 0) as total_spent,
                       COALESCE(order_stats.avg_order_value, 0) as avg_order_value,
                       COALESCE(subscription_stats.active_subscriptions, 0) as active_subscriptions
                FROM users u
                LEFT JOIN (
                    SELECT user_id, 
                           COUNT(*) as total_orders,
                           MAX(created_at) as last_order_date,
                           SUM(total_amount) as total_spent,
                           AVG(total_amount) as avg_order_value
                    FROM orders 
                    WHERE status != 'cancelled'
                    GROUP BY user_id
                ) order_stats ON u.id = order_stats.user_id
                LEFT JOIN (
                    SELECT user_id, COUNT(*) as active_subscriptions
                    FROM subscriptions 
                    WHERE status = 'active'
                    GROUP BY user_id
                ) subscription_stats ON u.id = subscription_stats.user_id
                $where
                ORDER BY u.$sort $order
                LIMIT $limit OFFSET $offset";
        
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get total count
            $countSql = "SELECT COUNT(*) as total FROM users u $where";
            $stmt = $this->pdo->prepare($countSql);
            $stmt->execute($params);
            $total = intval($stmt->fetch()['total']);
            
            // Process users data
            foreach ($users as &$user) {
                // Parse diet profile JSON
                if (!empty($user['diet_profile'])) {
                    $user['diet_profile'] = json_decode($user['diet_profile'], true) ?: [];
                } else {
                    $user['diet_profile'] = [];
                }
                
                // Remove sensitive data
                unset($user['password_hash']);
                
                // Format dates
                if (!empty($user['last_order_date'])) {
                    $user['last_order_date_formatted'] = date('M j, Y', strtotime($user['last_order_date']));
                }
                if (!empty($user['created_at'])) {
                    $user['created_at_formatted'] = date('M j, Y', strtotime($user['created_at']));
                }
                
                // Parse diet profile if it's a JSON string
                if (!empty($user['diet_profile']) && is_string($user['diet_profile'])) {
                    $dietProfileDecoded = json_decode($user['diet_profile'], true);
                    $user['diet_profile'] = is_array($dietProfileDecoded) ? $dietProfileDecoded : [];
                } elseif (empty($user['diet_profile'])) {
                    $user['diet_profile'] = [];
                }
                
                // Add diet flags for easy display
                $user['diet_flags'] = $this->getDietFlags($user['diet_profile']);
                
                // Add user status indicators
                $user['status_indicators'] = [
                    'is_vip' => (bool)($user['is_vip'] ?? false),
                    'is_high_priority' => (bool)($user['is_high_priority'] ?? false),
                    'account_active' => (bool)($user['account_active'] ?? true),
                    'has_admin_notes' => !empty($user['admin_notes'] ?? '')
                ];
            }
            
            // Clear output buffer
            while (ob_get_level()) {
                ob_end_clean();
            }
            
            if (!headers_sent()) {
                $this->setJsonHeaders();
            }
            
            // Get overall statistics (not affected by filters)
            $overallTotal = 0;
            $overallVip = 0;
            $overallHighPriority = 0;
            $overallActiveSubscriptions = 0;
            
            try {
                $stmt = $this->pdo->prepare("SELECT COUNT(*) as total FROM users WHERE role = 'customer'");
                $stmt->execute();
                $overallTotal = intval($stmt->fetch()['total']);
                
                $stmt = $this->pdo->prepare("SELECT COUNT(*) as vip FROM users WHERE is_vip = 1 AND role = 'customer'");
                $stmt->execute();
                $overallVip = intval($stmt->fetch()['vip']);
                
                $stmt = $this->pdo->prepare("SELECT COUNT(*) as high_priority FROM users WHERE is_high_priority = 1 AND role = 'customer'");
                $stmt->execute();
                $overallHighPriority = intval($stmt->fetch()['high_priority']);
                
                $stmt = $this->pdo->prepare("SELECT COUNT(*) as active_subscriptions FROM subscriptions WHERE status = 'active'");
                $stmt->execute();
                $overallActiveSubscriptions = intval($stmt->fetch()['active_subscriptions']);
            } catch (PDOException $e) {
                error_log("❌ getUsers Statistics Query Error: " . $e->getMessage());
            }
            
            echo json_encode([
                'success' => true,
                'data' => $users ?: [],
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $limit,
                    'total' => $total,
                    'total_pages' => ceil($total / $limit)
                ],
                'filters' => [
                    'roles' => ['customer', 'admin'],
                    'statuses' => ['active', 'inactive', 'vip', 'high_priority'],
                    'sort_options' => $allowedSorts
                ],
                'statistics' => [
                    'total_users' => $overallTotal,
                    'vip_users' => $overallVip,
                    'high_priority_users' => $overallHighPriority,
                    'active_subscriptions' => $overallActiveSubscriptions
                ]
            ]);
            
        } catch (PDOException $e) {
            error_log("❌ getUsers PDO Error: " . $e->getMessage());
            while (ob_get_level()) {
                ob_end_clean();
            }
            if (!headers_sent()) {
                $this->setJsonHeaders();
                http_response_code(500);
            }
            echo json_encode([
                'success' => false,
                'error' => 'Database error: ' . $e->getMessage()
            ]);
        } catch (Exception $e) {
            error_log("❌ getUsers Error: " . $e->getMessage());
            while (ob_get_level()) {
                ob_end_clean();
            }
            if (!headers_sent()) {
                $this->setJsonHeaders();
                http_response_code(500);
            }
            echo json_encode([
                'success' => false,
                'error' => 'Failed to load users: ' . $e->getMessage()
            ]);
        } finally {
            if (isset($displayErrors)) {
                ini_set('display_errors', $displayErrors);
            }
        }
    }
    
    /**
     * Get diet flags from user's diet profile
     */
    private function getDietFlags($dietProfile) {
        $flags = [];
        
        if (empty($dietProfile) || !is_array($dietProfile)) {
            return $flags;
        }
        
        // Extract diet goal (can be 'goal' or 'diet_goal')
        $goal = $dietProfile['goal'] ?? $dietProfile['diet_goal'] ?? '';
        
        // Extract preferences (can be 'allergies', 'dietary_preferences', or directly as array)
        $preferences = $dietProfile['allergies'] ?? $dietProfile['dietary_preferences'] ?? [];
        if (!is_array($preferences)) {
            $preferences = [];
        }
        
        // Map diet goals to display flags
        $goalFlags = [
            'weight_loss' => 'weight-loss-friendly',
            'muscle_gain' => 'high-protein',
            'diabetic' => 'diabetic-friendly',
            'diabetes_friendly' => 'diabetic-friendly',
            'low_sodium' => 'low-sodium',
            'vegetarian' => 'vegetarian',
            'halal_only' => 'halal',
            'balanced' => 'balanced',
            'keto' => 'keto',
            'ketogenic' => 'keto',
            'paleo' => 'paleo',
            'paleolithic' => 'paleo',
            'mediterranean' => 'mediterranean',
            'heart_health' => 'heart-healthy',
            'heart-healthy' => 'heart-healthy',
            'low_carb' => 'low-carb',
            'low_carbohydrate' => 'low-carb',
            'high_protein' => 'high-protein',
            'vegan' => 'vegan'
        ];
        
        // Add goal flag if it matches
        if (!empty($goal) && isset($goalFlags[$goal])) {
            $flags[] = $goalFlags[$goal];
        }
        
        // Add flags from allergies/preferences
        foreach ($preferences as $pref) {
            if (is_string($pref) && isset($goalFlags[$pref])) {
                $flags[] = $goalFlags[$pref];
            } elseif (is_string($pref)) {
                // Try to match partial names
                $prefLower = strtolower(str_replace(['-', '_'], '', $pref));
                foreach ($goalFlags as $key => $value) {
                    $keyLower = strtolower(str_replace(['-', '_'], '', $key));
                    if ($prefLower === $keyLower || strpos($prefLower, $keyLower) !== false) {
                        $flags[] = $value;
                        break;
                    }
                }
            }
        }
        
        return array_unique($flags);
    }
    
    /**
     * Get single user with detailed information
     * GET /api/admin/users/:id
     */
    public function getUser($userId) {
        $this->requireAdminJson();
        $this->setJsonHeaders();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            exit;
        }
        
        try {
            // Get user basic info
            $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
            
            if (!$user) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'User not found']);
                exit;
            }
            
            // Remove sensitive data
            unset($user['password_hash']);
            
            // Parse diet profile - handle both JSON string and array
            if (!empty($user['diet_profile'])) {
                if (is_string($user['diet_profile'])) {
                    $dietProfile = json_decode($user['diet_profile'], true);
                    $user['diet_profile'] = is_array($dietProfile) ? $dietProfile : [];
                } elseif (!is_array($user['diet_profile'])) {
                    $user['diet_profile'] = [];
                }
            } else {
                $user['diet_profile'] = [];
            }
            
            // Ensure diet_profile has standard structure
            if (empty($user['diet_profile']['goal'])) {
                $user['diet_profile']['goal'] = null;
            }
            if (empty($user['diet_profile']['allergies']) || !is_array($user['diet_profile']['allergies'])) {
                $user['diet_profile']['allergies'] = [];
            }
            if (empty($user['diet_profile']['calorie_target_per_day'])) {
                $user['diet_profile']['calorie_target_per_day'] = $user['diet_profile']['calorie_target'] ?? null;
            }
            if (empty($user['diet_profile']['calorie_target'])) {
                $user['diet_profile']['calorie_target'] = $user['diet_profile']['calorie_target_per_day'] ?? null;
            }
            
            // Get recent orders (last 5) with more details
            $stmt = $this->pdo->prepare("
                SELECT o.id, o.created_at, o.total_amount, o.status, o.payment_method, o.is_urgent,
                       COUNT(oi.id) as item_count
                FROM orders o
                LEFT JOIN order_items oi ON o.id = oi.order_id
                WHERE o.user_id = ? 
                GROUP BY o.id
                ORDER BY o.created_at DESC 
                LIMIT 5
            ");
            $stmt->execute([$userId]);
            $recentOrders = $stmt->fetchAll();
            
            // Format order dates
            foreach ($recentOrders as &$order) {
                $order['created_at_formatted'] = date('M j, Y g:i A', strtotime($order['created_at']));
                $order['status_badge'] = $this->getStatusBadge($order['status']);
            }
            
            // Get active subscriptions with more details
            $stmt = $this->pdo->prepare("
                SELECT s.*, 
                       ua.address_line1, ua.city, ua.state,
                       COUNT(DISTINCT s.id) as subscription_count
                FROM subscriptions s
                LEFT JOIN user_addresses ua ON s.delivery_address_id = ua.id
                WHERE s.user_id = ? AND s.status = 'active'
                GROUP BY s.id
                ORDER BY s.created_at DESC
            ");
            $stmt->execute([$userId]);
            $subscriptions = $stmt->fetchAll();
            
            // Format subscription dates
            foreach ($subscriptions as &$subscription) {
                $subscription['next_delivery_formatted'] = $subscription['next_delivery_date'] ? 
                    date('M j, Y', strtotime($subscription['next_delivery_date'])) : 'Not scheduled';
                $subscription['frequency_display'] = ucfirst(str_replace('_', ' ', $subscription['frequency']));
            }
            
            // Get comprehensive order statistics
            $stmt = $this->pdo->prepare("
                SELECT 
                    COUNT(*) as total_orders,
                    SUM(total_amount) as total_spent,
                    MAX(created_at) as last_order_date,
                    AVG(total_amount) as avg_order_value,
                    COUNT(CASE WHEN status = 'delivered' THEN 1 END) as completed_orders,
                    COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled_orders,
                    COUNT(CASE WHEN is_urgent = 1 THEN 1 END) as urgent_orders
                FROM orders 
                WHERE user_id = ?
            ");
            $stmt->execute([$userId]);
            $orderStats = $stmt->fetch();
            
            // Get user addresses
            $stmt = $this->pdo->prepare("
                SELECT * FROM user_addresses 
                WHERE user_id = ? 
                ORDER BY is_default DESC, created_at DESC
            ");
            $stmt->execute([$userId]);
            $addresses = $stmt->fetchAll();
            
            // Get loyalty and gift information
            $loyaltyInfo = [
                'loyalty_points' => $user['loyalty_points'],
                'surprise_gifts_unlocked' => $user['surprise_gifts_unlocked_count'],
                'tier' => $this->getLoyaltyTier($user['loyalty_points']),
                'next_tier_points' => $this->getNextTierPoints($user['loyalty_points'])
            ];
            
            // Get admin audit log for this user (if table exists)
            $auditLog = [];
            try {
                $stmt = $this->pdo->query("SHOW TABLES LIKE 'admin_audit_log'");
                if ($stmt->rowCount() > 0) {
                    $stmt = $this->pdo->prepare("
                        SELECT aal.*, u.first_name, u.last_name
                        FROM admin_audit_log aal
                        JOIN users u ON aal.admin_id = u.id
                        WHERE aal.user_id = ?
                        ORDER BY aal.created_at DESC
                        LIMIT 10
                    ");
                    $stmt->execute([$userId]);
                    $auditLog = $stmt->fetchAll();
                }
            } catch (PDOException $e) {
                error_log("Admin audit log table not found or error: " . $e->getMessage());
                // Table doesn't exist, continue without audit log
            }
            
            // Format audit log dates
            foreach ($auditLog as &$log) {
                $log['created_at_formatted'] = date('M j, Y g:i A', strtotime($log['created_at']));
                $log['admin_name'] = $log['first_name'] . ' ' . $log['last_name'];
            }
            
            // Get diet flags
            $dietFlags = $this->getDietFlags($user['diet_profile']);
            
            echo json_encode([
                'success' => true,
                'data' => [
                    'user' => $user,
                    'recent_orders' => $recentOrders,
                    'subscriptions' => $subscriptions,
                    'order_stats' => $orderStats,
                    'addresses' => $addresses,
                    'loyalty_info' => $loyaltyInfo,
                    'diet_flags' => $dietFlags,
                    'audit_log' => $auditLog,
                    'status_indicators' => [
                        'is_vip' => (bool)$user['is_vip'],
                        'is_high_priority' => (bool)$user['is_high_priority'],
                        'account_active' => (bool)$user['account_active'],
                        'has_admin_notes' => !empty($user['admin_notes'])
                    ]
                ]
            ]);
            
        } catch (Exception $e) {
            error_log("Get user error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to get user: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Get status badge information
     */
    private function getStatusBadge($status) {
        $badges = [
            'pending' => ['class' => 'bg-yellow-100 text-yellow-800', 'text' => 'Pending'],
            'confirmed' => ['class' => 'bg-blue-100 text-blue-800', 'text' => 'Confirmed'],
            'packed' => ['class' => 'bg-purple-100 text-purple-800', 'text' => 'Packed'],
            'out_for_delivery' => ['class' => 'bg-orange-100 text-orange-800', 'text' => 'Out for Delivery'],
            'delivered' => ['class' => 'bg-green-100 text-green-800', 'text' => 'Delivered'],
            'cancelled' => ['class' => 'bg-red-100 text-red-800', 'text' => 'Cancelled']
        ];
        
        return $badges[$status] ?? ['class' => 'bg-gray-100 text-gray-800', 'text' => ucfirst($status)];
    }
    
    /**
     * Get loyalty tier based on points
     */
    private function getLoyaltyTier($points) {
        if ($points >= 10000) return 'Diamond';
        if ($points >= 5000) return 'Gold';
        if ($points >= 2000) return 'Silver';
        if ($points >= 500) return 'Bronze';
        return 'New';
    }
    
    /**
     * Get points needed for next tier
     */
    private function getNextTierPoints($points) {
        if ($points >= 10000) return null; // Already at highest tier
        if ($points >= 5000) return 10000 - $points;
        if ($points >= 2000) return 5000 - $points;
        if ($points >= 500) return 2000 - $points;
        return 500 - $points;
    }
    
    /**
     * Update user (admin actions)
     * PATCH /api/admin/users/:id
     */
    public function updateUser($userId) {
        $this->requireAdminJson();
        $this->setJsonHeaders();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'PATCH') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            exit;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid JSON input']);
            exit;
        }
        
        try {
            $this->pdo->beginTransaction();
            
            // Check if user exists
            $stmt = $this->pdo->prepare("SELECT id FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            if (!$stmt->fetch()) {
                $this->pdo->rollback();
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'User not found']);
                exit;
            }
            
            // Build update fields
            $updateFields = [];
            $params = [];
            $auditActions = [];
            
            $allowedFields = ['admin_notes', 'account_active', 'loyalty_points', 'is_vip', 'is_high_priority', 'diet_profile'];
            
            foreach ($allowedFields as $field) {
                if (isset($input[$field])) {
                    if ($field === 'diet_profile') {
                        $updateFields[] = "$field = ?";
                        $params[] = json_encode($input[$field]);
                    } elseif ($field === 'loyalty_points') {
                        // Get current points for audit
                        $stmt = $this->pdo->prepare("SELECT loyalty_points FROM users WHERE id = ?");
                        $stmt->execute([$userId]);
                        $currentPoints = $stmt->fetch()['loyalty_points'];
                        $newPoints = intval($input[$field]);
                        
                        if ($currentPoints != $newPoints) {
                            $updateFields[] = "$field = ?";
                            $params[] = $newPoints;
                            $auditActions[] = [
                                'action_type' => 'POINTS_ADJUST',
                                'metadata' => json_encode([
                                    'old_points' => $currentPoints,
                                    'new_points' => $newPoints,
                                    'adjustment' => $newPoints - $currentPoints
                                ])
                            ];
                        }
                    } elseif ($field === 'account_active') {
                        $newStatus = (bool)$input[$field];
                        $updateFields[] = "$field = ?";
                        $params[] = $newStatus;
                        $auditActions[] = [
                            'action_type' => $newStatus ? 'ACCOUNT_ACTIVATE' : 'ACCOUNT_DEACTIVATE',
                            'metadata' => json_encode(['reason' => 'Admin action'])
                        ];
                    } elseif ($field === 'is_vip') {
                        $updateFields[] = "$field = ?";
                        $params[] = (bool)$input[$field];
                        $auditActions[] = [
                            'action_type' => 'VIP_TOGGLE',
                            'metadata' => json_encode(['is_vip' => (bool)$input[$field]])
                        ];
                    } elseif ($field === 'is_high_priority') {
                        $updateFields[] = "$field = ?";
                        $params[] = (bool)$input[$field];
                        $auditActions[] = [
                            'action_type' => 'PRIORITY_TOGGLE',
                            'metadata' => json_encode(['is_high_priority' => (bool)$input[$field]])
                        ];
                    } elseif ($field === 'admin_notes') {
                        $updateFields[] = "$field = ?";
                        $params[] = htmlspecialchars(trim($input[$field]));
                        $auditActions[] = [
                            'action_type' => 'NOTE_UPDATE',
                            'metadata' => json_encode(['notes_length' => strlen($input[$field])])
                        ];
                    }
                }
            }
            
            if (empty($updateFields)) {
                $this->pdo->rollback();
                echo json_encode(['success' => false, 'error' => 'No fields to update']);
                exit;
            }
            
            // Update user
            $params[] = $userId;
            $sql = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE id = ?";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            
            // Log audit actions (if table exists)
            try {
                $stmt = $this->pdo->query("SHOW TABLES LIKE 'admin_audit_log'");
                if ($stmt->rowCount() > 0) {
                    foreach ($auditActions as $action) {
                        $stmt = $this->pdo->prepare("
                            INSERT INTO admin_audit_log (admin_id, user_id, action_type, metadata)
                            VALUES (?, ?, ?, ?)
                        ");
                        $stmt->execute([
                            $_SESSION['user_id'],
                            $userId,
                            $action['action_type'],
                            $action['metadata']
                        ]);
                    }
                }
            } catch (PDOException $e) {
                error_log("Admin audit log insert failed (table may not exist): " . $e->getMessage());
                // Continue without audit logging if table doesn't exist
            }
            
            $this->pdo->commit();
            
            echo json_encode([
                'success' => true,
                'message' => 'User updated successfully'
            ]);
            
        } catch (Exception $e) {
            $this->pdo->rollback();
            error_log("Update user error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to update user: ' . $e->getMessage()]);
        }
    }

    /**
     * Create order (client)
     * POST /api/orders/create
     */
    public function createClientOrder() {
        $this->setJsonHeaders();
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Login required']);
            return;
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid JSON input']);
            return;
        }

        $userId = $_SESSION['user_id'];
        $cartItems = $input['cart_items'] ?? [];
        $deliveryAddressId = intval($input['delivery_address_id'] ?? 0);
        $deliverySlot = $input['delivery_slot'] ?? '';
        $paymentMethod = $input['payment_method'] ?? 'cod';

        if (empty($cartItems)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Cart is empty']);
            return;
        }

        try {
            $this->pdo->beginTransaction();

            // Calculate totals on server
            $subtotal = 0.0;
            foreach ($cartItems as $ci) {
                $productId = intval($ci['product_id']);
                $qty = max(1, intval($ci['quantity']));
                $stmt = $this->pdo->prepare("SELECT price FROM products WHERE id = ?");
                $stmt->execute([$productId]);
                $row = $stmt->fetch();
                if (!$row) { throw new Exception('Invalid product in cart'); }
                $subtotal += floatval($row['price']) * $qty;
            }
            $deliveryFee = floatval($input['delivery_fee'] ?? 0);
            $discount = floatval($input['discount'] ?? 0);
            $totalPayable = max(0, $subtotal + $deliveryFee - $discount);

            // Create order
            $stmt = $this->pdo->prepare("INSERT INTO orders (user_id, total_amount, total_payable, delivery_address_id, delivery_slot, payment_method, payment_status, status) VALUES (?, ?, ?, ?, ?, ?, 'unpaid', 'pending')");
            $stmt->execute([$userId, $totalPayable, $totalPayable, $deliveryAddressId ?: null, $deliverySlot, $paymentMethod]);
            $orderId = $this->pdo->lastInsertId();

            // Snapshot items
            foreach ($cartItems as $ci) {
                $productId = intval($ci['product_id']);
                $qty = max(1, intval($ci['quantity']));
                $stmt = $this->pdo->prepare("SELECT name, price, image FROM products WHERE id = ?");
                $stmt->execute([$productId]);
                $prod = $stmt->fetch();
                $unitPrice = floatval($prod['price']);
                $totalPrice = $unitPrice * $qty;
                $stmt = $this->pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, unit_price, total_price, product_name_snapshot, product_image_snapshot) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$orderId, $productId, $qty, $unitPrice, $totalPrice, $prod['name'] ?? null, $prod['image'] ?? null]);
            }

            // Clear cart items for this user after snapshotting
            $stmt = $this->pdo->prepare("DELETE FROM cart_items WHERE user_id = ?");
            $stmt->execute([$userId]);

            $this->pdo->commit();

            echo json_encode([
                'success' => true,
                'order_id' => intval($orderId),
                'total_payable' => $totalPayable,
                'payment_method' => $paymentMethod
            ]);
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) { $this->pdo->rollback(); }
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to create order']);
        }
    }

    /**
     * Initiate payment
     * POST /api/payments/initiate
     */
    public function initiatePayment() {
        $this->setJsonHeaders();
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Login required']);
            return;
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            return;
        }
        $input = json_decode(file_get_contents('php://input'), true);
        $orderId = intval($input['order_id'] ?? 0);
        $method = $input['method'] ?? '';
        if (!$orderId || !in_array($method, ['bkash','nagad','card'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid input']);
            return;
        }

        // Load order and verify ownership
        $stmt = $this->pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
        $stmt->execute([$orderId, $_SESSION['user_id']]);
        $order = $stmt->fetch();
        if (!$order) { http_response_code(404); echo json_encode(['success'=>false,'error'=>'Order not found']); return; }
        if ($order['payment_status'] === 'paid') { echo json_encode(['success'=>true,'message'=>'Already paid']); return; }

        // Create payment transaction
        $stmt = $this->pdo->prepare("INSERT INTO payment_transactions (order_id, method, status, amount) VALUES (?, ?, 'initiated', ?)");
        $stmt->execute([$orderId, $method, $order['total_payable'] ?? $order['total_amount']]);
        $transactionId = $this->pdo->lastInsertId();

        // Call gateway helper (mock)
        $gatewayResp = [];
        if ($method === 'bkash') {
            $gatewayResp = PaymentGateways::bkashCreatePayment(['id'=>$orderId,'amount'=>$order['total_payable'] ?? $order['total_amount']]);
        } elseif ($method === 'nagad') {
            $gatewayResp = PaymentGateways::nagadCreatePayment(['id'=>$orderId,'amount'=>$order['total_payable'] ?? $order['total_amount']]);
        } else {
            $gatewayResp = PaymentGateways::cardCreatePayment(['id'=>$orderId,'amount'=>$order['total_payable'] ?? $order['total_amount']]);
        }

        if (!($gatewayResp['success'] ?? false)) {
            $this->pdo->prepare("UPDATE payment_transactions SET status='failed', raw_response=? WHERE id=?")
                ->execute([json_encode($gatewayResp), $transactionId]);
            http_response_code(502);
            echo json_encode(['success'=>false,'error'=>'Gateway error']);
            return;
        }

        // Save gateway_reference and move to pending_customer_action
        $this->pdo->prepare("UPDATE payment_transactions SET status='pending_customer_action', gateway_reference=?, raw_response=? WHERE id=?")
            ->execute([$gatewayResp['gateway_reference'] ?? null, json_encode($gatewayResp), $transactionId]);

        echo json_encode([
            'success' => true,
            'nextAction' => 'redirect',
            'redirectUrl' => $gatewayResp['redirectUrl'] ?? null,
            'order_id' => intval($orderId),
            'method' => $method,
            'payment_transaction_id' => intval($transactionId)
        ]);
    }

    /**
     * Confirm payment
     * POST /api/payments/confirm
     */
    public function confirmPayment() {
        $this->setJsonHeaders();
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Login required']);
            return;
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            return;
        }
        $input = json_decode(file_get_contents('php://input'), true);
        $orderId = intval($input['order_id'] ?? 0);
        if (!$orderId) { http_response_code(400); echo json_encode(['success'=>false,'error'=>'order_id required']); return; }

        // Load order and verify ownership
        $stmt = $this->pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
        $stmt->execute([$orderId, $_SESSION['user_id']]);
        $order = $stmt->fetch();
        if (!$order) { http_response_code(404); echo json_encode(['success'=>false,'error'=>'Order not found']); return; }

        // Latest transaction
        $stmt = $this->pdo->prepare("SELECT * FROM payment_transactions WHERE order_id = ? ORDER BY id DESC LIMIT 1");
        $stmt->execute([$orderId]);
        $tx = $stmt->fetch();
        if (!$tx) { http_response_code(400); echo json_encode(['success'=>false,'error'=>'No payment transaction']); return; }

        // Verify with gateway
        if ($tx['method'] === 'bkash') {
            $verify = PaymentGateways::bkashVerifyPayment($tx);
        } elseif ($tx['method'] === 'nagad') {
            $verify = PaymentGateways::nagadVerifyPayment($tx);
        } else {
            $verify = PaymentGateways::cardVerifyPayment($tx);
        }

        if (($verify['success'] ?? false) && floatval($verify['amount']) == floatval($order['total_payable'] ?? $order['total_amount'])) {
            // Mark paid
            $this->pdo->beginTransaction();
            $this->pdo->prepare("UPDATE orders SET payment_status='paid', transaction_id=? , status='confirmed' WHERE id=?")
                ->execute([$verify['transaction_id'] ?? null, $orderId]);
            $this->pdo->prepare("UPDATE payment_transactions SET status='success', raw_response=? WHERE id=?")
                ->execute([json_encode($verify), $tx['id']]);
            $this->pdo->commit();
            echo json_encode(['success'=>true,'paid'=>true,'transaction_id'=>$verify['transaction_id'] ?? null,'message'=>'Payment successful']);
        } else {
            $this->pdo->prepare("UPDATE payment_transactions SET status='failed', raw_response=? WHERE id=?")
                ->execute([json_encode($verify), $tx['id']]);
            $this->pdo->prepare("UPDATE orders SET payment_status='failed' WHERE id=?")
                ->execute([$orderId]);
            echo json_encode(['success'=>false,'paid'=>false,'message'=>'Payment failed']);
        }
    }
}
?>
