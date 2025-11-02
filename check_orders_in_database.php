<?php
/**
 * Diagnostic Script: Check Orders in Database
 * 
 * This script helps verify if orders exist in the database
 * and provides information about the Orders Management section.
 * 
 * Access: http://localhost/check_orders_in_database.php
 */

require_once 'config/database.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders Database Check - Admin Diagnostic</title>
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
        h2 {
            color: #2563eb;
            margin-top: 30px;
        }
        .success {
            background: #d1fae5;
            color: #065f46;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
            border-left: 4px solid #10b981;
        }
        .warning {
            background: #fef3c7;
            color: #92400e;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
            border-left: 4px solid #f59e0b;
        }
        .error {
            background: #fee2e2;
            color: #991b1b;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
            border-left: 4px solid #ef4444;
        }
        .info {
            background: #dbeafe;
            color: #1e40af;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
            border-left: 4px solid #3b82f6;
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
        tr:hover {
            background: #f9fafb;
        }
        .stat-box {
            display: inline-block;
            padding: 15px 25px;
            margin: 10px;
            background: #eff6ff;
            border-radius: 8px;
            border-left: 4px solid #2563eb;
        }
        .stat-number {
            font-size: 32px;
            font-weight: bold;
            color: #2563eb;
        }
        .stat-label {
            color: #64748b;
            font-size: 14px;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background: #2563eb;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            margin: 10px 5px;
            font-weight: bold;
        }
        .button:hover {
            background: #1d4ed8;
        }
        code {
            background: #f1f5f9;
            padding: 2px 6px;
            border-radius: 3px;
            color: #dc2626;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📊 Orders Database Diagnostic Tool</h1>
        
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
            
            // Check if orders table exists
            $stmt = $pdo->query("SHOW TABLES LIKE 'orders'");
            $tableExists = $stmt->rowCount() > 0;
            
            if (!$tableExists) {
                echo '<div class="error">❌ ERROR: The "orders" table does not exist in the database!</div>';
                echo '<p>You need to run the database schema to create the orders table.</p>';
                exit;
            }
            
            echo '<div class="success">✅ Orders table exists</div>';
            
            // Get total orders count
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM orders");
            $total = $stmt->fetch()['total'];
            
            echo '<div class="stat-box">';
            echo '<div class="stat-number">' . $total . '</div>';
            echo '<div class="stat-label">Total Orders</div>';
            echo '</div>';
            
            // Get orders by status
            $stmt = $pdo->query("
                SELECT 
                    status,
                    COUNT(*) as count
                FROM orders
                GROUP BY status
                ORDER BY count DESC
            ");
            $statusCounts = $stmt->fetchAll();
            
            if ($total > 0) {
                echo '<h2>📈 Orders by Status</h2>';
                echo '<table>';
                echo '<tr><th>Status</th><th>Count</th></tr>';
                foreach ($statusCounts as $row) {
                    echo '<tr>';
                    echo '<td><strong>' . htmlspecialchars(ucfirst($row['status'])) . '</strong></td>';
                    echo '<td>' . $row['count'] . '</td>';
                    echo '</tr>';
                }
                echo '</table>';
                
                // Get recent orders
                echo '<h2>📋 Recent Orders (Last 10)</h2>';
                $stmt = $pdo->query("
                    SELECT 
                        o.id,
                        o.total_amount,
                        o.status,
                        o.payment_method,
                        o.created_at,
                        COALESCE(u.first_name, '') as first_name,
                        COALESCE(u.last_name, '') as last_name,
                        COALESCE(u.phone, '') as phone
                    FROM orders o
                    LEFT JOIN users u ON o.user_id = u.id
                    ORDER BY o.created_at DESC
                    LIMIT 10
                ");
                $recentOrders = $stmt->fetchAll();
                
                echo '<table>';
                echo '<tr>';
                echo '<th>Order ID</th>';
                echo '<th>Customer</th>';
                echo '<th>Phone</th>';
                echo '<th>Amount</th>';
                echo '<th>Status</th>';
                echo '<th>Payment</th>';
                echo '<th>Date</th>';
                echo '</tr>';
                
                foreach ($recentOrders as $order) {
                    echo '<tr>';
                    echo '<td><strong>#' . $order['id'] . '</strong></td>';
                    echo '<td>' . htmlspecialchars(trim($order['first_name'] . ' ' . $order['last_name'])) . '</td>';
                    echo '<td>' . htmlspecialchars($order['phone']) . '</td>';
                    echo '<td>৳' . number_format($order['total_amount'], 2) . '</td>';
                    echo '<td><span style="padding: 4px 8px; background: #eff6ff; border-radius: 4px;">' . htmlspecialchars($order['status']) . '</span></td>';
                    echo '<td>' . strtoupper($order['payment_method']) . '</td>';
                    echo '<td>' . date('M j, Y g:i A', strtotime($order['created_at'])) . '</td>';
                    echo '</tr>';
                }
                echo '</table>';
                
                echo '<div class="success">✅ Orders found! You should be able to see them in the Admin Orders Management section.</div>';
                
            } else {
                echo '<div class="warning">⚠️ No orders found in the database.</div>';
                echo '<p>This means:</p>';
                echo '<ul>';
                echo '<li>No customers have placed orders yet</li>';
                echo '<li>The Orders Management page will show "No orders found"</li>';
                echo '<li>You may want to test by placing an order as a customer</li>';
                echo '</ul>';
            }
            
            // Access instructions
            echo '<h2>🔗 How to Access Orders Management</h2>';
            echo '<div class="info">';
            echo '<h3>Step 1: Login as Admin</h3>';
            echo '<p>Go to: <code>http://localhost/login</code> (or your site URL)</p>';
            echo '<h3>Step 2: Navigate to Admin Panel</h3>';
            echo '<p>After login, go to: <code>http://localhost/admin</code></p>';
            echo '<h3>Step 3: Find Orders in Sidebar</h3>';
            echo '<p>In the left sidebar menu, look for <strong>"Orders"</strong> menu item</p>';
            echo '<p>It has a shopping bag icon (🛍️) and is located between "Inventory" and "Products"</p>';
            echo '<h3>Step 4: Direct Access</h3>';
            echo '<p>Or go directly to: <code>http://localhost/admin/orders</code></p>';
            echo '</div>';
            
            // Quick links
            echo '<h2>🚀 Quick Links</h2>';
            echo '<a href="/admin/orders" class="button">Go to Orders Management</a>';
            echo '<a href="/admin" class="button">Go to Admin Dashboard</a>';
            echo '<a href="/" class="button">Go to Homepage</a>';
            
            // Check filters
            echo '<h2>🔍 Filter Information</h2>';
            echo '<div class="info">';
            echo '<p>If you see "No orders found" on the Orders page, check:</p>';
            echo '<ul>';
            echo '<li><strong>Status Filter:</strong> Make sure it\'s set to "All Orders"</li>';
            echo '<li><strong>Date Filters:</strong> Clear the date range filters</li>';
            echo '<li><strong>Search Filter:</strong> Clear any search terms</li>';
            echo '<li><strong>Driver Filter:</strong> Set to "All Drivers"</li>';
            echo '<li>Click "Clear Filters" button</li>';
            echo '</ul>';
            echo '</div>';
            
        } catch (PDOException $e) {
            echo '<div class="error">';
            echo '<h3>❌ Database Connection Error</h3>';
            echo '<p><strong>Error:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
            echo '<p>Please check your database configuration in <code>config/database.php</code></p>';
            echo '</div>';
        }
        ?>
        
        <hr style="margin: 30px 0;">
        <p style="text-align: center; color: #64748b;">
            <small>Diagnostic Tool - Generated on <?php echo date('Y-m-d H:i:s'); ?></small>
        </p>
    </div>
</body>
</html>

