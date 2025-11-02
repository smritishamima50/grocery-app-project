<?php
/**
 * Test script to verify and add the 12 new products
 * This script can be used to test the product addition process
 */

require_once 'config/database.php';

try {
    echo "=== Product Addition Test Script ===\n\n";
    
    // Check if categories exist
    echo "1. Checking categories...\n";
    $stmt = $pdo->query("SELECT id, name FROM categories WHERE name IN ('Cooking', 'Rice & Grains', 'Fruits & Vegetables', 'Dairy & Eggs', 'Meat & Poultry') ORDER BY name");
    $categories = $stmt->fetchAll();
    
    if (empty($categories)) {
        echo "   ⚠️  No matching categories found. Please ensure categories exist.\n";
    } else {
        echo "   ✅ Found " . count($categories) . " categories:\n";
        foreach ($categories as $cat) {
            echo "      - ID: {$cat['id']}, Name: {$cat['name']}\n";
        }
    }
    
    echo "\n";
    
    // Check existing products with these names
    echo "2. Checking for existing products...\n";
    $productNames = [
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
    
    $placeholders = str_repeat('?,', count($productNames) - 1) . '?';
    $stmt = $pdo->prepare("SELECT name FROM products WHERE name IN ($placeholders)");
    $stmt->execute($productNames);
    $existing = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($existing)) {
        echo "   ✅ No existing products found with these names. Ready to add.\n";
    } else {
        echo "   ⚠️  Found " . count($existing) . " existing products:\n";
        foreach ($existing as $name) {
            echo "      - $name\n";
        }
        echo "   Note: Running the SQL script will create duplicates if products exist.\n";
    }
    
    echo "\n";
    
    // Read and execute SQL file
    echo "3. Ready to execute SQL script...\n";
    echo "   To add the products, run:\n";
    echo "   - MySQL CLI: mysql -u root -p grocery_app < database/add_12_new_products.sql\n";
    echo "   - Or use phpMyAdmin to import the SQL file\n";
    echo "   - Or uncomment the code below to execute programmatically\n\n";
    
    /*
    // Uncomment this section to execute the SQL script programmatically
    echo "   Executing SQL script...\n";
    $sql = file_get_contents('database/add_12_new_products.sql');
    
    // Split by semicolon and execute each statement
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    $successCount = 0;
    foreach ($statements as $statement) {
        if (empty($statement) || strpos($statement, '--') === 0) {
            continue; // Skip empty statements and comments
        }
        
        try {
            $pdo->exec($statement);
            $successCount++;
        } catch (PDOException $e) {
            echo "   ⚠️  Error executing statement: " . $e->getMessage() . "\n";
        }
    }
    
    echo "   ✅ Executed $successCount statements successfully.\n\n";
    */
    
    // Final verification
    echo "4. Verification after execution:\n";
    echo "   Run this query to verify:\n";
    echo "   SELECT COUNT(*) as total FROM products WHERE name IN ('Salt', 'Honey', 'Dates', 'Shosha (Cucumber)', 'Pudinapata (Mint Leaf)', 'Kagzi (Lemon)', 'Beef Premium Cube', 'Diploma Instant Full Cream Milk Powder 1kg (Foil Pack)', 'Chinigura Rice Loose (P) (BRRI-34)', 'Nazirshail Rice Loose (P) (Sompa Katari)', 'Miniket Rice Loose(S) (BRRI-28)', 'Fresh Instant Full Cream Milk Powder 1000gm');\n";
    echo "   Expected result: 12\n\n";
    
    echo "=== Script completed ===\n";
    
} catch (PDOException $e) {
    echo "❌ Database Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>

