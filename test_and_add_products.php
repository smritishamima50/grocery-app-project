<?php
/**
 * TEST AND ADD PRODUCTS - Complete Solution
 * This script will check your database and add the 12 products
 * Access: http://localhost/test_and_add_products.php
 */

require_once 'config/database.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test & Add Products</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f0f2f5; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; background: white; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); padding: 30px; }
        h1 { color: #333; margin-bottom: 20px; border-bottom: 3px solid #4CAF50; padding-bottom: 10px; }
        .section { margin: 20px 0; padding: 20px; background: #f9f9f9; border-radius: 8px; border-left: 4px solid #4CAF50; }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0; border: 1px solid #f5c6cb; }
        .info { background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px; margin: 10px 0; border: 1px solid #bee5eb; }
        .warning { background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin: 10px 0; border: 1px solid #ffeaa7; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #4CAF50; color: white; }
        tr:hover { background: #f5f5f5; }
        .btn { display: inline-block; padding: 12px 24px; background: #4CAF50; color: white; text-decoration: none; border-radius: 5px; margin: 5px; font-weight: bold; }
        .btn:hover { background: #45a049; }
        .btn-danger { background: #f44336; }
        .btn-danger:hover { background: #da190b; }
        code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; font-family: monospace; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🛒 Test & Add 12 Products - Complete Solution</h1>
        
        <?php
        $productsToAdd = [
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
            echo "<div class='section'>";
            echo "<h2>Step 1: Current Database Status</h2>";
            
            // Check total products
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM products");
            $totalProducts = $stmt->fetch()['total'];
            
            $stmt = $pdo->query("SELECT COUNT(*) as active FROM products WHERE is_active = 1");
            $activeProducts = $stmt->fetch()['active'];
            
            echo "<div class='info'>";
            echo "<strong>Total products in database:</strong> $totalProducts<br>";
            echo "<strong>Active products:</strong> $activeProducts<br>";
            echo "<strong>Expected after adding:</strong> " . ($totalProducts + 12) . " total products";
            echo "</div>";
            
            // Check existing products
            $placeholders = str_repeat('?,', count($productsToAdd) - 1) . '?';
            $stmt = $pdo->prepare("SELECT name, id, is_active, stock_quantity FROM products WHERE name IN ($placeholders)");
            $stmt->execute($productsToAdd);
            $existing = $stmt->fetchAll();
            $existingNames = array_column($existing, 'name');
            $missing = array_diff($productsToAdd, $existingNames);
            
            echo "<div class='info'>";
            echo "<strong>Products already in database:</strong> " . count($existing) . "/12<br>";
            echo "<strong>Missing products:</strong> " . count($missing) . "/12";
            echo "</div>";
            
            if (count($existing) > 0) {
                echo "<table>";
                echo "<tr><th>ID</th><th>Product Name</th><th>Status</th><th>Stock</th></tr>";
                foreach ($existing as $prod) {
                    $status = $prod['is_active'] ? '✅ Active' : '❌ Inactive';
                    echo "<tr><td>{$prod['id']}</td><td>{$prod['name']}</td><td>$status</td><td>{$prod['stock_quantity']}</td></tr>";
                }
                echo "</table>";
            }
            
            echo "</div>";
            
            // Step 2: Check Categories
            echo "<div class='section'>";
            echo "<h2>Step 2: Category Verification</h2>";
            
            $requiredCategories = [
                'Cooking' => 'Cooking oils, spices, and cooking essentials',
                'Rice & Grains' => 'Various types of rice and grain products',
                'Fruits & Vegetables' => 'Fresh fruits and vegetables',
                'Dairy & Eggs' => 'Milk, cheese, eggs and dairy products',
                'Meat & Poultry' => 'Fresh meat and poultry products'
            ];
            
            $categoryIds = [];
            foreach ($requiredCategories as $catName => $catDesc) {
                $stmt = $pdo->prepare("SELECT id FROM categories WHERE name = ?");
                $stmt->execute([$catName]);
                $cat = $stmt->fetch();
                
                if ($cat) {
                    $categoryIds[$catName] = $cat['id'];
                    echo "<div class='success'>✅ Category '$catName' exists (ID: {$cat['id']})</div>";
                } else {
                    // Create category
                    $stmt = $pdo->prepare("INSERT INTO categories (name, description, image) VALUES (?, ?, ?)");
                    $stmt->execute([$catName, $catDesc, 'https://picsum.photos/300/200?random=' . rand(100, 999)]);
                    $newId = $pdo->lastInsertId();
                    $categoryIds[$catName] = $newId;
                    echo "<div class='success'>✅ Created category '$catName' (ID: $newId)</div>";
                }
            }
            
            echo "</div>";
            
            // Step 3: Add Missing Products
            if (count($missing) > 0) {
                echo "<div class='section'>";
                echo "<h2>Step 3: Adding Missing Products</h2>";
                
                $productData = [
                    ['Salt', 'Premium Brand', 'Pure refined iodized salt for cooking and seasoning.', 35.00, '1kg', 150, 20, 'kg', 'Cooking'],
                    ['Honey', 'Natural Premium', 'Pure natural honey collected from local beehives.', 450.00, '500gm', 80, 15, 'packs', 'Cooking'],
                    ['Dates', 'Premium Quality', 'Premium quality dates, naturally dried and sweet.', 350.00, '500gm', 100, 20, 'packs', 'Fruits & Vegetables'],
                    ['Shosha (Cucumber)', 'Fresh Farm', 'Fresh, crisp cucumbers locally sourced from trusted farms.', 60.00, '1kg', 200, 30, 'kg', 'Fruits & Vegetables'],
                    ['Pudinapata (Mint Leaf)', 'Fresh Garden', 'Fresh mint leaves harvested from local gardens.', 50.00, '100gm', 120, 25, 'packs', 'Fruits & Vegetables'],
                    ['Kagzi (Lemon)', 'Fresh Farm', 'Fresh kagzi lemons, known for their thin skin and juicy flesh.', 80.00, '1kg', 180, 30, 'kg', 'Fruits & Vegetables'],
                    ['Beef Premium Cube', 'Premium Meat', 'Premium quality beef cubes, freshly cut and prepared.', 550.00, '1kg', 60, 15, 'kg', 'Meat & Poultry'],
                    ['Diploma Instant Full Cream Milk Powder 1kg (Foil Pack)', 'Diploma', 'Premium quality instant full cream milk powder in convenient foil packaging.', 650.00, '1kg', 90, 20, 'packs', 'Dairy & Eggs'],
                    ['Chinigura Rice Loose (P) (BRRI-34)', 'Premium Quality', 'Premium quality Chinigura rice, BRRI-34 variety.', 95.00, '1kg', 200, 40, 'kg', 'Rice & Grains'],
                    ['Nazirshail Rice Loose (P) (Sompa Katari)', 'Premium Quality', 'Premium Nazirshail rice, Sompa Katari variety.', 120.00, '1kg', 150, 30, 'kg', 'Rice & Grains'],
                    ['Miniket Rice Loose(S) (BRRI-28)', 'Premium Quality', 'Quality Miniket rice, BRRI-28 variety.', 75.00, '1kg', 180, 35, 'kg', 'Rice & Grains'],
                    ['Fresh Instant Full Cream Milk Powder 1000gm', 'Fresh', 'Premium instant full cream milk powder, 1000gm pack.', 620.00, '1000gm', 85, 20, 'packs', 'Dairy & Eggs']
                ];
                
                $added = 0;
                $errors = 0;
                
                foreach ($productData as $index => $product) {
                    $name = $product[0];
                    
                    if (!in_array($name, $missing)) {
                        continue;
                    }
                    
                    // Delete if exists (cleanup)
                    $stmt = $pdo->prepare("DELETE FROM products WHERE name = ?");
                    $stmt->execute([$name]);
                    
                    $catId = $categoryIds[$product[8]] ?? null;
                    if (!$catId) {
                        echo "<div class='error'>❌ Category not found for: $name</div>";
                        $errors++;
                        continue;
                    }
                    
                    $isFrozen = ($name === 'Beef Premium Cube') ? 1 : 0;
                    $isEco = (in_array($product[8], ['Fruits & Vegetables', 'Rice & Grains'])) ? 1 : 0;
                    
                    try {
                        $stmt = $pdo->prepare("
                            INSERT INTO products (
                                name, brand, description, price, unit_size, stock_quantity,
                                low_stock_threshold, unit, category_id, image, nutrition_info,
                                diet_tags, is_eco_friendly, is_frozen, is_active, created_at
                            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, NOW())
                        ");
                        
                        $imageUrl = 'https://picsum.photos/300/200?random=' . (200 + $index);
                        $nutrition = 'Premium quality product with essential nutrients.';
                        $dietTags = '["halal", "vegetarian", "gluten-free"]';
                        
                        $stmt->execute([
                            $product[0], $product[1], $product[2], $product[3], $product[4],
                            $product[5], $product[6], $product[7], $catId, $imageUrl,
                            $nutrition, $dietTags, $isEco, $isFrozen
                        ]);
                        
                        echo "<div class='success'>✅ Added: $name</div>";
                        $added++;
                    } catch (PDOException $e) {
                        echo "<div class='error'>❌ Error adding $name: " . $e->getMessage() . "</div>";
                        $errors++;
                    }
                }
                
                echo "<div class='info'><strong>Summary: Added $added products, $errors errors</strong></div>";
                echo "</div>";
            } else {
                echo "<div class='section'>";
                echo "<h2>Step 3: All Products Already Exist</h2>";
                echo "<div class='success'>✅ All 12 products are already in the database!</div>";
                echo "</div>";
            }
            
            // Final Verification
            echo "<div class='section'>";
            echo "<h2>Final Verification</h2>";
            
            $stmt = $pdo->prepare("
                SELECT p.id, p.name, p.is_active, p.stock_quantity, c.name as category_name
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.name IN ($placeholders)
                ORDER BY p.name
            ");
            $stmt->execute($productsToAdd);
            $finalProducts = $stmt->fetchAll();
            
            if (count($finalProducts) === 12) {
                $activeCount = count(array_filter($finalProducts, function($p) { return $p['is_active'] == 1; }));
                $stockCount = count(array_filter($finalProducts, function($p) { return $p['stock_quantity'] > 0; }));
                
                echo "<div class='success'>";
                echo "<h3>🎉 SUCCESS! All 12 Products Added!</h3>";
                echo "<p>✅ Products in database: " . count($finalProducts) . "/12</p>";
                echo "<p>✅ Active products: $activeCount/12</p>";
                echo "<p>✅ Products with stock: $stockCount/12</p>";
                echo "</div>";
                
                echo "<table>";
                echo "<tr><th>ID</th><th>Product Name</th><th>Category</th><th>Price</th><th>Stock</th><th>Status</th></tr>";
                foreach ($finalProducts as $prod) {
                    $status = $prod['is_active'] ? '✅ Active' : '❌ Inactive';
                    echo "<tr>";
                    echo "<td>{$prod['id']}</td>";
                    echo "<td><strong>{$prod['name']}</strong></td>";
                    echo "<td>{$prod['category_name']}</td>";
                    echo "<td>৳{$prod['price']}</td>";
                    echo "<td>{$prod['stock_quantity']}</td>";
                    echo "<td>$status</td>";
                    echo "</tr>";
                }
                echo "</table>";
                
                echo "<div style='margin-top: 20px;'>";
                echo "<a href='index.php?route=products' class='btn'>View Products Page</a>";
                echo "<a href='index.php' class='btn'>View Homepage</a>";
                echo "</div>";
            } else {
                echo "<div class='error'>";
                echo "<h3>⚠️ Only " . count($finalProducts) . "/12 products found</h3>";
                echo "<p>Please run the SQL file: <code>database/add_12_products_FINAL.sql</code></p>";
                echo "</div>";
            }
            
            echo "</div>";
            
        } catch (PDOException $e) {
            echo "<div class='error'>❌ Database Error: " . $e->getMessage() . "</div>";
        }
        ?>
        
        <div class="section">
            <h2>📋 Alternative: Use SQL File</h2>
            <p>If the above script doesn't work, use the SQL file directly:</p>
            <ol>
                <li>Open phpMyAdmin: <code>http://localhost/phpmyadmin</code></li>
                <li>Select your database: <code>grocery_app</code></li>
                <li>Click <strong>Import</strong> tab</li>
                <li>Choose file: <code>database/add_12_products_FINAL.sql</code></li>
                <li>Click <strong>Go</strong></li>
            </ol>
            <p><strong>SQL File Location:</strong> <code>database/add_12_products_FINAL.sql</code></p>
        </div>
    </div>
</body>
</html>

