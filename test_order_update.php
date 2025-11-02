<?php
/**
 * DIRECT DATABASE TEST - Test if we can update orders table
 * This bypasses all API/route logic and directly tests the database
 * 
 * Visit: http://localhost/test_order_update.php?order_id=1&status=packed&driver=TestDriver
 */

session_start();
require_once 'config/database.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Direct Order Update Test</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #1a1a1a; color: #0f0; }
        .success { color: #4f4; }
        .error { color: #f44; }
        .info { color: #ff4; }
        pre { background: #000; padding: 10px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>🧪 Direct Database Update Test</h1>
    
    <?php
    $orderId = $_GET['order_id'] ?? null;
    $newStatus = $_GET['status'] ?? null;
    $newDriver = $_GET['driver'] ?? null;
    
    if (!$orderId) {
        echo "<div class='info'>⚠️ Usage: ?order_id=1&status=packed&driver=TestDriver</div>";
        
        // Show sample orders
        echo "<h2>Available Orders:</h2>";
        try {
            $stmt = $pdo->query("SELECT id, status, assigned_driver FROM orders LIMIT 10");
            $orders = $stmt->fetchAll();
            if ($orders) {
                echo "<table border='1' style='color: #0f0;'>";
                echo "<tr><th>ID</th><th>Status</th><th>Driver</th><th>Test Link</th></tr>";
                foreach ($orders as $order) {
                    echo "<tr>";
                    echo "<td>{$order['id']}</td>";
                    echo "<td>{$order['status']}</td>";
                    echo "<td>" . ($order['assigned_driver'] ?? 'NULL') . "</td>";
                    echo "<td><a href='?order_id={$order['id']}&status=packed&driver=TestDriver' style='color: #4f4;'>Test Update</a></td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<div class='error'>❌ No orders found in database</div>";
            }
        } catch (Exception $e) {
            echo "<div class='error'>❌ Error: {$e->getMessage()}</div>";
        }
        exit;
    }
    
    echo "<div class='info'>📋 Testing Order ID: $orderId</div>";
    if ($newStatus) echo "<div class='info'>📋 New Status: $newStatus</div>";
    if ($newDriver) echo "<div class='info'>📋 New Driver: $newDriver</div>";
    
    try {
        // Step 1: Get current order
        echo "<h2>Step 1: Get Current Order</h2>";
        $stmt = $pdo->prepare("SELECT id, status, assigned_driver, updated_at FROM orders WHERE id = ?");
        $stmt->execute([$orderId]);
        $before = $stmt->fetch();
        
        if (!$before) {
            echo "<div class='error'>❌ Order #$orderId not found!</div>";
            exit;
        }
        
        echo "<div class='success'>✅ Order found</div>";
        echo "<pre>";
        echo "BEFORE UPDATE:\n";
        echo "  ID: {$before['id']}\n";
        echo "  Status: {$before['status']}\n";
        echo "  Driver: " . ($before['assigned_driver'] ?? 'NULL') . "\n";
        echo "  Updated At: {$before['updated_at']}\n";
        echo "</pre>";
        
        // Step 2: Start transaction
        echo "<h2>Step 2: Start Transaction</h2>";
        $pdo->beginTransaction();
        echo "<div class='success'>✅ Transaction started</div>";
        
        // Step 3: Build UPDATE query
        echo "<h2>Step 3: Build UPDATE Query</h2>";
        $updateFields = [];
        $params = [];
        
        if ($newStatus) {
            $updateFields[] = 'status = ?';
            $params[] = $newStatus;
        }
        
        if ($newDriver !== null) {
            $updateFields[] = 'assigned_driver = ?';
            $params[] = ($newDriver === '' || $newDriver === null) ? null : $newDriver;
        }
        
        if (empty($updateFields)) {
            echo "<div class='error'>❌ No fields to update</div>";
            $pdo->rollBack();
            exit;
        }
        
        $updateFields[] = 'updated_at = NOW()';
        $params[] = $orderId;
        
        $sql = "UPDATE orders SET " . implode(', ', $updateFields) . " WHERE id = ?";
        echo "<pre>SQL: $sql</pre>";
        echo "<pre>Params: " . print_r($params, true) . "</pre>";
        
        // Step 4: Execute UPDATE
        echo "<h2>Step 4: Execute UPDATE</h2>";
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute($params);
        $rowsAffected = $stmt->rowCount();
        
        echo "<div class='" . ($result ? 'success' : 'error') . "'>";
        echo ($result ? '✅' : '❌') . " Execute result: " . ($result ? 'TRUE' : 'FALSE');
        echo "</div>";
        echo "<div class='info'>📊 Rows affected: $rowsAffected</div>";
        
        if ($rowsAffected === 0) {
            echo "<div class='error'>❌ NO ROWS AFFECTED! This is the problem!</div>";
            $pdo->rollBack();
            exit;
        }
        
        // Step 5: Commit
        echo "<h2>Step 5: Commit Transaction</h2>";
        if ($pdo->commit()) {
            echo "<div class='success'>✅ Transaction committed</div>";
        } else {
            echo "<div class='error'>❌ Commit failed!</div>";
            exit;
        }
        
        // Step 6: Verify
        echo "<h2>Step 6: Verify Update</h2>";
        $stmt = $pdo->prepare("SELECT id, status, assigned_driver, updated_at FROM orders WHERE id = ?");
        $stmt->execute([$orderId]);
        $after = $stmt->fetch();
        
        echo "<pre>";
        echo "AFTER UPDATE:\n";
        echo "  ID: {$after['id']}\n";
        echo "  Status: {$after['status']}\n";
        echo "  Driver: " . ($after['assigned_driver'] ?? 'NULL') . "\n";
        echo "  Updated At: {$after['updated_at']}\n";
        echo "</pre>";
        
        // Compare
        $statusChanged = ($newStatus && $before['status'] !== $after['status']);
        $driverChanged = ($newDriver !== null && $before['assigned_driver'] !== $after['assigned_driver']);
        
        if ($statusChanged || $driverChanged) {
            echo "<div class='success'>✅ UPDATE SUCCESSFUL!</div>";
            if ($statusChanged) {
                echo "<div class='success'>   ✓ Status changed: '{$before['status']}' → '{$after['status']}'</div>";
            }
            if ($driverChanged) {
                echo "<div class='success'>   ✓ Driver changed: '" . ($before['assigned_driver'] ?? 'NULL') . "' → '" . ($after['assigned_driver'] ?? 'NULL') . "'</div>";
            }
        } else {
            echo "<div class='error'>❌ UPDATE FAILED - Values did not change!</div>";
        }
        
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        echo "<div class='error'>❌ ERROR: {$e->getMessage()}</div>";
        echo "<pre>{$e->getTraceAsString()}</pre>";
    }
    ?>
    
    <br><br>
    <a href="?" style="color: #4f4;">← Test Another Order</a>
</body>
</html>

