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
        if (!$this->adminMiddleware->isAdmin()) {
            $this->setJsonHeaders();
            http_response_code(403);
            echo json_encode(['error' => 'Access denied. Admin privileges required.']);
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
        // Start output buffering to catch any accidental output
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
            ob_end_clean();
            http_response_code(403);
            $errorMsg = 'Access denied. Admin privileges required. User ID: ' . ($_SESSION['user_id'] ?? 'NOT SET') . ', Role: ' . ($_SESSION['role'] ?? 'NOT SET');
            error_log("❌ " . $errorMsg);
            echo json_encode(['success' => false, 'error' => $errorMsg]);
            exit;
        }
        
        error_log("✅ Admin access verified for user ID: " . $_SESSION['user_id']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'PATCH') {
            ob_end_clean();
            error_log("❌ Invalid method: " . $_SERVER['REQUEST_METHOD'] . " (expected PATCH)");
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method not allowed. Expected PATCH, got ' . $_SERVER['REQUEST_METHOD']]);
            exit;
        }

        $rawInput = file_get_contents('php://input');
        error_log("📥 Raw input length: " . strlen($rawInput));
        error_log("📥 Raw input received: " . ($rawInput ? substr($rawInput, 0, 500) : 'EMPTY'));
        
        if (empty($rawInput)) {
            ob_end_clean();
            error_log("❌ Empty input received");
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Request body is empty']);
            exit;
        }
        
        $input = json_decode($rawInput, true);
        $jsonError = json_last_error();
        if ($jsonError !== JSON_ERROR_NONE) {
            ob_end_clean();
            error_log("❌ JSON decode error: " . json_last_error_msg());
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid JSON: ' . json_last_error_msg()]);
            exit;
        }
        
        error_log("📥 Parsed input: " . print_r($input, true));

        $newStatus = $input['status'] ?? null;
        $assignedDriver = $input['assigned_driver'] ?? null;
        $adminNotes = $input['admin_notes'] ?? null;
        
        error_log("📊 Extracted params:");
        error_log("   - newStatus: " . ($newStatus ?? 'NULL'));
        error_log("   - assignedDriver: " . ($assignedDriver ?? 'NULL'));
        error_log("   - adminNotes: " . ($adminNotes ? 'SET' : 'NULL'));

        // Validate that at least one field is being updated
        if ($newStatus === null && $assignedDriver === null && $adminNotes === null) {
            ob_end_clean();
            error_log("❌ No fields to update");
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'At least one field (status, assigned_driver, or admin_notes) must be provided']);
            exit;
        }

        try {
            // Verify order exists first
            $checkStmt = $this->pdo->prepare("SELECT id, status, assigned_driver FROM orders WHERE id = ?");
            $checkStmt->execute([$orderId]);
            $currentOrder = $checkStmt->fetch();
            
            if (!$currentOrder) {
                ob_end_clean();
                error_log("❌ Order not found: ID $orderId");
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => "Order #$orderId not found"]);
                exit;
            }
            
            error_log("✅ Order found:");
            error_log("   - Current status: " . $currentOrder['status']);
            error_log("   - Current driver: " . ($currentOrder['assigned_driver'] ?? 'NULL'));
            
            $this->pdo->beginTransaction();
            error_log("✅ Transaction started");

            $oldStatus = $currentOrder['status'];
            $statusChanged = ($newStatus !== null && $newStatus !== $oldStatus);
            
            // Store validated driver value for later use in verification
            $validatedDriverValue = null;

            // Update order - build update fields dynamically
            $updateFields = [];
            $params = [];

            // Update status if provided and different from current
            if ($newStatus !== null) {
                $updateFields[] = 'status = ?';
                $params[] = $newStatus;
            }

            if ($assignedDriver !== null) {
                // Convert empty string to null for database consistency
                $driverInput = (trim($assignedDriver) === '' || $assignedDriver === null) ? null : trim($assignedDriver);
                
                // If a driver name is provided, validate it exists in the database and get the exact name
                $driverValue = null;
                if ($driverInput) {
                    // Look up the driver in the database (case-insensitive) to get the exact name
                    $driverCheckStmt = $this->pdo->prepare("SELECT name FROM drivers WHERE LOWER(TRIM(name)) = LOWER(TRIM(?)) AND is_active = 1 LIMIT 1");
                    $driverCheckStmt->execute([$driverInput]);
                    $driverRecord = $driverCheckStmt->fetch();
                    
                    if ($driverRecord) {
                        // Use the exact name from the database (preserves correct capitalization)
                        $driverValue = trim($driverRecord['name']);
                        $validatedDriverValue = $driverValue; // Store for verification
                        error_log("✅ Driver validated and found in database: '" . $driverValue . "' (input was: '" . $driverInput . "')");
                    } else {
                        error_log("❌ ERROR: Driver name '" . $driverInput . "' not found in active drivers list");
                        // Don't save invalid driver names - return error
                        $this->pdo->rollback();
                        ob_end_clean();
                        http_response_code(400);
                        echo json_encode([
                            'success' => false, 
                            'error' => "Driver '" . htmlspecialchars($driverInput) . "' not found. Please select a valid driver from the list."
                        ]);
                        exit;
                    }
                }
                
                $updateFields[] = 'assigned_driver = ?';
                $params[] = $driverValue;
                error_log("🔧 Driver value to set: " . ($driverValue ?? 'NULL'));
            }

            if ($adminNotes !== null) {
                $updateFields[] = 'admin_notes = ?';
                $params[] = $adminNotes;
            }

            // Only update if there are fields to update
            if (empty($updateFields)) {
                $this->pdo->rollback();
                ob_end_clean();
                error_log("⚠️ WARNING: No update fields to process!");
                echo json_encode(['success' => false, 'error' => 'No fields to update']);
                exit;
            }
            
            // IMPORTANT: Add orderId to params LAST (for WHERE clause)
            $params[] = $orderId;
            
            // Build SQL - ensure updated_at is always set
            $sql = "UPDATE orders SET " . implode(', ', $updateFields) . ", updated_at = NOW() WHERE id = ?";
            
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
                
            } catch (PDOException $e) {
                error_log("❌ PDO EXCEPTION: " . $e->getMessage());
                error_log("❌ Error Code: " . $e->getCode());
                throw $e;
            }
            
            // CRITICAL: Verify the update actually happened BEFORE commit
            if ($rowsAffected === 0) {
                // Check if order still exists and get current values BEFORE rollback
                $checkStmt = $this->pdo->prepare("SELECT id, status, assigned_driver FROM orders WHERE id = ?");
                $checkStmt->execute([$orderId]);
                $checkOrder = $checkStmt->fetch();
                
                if (!$checkOrder) {
                    error_log("❌ Order #$orderId DOES NOT EXIST in database!");
                    $this->pdo->rollback();
                    ob_end_clean();
                    http_response_code(404);
                    echo json_encode(['success' => false, 'error' => "Order #$orderId not found in database"]);
                    exit;
                }
                
                error_log("⚠️ UPDATE returned 0 rows affected - checking if values are already the same");
                error_log("   Current status: {$checkOrder['status']}");
                error_log("   Current driver: " . ($checkOrder['assigned_driver'] ?? 'NULL'));
                error_log("   Trying to set status: " . ($newStatus ?? 'NULL'));
                error_log("   Trying to set driver: " . ($assignedDriver ?? 'NULL'));
                
                // Check if values are already the same (would cause 0 rows affected)
                $alreadySame = true;
                if ($newStatus !== null && $checkOrder['status'] !== $newStatus) {
                    $alreadySame = false;
                }
                if ($assignedDriver !== null) {
                    // Use validated driver value for comparison (case-insensitive)
                    $expectedDriver = $validatedDriverValue;
                    if ($expectedDriver === null && $assignedDriver) {
                        $expectedDriver = (trim($assignedDriver) === '' || $assignedDriver === null) ? null : trim($assignedDriver);
                    }
                    
                    $currentDriverRaw = $checkOrder['assigned_driver'] ?? null;
                    $currentDriver = ($currentDriverRaw === '' || $currentDriverRaw === null) ? null : trim($currentDriverRaw);
                    
                    // Compare using case-insensitive normalized values
                    $currentNormalized = $currentDriver ? strtolower(trim($currentDriver)) : '';
                    $expectedNormalized = $expectedDriver ? strtolower(trim($expectedDriver)) : '';
                    
                    error_log("   Driver comparison: current='$currentNormalized' vs expected='$expectedNormalized'");
                    
                    if ($currentNormalized !== $expectedNormalized && 
                        !(($currentNormalized === '') && ($expectedNormalized === ''))) {
                        $alreadySame = false;
                        error_log("   ❌ Drivers are DIFFERENT - this is a problem!");
                    } else {
                        error_log("   ✅ Drivers are the same");
                    }
                }
                
                // If values are already the same, commit and return success
                if ($alreadySame) {
                    error_log("ℹ️ Values are already set to what we're trying to set - no update needed");
                    // Commit the transaction (nothing changed, but transaction is still open)
                    $this->pdo->commit();
                    
                    // Normalize driver value for response
                    $responseDriverValue = $checkOrder['assigned_driver'] ?? null;
                    if ($responseDriverValue === '' || $responseDriverValue === null) {
                        $responseDriverValue = null;
                    } else {
                        $responseDriverValue = trim($responseDriverValue);
                    }
                    
                    $response = [
                        'success' => true,
                        'message' => $responseDriverValue ? 'Driver is already assigned to this order' : 'Order status is already up to date',
                        'database_updated' => true,
                        'verification_passed' => true
                    ];
                    
                    if ($newStatus !== null) {
                        $response['new_status'] = $checkOrder['status'];
                    }
                    if ($assignedDriver !== null) {
                        $response['assigned_driver'] = $responseDriverValue;
                    }
                    
                    ob_end_clean();
                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode($response);
                    exit;
                } else {
                    // Values are different but 0 rows affected - this is a problem!
                    error_log("❌ ========== CRITICAL ERROR: NO ROWS AFFECTED ==========");
                    error_log("❌ UPDATE query executed successfully BUT 0 ROWS WERE UPDATED!");
                    error_log("❌ Order ID: $orderId");
                    error_log("❌ SQL: $sql");
                    error_log("❌ Params: " . print_r($params, true));
                    error_log("❌ This should NOT happen - values are different but update failed!");
                    
                    $this->pdo->rollback();
                    ob_end_clean();
                    http_response_code(500);
                    echo json_encode([
                        'success' => false, 
                        'error' => 'Database update failed: No rows affected. Current: status=' . $checkOrder['status'] . ', driver=' . ($checkOrder['assigned_driver'] ?? 'NULL') . '. Please try again or contact support.'
                    ]);
                    exit;
                }
            }
            
            error_log("✅ UPDATE CONFIRMED: $rowsAffected row(s) affected in database");

            // Track driver change using validated driver value
            $oldDriver = $currentOrder['assigned_driver'] ?? null;
            $driverChanged = false;
            if ($assignedDriver !== null) {
                // Use validated driver value if available, otherwise use input (normalized)
                $expectedDriver = $validatedDriverValue;
                if ($expectedDriver === null && $assignedDriver) {
                    // Fallback to input if validation didn't run
                    $expectedDriver = (trim($assignedDriver) === '' || $assignedDriver === null) ? null : trim($assignedDriver);
                }
                
                $oldDriverNormalized = ($oldDriver === '' || $oldDriver === null) ? null : trim(strtolower($oldDriver));
                $expectedDriverNormalized = ($expectedDriver === '' || $expectedDriver === null) ? null : trim(strtolower($expectedDriver));
                
                // Case-insensitive comparison
                $driverChanged = ($expectedDriverNormalized !== $oldDriverNormalized && 
                                 !(($expectedDriverNormalized === null || $expectedDriverNormalized === '') && 
                                   ($oldDriverNormalized === null || $oldDriverNormalized === '')));
                
                if ($driverChanged) {
                    error_log("🚗 Driver change detected: '" . ($oldDriver ?? 'NULL') . "' → '" . ($expectedDriver ?? 'NULL') . "'");
                } else {
                    error_log("ℹ️ Driver unchanged: '" . ($oldDriver ?? 'NULL') . "' (same as '" . ($expectedDriver ?? 'NULL') . "')");
                }
            }
            
            // Record status change in history only if status actually changed
            if ($newStatus !== null && $statusChanged) {
                try {
                    $stmt = $this->pdo->prepare("
                        INSERT INTO order_status_history (order_id, old_status, new_status, changed_by_admin_id, admin_name, notes)
                        VALUES (?, ?, ?, ?, ?, ?)
                    ");
                    $notes = $adminNotes ?? '';
                    if ($driverChanged && $validatedDriverValue) {
                        $notes = ($notes ? $notes . ' | ' : '') . "Driver assigned: " . trim($validatedDriverValue);
                    }
                    $stmt->execute([
                        $orderId,
                        $oldStatus,
                        $newStatus,
                        $_SESSION['user_id'] ?? null,
                        ($_SESSION['first_name'] ?? '') . ' ' . ($_SESSION['last_name'] ?? ''),
                        $notes
                    ]);
                } catch (Exception $e) {
                    // Log error but don't fail the update
                    error_log("Failed to insert status history: " . $e->getMessage());
                }

                // Add delivery update only if status changed
                try {
                    $message = "Order status updated from $oldStatus to $newStatus";
                    if ($driverChanged && $validatedDriverValue) {
                        $message .= " and assigned to " . trim($validatedDriverValue);
                    }
                    $stmt = $this->pdo->prepare("
                        INSERT INTO delivery_updates (order_id, status, message) 
                        VALUES (?, ?, ?)
                    ");
                    $stmt->execute([
                        $orderId, 
                        $newStatus, 
                        $message
                    ]);
                } catch (Exception $e) {
                    // Log error but don't fail the update
                    error_log("Failed to insert delivery update: " . $e->getMessage());
                }
            } elseif ($driverChanged) {
                // Driver changed without status change - still log it
                try {
                    $driverMessage = $validatedDriverValue 
                        ? "Driver assigned: " . trim($validatedDriverValue) 
                        : "Driver unassigned";
                    
                    $stmt = $this->pdo->prepare("
                        INSERT INTO delivery_updates (order_id, status, message) 
                        VALUES (?, ?, ?)
                    ");
                    $stmt->execute([
                        $orderId, 
                        $oldStatus, 
                        $driverMessage
                    ]);
                    error_log("✅ Driver assignment logged in delivery_updates");
                } catch (Exception $e) {
                    error_log("Failed to log driver assignment: " . $e->getMessage());
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
            
            if ($assignedDriver !== null) {
                // Use the validated driver value (not the input) for verification
                $expectedDriver = $validatedDriverValue;
                if ($expectedDriver === null || $expectedDriver === '') {
                    $expectedDriver = null; // Normalize empty to null
                } else {
                    $expectedDriver = trim($expectedDriver);
                }
                
                // Get actual driver value from database - handle both NULL and empty string
                $actualDriverRaw = $updatedOrder['assigned_driver'] ?? null;
                $actualDriver = ($actualDriverRaw === '' || $actualDriverRaw === null) ? null : trim($actualDriverRaw);
                
                error_log("🔍 Driver verification:");
                error_log("   - Expected (validated): " . ($expectedDriver ?? 'NULL'));
                error_log("   - Actual (from DB): " . ($actualDriverRaw ?? 'NULL'));
                error_log("   - Actual (normalized): " . ($actualDriver ?? 'NULL'));
                
                // Compare using case-insensitive, trimmed values
                $expectedNormalized = $expectedDriver ? strtolower(trim($expectedDriver)) : '';
                $actualNormalized = $actualDriver ? strtolower(trim($actualDriver)) : '';
                
                if ($expectedNormalized !== $actualNormalized && 
                    !(($expectedNormalized === '') && ($actualNormalized === ''))) {
                    $verificationErrors[] = "Driver mismatch: Expected '" . ($expectedDriver ?? 'NULL') . "' but got '" . ($actualDriver ?? 'NULL') . "'";
                    $verificationPassed = false;
                } else {
                    error_log("✅ Driver verified in database: " . ($actualDriver ?? 'NULL'));
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
            
            // Build clear success message
            if (empty($updateMessages)) {
                $successMessage = 'Order updated successfully';
            } else {
                $successMessage = implode(', ', $updateMessages);
            }
            
            $response = [
                'success' => true, 
                'message' => $successMessage,
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
            if ($assignedDriver !== null) {
                // Return the normalized driver value (null or trimmed string)
                $returnDriverValue = $updatedOrder['assigned_driver'] ?? null;
                // Normalize empty strings to null for consistency
                if ($returnDriverValue === '' || $returnDriverValue === null) {
                    $returnDriverValue = null;
                } else {
                    $returnDriverValue = trim($returnDriverValue);
                }
                $response['assigned_driver'] = $returnDriverValue;
                error_log("📤 Returning assigned_driver: " . ($returnDriverValue ?? 'null'));
            }
            
            // Log final response before sending
            error_log("📤 Final response: " . json_encode($response));
            error_log("========================================");
            
            // Clear any accidental output and set headers before sending JSON
            ob_end_clean();
            header('Content-Type: application/json; charset=utf-8');
            
            // Send JSON response
            echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;

        } catch (Exception $e) {
            // Clear any output buffer
            ob_end_clean();
            
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollback();
                error_log("⚠️ Transaction rolled back due to error");
            }
            
            $errorMsg = 'Failed to update order: ' . $e->getMessage();
            error_log("❌ Update order API error: " . $errorMsg);
            error_log("❌ Stack trace: " . $e->getTraceAsString());
            
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $errorMsg]);
            exit;
        }
    }

    /**
     * Get drivers list
     * GET /api/admin/drivers
     */
    public function drivers() {
        $this->requireAdminJson();
        $this->setJsonHeaders();
        
        try {
            $method = $_SERVER['REQUEST_METHOD'];
            
            // GET: Fetch all drivers
            if ($method === 'GET') {
                $stmt = $this->pdo->prepare("
                    SELECT d.*, 
                           d.name,
                           COALESCE(d.status, 
                               CASE WHEN d.is_active = 1 THEN 'active' ELSE 'inactive' END
                           ) as status
                    FROM drivers d 
                    WHERE d.is_active = 1
                    ORDER BY d.name ASC
                ");
                $stmt->execute();
                $drivers = $stmt->fetchAll();

                echo json_encode(['success' => true, 'drivers' => $drivers]);
            }
            // POST: Create new driver(s)
            elseif ($method === 'POST') {
                $input = json_decode(file_get_contents('php://input'), true);
                
                // Check if bulk import
                if (isset($input['drivers']) && is_array($input['drivers'])) {
                    $this->bulkImportDrivers($input['drivers']);
                } else {
                    $this->createDriver($input);
                }
            }
            // PUT: Update driver
            elseif ($method === 'PUT') {
                $input = json_decode(file_get_contents('php://input'), true);
                $this->updateDriver($input);
            }
            // DELETE: Delete driver
            elseif ($method === 'DELETE') {
                $input = json_decode(file_get_contents('php://input'), true);
                $driverId = $input['id'] ?? null;
                
                if (!$driverId) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'error' => 'Driver ID is required']);
                    return;
                }
                
                $stmt = $this->pdo->prepare("DELETE FROM drivers WHERE id = ?");
                $stmt->execute([$driverId]);
                
                echo json_encode(['success' => true, 'message' => 'Driver deleted successfully']);
            }
            else {
                http_response_code(405);
                echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            }

        } catch (Exception $e) {
            error_log("Drivers API error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to process request: ' . $e->getMessage()]);
        }
    }
    
    private function createDriver($data) {
        // Handle both old format (name) and new format (first_name, last_name)
        $name = $data['name'] ?? '';
        $firstName = $data['first_name'] ?? '';
        $lastName = $data['last_name'] ?? '';
        
        // If using old format, split name
        if (!empty($name) && empty($firstName)) {
            $nameParts = explode(' ', $name, 2);
            $firstName = $nameParts[0] ?? '';
            $lastName = $nameParts[1] ?? '';
        }
        
        // Generate driver_id if not provided
        $driverId = $data['driver_id'] ?? 'DRV' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        // Check for duplicate driver_id
        while (true) {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM drivers WHERE driver_id = ?");
            $stmt->execute([$driverId]);
            if ($stmt->fetchColumn() == 0) break;
            $driverId = 'DRV' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        }
        
        $phone = $data['phone'] ?? '';
        $email = $data['email'] ?? null;
        $vehicleType = $data['vehicle_type'] ?? 'motorcycle';
        $licenseNumber = $data['license_number'] ?? null;
        $vehicleNumber = $data['vehicle_number'] ?? null;
        $vehicleModel = $data['vehicle_model'] ?? null;
        $address = $data['address'] ?? null;
        $city = $data['city'] ?? null;
        $state = $data['state'] ?? null;
        $zipCode = $data['zip_code'] ?? null;
        $joiningDate = $data['joining_date'] ?? date('Y-m-d');
        
        // Map old format to new format
        $status = $data['status'] ?? 'active';
        $availabilityStatus = $data['availability_status'] ?? 'available';
        $isActive = $data['is_active'] ?? 1;
        
        // If using old is_active format, convert to status
        if ($isActive == 0) {
            $status = 'inactive';
        }
        
        $stmt = $this->pdo->prepare("
            INSERT INTO drivers (
                driver_id, first_name, last_name, phone, email, 
                vehicle_type, license_number, vehicle_number, vehicle_model,
                address, city, state, zip_code, joining_date,
                status, availability_status, is_active,
                created_at
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW()
            )
        ");
        
        $stmt->execute([
            $driverId, $firstName, $lastName, $phone, $email,
            $vehicleType, $licenseNumber, $vehicleNumber, $vehicleModel,
            $address, $city, $state, $zipCode, $joiningDate,
            $status, $availabilityStatus, $isActive
        ]);
        
        $newDriverId = $this->pdo->lastInsertId();
        
        echo json_encode([
            'success' => true, 
            'message' => 'Driver created successfully',
            'driver_id' => $newDriverId
        ]);
    }
    
    private function bulkImportDrivers($drivers) {
        $imported = 0;
        $errors = [];
        
        foreach ($drivers as $index => $data) {
            try {
                // Handle both old format (name) and new format (first_name, last_name)
                $name = $data['name'] ?? '';
                $firstName = $data['first_name'] ?? '';
                $lastName = $data['last_name'] ?? '';
                
                // If using old format, split name
                if (!empty($name) && empty($firstName)) {
                    $nameParts = explode(' ', $name, 2);
                    $firstName = $nameParts[0] ?? '';
                    $lastName = $nameParts[1] ?? '';
                }
                
                // Generate driver_id if not provided
                $driverId = $data['driver_id'] ?? 'DRV' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
                
                // Check for duplicate driver_id
                while (true) {
                    $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM drivers WHERE driver_id = ?");
                    $stmt->execute([$driverId]);
                    if ($stmt->fetchColumn() == 0) break;
                    $driverId = 'DRV' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
                }
                
                $phone = $data['phone'] ?? '';
                $email = $data['email'] ?? null;
                $vehicleType = $data['vehicle_type'] ?? 'motorcycle';
                $licenseNumber = $data['license_number'] ?? null;
                $vehicleNumber = $data['vehicle_number'] ?? null;
                $vehicleModel = $data['vehicle_model'] ?? null;
                $address = $data['address'] ?? null;
                $city = $data['city'] ?? null;
                $state = $data['state'] ?? null;
                $zipCode = $data['zip_code'] ?? null;
                $joiningDate = $data['joining_date'] ?? date('Y-m-d');
                
                // Map old format to new format
                $status = $data['status'] ?? 'active';
                $availabilityStatus = $data['availability_status'] ?? 'available';
                $isActive = $data['is_active'] ?? 1;
                
                // If using old is_active format, convert to status
                if ($isActive == 0) {
                    $status = 'inactive';
                }
                
                $stmt = $this->pdo->prepare("
                    INSERT INTO drivers (
                        driver_id, first_name, last_name, phone, email, 
                        vehicle_type, license_number, vehicle_number, vehicle_model,
                        address, city, state, zip_code, joining_date,
                        status, availability_status, is_active,
                        created_at
                    ) VALUES (
                        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW()
                    )
                ");
                
                $stmt->execute([
                    $driverId, $firstName, $lastName, $phone, $email,
                    $vehicleType, $licenseNumber, $vehicleNumber, $vehicleModel,
                    $address, $city, $state, $zipCode, $joiningDate,
                    $status, $availabilityStatus, $isActive
                ]);
                
                $imported++;
            } catch (Exception $e) {
                $errors[] = "Row " . ($index + 1) . ": " . $e->getMessage();
                error_log("Bulk import error for row " . ($index + 1) . ": " . $e->getMessage());
            }
        }
        
        echo json_encode([
            'success' => true,
            'message' => "Imported $imported drivers successfully",
            'imported' => $imported,
            'errors' => $errors
        ]);
    }
    
    private function updateDriver($data) {
        $driverId = $data['id'] ?? null;
        
        if (!$driverId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Driver ID is required']);
            return;
        }
        
        // Build update query dynamically
        $fields = [];
        $params = [];
        
        $allowedFields = [
            'first_name', 'last_name', 'phone', 'email', 'vehicle_type',
            'license_number', 'vehicle_number', 'vehicle_model', 'address',
            'city', 'state', 'zip_code', 'joining_date', 'status',
            'availability_status', 'notes'
        ];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = ?";
                $params[] = $data[$field];
            }
        }
        
        if (empty($fields)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'No fields to update']);
            return;
        }
        
        // Handle is_active mapping
        if (isset($data['is_active'])) {
            $fields[] = "is_active = ?";
            $params[] = $data['is_active'];
        }
        
        $params[] = $driverId;
        
        $sql = "UPDATE drivers SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        
        echo json_encode(['success' => true, 'message' => 'Driver updated successfully']);
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
        $this->requireAdminJson();
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
        
        // Search filter
        if (!empty($search)) {
            $where .= " AND (p.name LIKE ? OR p.brand LIKE ? OR p.description LIKE ?)";
            $searchTerm = "%$search%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
        }
        
        // Category filter
        if (!empty($category)) {
            $where .= " AND p.category_id = ?";
            $params[] = $category;
        }
        
        // Status filter
        if ($status === 'active') {
            $where .= " AND p.is_active = 1";
        } elseif ($status === 'inactive') {
            $where .= " AND p.is_active = 0";
        }
        
        // Stock filter
        if ($stock === 'in_stock') {
            $where .= " AND p.stock_quantity > p.low_stock_threshold";
        } elseif ($stock === 'low_stock') {
            $where .= " AND p.stock_quantity > 0 AND p.stock_quantity <= p.low_stock_threshold";
        } elseif ($stock === 'out_of_stock') {
            $where .= " AND p.stock_quantity = 0";
        }
        
        // Get products
        $sql = "SELECT p.*, c.name as category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                $where 
                ORDER BY p.created_at DESC 
                LIMIT $limit OFFSET $offset";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $products = $stmt->fetchAll();
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM products p $where";
        $stmt = $this->pdo->prepare($countSql);
        $stmt->execute($params);
        $total = $stmt->fetch()['total'];
        
        // Get categories for filter
        $stmt = $this->pdo->prepare("SELECT id, name FROM categories ORDER BY name");
        $stmt->execute();
        $categories = $stmt->fetchAll();
        
        // Get overall statistics (not affected by filters)
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as total FROM products");
        $stmt->execute();
        $overallTotal = $stmt->fetch()['total'];
        
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as active FROM products WHERE is_active = 1");
        $stmt->execute();
        $overallActive = $stmt->fetch()['active'];
        
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as low_stock FROM products WHERE stock_quantity > 0 AND stock_quantity <= low_stock_threshold");
        $stmt->execute();
        $overallLowStock = $stmt->fetch()['low_stock'];
        
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as out_of_stock FROM products WHERE stock_quantity = 0");
        $stmt->execute();
        $overallOutOfStock = $stmt->fetch()['out_of_stock'];
        
        echo json_encode([
            'success' => true,
            'data' => $products,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $limit,
                'total' => $total,
                'total_pages' => ceil($total / $limit)
            ],
            'filters' => [
                'categories' => $categories
            ],
            'statistics' => [
                'total_products' => intval($overallTotal),
                'active_products' => intval($overallActive),
                'low_stock_products' => intval($overallLowStock),
                'out_of_stock_products' => intval($overallOutOfStock)
            ]
        ]);
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
            $unitSize = htmlspecialchars(trim($input['unit_size'] ?? ''));
            $stockQuantity = intval($input['stock_quantity'] ?? 0);
            $lowStockThreshold = intval($input['low_stock_threshold'] ?? 10);
            $unit = htmlspecialchars(trim($input['unit'] ?? ''));
            $categoryId = intval($input['category_id']);
            $image = htmlspecialchars(trim($input['image'] ?? ''));
            $nutritionInfo = htmlspecialchars(trim($input['nutrition_info'] ?? ''));
            $dietTags = is_array($input['diet_tags'] ?? []) ? $input['diet_tags'] : [];
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
            
            // Prepare diet_tags JSON - handle older MySQL versions that don't support JSON
            // For MySQL < 5.7, use TEXT and store as JSON string
            // For MySQL >= 5.7, use JSON type
            if (empty($dietTags) || !is_array($dietTags)) {
                $dietTagsJson = '[]';
            } else {
                $dietTagsJson = json_encode($dietTags);
                if ($dietTagsJson === false) {
                    $dietTagsJson = '[]';
                }
            }
            
            // Convert boolean values to integers for MySQL compatibility (BOOLEAN = TINYINT(1))
            $isEcoFriendlyInt = $isEcoFriendly ? 1 : 0;
            $isFrozenInt = $isFrozen ? 1 : 0;
            $isActiveInt = $isActive ? 1 : 0;
            
            // Handle empty strings as NULL for optional fields
            $brand = empty($brand) ? null : $brand;
            $description = empty($description) ? null : $description;
            $unitSize = empty($unitSize) ? null : $unitSize;
            $unit = empty($unit) ? null : $unit;
            $image = empty($image) ? null : $image;
            $nutritionInfo = empty($nutritionInfo) ? null : $nutritionInfo;
            
            // Insert product - use explicit field list to avoid column count mismatches
            $sql = "INSERT INTO products (name, brand, description, price, unit_size, stock_quantity, low_stock_threshold, unit, category_id, image, nutrition_info, diet_tags, is_eco_friendly, is_frozen, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute([
                $name, 
                $brand, 
                $description, 
                $price, 
                $unitSize, 
                $stockQuantity, 
                $lowStockThreshold, 
                $unit, 
                $categoryId, 
                $image, 
                $nutritionInfo, 
                $dietTagsJson, 
                $isEcoFriendlyInt, 
                $isFrozenInt, 
                $isActiveInt
            ]);
            
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
            
            $allowedFields = ['name', 'brand', 'description', 'price', 'unit_size', 'stock_quantity', 'low_stock_threshold', 'unit', 'category_id', 'image', 'nutrition_info', 'diet_tags', 'is_eco_friendly', 'is_frozen', 'is_active'];
            
            foreach ($allowedFields as $field) {
                if (isset($input[$field])) {
                    if ($field === 'diet_tags') {
                        $updateFields[] = "$field = ?";
                        $params[] = json_encode(is_array($input[$field]) ? $input[$field] : []);
                    } elseif (in_array($field, ['is_eco_friendly', 'is_frozen', 'is_active'])) {
                        $updateFields[] = "$field = ?";
                        $params[] = (bool)$input[$field];
                    } elseif (in_array($field, ['price', 'stock_quantity', 'low_stock_threshold', 'category_id'])) {
                        $updateFields[] = "$field = ?";
                        $params[] = floatval($input[$field]);
                    } else {
                        $updateFields[] = "$field = ?";
                        $params[] = htmlspecialchars(trim($input[$field]));
                    }
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
        $this->requireAdminJson();
        $this->setJsonHeaders();
        
        $page = intval($_GET['page'] ?? 1);
        $limit = intval($_GET['limit'] ?? 20);
        $offset = ($page - 1) * $limit;
        $search = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? 'all'; // all, active, inactive, expired
        
        $where = "WHERE 1=1";
        $params = [];
        
        // Search filter
        if (!empty($search)) {
            $where .= " AND code LIKE ?";
            $params[] = "%$search%";
        }
        
        // Status filter
        if ($status === 'active') {
            $where .= " AND is_active = 1 AND (expiry_date IS NULL OR expiry_date >= CURDATE()) AND (usage_limit IS NULL OR used_count < usage_limit)";
        } elseif ($status === 'inactive') {
            $where .= " AND is_active = 0";
        } elseif ($status === 'expired') {
            $where .= " AND expiry_date < CURDATE()";
        }
        
        // Get coupons
        $sql = "SELECT * FROM coupons $where ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $coupons = $stmt->fetchAll();
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM coupons $where";
        $stmt = $this->pdo->prepare($countSql);
        $stmt->execute($params);
        $total = $stmt->fetch()['total'];
        
        echo json_encode([
            'success' => true,
            'data' => $coupons,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $limit,
                'total' => $total,
                'total_pages' => ceil($total / $limit)
            ]
        ]);
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
            $discountType = $input['discount_type'];
            $discountValue = floatval($input['discount_value']);
            $minOrderAmount = floatval($input['min_order_amount'] ?? 0);
            $startDate = $input['start_date'] ?? null;
            $expiryDate = $input['expiry_date'] ?? null;
            $usageLimit = isset($input['usage_limit']) ? intval($input['usage_limit']) : null;
            $maxUsesPerUser = intval($input['max_uses_per_user'] ?? 1);
            $isActive = isset($input['is_active']) ? (bool)$input['is_active'] : true;
            
            // Validate discount type
            if (!in_array($discountType, ['percentage', 'flat'])) {
                throw new Exception('Invalid discount type');
            }
            
            // Validate discount value
            if ($discountValue <= 0) {
                throw new Exception('Discount value must be greater than 0');
            }
            
            if ($discountType === 'percentage' && $discountValue > 100) {
                throw new Exception('Percentage discount cannot exceed 100%');
            }
            
            // Validate dates
            if ($startDate && $expiryDate && $startDate > $expiryDate) {
                throw new Exception('Start date cannot be after expiry date');
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
                        $updateFields[] = "$field = ?";
                        $params[] = strtoupper(trim($input[$field]));
                    } elseif (in_array($field, ['discount_value', 'min_order_amount'])) {
                        $updateFields[] = "$field = ?";
                        $params[] = floatval($input[$field]);
                    } elseif (in_array($field, ['usage_limit', 'max_uses_per_user'])) {
                        $updateFields[] = "$field = ?";
                        $params[] = intval($input[$field]);
                    } elseif ($field === 'is_active') {
                        $updateFields[] = "$field = ?";
                        $params[] = (bool)$input[$field];
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
            
            $params[] = $couponId;
            $sql = "UPDATE coupons SET " . implode(', ', $updateFields) . " WHERE id = ?";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            
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
        $this->requireAdminJson();
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
        $users = $stmt->fetchAll();
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM users u $where";
        $stmt = $this->pdo->prepare($countSql);
        $stmt->execute($params);
        $total = $stmt->fetch()['total'];
        
        // Process users data
        foreach ($users as &$user) {
            // Parse diet profile JSON
            if ($user['diet_profile']) {
                $user['diet_profile'] = json_decode($user['diet_profile'], true) ?: [];
            } else {
                $user['diet_profile'] = [];
            }
            
            // Remove sensitive data
            unset($user['password_hash']);
            
            // Format dates
            if ($user['last_order_date']) {
                $user['last_order_date_formatted'] = date('M j, Y', strtotime($user['last_order_date']));
            }
            $user['created_at_formatted'] = date('M j, Y', strtotime($user['created_at']));
            
            // Add diet flags for easy display
            $user['diet_flags'] = $this->getDietFlags($user['diet_profile']);
            
            // Add user status indicators
            $user['status_indicators'] = [
                'is_vip' => (bool)$user['is_vip'],
                'is_high_priority' => (bool)$user['is_high_priority'],
                'account_active' => (bool)$user['account_active'],
                'has_admin_notes' => !empty($user['admin_notes'])
            ];
        }
        
        echo json_encode([
            'success' => true,
            'data' => $users,
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
            ]
        ]);
    }
    
    /**
     * Get diet flags from user's diet profile
     */
    private function getDietFlags($dietProfile) {
        $flags = [];
        
        if (empty($dietProfile)) {
            return $flags;
        }
        
        // Extract diet preferences from JSON
        $preferences = $dietProfile['dietary_preferences'] ?? [];
        $goal = $dietProfile['diet_goal'] ?? '';
        
        // Map diet goals to flags
        $goalFlags = [
            'vegetarian' => 'vegetarian',
            'vegan' => 'vegan',
            'diabetes_friendly' => 'diabetic-friendly',
            'low_sodium' => 'low-sodium',
            'ketogenic' => 'keto',
            'paleolithic' => 'paleo',
            'mediterranean' => 'mediterranean',
            'heart_health' => 'heart-healthy',
            'low_carbohydrate' => 'low-carb',
            'high_protein' => 'high-protein'
        ];
        
        if (isset($goalFlags[$goal])) {
            $flags[] = $goalFlags[$goal];
        }
        
        // Add additional flags from preferences
        if (is_array($preferences)) {
            foreach ($preferences as $pref) {
                if (isset($goalFlags[$pref])) {
                    $flags[] = $goalFlags[$pref];
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
            
            // Parse diet profile
            if ($user['diet_profile']) {
                $user['diet_profile'] = json_decode($user['diet_profile'], true) ?: [];
            } else {
                $user['diet_profile'] = [];
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
            
            // Get admin audit log for this user
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
            
            // Log audit actions
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
        $deliverySlot = trim($input['delivery_slot'] ?? '');
        
        // Normalize payment method - frontend may send 'cod', database expects 'cod' (not 'cash_on_delivery')
        $paymentMethodInput = strtolower(trim($input['payment_method'] ?? 'cod'));
        $paymentMethodMap = [
            'cod' => 'cod',
            'cash_on_delivery' => 'cod',
            'bkash' => 'bkash',
            'nagad' => 'nagad',
            'card' => 'card',
            'wallet' => 'cod'  // Wallet not in enum, default to cod
        ];
        $paymentMethod = $paymentMethodMap[$paymentMethodInput] ?? 'cod';

        if (empty($cartItems)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Cart is empty']);
            return;
        }
        
        // Validate required fields
        if (empty($deliverySlot)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Delivery slot is required']);
            return;
        }
        
        // Validate address (either existing or new)
        if (!$deliveryAddressId && (empty($input['new_address_line1']) || empty($input['new_city']))) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Delivery address is required']);
            return;
        }

        try {
            $this->pdo->beginTransaction();
            
            error_log("🛒 Starting order creation for user: $userId");
            error_log("🛒 Cart items count: " . count($cartItems));
            error_log("🛒 Delivery address ID: $deliveryAddressId");
            error_log("🛒 Delivery slot: $deliverySlot");
            error_log("🛒 Payment method: $paymentMethod");

            // Handle new address creation if provided
            if (!$deliveryAddressId && !empty($input['new_address_line1']) && !empty($input['new_city'])) {
                error_log("🏠 Creating new address for user");
                $addressType = $input['new_address_type'] ?? 'home';
                $isDefault = isset($input['new_is_default']) && $input['new_is_default'] ? 1 : 0;
                
                // If this is the default address, unset other defaults
                if ($isDefault) {
                    $stmt = $this->pdo->prepare("UPDATE user_addresses SET is_default = FALSE WHERE user_id = ?");
                    $stmt->execute([$userId]);
                }
                
                $stmt = $this->pdo->prepare("INSERT INTO user_addresses (user_id, address_type, address_line1, address_line2, city, state, zip_code, country, is_default) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $userId,
                    $addressType,
                    trim($input['new_address_line1']),
                    trim($input['new_address_line2'] ?? ''),
                    trim($input['new_city']),
                    trim($input['new_state'] ?? ''),
                    trim($input['new_zip_code'] ?? ''),
                    trim($input['new_country'] ?? 'Bangladesh'),
                    $isDefault
                ]);
                $deliveryAddressId = $this->pdo->lastInsertId();
                error_log("✅ New address created with ID: $deliveryAddressId");
            }

            // Calculate totals on server
            $subtotal = 0.0;
            foreach ($cartItems as $ci) {
                $productId = intval($ci['product_id']);
                $qty = max(1, intval($ci['quantity']));
                $stmt = $this->pdo->prepare("SELECT price FROM products WHERE id = ?");
                $stmt->execute([$productId]);
                $row = $stmt->fetch();
                if (!$row) { 
                    throw new Exception("Invalid product in cart: Product ID $productId not found"); 
                }
                $subtotal += floatval($row['price']) * $qty;
            }
            $deliveryFee = floatval($input['delivery_fee'] ?? 0);
            $discount = floatval($input['discount'] ?? 0);
            $totalPayable = max(0, $subtotal + $deliveryFee - $discount);
            
            error_log("💰 Order totals - Subtotal: $subtotal, Delivery: $deliveryFee, Discount: $discount, Total: $totalPayable");

            // Create order
            // Note: Database enum for payment_status: 'unpaid','pending','paid','failed','refunded'
            // Database enum for payment_method: 'bkash','nagad','card','cod'
            // Database enum for status: 'pending','confirmed','packed','out_for_delivery','delivered','cancelled'
            $packagingOption = strtolower(trim($input['packaging_option'] ?? 'standard'));
            $packagingCost = 0;
            if ($packagingOption === 'reusable_bag') {
                $packagingCost = 20.00;
            }
            $finalTotalWithPackaging = $totalPayable + $packagingCost;
            
            $stmt = $this->pdo->prepare("INSERT INTO orders (user_id, total_amount, delivery_address_id, delivery_slot, payment_method, payment_status, status, packaging_option, packaging_cost) VALUES (?, ?, ?, ?, ?, 'unpaid', 'pending', ?, ?)");
            $stmt->execute([$userId, $finalTotalWithPackaging, $deliveryAddressId ?: null, $deliverySlot, $paymentMethod, $packagingOption, $packagingCost]);
            $orderId = $this->pdo->lastInsertId();
            
            if (!$orderId) {
                throw new Exception('Failed to create order - no order ID returned');
            }
            
            error_log("✅ Order created successfully - Order ID: $orderId, User ID: $userId, Total: $totalPayable");

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
            $cartCleared = $stmt->rowCount();
            error_log("🛒 Cleared $cartCleared cart items for user");

            // Handle selected surprise gift - link to order
            require_once __DIR__ . '/../helpers/SurpriseGiftHelper.php';
            $surpriseGiftHelper = new SurpriseGiftHelper($this->pdo);
            
            // Check if user has a selected surprise gift (from database with NULL order_id)
            $stmt = $this->pdo->prepare("
                SELECT usg.*, sg.product_id, sg.quantity as gift_quantity, sg.name as gift_name
                FROM user_surprise_gifts usg
                JOIN surprise_gifts sg ON usg.surprise_gift_id = sg.id
                WHERE usg.user_id = ? AND usg.order_id IS NULL
                LIMIT 1
            ");
            $stmt->execute([$userId]);
            $selectedGift = $stmt->fetch();
            
            if ($selectedGift) {
                error_log("🎁 Found selected surprise gift: " . $selectedGift['gift_name']);
                
                // Update user_surprise_gifts with order_id
                $stmt = $this->pdo->prepare("
                    UPDATE user_surprise_gifts 
                    SET order_id = ? 
                    WHERE id = ?
                ");
                $stmt->execute([$orderId, $selectedGift['id']]);
                
                // Add gift product to order_items (free - price 0)
                $giftProductId = $selectedGift['product_id'];
                $giftQuantity = $selectedGift['quantity'] ?? 1;
                
                $stmt = $this->pdo->prepare("SELECT name, image FROM products WHERE id = ?");
                $stmt->execute([$giftProductId]);
                $giftProduct = $stmt->fetch();
                
                if ($giftProduct) {
                    $stmt = $this->pdo->prepare("
                        INSERT INTO order_items (order_id, product_id, quantity, unit_price, total_price, product_name_snapshot, product_image_snapshot) 
                        VALUES (?, ?, ?, 0.00, 0.00, ?, ?)
                    ");
                    $stmt->execute([
                        $orderId, 
                        $giftProductId, 
                        $giftQuantity,
                        $giftProduct['name'] ?? null,
                        $giftProduct['image'] ?? null
                    ]);
                    
                    // Update gift usage count
                    $stmt = $this->pdo->prepare("
                        UPDATE surprise_gifts 
                        SET current_uses = current_uses + 1 
                        WHERE id = ?
                    ");
                    $stmt->execute([$selectedGift['surprise_gift_id']]);
                    
                    // Update order to mark it has surprise gift
                    $giftMessage = "🎁 You unlocked a surprise gift! " . $selectedGift['gift_name'] . " added to your order";
                    $stmt = $this->pdo->prepare("
                        UPDATE orders 
                        SET has_surprise_gift = 1, 
                            surprise_gift_message = ?
                        WHERE id = ?
                    ");
                    $stmt->execute([$giftMessage, $orderId]);
                    
                    error_log("✅ Surprise gift linked to order: " . $selectedGift['gift_name']);
                }
            } else {
                // Also check session for backward compatibility
                if (isset($_SESSION['selected_surprise_gift'])) {
                    $sessionGift = $_SESSION['selected_surprise_gift'];
                    error_log("🎁 Found surprise gift in session: " . $sessionGift['name']);
                    
                    // Get gift details
                    $stmt = $this->pdo->prepare("
                        SELECT * FROM surprise_gifts 
                        WHERE id = ?
                    ");
                    $stmt->execute([$sessionGift['gift_id']]);
                    $gift = $stmt->fetch();
                    
                    if ($gift) {
                        // Add to user_surprise_gifts with order_id
                        $stmt = $this->pdo->prepare("
                            INSERT INTO user_surprise_gifts (user_id, order_id, surprise_gift_id, quantity) 
                            VALUES (?, ?, ?, ?)
                        ");
                        $stmt->execute([$userId, $orderId, $gift['id'], $gift['quantity'] ?? 1]);
                        
                        // Add gift to order items
                        $stmt = $this->pdo->prepare("SELECT name, image FROM products WHERE id = ?");
                        $stmt->execute([$gift['product_id']]);
                        $giftProduct = $stmt->fetch();
                        
                        if ($giftProduct) {
                            $stmt = $this->pdo->prepare("
                                INSERT INTO order_items (order_id, product_id, quantity, unit_price, total_price, product_name_snapshot, product_image_snapshot) 
                                VALUES (?, ?, ?, 0.00, 0.00, ?, ?)
                            ");
                            $stmt->execute([
                                $orderId, 
                                $gift['product_id'], 
                                $gift['quantity'] ?? 1,
                                $giftProduct['name'] ?? null,
                                $giftProduct['image'] ?? null
                            ]);
                            
                            // Update gift usage count
                            $stmt = $this->pdo->prepare("
                                UPDATE surprise_gifts 
                                SET current_uses = current_uses + 1 
                                WHERE id = ?
                            ");
                            $stmt->execute([$gift['id']]);
                            
                            // Update order
                            $giftMessage = "🎁 You unlocked a surprise gift! " . $gift['name'] . " added to your order";
                            $stmt = $this->pdo->prepare("
                                UPDATE orders 
                                SET has_surprise_gift = 1, 
                                    surprise_gift_message = ?
                                WHERE id = ?
                            ");
                            $stmt->execute([$giftMessage, $orderId]);
                            
                            error_log("✅ Surprise gift from session linked to order");
                        }
                        
                        // Clear session
                        unset($_SESSION['selected_surprise_gift']);
                    }
                }
            }

            $this->pdo->commit();
            error_log("✅ Transaction committed successfully");

            $response = [
                'success' => true,
                'order_id' => intval($orderId),
                'total_payable' => $finalTotalWithPackaging,
                'payment_method' => $paymentMethod,
                'message' => 'Order placed successfully!'
            ];
            
            error_log("📤 Sending success response: " . json_encode($response));
            echo json_encode($response);
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) { 
                $this->pdo->rollback(); 
                error_log("❌ Transaction rolled back due to error");
            }
            error_log("❌ Order creation failed: " . $e->getMessage());
            error_log("❌ Stack trace: " . $e->getTraceAsString());
            http_response_code(500);
            echo json_encode([
                'success' => false, 
                'error' => 'Failed to create order: ' . $e->getMessage()
            ]);
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
