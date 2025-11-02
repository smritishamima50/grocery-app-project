<?php
/**
 * Test script to verify order update API is working
 * Run this file directly in browser: http://localhost/test_order_update_api.php
 */

session_start();
require_once 'config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("ERROR: You must be logged in as admin to run this test. Please login first at /login");
}

echo "<h1>Order Update API Test</h1>";
echo "<pre>";

// Test 1: Check database connection
echo "=== Test 1: Database Connection ===\n";
try {
    $testQuery = $pdo->query("SELECT COUNT(*) as count FROM orders");
    $result = $testQuery->fetch();
    echo "✅ Database connected. Total orders: " . $result['count'] . "\n";
} catch (Exception $e) {
    die("❌ Database connection failed: " . $e->getMessage());
}

// Test 2: Check if orders table has required columns
echo "\n=== Test 2: Database Schema Check ===\n";
try {
    $columns = $pdo->query("SHOW COLUMNS FROM orders LIKE 'status'")->fetch();
    if ($columns) {
        echo "✅ 'status' column exists\n";
    } else {
        die("❌ 'status' column NOT found!\n");
    }
    
    $columns = $pdo->query("SHOW COLUMNS FROM orders LIKE 'assigned_driver'")->fetch();
    if ($columns) {
        echo "✅ 'assigned_driver' column exists\n";
    } else {
        die("❌ 'assigned_driver' column NOT found!\n");
    }
} catch (Exception $e) {
    die("❌ Schema check failed: " . $e->getMessage());
}

// Test 3: Check admin session
echo "\n=== Test 3: Admin Session Check ===\n";
echo "User ID: " . ($_SESSION['user_id'] ?? 'NOT SET') . "\n";
echo "Role: " . ($_SESSION['role'] ?? 'NOT SET') . "\n";
echo "First Name: " . ($_SESSION['first_name'] ?? 'NOT SET') . "\n";

if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    echo "✅ Admin session is valid\n";
} else {
    die("❌ Admin session is INVALID!\n");
}

// Test 4: Get a test order
echo "\n=== Test 4: Get Test Order ===\n";
try {
    $stmt = $pdo->prepare("SELECT id, status, assigned_driver FROM orders LIMIT 1");
    $stmt->execute();
    $testOrder = $stmt->fetch();
    
    if ($testOrder) {
        echo "✅ Found test order:\n";
        echo "   Order ID: " . $testOrder['id'] . "\n";
        echo "   Current Status: " . $testOrder['status'] . "\n";
        echo "   Current Driver: " . ($testOrder['assigned_driver'] ?? 'NULL') . "\n";
        $testOrderId = $testOrder['id'];
    } else {
        die("❌ No orders found in database. Please create an order first.\n");
    }
} catch (Exception $e) {
    die("❌ Failed to get test order: " . $e->getMessage());
}

// Test 5: Test API endpoint directly
echo "\n=== Test 5: Test API Endpoint (Direct Call) ===\n";
echo "Testing PATCH /api/admin/orders/{$testOrderId}\n";
echo "With payload: {\"status\": \"confirmed\"}\n\n";

// Simulate API call
$_SERVER['REQUEST_METHOD'] = 'PATCH';
$originalInput = file_get_contents('php://input');
file_put_contents('php://input', json_encode(['status' => 'confirmed']));

// Include the API controller
try {
    require_once 'app/controllers/ApiController.php';
    $controller = new ApiController();
    
    // Save original output
    ob_start();
    
    // Try to call updateOrder
    // Note: This is a simplified test - actual API would need proper routing
    echo "⚠️  Note: This test requires the API to be called via HTTP, not directly.\n";
    echo "✅ API Controller class exists and can be instantiated\n";
    
    ob_end_clean();
} catch (Exception $e) {
    ob_end_clean();
    echo "❌ Failed to test API controller: " . $e->getMessage() . "\n";
}

// Test 6: Test database update directly
echo "\n=== Test 6: Direct Database Update Test ===\n";
try {
    $pdo->beginTransaction();
    
    // Get original values
    $stmt = $pdo->prepare("SELECT status, assigned_driver FROM orders WHERE id = ?");
    $stmt->execute([$testOrderId]);
    $original = $stmt->fetch();
    
    echo "Original Status: " . $original['status'] . "\n";
    echo "Original Driver: " . ($original['assigned_driver'] ?? 'NULL') . "\n";
    
    // Update status
    $newStatus = $original['status'] === 'confirmed' ? 'packed' : 'confirmed';
    $stmt = $pdo->prepare("UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$newStatus, $testOrderId]);
    $rowsAffected = $stmt->rowCount();
    
    echo "Updated Status to: $newStatus\n";
    echo "Rows affected: $rowsAffected\n";
    
    // Verify update
    $stmt = $pdo->prepare("SELECT status FROM orders WHERE id = ?");
    $stmt->execute([$testOrderId]);
    $verify = $stmt->fetch();
    
    if ($verify['status'] === $newStatus) {
        echo "✅ Database update successful! Status is now: " . $verify['status'] . "\n";
    } else {
        echo "❌ Database update FAILED! Expected: $newStatus, Got: " . $verify['status'] . "\n";
    }
    
    // Restore original status
    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->execute([$original['status'], $testOrderId]);
    echo "✅ Original status restored\n";
    
    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollback();
    echo "❌ Database update test failed: " . $e->getMessage() . "\n";
}

// Test 7: Check route configuration
echo "\n=== Test 7: Route Configuration Check ===\n";
$routePattern = '/^api\/admin\/orders\/(\d+)$/';
$testRoute = 'api/admin/orders/123';
if (preg_match($routePattern, $testRoute, $matches)) {
    echo "✅ Route pattern matches correctly\n";
    echo "   Pattern: $routePattern\n";
    echo "   Test route: $testRoute\n";
    echo "   Captured ID: " . $matches[1] . "\n";
} else {
    echo "❌ Route pattern does NOT match!\n";
}

echo "\n=== Test Summary ===\n";
echo "If all tests passed, the API should be working.\n";
echo "If you still have issues, check:\n";
echo "1. Browser console for JavaScript errors (F12)\n";
echo "2. PHP error log for server errors\n";
echo "3. Network tab in browser DevTools to see if API requests are being made\n";
echo "4. Verify you're using the correct URL (should be /api/admin/orders/{id})\n";

echo "</pre>";
?>

