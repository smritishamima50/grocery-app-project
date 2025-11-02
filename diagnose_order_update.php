<?php
/**
 * Diagnostic tool to test order update functionality
 * Visit: http://localhost/diagnose_order_update.php
 */

session_start();
require_once 'config/database.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Order Update Diagnostic Tool</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #1a1a1a; color: #0f0; }
        .section { margin: 20px 0; padding: 15px; background: #2a2a2a; border-left: 3px solid #0f0; }
        .error { color: #f44; border-color: #f44; }
        .success { color: #4f4; border-color: #4f4; }
        .warning { color: #ff4; border-color: #ff4; }
        h2 { margin-top: 0; }
        pre { background: #000; padding: 10px; overflow-x: auto; }
        .test-btn { padding: 10px 20px; margin: 5px; background: #0f0; color: #000; border: none; cursor: pointer; }
        .test-btn:hover { background: #4f4; }
    </style>
</head>
<body>
    <h1>🔍 Order Update Diagnostic Tool</h1>
    
    <?php
    // Test 1: Session & Admin Status
    echo "<div class='section'>";
    echo "<h2>Test 1: Session & Admin Status</h2>";
    if (isset($_SESSION['user_id'])) {
        echo "<div class='success'>✅ User logged in (ID: {$_SESSION['user_id']})</div>";
        
        // Check admin status
        $stmt = $pdo->prepare("SELECT id, email, role FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        
        if ($user) {
            echo "<div class='success'>✅ User found in database</div>";
            echo "<pre>Email: {$user['email']}\nRole: {$user['role']}</pre>";
            
            if ($user['role'] === 'admin') {
                echo "<div class='success'>✅ User has admin role</div>";
            } else {
                echo "<div class='error'>❌ User is NOT admin! Role: {$user['role']}</div>";
            }
        } else {
            echo "<div class='error'>❌ User not found in database</div>";
        }
    } else {
        echo "<div class='error'>❌ User not logged in</div>";
        echo "<div class='warning'>⚠️ Go to /admin/orders page first to login</div>";
    }
    echo "</div>";
    
    // Test 2: Database Connection
    echo "<div class='section'>";
    echo "<h2>Test 2: Database Connection</h2>";
    try {
        $pdo->query("SELECT 1");
        echo "<div class='success'>✅ Database connection OK</div>";
    } catch (Exception $e) {
        echo "<div class='error'>❌ Database connection failed: {$e->getMessage()}</div>";
    }
    echo "</div>";
    
    // Test 3: Orders Table
    echo "<div class='section'>";
    echo "<h2>Test 3: Orders Table Structure</h2>";
    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM orders");
        $columns = $stmt->fetchAll();
        $hasStatus = false;
        $hasDriver = false;
        
        foreach ($columns as $col) {
            if ($col['Field'] === 'status') {
                $hasStatus = true;
                echo "<div class='success'>✅ Column 'status' exists (Type: {$col['Type']})</div>";
            }
            if ($col['Field'] === 'assigned_driver') {
                $hasDriver = true;
                echo "<div class='success'>✅ Column 'assigned_driver' exists (Type: {$col['Type']})</div>";
            }
        }
        
        if (!$hasStatus) echo "<div class='error'>❌ Column 'status' NOT found</div>";
        if (!$hasDriver) echo "<div class='error'>❌ Column 'assigned_driver' NOT found</div>";
    } catch (Exception $e) {
        echo "<div class='error'>❌ Error checking orders table: {$e->getMessage()}</div>";
    }
    echo "</div>";
    
    // Test 4: Sample Orders
    echo "<div class='section'>";
    echo "<h2>Test 4: Sample Orders</h2>";
    try {
        $stmt = $pdo->query("SELECT id, status, assigned_driver, updated_at FROM orders LIMIT 5");
        $orders = $stmt->fetchAll();
        
        if (empty($orders)) {
            echo "<div class='warning'>⚠️ No orders found in database</div>";
        } else {
            echo "<div class='success'>✅ Found " . count($orders) . " orders</div>";
            echo "<pre>";
            foreach ($orders as $order) {
                echo "Order #{$order['id']}: status='{$order['status']}', driver=" . ($order['assigned_driver'] ?? 'NULL') . "\n";
            }
            echo "</pre>";
        }
    } catch (Exception $e) {
        echo "<div class='error'>❌ Error fetching orders: {$e->getMessage()}</div>";
    }
    echo "</div>";
    
    // Test 5: Routing
    echo "<div class='section'>";
    echo "<h2>Test 5: Route Pattern Matching</h2>";
    
    // Check if index.php has the route
    $indexContent = file_get_contents('index.php');
    if (strpos($indexContent, 'api/admin/orders') !== false) {
        echo "<div class='success'>✅ Route pattern found in index.php</div>";
        
        // Extract the pattern
        if (preg_match('/api\/admin\/orders[^\']+\'/', $indexContent, $matches)) {
            echo "<pre>Route pattern: " . htmlspecialchars($matches[0]) . "</pre>";
        }
    } else {
        echo "<div class='error'>❌ Route pattern NOT found in index.php</div>";
    }
    echo "</div>";
    
    // Test 6: API Controller File
    echo "<div class='section'>";
    echo "<h2>Test 6: API Controller</h2>";
    if (file_exists('app/controllers/ApiController.php')) {
        echo "<div class='success'>✅ ApiController.php exists</div>";
        
        $controllerContent = file_get_contents('app/controllers/ApiController.php');
        if (strpos($controllerContent, 'function updateOrder') !== false) {
            echo "<div class='success'>✅ updateOrder() method exists</div>";
        } else {
            echo "<div class='error'>❌ updateOrder() method NOT found</div>";
        }
    } else {
        echo "<div class='error'>❌ ApiController.php NOT found</div>";
    }
    echo "</div>";
    
    // Test 7: Live API Test (if logged in as admin)
    if (isset($_SESSION['user_id']) && isset($user) && $user['role'] === 'admin') {
        echo "<div class='section'>";
        echo "<h2>Test 7: Live API Test</h2>";
        
        if (isset($_GET['test_order_id'])) {
            $testOrderId = (int)$_GET['test_order_id'];
            echo "<div class='warning'>Testing API endpoint with Order ID: $testOrderId</div>";
            
            // Get a real order first
            $stmt = $pdo->prepare("SELECT id, status FROM orders WHERE id = ?");
            $stmt->execute([$testOrderId]);
            $testOrder = $stmt->fetch();
            
            if ($testOrder) {
                echo "<div class='success'>✅ Test order found: Status = '{$testOrder['status']}'</div>";
                
                // Simulate API call
                $url = "http://localhost/api/admin/orders/$testOrderId";
                echo "<pre>Testing: PATCH $url</pre>";
                
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['status' => 'confirmed']));
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'Cookie: ' . session_name() . '=' . session_id()
                ]);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_COOKIE, session_name() . '=' . session_id());
                
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                
                echo "<div class='success'>Response Code: $httpCode</div>";
                echo "<pre>" . htmlspecialchars($response) . "</pre>";
            } else {
                echo "<div class='error'>❌ Order #$testOrderId not found</div>";
            }
        } else {
            // Get first order for testing
            $stmt = $pdo->query("SELECT id FROM orders LIMIT 1");
            $firstOrder = $stmt->fetch();
            
            if ($firstOrder) {
                echo "<div class='success'>✅ Found test order: #{$firstOrder['id']}</div>";
                echo "<a href='?test_order_id={$firstOrder['id']}' class='test-btn'>Test API Endpoint</a>";
            } else {
                echo "<div class='warning'>⚠️ No orders available for testing</div>";
            }
        }
        
        echo "</div>";
    } else {
        echo "<div class='section'>";
        echo "<h2>Test 7: Live API Test</h2>";
        echo "<div class='warning'>⚠️ Login as admin to test API endpoint</div>";
        echo "</div>";
    }
    ?>
    
    <div class="section">
        <h2>Next Steps</h2>
        <ol>
            <li>Check browser console (F12) when updating an order</li>
            <li>Check PHP error log: <code>C:\xampp\apache\logs\error.log</code></li>
            <li>Check Network tab (F12 → Network) for the PATCH request</li>
            <li>See <code>QUICK_FIX_GUIDE.md</code> for detailed troubleshooting</li>
        </ol>
    </div>
</body>
</html>
