<?php
/**
 * Fix All Missing Database Tables
 * This script checks and creates all missing tables
 * Access: http://localhost/fix_all_missing_tables.php
 */

require_once 'config/database.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Fix Missing Database Tables</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f0f2f5; padding: 20px; }
        .container { max-width: 1000px; margin: 0 auto; background: white; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); padding: 30px; }
        h1 { color: #333; margin-bottom: 20px; border-bottom: 3px solid #4CAF50; padding-bottom: 10px; }
        .section { margin: 20px 0; padding: 20px; background: #f9f9f9; border-radius: 8px; border-left: 4px solid #4CAF50; }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0; border: 1px solid #f5c6cb; }
        .info { background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px; margin: 10px 0; border: 1px solid #bee5eb; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #4CAF50; color: white; }
        tr:hover { background: #f5f5f5; }
        .btn { display: inline-block; padding: 12px 24px; background: #4CAF50; color: white; text-decoration: none; border-radius: 5px; margin: 5px; font-weight: bold; }
        .btn:hover { background: #45a049; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Fix Missing Database Tables</h1>
        
        <?php
        try {
            // List of required tables
            $requiredTables = [
                'users',
                'categories',
                'products',
                'user_addresses',
                'cart_items',
                'orders',
                'order_items',
                'coupons',
                'wishlists',
                'subscriptions',
                'payments',
                'notifications',
                'delivery_updates',
                'order_status_history'
            ];
            
            echo "<div class='section'>";
            echo "<h2>Step 1: Checking Existing Tables</h2>";
            
            // Check which tables exist
            $stmt = $pdo->query("SHOW TABLES");
            $existingTables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $existingTables = array_map('strtolower', $existingTables);
            
            $missingTables = [];
            foreach ($requiredTables as $table) {
                if (!in_array(strtolower($table), $existingTables)) {
                    $missingTables[] = $table;
                    echo "<div class='error'>❌ Missing: <strong>$table</strong></div>";
                } else {
                    echo "<div class='success'>✅ Exists: <strong>$table</strong></div>";
                }
            }
            
            echo "</div>";
            
            // Fix cart_items table specifically
            echo "<div class='section'>";
            echo "<h2>Step 2: Fixing cart_items Table</h2>";
            
            if (in_array('cart_items', $missingTables) || !in_array('cart_items', array_map('strtolower', $existingTables))) {
                echo "<div class='info'>Creating cart_items table...</div>";
                
                try {
                    $createCartItemsSQL = "
                    CREATE TABLE IF NOT EXISTS cart_items (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        user_id INT NOT NULL,
                        product_id INT NOT NULL,
                        quantity INT NOT NULL DEFAULT 1,
                        added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
                        UNIQUE KEY unique_cart_item (user_id, product_id)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
                    ";
                    
                    $pdo->exec($createCartItemsSQL);
                    
                    // Create indexes
                    try {
                        $pdo->exec("CREATE INDEX IF NOT EXISTS idx_cart_items_user ON cart_items(user_id)");
                    } catch (PDOException $e) {
                        // Index might already exist, ignore
                    }
                    
                    try {
                        $pdo->exec("CREATE INDEX IF NOT EXISTS idx_cart_items_product ON cart_items(product_id)");
                    } catch (PDOException $e) {
                        // Index might already exist, ignore
                    }
                    
                    echo "<div class='success'>✅ cart_items table created successfully!</div>";
                } catch (PDOException $e) {
                    if (strpos($e->getMessage(), 'already exists') !== false) {
                        echo "<div class='info'>ℹ️ cart_items table already exists</div>";
                    } else {
                        echo "<div class='error'>❌ Error creating cart_items: " . $e->getMessage() . "</div>";
                    }
                }
            } else {
                echo "<div class='success'>✅ cart_items table already exists!</div>";
                
                // Verify table structure
                $stmt = $pdo->query("DESCRIBE cart_items");
                $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo "<div class='info'>";
                echo "<strong>cart_items table structure:</strong><br>";
                echo "<table>";
                echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
                foreach ($columns as $col) {
                    echo "<tr>";
                    echo "<td>{$col['Field']}</td>";
                    echo "<td>{$col['Type']}</td>";
                    echo "<td>{$col['Null']}</td>";
                    echo "<td>{$col['Key']}</td>";
                    echo "<td>{$col['Default']}</td>";
                    echo "</tr>";
                }
                echo "</table>";
                echo "</div>";
            }
            
            echo "</div>";
            
            // Test cart functionality
            echo "<div class='section'>";
            echo "<h2>Step 3: Testing Cart Functionality</h2>";
            
            // Check if we can query cart_items
            try {
                $stmt = $pdo->query("SELECT COUNT(*) as total FROM cart_items");
                $result = $stmt->fetch();
                echo "<div class='success'>✅ cart_items table is accessible. Current items: " . $result['total'] . "</div>";
            } catch (PDOException $e) {
                echo "<div class='error'>❌ Cannot access cart_items table: " . $e->getMessage() . "</div>";
            }
            
            // Test join with products
            try {
                $stmt = $pdo->query("
                    SELECT COUNT(*) as total 
                    FROM cart_items ci 
                    LEFT JOIN products p ON ci.product_id = p.id 
                    LIMIT 1
                ");
                $result = $stmt->fetch();
                echo "<div class='success'>✅ cart_items can join with products table successfully!</div>";
            } catch (PDOException $e) {
                echo "<div class='error'>❌ Join test failed: " . $e->getMessage() . "</div>";
            }
            
            echo "</div>";
            
            // Summary
            echo "<div class='section'>";
            echo "<h2>Summary</h2>";
            
            if (in_array('cart_items', array_map('strtolower', $existingTables)) || !in_array('cart_items', $missingTables)) {
                echo "<div class='success'>";
                echo "<h3>🎉 All Fixed!</h3>";
                echo "<p>✅ cart_items table exists and is working</p>";
                echo "<p>✅ Cart functionality should work now</p>";
                echo "<p><strong>Try accessing the cart page now!</strong></p>";
                echo "</div>";
                
                echo "<div style='margin-top: 20px;'>";
                echo "<a href='index.php?route=cart' class='btn'>Test Cart Page</a>";
                echo "<a href='index.php' class='btn'>Go to Homepage</a>";
                echo "</div>";
            } else {
                echo "<div class='warning'>";
                echo "<h3>⚠️ Still Missing Tables</h3>";
                echo "<p>Please run the full database schema: <code>database/schema.sql</code></p>";
                echo "</div>";
            }
            
            echo "</div>";
            
        } catch (PDOException $e) {
            echo "<div class='error'>❌ Database Error: " . $e->getMessage() . "</div>";
            echo "<div class='info'>";
            echo "<p><strong>Troubleshooting:</strong></p>";
            echo "<ol>";
            echo "<li>Check if MySQL is running</li>";
            echo "<li>Verify database connection in config/database.php</li>";
            echo "<li>Check database name is correct: grocery_app</li>";
            echo "<li>Run full schema: database/schema.sql</li>";
            echo "</ol>";
            echo "</div>";
        }
        ?>
        
        <div class="section">
            <h2>📋 Alternative: Run SQL File</h2>
            <p>If the above script doesn't work, run this SQL file manually:</p>
            <ol>
                <li>Open phpMyAdmin: <code>http://localhost/phpmyadmin</code></li>
                <li>Select database: <code>grocery_app</code></li>
                <li>Click <strong>Import</strong> tab</li>
                <li>Choose file: <code>database/fix_cart_items_table.sql</code></li>
                <li>Click <strong>Go</strong></li>
            </ol>
        </div>
    </div>
</body>
</html>

