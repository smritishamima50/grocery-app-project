<?php
require_once 'config/database.php';

echo "<h2>Product Nutrition Information</h2>";

$stmt = $pdo->query("
    SELECT p.name, p.nutrition_info, c.name as category_name 
    FROM products p 
    JOIN categories c ON p.category_id = c.id 
    ORDER BY c.name, p.name
");
$products = $stmt->fetchAll();

$currentCategory = '';
foreach ($products as $product) {
    if ($currentCategory != $product['category_name']) {
        $currentCategory = $product['category_name'];
        echo "<h3 style='color: #2d5a27; margin-top: 20px;'>" . htmlspecialchars($currentCategory) . "</h3>";
    }
    
    echo "<div style='border: 1px solid #ddd; margin: 10px 0; padding: 15px; border-radius: 5px;'>";
    echo "<h4 style='margin: 0 0 10px 0; color: #333;'>" . htmlspecialchars($product['name']) . "</h4>";
    echo "<p style='margin: 0; color: #666;'>" . htmlspecialchars($product['nutrition_info']) . "</p>";
    echo "</div>";
}

echo "<p><strong>Total Products with Nutrition Info:</strong> " . count($products) . "</p>";
?>
