<?php
/**
 * Direct Product Addition Script
 * Access this file via browser: http://localhost/add_products_now.php
 */

require_once 'config/database.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Products to Database</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 900px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; }
        .success { color: #28a745; background: #d4edda; padding: 10px; border-radius: 4px; margin: 10px 0; }
        .error { color: #dc3545; background: #f8d7da; padding: 10px; border-radius: 4px; margin: 10px 0; }
        .info { color: #004085; background: #cce5ff; padding: 10px; border-radius: 4px; margin: 10px 0; }
        .warning { color: #856404; background: #fff3cd; padding: 10px; border-radius: 4px; margin: 10px 0; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f8f9fa; font-weight: bold; }
        .btn { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; margin: 10px 5px; }
        .btn:hover { background: #0056b3; }
        .btn-success { background: #28a745; }
        .btn-success:hover { background: #218838; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🛒 Add 12 Products to Database</h1>
        
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
            // Step 1: Check existing products
            echo "<h2>Step 1: Checking Existing Products</h2>";
            $placeholders = str_repeat('?,', count($productsToAdd) - 1) . '?';
            $stmt = $pdo->prepare("SELECT name, id, is_active FROM products WHERE name IN ($placeholders)");
            $stmt->execute($productsToAdd);
            $existing = $stmt->fetchAll();
            $existingNames = array_column($existing, 'name');

            echo "<div class='info'>Found " . count($existing) . " existing products out of 12</div>";

            if (count($existing) > 0) {
                echo "<table><tr><th>ID</th><th>Name</th><th>Status</th></tr>";
                foreach ($existing as $prod) {
                    $status = $prod['is_active'] ? '✅ Active' : '❌ Inactive';
                    echo "<tr><td>{$prod['id']}</td><td>{$prod['name']}</td><td>$status</td></tr>";
                }
                echo "</table>";
            }

            $missing = array_diff($productsToAdd, $existingNames);

            if (count($missing) > 0) {
                echo "<div class='warning'>Missing " . count($missing) . " products. Adding them now...</div>";
                
                // Step 2: Ensure categories exist
                echo "<h2>Step 2: Ensuring Categories Exist</h2>";
                
                $categories = [
                    ['Rice & Grains', 'Various types of rice and grain products'],
                    ['Cooking', 'Cooking oils, spices, and cooking essentials']
                ];

                foreach ($categories as $cat) {
                    $stmt = $pdo->prepare("SELECT id FROM categories WHERE name = ?");
                    $stmt->execute([$cat[0]]);
                    $catExists = $stmt->fetch();

                    if (!$catExists) {
                        $stmt = $pdo->prepare("INSERT INTO categories (name, description, image) VALUES (?, ?, ?)");
                        $stmt->execute([$cat[0], $cat[1], 'https://picsum.photos/300/200?random=' . rand(100, 999)]);
                        echo "<div class='success'>✅ Created category: {$cat[0]}</div>";
                    } else {
                        echo "<div class='info'>Category exists: {$cat[0]} (ID: {$catExists['id']})</div>";
                    }
                }

                // Step 3: Add missing products
                echo "<h2>Step 3: Adding Missing Products</h2>";

                // Get category IDs
                $stmt = $pdo->query("SELECT id, name FROM categories");
                $allCats = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
                $catMap = array_flip($allCats);

                $products = [
                    [
                        'name' => 'Salt',
                        'brand' => 'Premium Brand',
                        'description' => 'Pure refined iodized salt for cooking and seasoning. Essential for enhancing flavors in all your dishes. Free from impurities and suitable for all types of cooking.',
                        'price' => 35.00,
                        'unit_size' => '1kg',
                        'stock_quantity' => 150,
                        'low_stock_threshold' => 20,
                        'unit' => 'kg',
                        'category' => 'Cooking',
                        'nutrition' => 'Sodium: 39000mg per 100g. Iodized salt helps prevent iodine deficiency. Essential mineral for body functions.',
                        'diet_tags' => '["halal", "vegetarian", "gluten-free"]'
                    ],
                    [
                        'name' => 'Honey',
                        'brand' => 'Natural Premium',
                        'description' => 'Pure natural honey collected from local beehives. Unprocessed and unfiltered, preserving all natural enzymes and health benefits. Perfect as a natural sweetener for tea, baking, and cooking.',
                        'price' => 450.00,
                        'unit_size' => '500gm',
                        'stock_quantity' => 80,
                        'low_stock_threshold' => 15,
                        'unit' => 'packs',
                        'category' => 'Cooking',
                        'nutrition' => 'Rich in antioxidants, natural sugars, and enzymes. Contains vitamins and minerals. Calories: 304 per 100g.',
                        'diet_tags' => '["natural", "organic", "gluten-free"]'
                    ],
                    [
                        'name' => 'Dates',
                        'brand' => 'Premium Quality',
                        'description' => 'Premium quality dates, naturally dried and sweet. Rich in natural sugars, fiber, and essential nutrients. Perfect for snacks, desserts, and energy boost.',
                        'price' => 350.00,
                        'unit_size' => '500gm',
                        'stock_quantity' => 100,
                        'low_stock_threshold' => 20,
                        'unit' => 'packs',
                        'category' => 'Fruits & Vegetables',
                        'nutrition' => 'High in fiber, potassium, magnesium, and natural sugars. Rich in antioxidants. Calories: 282 per 100g. Natural energy source.',
                        'diet_tags' => '["halal", "vegan", "organic", "gluten-free"]'
                    ],
                    [
                        'name' => 'Shosha (Cucumber)',
                        'brand' => 'Fresh Farm',
                        'description' => 'Fresh, crisp cucumbers locally sourced from trusted farms. Perfect for salads, snacks, and pickling. High water content makes it refreshing and hydrating.',
                        'price' => 60.00,
                        'unit_size' => '1kg',
                        'stock_quantity' => 200,
                        'low_stock_threshold' => 30,
                        'unit' => 'kg',
                        'category' => 'Fruits & Vegetables',
                        'nutrition' => 'High water content (95%), low calories. Rich in vitamin K, vitamin C, and potassium. Contains antioxidants and anti-inflammatory compounds.',
                        'diet_tags' => '["halal", "vegan", "vegetarian", "organic", "gluten-free"]'
                    ],
                    [
                        'name' => 'Pudinapata (Mint Leaf)',
                        'brand' => 'Fresh Garden',
                        'description' => 'Fresh mint leaves harvested from local gardens. Aromatic and flavorful, perfect for teas, chutneys, salads, and garnishing. Natural digestive aid.',
                        'price' => 50.00,
                        'unit_size' => '100gm',
                        'stock_quantity' => 120,
                        'low_stock_threshold' => 25,
                        'unit' => 'packs',
                        'category' => 'Fruits & Vegetables',
                        'nutrition' => 'Low in calories, rich in antioxidants. Contains menthol which aids digestion. Good source of vitamin A and vitamin C. Natural breath freshener.',
                        'diet_tags' => '["halal", "vegan", "vegetarian", "organic", "gluten-free"]'
                    ],
                    [
                        'name' => 'Kagzi (Lemon)',
                        'brand' => 'Fresh Farm',
                        'description' => 'Fresh kagzi lemons, known for their thin skin and juicy flesh. Perfect for cooking, beverages, and garnishing. Rich in vitamin C and natural citric acid.',
                        'price' => 80.00,
                        'unit_size' => '1kg',
                        'stock_quantity' => 180,
                        'low_stock_threshold' => 30,
                        'unit' => 'kg',
                        'category' => 'Fruits & Vegetables',
                        'nutrition' => 'Excellent source of vitamin C (53mg per 100g). Rich in citric acid, flavonoids, and antioxidants. Aids digestion and boosts immunity.',
                        'diet_tags' => '["halal", "vegan", "vegetarian", "organic", "gluten-free"]'
                    ],
                    [
                        'name' => 'Beef Premium Cube',
                        'brand' => 'Premium Meat',
                        'description' => 'Premium quality beef cubes, freshly cut and prepared. Tender and flavorful, perfect for curries, stir-fries, and grilling. Source of high-quality protein.',
                        'price' => 550.00,
                        'unit_size' => '1kg',
                        'stock_quantity' => 60,
                        'low_stock_threshold' => 15,
                        'unit' => 'kg',
                        'category' => 'Meat & Poultry',
                        'nutrition' => 'High in protein (26g per 100g), iron, zinc, and B vitamins. Rich source of complete amino acids. Calories: 250 per 100g.',
                        'diet_tags' => '["halal", "protein-rich"]',
                        'is_frozen' => true
                    ],
                    [
                        'name' => 'Diploma Instant Full Cream Milk Powder 1kg (Foil Pack)',
                        'brand' => 'Diploma',
                        'description' => 'Premium quality instant full cream milk powder in convenient foil packaging. Easy to prepare, just add water. Rich and creamy taste, perfect for beverages and cooking.',
                        'price' => 650.00,
                        'unit_size' => '1kg',
                        'stock_quantity' => 90,
                        'low_stock_threshold' => 20,
                        'unit' => 'packs',
                        'category' => 'Dairy & Eggs',
                        'nutrition' => 'Full cream milk powder with all natural nutrients. Rich in calcium (1000mg per 100g), protein (26g per 100g), and vitamin D. Good source of vitamins A and B12.',
                        'diet_tags' => '["halal", "vegetarian", "protein-rich", "calcium-rich"]'
                    ],
                    [
                        'name' => 'Chinigura Rice Loose (P) (BRRI-34)',
                        'brand' => 'Premium Quality',
                        'description' => 'Premium quality Chinigura rice, BRRI-34 variety. Long grain, aromatic, and fluffy when cooked. Locally grown premium rice with excellent texture and taste.',
                        'price' => 95.00,
                        'unit_size' => '1kg',
                        'stock_quantity' => 200,
                        'low_stock_threshold' => 40,
                        'unit' => 'kg',
                        'category' => 'Rice & Grains',
                        'nutrition' => 'High in carbohydrates, gluten-free. Good source of energy. Contains B vitamins and essential minerals. Low in fat.',
                        'diet_tags' => '["halal", "vegan", "vegetarian", "gluten-free", "locally-grown"]'
                    ],
                    [
                        'name' => 'Nazirshail Rice Loose (P) (Sompa Katari)',
                        'brand' => 'Premium Quality',
                        'description' => 'Premium Nazirshail rice, Sompa Katari variety. Fine grain, aromatic, and premium quality. Known for its distinct flavor and texture, perfect for special occasions.',
                        'price' => 120.00,
                        'unit_size' => '1kg',
                        'stock_quantity' => 150,
                        'low_stock_threshold' => 30,
                        'unit' => 'kg',
                        'category' => 'Rice & Grains',
                        'nutrition' => 'Premium long grain rice, rich in carbohydrates. Gluten-free, contains B vitamins and essential minerals. Excellent source of energy.',
                        'diet_tags' => '["halal", "vegan", "vegetarian", "gluten-free", "premium"]'
                    ],
                    [
                        'name' => 'Miniket Rice Loose(S) (BRRI-28)',
                        'brand' => 'Premium Quality',
                        'description' => 'Quality Miniket rice, BRRI-28 variety. Short grain rice with good texture and taste. Popular choice for daily meals, easy to cook and digest.',
                        'price' => 75.00,
                        'unit_size' => '1kg',
                        'stock_quantity' => 180,
                        'low_stock_threshold' => 35,
                        'unit' => 'kg',
                        'category' => 'Rice & Grains',
                        'nutrition' => 'Short grain rice, high in carbohydrates. Gluten-free, contains B vitamins and essential minerals. Good source of energy for daily consumption.',
                        'diet_tags' => '["halal", "vegan", "vegetarian", "gluten-free"]'
                    ],
                    [
                        'name' => 'Fresh Instant Full Cream Milk Powder 1000gm',
                        'brand' => 'Fresh',
                        'description' => 'Premium instant full cream milk powder, 1000gm pack. Convenient and long-lasting. Rich and creamy when prepared, perfect for tea, coffee, beverages, and cooking.',
                        'price' => 620.00,
                        'unit_size' => '1000gm',
                        'stock_quantity' => 85,
                        'low_stock_threshold' => 20,
                        'unit' => 'packs',
                        'category' => 'Dairy & Eggs',
                        'nutrition' => 'Full cream milk powder with complete nutrition. Rich in calcium (1000mg per 100g), protein (26g per 100g), and essential vitamins. Good source of vitamin D, A, and B12.',
                        'diet_tags' => '["halal", "vegetarian", "protein-rich", "calcium-rich"]'
                    ]
                ];

                $addedCount = 0;
                $errorCount = 0;

                foreach ($products as $product) {
                    if (!in_array($product['name'], $missing)) {
                        continue; // Skip if already exists
                    }

                    // Get category ID
                    $stmt = $pdo->prepare("SELECT id FROM categories WHERE name = ?");
                    $stmt->execute([$product['category']]);
                    $category = $stmt->fetch();

                    if (!$category) {
                        echo "<div class='error'>❌ Category not found: {$product['category']} for product: {$product['name']}</div>";
                        $errorCount++;
                        continue;
                    }

                    $categoryId = $category['id'];
                    $isFrozen = isset($product['is_frozen']) && $product['is_frozen'] ? 1 : 0;
                    $imageUrl = 'https://picsum.photos/300/200?random=' . (100 + $addedCount);

                    try {
                        $stmt = $pdo->prepare("
                            INSERT INTO products (
                                name, brand, description, price, unit_size, stock_quantity, 
                                low_stock_threshold, unit, category_id, image, nutrition_info, 
                                diet_tags, is_eco_friendly, is_frozen, is_active
                            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                        ");

                        $stmt->execute([
                            $product['name'],
                            $product['brand'],
                            $product['description'],
                            $product['price'],
                            $product['unit_size'],
                            $product['stock_quantity'],
                            $product['low_stock_threshold'],
                            $product['unit'],
                            $categoryId,
                            $imageUrl,
                            $product['nutrition'],
                            $product['diet_tags'],
                            $product['category'] === 'Fruits & Vegetables' ? 1 : 0, // Eco-friendly for fresh produce
                            $isFrozen,
                            1 // Active
                        ]);

                        echo "<div class='success'>✅ Added: {$product['name']} ({$product['category']})</div>";
                        $addedCount++;
                    } catch (PDOException $e) {
                        if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                            echo "<div class='info'>ℹ️ Already exists: {$product['name']}</div>";
                        } else {
                            echo "<div class='error'>❌ Error adding {$product['name']}: " . $e->getMessage() . "</div>";
                            $errorCount++;
                        }
                    }
                }

                echo "<div class='success'><strong>Added $addedCount products successfully!</strong></div>";
                if ($errorCount > 0) {
                    echo "<div class='warning'>$errorCount products had errors</div>";
                }

            } else {
                echo "<div class='success'><strong>✅ All 12 products already exist in the database!</strong></div>";
            }

            // Final verification
            echo "<h2>Final Verification</h2>";
            $stmt = $pdo->prepare("
                SELECT p.id, p.name, p.is_active, c.name as category_name, p.price, p.stock_quantity
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.name IN ($placeholders)
                ORDER BY p.name
            ");
            $stmt->execute($productsToAdd);
            $finalProducts = $stmt->fetchAll();

            echo "<table>";
            echo "<tr><th>ID</th><th>Product Name</th><th>Category</th><th>Price</th><th>Stock</th><th>Status</th></tr>";
            foreach ($finalProducts as $prod) {
                $status = $prod['is_active'] ? '✅ Active' : '❌ Inactive';
                echo "<tr>";
                echo "<td>{$prod['id']}</td>";
                echo "<td>{$prod['name']}</td>";
                echo "<td>{$prod['category_name']}</td>";
                echo "<td>৳{$prod['price']}</td>";
                echo "<td>{$prod['stock_quantity']}</td>";
                echo "<td>$status</td>";
                echo "</tr>";
            }
            echo "</table>";

            if (count($finalProducts) === 12) {
                echo "<div class='success'><h3>✅ SUCCESS! All 12 products are in the database and active!</h3></div>";
                echo "<p><a href='index.php?route=products' class='btn btn-success'>View Products on Homepage</a></p>";
            } else {
                echo "<div class='warning'><strong>⚠️ Only " . count($finalProducts) . " products found (expected 12)</strong></div>";
            }

            // Count total active products
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM products WHERE is_active = 1");
            $totalActive = $stmt->fetch()['total'];
            echo "<div class='info'><strong>Total active products in database: $totalActive</strong></div>";

        } catch (PDOException $e) {
            echo "<div class='error'>❌ Database Error: " . $e->getMessage() . "</div>";
        }
        ?>
    </div>
</body>
</html>

