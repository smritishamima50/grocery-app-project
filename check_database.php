<?php
// Simple database check script
require_once 'config/database.php';

try {
    // Check current categories
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM categories");
    $categoryCount = $stmt->fetch()['total'];
    
    echo "Current categories in database: " . $categoryCount . "\n";
    
    // Check current products
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM products");
    $productCount = $stmt->fetch()['total'];
    
    echo "Current products in database: " . $productCount . "\n";
    
    // List all categories
    $stmt = $pdo->query("SELECT id, name FROM categories ORDER BY id");
    $categories = $stmt->fetchAll();
    
    echo "\nCurrent categories:\n";
    foreach ($categories as $category) {
        echo "ID: " . $category['id'] . " - " . $category['name'] . "\n";
    }
    
    // Check products by category
    $stmt = $pdo->query("
        SELECT c.name, COUNT(p.id) as product_count 
        FROM categories c 
        LEFT JOIN products p ON c.id = p.category_id 
        GROUP BY c.id, c.name 
        ORDER BY c.id
    ");
    $categoryStats = $stmt->fetchAll();
    
    echo "\nProducts by category:\n";
    foreach ($categoryStats as $stat) {
        echo $stat['name'] . ": " . $stat['product_count'] . " products\n";
    }
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}
?>
