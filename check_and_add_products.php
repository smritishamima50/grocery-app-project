<?php
/**
 * Check and Add 12 Products Script
 * This script checks if the products exist in the database and adds them if missing
 */

require_once 'config/database.php';

try {
    echo "=== Checking and Adding 12 Products ===\n\n";
    
    // List of products to check/add
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
    
    // Check existing products
    echo "1. Checking existing products...\n";
    $placeholders = str_repeat('?,', count($productsToAdd) - 1) . '?';
    $stmt = $pdo->prepare("SELECT name FROM products WHERE name IN ($placeholders)");
    $stmt->execute($productsToAdd);
    $existing = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "   Found " . count($existing) . " existing products:\n";
    foreach ($existing as $name) {
        echo "   - $name\n";
    }
    
    $missing = array_diff($productsToAdd, $existing);
    echo "\n   Missing " . count($missing) . " products\n";
    
    if (count($missing) > 0) {
        echo "\n2. Adding missing products...\n";
        
        // Read and execute SQL file
        $sqlFile = 'database/add_12_new_products.sql';
        if (!file_exists($sqlFile)) {
            die("❌ SQL file not found: $sqlFile\n");
        }
        
        $sql = file_get_contents($sqlFile);
        
        // Remove SELECT statements at the end (verification queries)
        $sql = preg_replace('/-- Verify products.*$/s', '', $sql);
        
        // Split by semicolon but keep it smart
        $statements = [];
        $currentStatement = '';
        $inQuotes = false;
        $quoteChar = '';
        
        for ($i = 0; $i < strlen($sql); $i++) {
            $char = $sql[$i];
            
            if (!$inQuotes && ($char === '"' || $char === "'")) {
                $inQuotes = true;
                $quoteChar = $char;
            } elseif ($inQuotes && $char === $quoteChar && $sql[$i-1] !== '\\') {
                $inQuotes = false;
            }
            
            $currentStatement .= $char;
            
            if (!$inQuotes && $char === ';') {
                $trimmed = trim($currentStatement);
                if (!empty($trimmed) && !preg_match('/^--/', $trimmed)) {
                    $statements[] = $trimmed;
                }
                $currentStatement = '';
            }
        }
        
        $successCount = 0;
        $errorCount = 0;
        
        foreach ($statements as $statement) {
            $statement = trim($statement);
            if (empty($statement) || strpos($statement, '--') === 0) {
                continue;
            }
            
            try {
                $pdo->exec($statement);
                $successCount++;
            } catch (PDOException $e) {
                $errorCount++;
                if (strpos($e->getMessage(), 'Duplicate entry') === false) {
                    echo "   ⚠️  Warning: " . substr($e->getMessage(), 0, 100) . "\n";
                }
            }
        }
        
        echo "   ✅ Executed $successCount statements successfully\n";
        if ($errorCount > 0) {
            echo "   ⚠️  $errorCount statements had issues (may be duplicates)\n";
        }
        
        // Verify again
        echo "\n3. Verifying products after addition...\n";
        $stmt = $pdo->prepare("SELECT name FROM products WHERE name IN ($placeholders)");
        $stmt->execute($productsToAdd);
        $afterAdd = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo "   Found " . count($afterAdd) . " products after addition:\n";
        foreach ($afterAdd as $name) {
            echo "   ✅ $name\n";
        }
        
        if (count($afterAdd) === 12) {
            echo "\n   ✅ SUCCESS! All 12 products are now in the database!\n";
        } else {
            echo "\n   ⚠️  Warning: Only " . count($afterAdd) . " products found (expected 12)\n";
        }
    } else {
        echo "\n   ✅ All products already exist in the database!\n";
    }
    
    // Check product count and active status
    echo "\n4. Checking product details...\n";
    $stmt = $pdo->prepare("
        SELECT p.name, p.is_active, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.name IN ($placeholders)
        ORDER BY p.name
    ");
    $stmt->execute($productsToAdd);
    $productDetails = $stmt->fetchAll();
    
    echo "   Product details:\n";
    foreach ($productDetails as $product) {
        $status = $product['is_active'] ? '✅ Active' : '❌ Inactive';
        echo "   - {$product['name']} | Category: {$product['category_name']} | $status\n";
    }
    
    // Count total products
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM products WHERE is_active = 1");
    $totalActive = $stmt->fetch()['total'];
    echo "\n5. Total active products in database: $totalActive\n";
    
    echo "\n=== Script completed ===\n";
    
} catch (PDOException $e) {
    echo "❌ Database Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>

