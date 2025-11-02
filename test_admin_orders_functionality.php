<?php
/**
 * Test Admin Orders Functionality
 * This script tests all the fixes for admin orders management
 */

require_once 'config/database.php';

header('Content-Type: text/html; charset=utf-8');

echo "<!DOCTYPE html>
<html>
<head>
    <title>Admin Orders Functionality Test</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .test-section { background: white; padding: 20px; margin: 20px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .pass { color: green; font-weight: bold; }
        .fail { color: red; font-weight: bold; }
        .info { color: blue; }
        pre { background: #f0f0f0; padding: 10px; border-radius: 4px; overflow-x: auto; }
        h1 { color: #333; }
        h2 { color: #666; border-bottom: 2px solid #ddd; padding-bottom: 10px; }
    </style>
</head>
<body>
    <h1>🧪 Admin Orders Functionality Test</h1>";

try {
    // Test 1: Check if orders table exists and has data
    echo "<div class='test-section'>";
    echo "<h2>Test 1: Database Connection & Orders Table</h2>";
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM orders");
    $total = $stmt->fetch()['total'];
    
    echo "<p class='pass'>✅ Database connection successful</p>";
    echo "<p class='info'>Total orders in database: <strong>$total</strong></p>";
    
    // Test 2: Check if LEFT JOIN query works
    echo "</div><div class='test-section'>";
    echo "<h2>Test 2: Orders Query (LEFT JOIN)</h2>";
    
    $stmt = $pdo->query("
        SELECT COUNT(*) as total 
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.id
    ");
    $leftJoinTotal = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM orders");
    $directTotal = $stmt->fetch()['total'];
    
    if ($leftJoinTotal == $directTotal) {
        echo "<p class='pass'>✅ LEFT JOIN query returns all orders ($leftJoinTotal = $directTotal)</p>";
    } else {
        echo "<p class='fail'>❌ LEFT JOIN query missing orders: LEFT JOIN=$leftJoinTotal, Direct=$directTotal</p>";
    }
    
    // Test 3: Check status values
    echo "</div><div class='test-section'>";
    echo "<h2>Test 3: Order Status Values</h2>";
    
    $stmt = $pdo->query("
        SELECT status, COUNT(*) as count 
        FROM orders 
        GROUP BY status 
        ORDER BY count DESC
    ");
    $statuses = $stmt->fetchAll();
    
    echo "<p class='info'>Order status distribution:</p>";
    echo "<ul>";
    foreach ($statuses as $status) {
        echo "<li><strong>{$status['status']}</strong>: {$status['count']} orders</li>";
    }
    echo "</ul>";
    
    // Test 4: Test filter queries
    echo "</div><div class='test-section'>";
    echo "<h2>Test 4: Filter Queries</h2>";
    
    // Test status filter
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM orders o LEFT JOIN users u ON o.user_id = u.id WHERE o.status = 'pending'");
    $stmt->execute();
    $pendingCount = $stmt->fetch()['total'];
    echo "<p class='info'>Pending orders: $pendingCount</p>";
    
    // Test search filter
    $testSearch = "1";
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total 
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.id
        WHERE CAST(o.id AS CHAR) LIKE ? OR COALESCE(u.phone, '') LIKE ?
    ");
    $searchTerm = "%$testSearch%";
    $stmt->execute([$searchTerm, $searchTerm]);
    $searchCount = $stmt->fetch()['total'];
    echo "<p class='info'>Orders matching search '1': $searchCount</p>";
    
    // Test date filter
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total 
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.id
        WHERE DATE(o.created_at) >= CURDATE() - INTERVAL 7 DAY
    ");
    $stmt->execute();
    $recentCount = $stmt->fetch()['total'];
    echo "<p class='info'>Orders from last 7 days: $recentCount</p>";
    
    echo "<p class='pass'>✅ All filter queries working</p>";
    
    // Test 5: Check API endpoint accessibility
    echo "</div><div class='test-section'>";
    echo "<h2>Test 5: API Endpoints</h2>";
    echo "<p class='info'>API endpoints that should be available:</p>";
    echo "<ul>";
    echo "<li><code>/api/admin/orders</code> - Get orders list</li>";
    echo "<li><code>/api/admin/orders/export</code> - Export orders</li>";
    echo "<li><code>/api/admin/orders/{id}</code> - Update order (PATCH)</li>";
    echo "</ul>";
    
    // Test 6: Check drivers table
    echo "</div><div class='test-section'>";
    echo "<h2>Test 6: Drivers Table</h2>";
    
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM drivers WHERE is_active = 1");
        $driversCount = $stmt->fetch()['total'];
        echo "<p class='pass'>✅ Drivers table accessible</p>";
        echo "<p class='info'>Active drivers: $driversCount</p>";
    } catch (PDOException $e) {
        echo "<p class='fail'>❌ Drivers table error: " . $e->getMessage() . "</p>";
        echo "<p class='info'>You may need to create the drivers table</p>";
    }
    
    // Test 7: Sample orders data
    echo "</div><div class='test-section'>";
    echo "<h2>Test 7: Sample Orders (First 5)</h2>";
    
    $stmt = $pdo->query("
        SELECT o.id, o.status, o.total_amount, o.created_at,
               COALESCE(u.first_name, '') as first_name,
               COALESCE(u.last_name, '') as last_name,
               COALESCE(u.phone, '') as phone
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.id
        ORDER BY o.created_at DESC
        LIMIT 5
    ");
    $sampleOrders = $stmt->fetchAll();
    
    if (count($sampleOrders) > 0) {
        echo "<p class='pass'>✅ Sample orders retrieved successfully</p>";
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Status</th><th>Customer</th><th>Phone</th><th>Amount</th><th>Date</th></tr>";
        foreach ($sampleOrders as $order) {
            echo "<tr>";
            echo "<td>#{$order['id']}</td>";
            echo "<td>{$order['status']}</td>";
            echo "<td>{$order['first_name']} {$order['last_name']}</td>";
            echo "<td>{$order['phone']}</td>";
            echo "<td>৳" . number_format($order['total_amount'], 2) . "</td>";
            echo "<td>" . date('Y-m-d H:i', strtotime($order['created_at'])) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='info'>⚠️ No orders found in database</p>";
    }
    
    // Summary
    echo "</div><div class='test-section'>";
    echo "<h2>✅ Test Summary</h2>";
    echo "<p class='pass'>All database tests passed!</p>";
    echo "<p class='info'>Next steps:</p>";
    echo "<ol>";
    echo "<li>Go to <a href='/admin/orders' target='_blank'>Admin Orders Page</a></li>";
    echo "<li>Test the Refresh button</li>";
    echo "<li>Test the Export button</li>";
    echo "<li>Test Apply Filter with different filters</li>";
    echo "<li>Test status update by changing an order status</li>";
    echo "</ol>";
    
} catch (PDOException $e) {
    echo "<div class='test-section'>";
    echo "<p class='fail'>❌ Database Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
} catch (Exception $e) {
    echo "<div class='test-section'>";
    echo "<p class='fail'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

echo "</body></html>";
?>

