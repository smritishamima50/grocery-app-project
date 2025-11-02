<?php
require_once 'config/database.php';

echo "<h2>Dairy & Eggs Products in Your Database</h2>";

$stmt = $pdo->prepare("
    SELECT p.name, p.price, p.unit, p.description 
    FROM products p 
    JOIN categories c ON p.category_id = c.id 
    WHERE c.name = 'Dairy & Eggs' 
    ORDER BY p.name
");
$stmt->execute();
$products = $stmt->fetchAll();

echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>Product Name</th><th>Price</th><th>Unit</th><th>Description</th></tr>";

foreach ($products as $product) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($product['name']) . "</td>";
    echo "<td>৳" . number_format($product['price'], 2) . "</td>";
    echo "<td>" . htmlspecialchars($product['unit']) . "</td>";
    echo "<td>" . htmlspecialchars($product['description']) . "</td>";
    echo "</tr>";
}

echo "</table>";
echo "<p><strong>Total Dairy Products:</strong> " . count($products) . "</p>";
?>
