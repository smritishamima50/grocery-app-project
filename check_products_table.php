<?php
/**
 * Check Products Table Structure
 * Run this to verify your products table matches the expected structure
 */

require_once 'config/database.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Products Table Structure Check</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
        table { border-collapse: collapse; width: 100%; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #4CAF50; color: white; }
        tr:nth-child(even) { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>Products Table Structure Check</h1>
    
    <?php
    try {
        // Get actual table structure
        $stmt = $pdo->query("DESCRIBE products");
        $actualColumns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Expected columns (from schema)
        $expectedColumns = [
            'id' => 'INT',
            'name' => 'VARCHAR(255)',
            'brand' => 'VARCHAR(255)',
            'description' => 'TEXT',
            'price' => 'DECIMAL(10,2)',
            'unit_size' => 'VARCHAR(50)',
            'stock_quantity' => 'INT',
            'low_stock_threshold' => 'INT',
            'unit' => 'VARCHAR(50)',
            'category_id' => 'INT',
            'image' => 'VARCHAR(255)',
            'nutrition_info' => 'TEXT',
            'diet_tags' => 'JSON',
            'is_eco_friendly' => 'TINYINT(1)',
            'is_frozen' => 'TINYINT(1)',
            'is_active' => 'TINYINT(1)',
            'created_at' => 'TIMESTAMP',
            'updated_at' => 'TIMESTAMP'
        ];
        
        echo "<h2>Actual Table Structure:</h2>";
        echo "<table>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        
        $actualFields = [];
        foreach ($actualColumns as $col) {
            $actualFields[$col['Field']] = $col;
            echo "<tr>";
            echo "<td>" . htmlspecialchars($col['Field']) . "</td>";
            echo "<td>" . htmlspecialchars($col['Type']) . "</td>";
            echo "<td>" . htmlspecialchars($col['Null']) . "</td>";
            echo "<td>" . htmlspecialchars($col['Key']) . "</td>";
            echo "<td>" . htmlspecialchars($col['Default'] ?? 'NULL') . "</td>";
            echo "<td>" . htmlspecialchars($col['Extra']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Check for missing columns
        echo "<h2>Column Check:</h2>";
        $missing = [];
        foreach (array_keys($expectedColumns) as $field) {
            if (!isset($actualFields[$field])) {
                $missing[] = $field;
                echo "<p class='error'>❌ Missing column: <strong>$field</strong></p>";
            } else {
                echo "<p class='success'>✅ Column exists: <strong>$field</strong></p>";
            }
        }
        
        // Check diet_tags type
        if (isset($actualFields['diet_tags'])) {
            $dietTagsType = $actualFields['diet_tags']['Type'];
            if (stripos($dietTagsType, 'json') === false) {
                echo "<p class='warning'>⚠️ diet_tags column exists but is not JSON type. Current type: $dietTagsType</p>";
                echo "<p class='warning'>If you're using MySQL 5.7+, it should be JSON. For older versions, use TEXT.</p>";
            } else {
                echo "<p class='success'>✅ diet_tags is JSON type</p>";
            }
        }
        
        // Check boolean columns
        foreach (['is_eco_friendly', 'is_frozen', 'is_active'] as $boolField) {
            if (isset($actualFields[$boolField])) {
                $type = $actualFields[$boolField]['Type'];
                if (stripos($type, 'tinyint') === false && stripos($type, 'bool') === false) {
                    echo "<p class='warning'>⚠️ $boolField is not BOOLEAN/TINYINT type. Current type: $type</p>";
                } else {
                    echo "<p class='success'>✅ $boolField is BOOLEAN type</p>";
                }
            }
        }
        
        // Test insert
        echo "<h2>Test Insert:</h2>";
        try {
            $testData = [
                'Test Product ' . time(),
                'Test Brand',
                'Test Description',
                99.99,
                '1kg',
                100,
                10,
                'kg',
                1, // category_id - will fail if no categories exist
                'https://example.com/test.jpg',
                'Test nutrition',
                '[]',
                0, // is_eco_friendly
                0, // is_frozen
                1  // is_active
            ];
            
            $stmt = $pdo->prepare("INSERT INTO products (name, brand, description, price, unit_size, stock_quantity, low_stock_threshold, unit, category_id, image, nutrition_info, diet_tags, is_eco_friendly, is_frozen, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $result = $stmt->execute($testData);
            
            if ($result) {
                $testId = $pdo->lastInsertId();
                echo "<p class='success'>✅ Test insert successful! Product ID: $testId</p>";
                
                // Clean up test product
                $pdo->prepare("DELETE FROM products WHERE id = ?")->execute([$testId]);
                echo "<p class='success'>✅ Test product cleaned up</p>";
            } else {
                $errorInfo = $stmt->errorInfo();
                echo "<p class='error'>❌ Test insert failed: " . htmlspecialchars($errorInfo[2] ?? 'Unknown error') . "</p>";
            }
        } catch (PDOException $e) {
            echo "<p class='error'>❌ Test insert error: " . htmlspecialchars($e->getMessage()) . "</p>";
            echo "<p><strong>Error details:</strong> " . htmlspecialchars(print_r($e->errorInfo ?? [], true)) . "</p>";
        }
        
        if (empty($missing)) {
            echo "<h2 class='success'>✅ All required columns exist!</h2>";
        } else {
            echo "<h2 class='error'>❌ Missing columns detected. Please run migration scripts.</h2>";
        }
        
    } catch (PDOException $e) {
        echo "<p class='error'>❌ Database connection error: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    ?>
    
    <hr>
    <p><a href="/admin/products">← Back to Products</a></p>
</body>
</html>

