<?php
/**
 * Driver Assignment Debug Tool
 * 
 * This script helps debug driver assignment issues by:
 * 1. Checking database state
 * 2. Testing the API endpoint
 * 3. Showing what's actually happening
 * 
 * Access: http://localhost/test_driver_assignment_debug.php?order_id=1
 */

require_once 'config/database.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Driver Assignment Debug</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        .container { background: white; padding: 20px; border-radius: 8px; max-width: 800px; margin: 0 auto; }
        .success { background: #d4edda; color: #155724; padding: 10px; border-radius: 4px; margin: 10px 0; }
        .error { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin: 10px 0; }
        .info { background: #d1ecf1; color: #0c5460; padding: 10px; border-radius: 4px; margin: 10px 0; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #007bff; color: white; }
        code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Driver Assignment Debug Tool</h1>
        
        <?php
        $orderId = $_GET['order_id'] ?? null;
        
        try {
            $pdo = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            
            echo '<div class="success">✅ Database connection successful!</div>';
            
            // Check order
            if ($orderId) {
                $stmt = $pdo->prepare("SELECT id, status, assigned_driver, updated_at FROM orders WHERE id = ?");
                $stmt->execute([$orderId]);
                $order = $stmt->fetch();
                
                if ($order) {
                    echo '<h2>📦 Order #' . $orderId . '</h2>';
                    echo '<table>';
                    echo '<tr><th>Field</th><th>Value</th></tr>';
                    echo '<tr><td>Status</td><td>' . htmlspecialchars($order['status']) . '</td></tr>';
                    echo '<tr><td>Assigned Driver</td><td><strong>' . htmlspecialchars($order['assigned_driver'] ?? 'NULL') . '</strong></td></tr>';
                    echo '<tr><td>Updated At</td><td>' . htmlspecialchars($order['updated_at']) . '</td></tr>';
                    echo '</table>';
                } else {
                    echo '<div class="error">❌ Order #' . $orderId . ' not found</div>';
                }
            } else {
                echo '<div class="info">ℹ️ Add ?order_id=1 to URL to check specific order</div>';
            }
            
            // Get all drivers
            echo '<h2>🚗 Active Drivers</h2>';
            $stmt = $pdo->query("SELECT id, name, phone FROM drivers WHERE is_active = 1 ORDER BY name");
            $drivers = $stmt->fetchAll();
            
            if (empty($drivers)) {
                echo '<div class="error">❌ No active drivers found</div>';
            } else {
                echo '<table>';
                echo '<tr><th>ID</th><th>Name</th><th>Phone</th></tr>';
                foreach ($drivers as $driver) {
                    echo '<tr>';
                    echo '<td>' . $driver['id'] . '</td>';
                    echo '<td>' . htmlspecialchars($driver['name']) . '</td>';
                    echo '<td>' . htmlspecialchars($driver['phone']) . '</td>';
                    echo '</tr>';
                }
                echo '</table>';
            }
            
            // Test API endpoint
            echo '<h2>🧪 Test API Endpoint</h2>';
            echo '<div class="info">';
            echo '<p><strong>Endpoint:</strong> <code>PATCH /api/admin/orders/{order_id}</code></p>';
            echo '<p><strong>Expected JSON:</strong> <code>{"assigned_driver": "Driver Name"}</code></p>';
            echo '<p><strong>Note:</strong> Must be logged in as admin</p>';
            echo '</div>';
            
            // Recent orders with drivers
            echo '<h2>📋 Recent Orders (Last 10)</h2>';
            $stmt = $pdo->query("
                SELECT id, status, assigned_driver, updated_at 
                FROM orders 
                ORDER BY updated_at DESC 
                LIMIT 10
            ");
            $orders = $stmt->fetchAll();
            
            echo '<table>';
            echo '<tr><th>Order ID</th><th>Status</th><th>Assigned Driver</th><th>Updated At</th></tr>';
            foreach ($orders as $o) {
                echo '<tr>';
                echo '<td><a href="?order_id=' . $o['id'] . '">#' . $o['id'] . '</a></td>';
                echo '<td>' . htmlspecialchars($o['status']) . '</td>';
                echo '<td><strong>' . htmlspecialchars($o['assigned_driver'] ?? 'NULL') . '</strong></td>';
                echo '<td>' . htmlspecialchars($o['updated_at']) . '</td>';
                echo '</tr>';
            }
            echo '</table>';
            
        } catch (Exception $e) {
            echo '<div class="error">❌ Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
        ?>
        
        <hr>
        <p><small>Use this tool to verify driver assignments in the database</small></p>
    </div>
</body>
</html>


