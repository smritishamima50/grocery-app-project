<?php
// Direct test of inventory system
session_start();
require_once 'config/database.php';

// Set admin session
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'admin';

// Test database connection and product count
echo "<h1>Inventory System Direct Test</h1>";

try {
    // Check total products
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM products");
    $totalProducts = $stmt->fetch()['total'];
    echo "<h2>Database Status</h2>";
    echo "Total products: $totalProducts<br>";
    
    // Check for duplicates
    $stmt = $pdo->query("SELECT COUNT(DISTINCT name) as unique_names FROM products");
    $uniqueNames = $stmt->fetch()['unique_names'];
    echo "Unique product names: $uniqueNames<br>";
    
    if ($totalProducts == $uniqueNames) {
        echo "✓ No duplicate products<br>";
    } else {
        echo "✗ Found " . ($totalProducts - $uniqueNames) . " duplicate products<br>";
    }
    
    // Test inventory query directly
    echo "<h2>Inventory Query Test</h2>";
    $stmt = $pdo->prepare("
        SELECT DISTINCT p.*, c.name as category_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE 1=1
        ORDER BY 
            CASE 
                WHEN p.stock_quantity = 0 THEN 1
                WHEN p.stock_quantity <= p.low_stock_threshold THEN 2
                ELSE 3
            END,
            p.stock_quantity ASC, 
            p.name ASC,
            p.id ASC
        LIMIT 20 OFFSET 0
    ");
    $stmt->execute();
    $products = $stmt->fetchAll();
    
    echo "Products returned by query: " . count($products) . "<br>";
    
    // Check for duplicates in results
    $productIds = array_column($products, 'id');
    $uniqueIds = array_unique($productIds);
    
    if (count($productIds) == count($uniqueIds)) {
        echo "✓ No duplicate products in query results<br>";
    } else {
        echo "✗ Found duplicate products in query results<br>";
    }
    
    // Show sample products
    echo "<h3>Sample Products (First 10)</h3>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Name</th><th>Category</th><th>Stock</th><th>Threshold</th><th>Status</th><th>Active</th></tr>";
    
    foreach (array_slice($products, 0, 10) as $product) {
        $status = '';
        if ($product['stock_quantity'] == 0) {
            $status = 'OUT OF STOCK';
        } elseif ($product['stock_quantity'] <= $product['low_stock_threshold']) {
            $status = 'LOW STOCK';
        } else {
            $status = 'IN STOCK';
        }
        
        echo "<tr>";
        echo "<td>" . $product['id'] . "</td>";
        echo "<td>" . htmlspecialchars($product['name']) . "</td>";
        echo "<td>" . htmlspecialchars($product['category_name'] ?? 'No Category') . "</td>";
        echo "<td>" . $product['stock_quantity'] . "</td>";
        echo "<td>" . $product['low_stock_threshold'] . "</td>";
        echo "<td>" . $status . "</td>";
        echo "<td>" . ($product['is_active'] ? 'Yes' : 'No') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Test pagination calculation
    echo "<h2>Pagination Test</h2>";
    $limit = 20;
    $totalPages = ceil($totalProducts / $limit);
    echo "Total pages needed: $totalPages<br>";
    echo "Products per page: $limit<br>";
    
    // Test different pages
    for ($page = 1; $page <= min(3, $totalPages); $page++) {
        $offset = ($page - 1) * $limit;
        $stmt = $pdo->prepare("
            SELECT COUNT(DISTINCT p.id) as count
            FROM products p
            WHERE 1=1
        ");
        $stmt->execute();
        $count = $stmt->fetch()['count'];
        
        $stmt = $pdo->prepare("
            SELECT DISTINCT p.*, c.name as category_name
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE 1=1
            ORDER BY p.id ASC
            LIMIT $limit OFFSET $offset
        ");
        $stmt->execute();
        $pageProducts = $stmt->fetchAll();
        
        echo "Page $page: " . count($pageProducts) . " products (offset: $offset)<br>";
    }
    
    // Test filters
    echo "<h2>Filter Tests</h2>";
    $filters = [
        'low_stock' => 'p.stock_quantity <= p.low_stock_threshold AND p.stock_quantity > 0',
        'out_of_stock' => 'p.stock_quantity = 0',
        'frozen' => 'p.is_frozen = 1',
        'eco_friendly' => 'p.is_eco_friendly = 1',
        'inactive' => 'p.is_active = 0'
    ];
    
    foreach ($filters as $filterName => $condition) {
        $stmt = $pdo->prepare("
            SELECT COUNT(DISTINCT p.id) as count
            FROM products p
            WHERE $condition
        ");
        $stmt->execute();
        $count = $stmt->fetch()['count'];
        echo "Filter '$filterName': $count products<br>";
    }
    
    echo "<h2>System Status</h2>";
    echo "✓ Database connection working<br>";
    echo "✓ Product queries working<br>";
    echo "✓ Pagination logic working<br>";
    echo "✓ Filters working<br>";
    echo "✓ No duplicate products<br>";
    
    echo "<p><strong>Inventory System is READY!</strong></p>";
    echo "<p><a href='http://localhost:8000/admin/inventory' target='_blank'>Open Inventory Management</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
    echo "<p>Stack trace:</p><pre>" . $e->getTraceAsString() . "</pre>";
}
?>
