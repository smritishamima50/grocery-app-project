<?php
/**
 * Complete Product Verification Script
 * This script verifies all 12 products are added and displays properly
 * Access: http://localhost/verify_products_complete.php
 */

require_once 'config/database.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Complete Product Verification</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; background: white; border-radius: 15px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); overflow: hidden; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; }
        .header h1 { font-size: 2.5em; margin-bottom: 10px; }
        .content { padding: 30px; }
        .section { margin-bottom: 30px; padding: 20px; background: #f8f9fa; border-radius: 10px; border-left: 5px solid #667eea; }
        .section h2 { color: #333; margin-bottom: 15px; font-size: 1.8em; }
        .success { color: #28a745; background: #d4edda; padding: 15px; border-radius: 8px; margin: 10px 0; border-left: 5px solid #28a745; }
        .error { color: #dc3545; background: #f8d7da; padding: 15px; border-radius: 8px; margin: 10px 0; border-left: 5px solid #dc3545; }
        .info { color: #004085; background: #cce5ff; padding: 15px; border-radius: 8px; margin: 10px 0; border-left: 5px solid #004085; }
        .warning { color: #856404; background: #fff3cd; padding: 15px; border-radius: 8px; margin: 10px 0; border-left: 5px solid #856404; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; background: white; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        th { background: #667eea; color: white; padding: 15px; text-align: left; font-weight: 600; }
        td { padding: 12px 15px; border-bottom: 1px solid #e0e0e0; }
        tr:hover { background: #f5f5f5; }
        .status-badge { padding: 5px 10px; border-radius: 20px; font-size: 0.85em; font-weight: 600; }
        .status-active { background: #28a745; color: white; }
        .status-inactive { background: #dc3545; color: white; }
        .btn { display: inline-block; padding: 12px 25px; margin: 10px 5px; border-radius: 8px; text-decoration: none; font-weight: 600; transition: all 0.3s; }
        .btn-primary { background: #667eea; color: white; }
        .btn-primary:hover { background: #5568d3; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4); }
        .btn-success { background: #28a745; color: white; }
        .btn-success:hover { background: #218838; }
        .stat-box { display: inline-block; background: white; padding: 20px; border-radius: 10px; margin: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); text-align: center; min-width: 150px; }
        .stat-number { font-size: 2.5em; font-weight: bold; color: #667eea; }
        .stat-label { color: #666; margin-top: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🛒 Complete Product Verification</h1>
            <p>Verifying all 12 products are added and configured correctly</p>
        </div>
        
        <div class="content">
            <?php
            $productsToCheck = [
                'Salt',
                'Honey',
                'Dates',
                'Shosha (Cucumber)',
                'Pudinapata (Mint Leaf)',
                'Kagzi (Lemon)',
                'Beef Premium Cube',
                'Diploma Instant Full Cream Milk Powder 1kg (Foil Pack)',
                'Chinigura Rice Loose (P) (BRRI-34)',
                'Nazirshail Rice Loose (P) (Sompa Katari)',
                'Miniket Rice Loose(S) (BRRI-28)',
                'Fresh Instant Full Cream Milk Powder 1000gm'
            ];

            try {
                // Statistics
                echo "<div class='section'>";
                echo "<h2>📊 Database Statistics</h2>";
                
                $stmt = $pdo->query("SELECT COUNT(*) as total FROM products");
                $totalProducts = $stmt->fetch()['total'];
                
                $stmt = $pdo->query("SELECT COUNT(*) as active FROM products WHERE is_active = 1");
                $activeProducts = $stmt->fetch()['active'];
                
                $stmt = $pdo->query("SELECT COUNT(*) as categories FROM categories");
                $totalCategories = $stmt->fetch()['categories'];
                
                echo "<div style='text-align: center; margin: 20px 0;'>";
                echo "<div class='stat-box'><div class='stat-number'>$totalProducts</div><div class='stat-label'>Total Products</div></div>";
                echo "<div class='stat-box'><div class='stat-number'>$activeProducts</div><div class='stat-label'>Active Products</div></div>";
                echo "<div class='stat-box'><div class='stat-number'>$totalCategories</div><div class='stat-label'>Categories</div></div>";
                echo "</div>";
                echo "</div>";

                // Check all 12 products
                echo "<div class='section'>";
                echo "<h2>✅ Product Verification</h2>";
                
                $placeholders = str_repeat('?,', count($productsToCheck) - 1) . '?';
                $stmt = $pdo->prepare("
                    SELECT p.*, c.name as category_name 
                    FROM products p 
                    LEFT JOIN categories c ON p.category_id = c.id 
                    WHERE p.name IN ($placeholders)
                    ORDER BY p.name
                ");
                $stmt->execute($productsToCheck);
                $products = $stmt->fetchAll();
                
                $foundCount = count($products);
                $activeCount = count(array_filter($products, function($p) { return $p['is_active'] == 1; }));
                $inStockCount = count(array_filter($products, function($p) { return $p['stock_quantity'] > 0; }));

                if ($foundCount === 12) {
                    echo "<div class='success'><strong>✅ Perfect! All 12 products found in database!</strong></div>";
                } else {
                    echo "<div class='error'><strong>❌ Only $foundCount out of 12 products found. Missing: " . (12 - $foundCount) . " products</strong></div>";
                    echo "<div class='info'>💡 Run <a href='add_products_now.php' style='color: #667eea; font-weight: bold;'>add_products_now.php</a> to add missing products.</div>";
                }

                if ($activeCount === 12) {
                    echo "<div class='success'>✅ All $activeCount products are active</div>";
                } else {
                    echo "<div class='warning'>⚠️ Only $activeCount out of $foundCount products are active</div>";
                }

                if ($inStockCount === 12) {
                    echo "<div class='success'>✅ All $inStockCount products have stock available</div>";
                } else {
                    echo "<div class='warning'>⚠️ Only $inStockCount out of $foundCount products have stock</div>";
                }

                echo "<table>";
                echo "<tr><th>#</th><th>Product Name</th><th>Category</th><th>Price</th><th>Stock</th><th>Status</th><th>Actions</th></tr>";
                
                $index = 1;
                foreach ($productsToCheck as $productName) {
                    $product = null;
                    foreach ($products as $p) {
                        if ($p['name'] === $productName) {
                            $product = $p;
                            break;
                        }
                    }
                    
                    if ($product) {
                        $statusClass = $product['is_active'] ? 'status-active' : 'status-inactive';
                        $statusText = $product['is_active'] ? 'Active' : 'Inactive';
                        $stockStatus = $product['stock_quantity'] > 0 ? $product['stock_quantity'] . ' ' . ($product['unit'] ?? '') : 'Out of Stock';
                        
                        echo "<tr>";
                        echo "<td>$index</td>";
                        echo "<td><strong>" . htmlspecialchars($product['name']) . "</strong></td>";
                        echo "<td>" . htmlspecialchars($product['category_name'] ?? 'N/A') . "</td>";
                        echo "<td>৳" . number_format($product['price'], 2) . "</td>";
                        echo "<td>$stockStatus</td>";
                        echo "<td><span class='status-badge $statusClass'>$statusText</span></td>";
                        echo "<td><a href='index.php?route=products/" . $product['id'] . "' class='btn btn-primary' style='padding: 5px 10px; font-size: 0.85em;'>View</a></td>";
                        echo "</tr>";
                    } else {
                        echo "<tr style='background: #fff3cd;'>";
                        echo "<td>$index</td>";
                        echo "<td><strong>" . htmlspecialchars($productName) . "</strong></td>";
                        echo "<td colspan='5'><span style='color: #856404;'>❌ NOT FOUND IN DATABASE</span></td>";
                        echo "</tr>";
                    }
                    $index++;
                }
                echo "</table>";
                echo "</div>";

                // Category Verification
                echo "<div class='section'>";
                echo "<h2>📂 Category Verification</h2>";
                
                $requiredCategories = ['Cooking', 'Rice & Grains', 'Fruits & Vegetables', 'Dairy & Eggs', 'Meat & Poultry'];
                $stmt = $pdo->query("SELECT id, name FROM categories WHERE name IN ('" . implode("','", $requiredCategories) . "')");
                $existingCategories = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
                $existingCategoryNames = array_values($existingCategories);
                
                foreach ($requiredCategories as $catName) {
                    if (in_array($catName, $existingCategoryNames)) {
                        $catId = array_search($catName, $existingCategories);
                        echo "<div class='success'>✅ Category '$catName' exists (ID: $catId)</div>";
                    } else {
                        echo "<div class='error'>❌ Category '$catName' is missing</div>";
                    }
                }
                echo "</div>";

                // Frontend Display Check
                echo "<div class='section'>";
                echo "<h2>🌐 Frontend Display Check</h2>";
                
                // Test query similar to ProductController
                $stmt = $pdo->prepare("
                    SELECT COUNT(*) as count 
                    FROM products p 
                    WHERE p.is_active = 1 AND p.name IN ($placeholders)
                ");
                $stmt->execute($productsToCheck);
                $frontendVisible = $stmt->fetch()['count'];
                
                if ($frontendVisible === 12) {
                    echo "<div class='success'>✅ All 12 products will be visible on the frontend</div>";
                } else {
                    echo "<div class='warning'>⚠️ Only $frontendVisible products will be visible on frontend (need to be active)</div>";
                }

                // Test homepage query
                $stmt = $pdo->prepare("
                    SELECT COUNT(*) as count 
                    FROM products p 
                    WHERE p.is_active = 1 AND p.stock_quantity > 0 AND p.name IN ($placeholders)
                ");
                $stmt->execute($productsToCheck);
                $homepageVisible = $stmt->fetch()['count'];
                
                if ($homepageVisible >= 8) {
                    echo "<div class='success'>✅ $homepageVisible products will appear on homepage (showing newest first)</div>";
                } else {
                    echo "<div class='info'>ℹ️ $homepageVisible products available for homepage (shows top 8 newest)</div>";
                }
                
                echo "<div style='margin-top: 20px;'>";
                echo "<a href='index.php?route=products' class='btn btn-primary'>View Products Page</a>";
                echo "<a href='index.php' class='btn btn-primary'>View Homepage</a>";
                echo "<a href='add_products_now.php' class='btn btn-success'>Add Missing Products</a>";
                echo "</div>";
                echo "</div>";

                // Summary
                echo "<div class='section'>";
                echo "<h2>📋 Summary</h2>";
                
                $allGood = ($foundCount === 12 && $activeCount === 12 && $inStockCount === 12);
                
                if ($allGood) {
                    echo "<div class='success' style='font-size: 1.2em; padding: 20px;'>";
                    echo "<h3>🎉 Everything is Perfect!</h3>";
                    echo "<p>✅ All 12 products are in the database</p>";
                    echo "<p>✅ All products are active and will display on frontend</p>";
                    echo "<p>✅ All products have stock available</p>";
                    echo "<p>✅ Products are properly categorized</p>";
                    echo "<p><strong>Your products are ready to be displayed!</strong></p>";
                    echo "</div>";
                } else {
                    echo "<div class='warning' style='font-size: 1.1em; padding: 20px;'>";
                    echo "<h3>⚠️ Action Required</h3>";
                    echo "<p>Found: $foundCount/12 products</p>";
                    echo "<p>Active: $activeCount/12 products</p>";
                    echo "<p>In Stock: $inStockCount/12 products</p>";
                    echo "<p><strong>Please run <a href='add_products_now.php' style='color: #667eea;'>add_products_now.php</a> to fix issues.</strong></p>";
                    echo "</div>";
                }
                echo "</div>";

            } catch (PDOException $e) {
                echo "<div class='error'>❌ Database Error: " . $e->getMessage() . "</div>";
            }
            ?>
        </div>
    </div>
</body>
</html>

