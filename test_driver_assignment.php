<?php
/**
 * Driver Assignment Diagnostic Test
 * 
 * This script tests the driver assignment functionality to verify:
 * 1. API endpoint is accessible
 * 2. Driver assignment updates database
 * 3. Response includes correct driver value
 * 
 * Access: http://localhost/test_driver_assignment.php
 */

require_once 'config/database.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver Assignment Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1200px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 3px solid #2563eb;
            padding-bottom: 10px;
        }
        .success {
            background: #d1fae5;
            color: #065f46;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
        }
        .error {
            background: #fee2e2;
            color: #991b1b;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
        }
        .info {
            background: #dbeafe;
            color: #1e40af;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #2563eb;
            color: white;
        }
        code {
            background: #f1f5f9;
            padding: 2px 6px;
            border-radius: 3px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Driver Assignment Diagnostic Test</h1>
        
        <?php
        try {
            $pdo = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );
            
            echo '<div class="success">✅ Database connection successful!</div>';
            
            // Check if orders table exists and has assigned_driver column
            $stmt = $pdo->query("SHOW COLUMNS FROM orders LIKE 'assigned_driver'");
            $columnExists = $stmt->rowCount() > 0;
            
            if (!$columnExists) {
                echo '<div class="error">❌ ERROR: The "assigned_driver" column does not exist in the orders table!</div>';
                echo '<p>You need to add the column. Run:</p>';
                echo '<code>ALTER TABLE orders ADD COLUMN assigned_driver VARCHAR(255) NULL;</code>';
            } else {
                echo '<div class="success">✅ "assigned_driver" column exists in orders table</div>';
            }
            
            // Check if drivers table exists
            $stmt = $pdo->query("SHOW TABLES LIKE 'drivers'");
            $driversTableExists = $stmt->rowCount() > 0;
            
            if (!$driversTableExists) {
                echo '<div class="error">❌ ERROR: The "drivers" table does not exist!</div>';
                echo '<p>You need to create the drivers table. Example:</p>';
                echo '<code>CREATE TABLE drivers (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(255) NOT NULL, is_active BOOLEAN DEFAULT TRUE);</code>';
            } else {
                echo '<div class="success">✅ "drivers" table exists</div>';
                
                // Get active drivers
                $stmt = $pdo->query("SELECT name FROM drivers WHERE is_active = 1 ORDER BY name");
                $drivers = $stmt->fetchAll();
                
                echo '<h2>📋 Active Drivers</h2>';
                if (empty($drivers)) {
                    echo '<div class="error">⚠️ No active drivers found in database</div>';
                    echo '<p>Add drivers using:</p>';
                    echo '<code>INSERT INTO drivers (name, is_active) VALUES (&#39;Driver Name&#39;, 1);</code>';
                } else {
                    echo '<table>';
                    echo '<tr><th>Driver Name</th></tr>';
                    foreach ($drivers as $driver) {
                        echo '<tr><td>' . htmlspecialchars($driver['name']) . '</td></tr>';
                    }
                    echo '</table>';
                }
            }
            
            // Check orders with drivers assigned
            echo '<h2>📦 Orders with Assigned Drivers</h2>';
            $stmt = $pdo->query("
                SELECT 
                    o.id,
                    o.assigned_driver,
                    o.status,
                    o.total_amount,
                    o.created_at,
                    COALESCE(u.first_name, '') as first_name,
                    COALESCE(u.last_name, '') as last_name
                FROM orders o
                LEFT JOIN users u ON o.user_id = u.id
                WHERE o.assigned_driver IS NOT NULL AND o.assigned_driver != ''
                ORDER BY o.created_at DESC
                LIMIT 10
            ");
            $ordersWithDrivers = $stmt->fetchAll();
            
            if (empty($ordersWithDrivers)) {
                echo '<div class="info">ℹ️ No orders have drivers assigned yet</div>';
            } else {
                echo '<table>';
                echo '<tr>';
                echo '<th>Order ID</th>';
                echo '<th>Customer</th>';
                echo '<th>Assigned Driver</th>';
                echo '<th>Status</th>';
                echo '<th>Amount</th>';
                echo '<th>Created</th>';
                echo '</tr>';
                foreach ($ordersWithDrivers as $order) {
                    echo '<tr>';
                    echo '<td>#' . $order['id'] . '</td>';
                    echo '<td>' . htmlspecialchars(trim($order['first_name'] . ' ' . $order['last_name'])) . '</td>';
                    echo '<td><strong>' . htmlspecialchars($order['assigned_driver']) . '</strong></td>';
                    echo '<td>' . htmlspecialchars($order['status']) . '</td>';
                    echo '<td>৳' . number_format($order['total_amount'], 2) . '</td>';
                    echo '<td>' . date('M j, Y g:i A', strtotime($order['created_at'])) . '</td>';
                    echo '</tr>';
                }
                echo '</table>';
            }
            
            // Get recent orders (last 5) for testing
            echo '<h2>🧪 Test Orders (Recent 5)</h2>';
            $stmt = $pdo->query("
                SELECT 
                    o.id,
                    o.assigned_driver,
                    o.status,
                    o.total_amount,
                    o.created_at,
                    COALESCE(u.first_name, '') as first_name,
                    COALESCE(u.last_name, '') as last_name
                FROM orders o
                LEFT JOIN users u ON o.user_id = u.id
                ORDER BY o.created_at DESC
                LIMIT 5
            ");
            $recentOrders = $stmt->fetchAll();
            
            if (empty($recentOrders)) {
                echo '<div class="error">❌ No orders found in database</div>';
            } else {
                echo '<table>';
                echo '<tr>';
                echo '<th>Order ID</th>';
                echo '<th>Customer</th>';
                echo '<th>Current Driver</th>';
                echo '<th>Status</th>';
                echo '<th>Test Assignment</th>';
                echo '</tr>';
                foreach ($recentOrders as $order) {
                    echo '<tr>';
                    echo '<td>#' . $order['id'] . '</td>';
                    echo '<td>' . htmlspecialchars(trim($order['first_name'] . ' ' . $order['last_name'])) . '</td>';
                    echo '<td>' . htmlspecialchars($order['assigned_driver'] ?? 'NULL') . '</td>';
                    echo '<td>' . htmlspecialchars($order['status']) . '</td>';
                    echo '<td>';
                    if (!empty($drivers)) {
                        echo '<select onchange="testDriverAssignment(' . $order['id'] . ', this.value)">';
                        echo '<option value="">Unassign</option>';
                        foreach ($drivers as $driver) {
                            $selected = (trim($order['assigned_driver'] ?? '') === trim($driver['name'])) ? 'selected' : '';
                            echo '<option value="' . htmlspecialchars($driver['name']) . '" ' . $selected . '>';
                            echo htmlspecialchars($driver['name']);
                            echo '</option>';
                        }
                        echo '</select>';
                    } else {
                        echo 'No drivers available';
                    }
                    echo '</td>';
                    echo '</tr>';
                }
                echo '</table>';
            }
            
            // API endpoint check
            echo '<h2>🔗 API Endpoint Check</h2>';
            echo '<div class="info">';
            echo '<p><strong>API Endpoint:</strong> <code>/api/admin/orders/{orderId}</code></p>';
            echo '<p><strong>Method:</strong> <code>PATCH</code></p>';
            echo '<p><strong>Expected JSON:</strong> <code>{"assigned_driver": "Driver Name"}</code> or <code>{"assigned_driver": null}</code></p>';
            echo '<p><strong>Note:</strong> You must be logged in as admin to use the API</p>';
            echo '</div>';
            
            // Instructions
            echo '<h2>📝 Testing Instructions</h2>';
            echo '<div class="info">';
            echo '<ol>';
            echo '<li>Go to <a href="/admin/orders" target="_blank">Admin Orders Page</a></li>';
            echo '<li>Find an order in the table</li>';
            echo '<li>Select a driver from the dropdown in the "Driver" column</li>';
            echo '<li>Check browser console (F12) for logs</li>';
            echo '<li>Verify the dropdown shows the selected driver</li>';
            echo '<li>Refresh the page and verify the driver is still assigned</li>';
            echo '<li>Check this page to see if the driver appears in "Orders with Assigned Drivers"</li>';
            echo '</ol>';
            echo '</div>';
            
        } catch (PDOException $e) {
            echo '<div class="error">';
            echo '<h3>❌ Database Connection Error</h3>';
            echo '<p><strong>Error:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
            echo '<p>Please check your database configuration in <code>config/database.php</code></p>';
            echo '</div>';
        }
        ?>
        
        <script>
        async function testDriverAssignment(orderId, driverName) {
            const driverValue = (driverName && driverName.trim() !== '') ? driverName.trim() : null;
            
            console.log('🧪 Testing driver assignment...');
            console.log('Order ID:', orderId);
            console.log('Driver:', driverValue);
            
            try {
                const response = await fetch(`/api/admin/orders/${orderId}`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        assigned_driver: driverValue
                    })
                });
                
                const result = await response.json();
                console.log('Response:', result);
                
                if (result.success) {
                    alert('✅ Driver assignment test successful!\n\nResponse: ' + JSON.stringify(result, null, 2));
                } else {
                    alert('❌ Driver assignment test failed!\n\nError: ' + (result.error || 'Unknown error'));
                }
            } catch (error) {
                console.error('Test error:', error);
                alert('❌ Test failed: ' + error.message);
            }
        }
        </script>
        
        <hr style="margin: 30px 0;">
        <p style="text-align: center; color: #64748b;">
            <small>Diagnostic Tool - Generated on <?php echo date('Y-m-d H:i:s'); ?></small>
        </p>
    </div>
</body>
</html>

