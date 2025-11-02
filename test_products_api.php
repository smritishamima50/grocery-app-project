<?php
/**
 * Test script to diagnose Products API issues
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Products API Diagnostic Test</h1>\n";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { color: green; }
    .error { color: red; }
    .info { color: blue; }
    pre { background: #f5f5f5; padding: 10px; border-radius: 5px; overflow-x: auto; }
</style>\n";

// Start session
session_start();

// Load database config
require_once 'config/database.php';

// Simulate admin session for testing
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'admin';

// Load ApiController
require_once 'app/controllers/BaseController.php';
require_once 'app/middleware/AdminMiddleware.php';
require_once 'app/controllers/ApiController.php';

echo "<h2>1. Database Connection Test</h2>\n";
try {
    $testQuery = $pdo->query("SELECT 1");
    echo "<p class='success'>✓ Database connection successful</p>\n";
} catch (PDOException $e) {
    echo "<p class='error'>✗ Database connection failed: " . htmlspecialchars($e->getMessage()) . "</p>\n";
    exit;
}

echo "<h2>2. Products Table Check</h2>\n";
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'products'");
    if ($stmt->rowCount() > 0) {
        echo "<p class='success'>✓ Products table exists</p>\n";
        
        // Check table structure
        $stmt = $pdo->query("DESCRIBE products");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "<p class='info'>Columns found: " . count($columns) . "</p>\n";
        echo "<pre>" . implode(', ', $columns) . "</pre>\n";
        
        // Check row count
        $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM products");
        $count = $stmt->fetch()['cnt'];
        echo "<p class='info'>Total products: $count</p>\n";
    } else {
        echo "<p class='error'>✗ Products table does not exist</p>\n";
        exit;
    }
} catch (PDOException $e) {
    echo "<p class='error'>✗ Error checking products table: " . htmlspecialchars($e->getMessage()) . "</p>\n";
    exit;
}

echo "<h2>3. Categories Table Check</h2>\n";
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'categories'");
    if ($stmt->rowCount() > 0) {
        echo "<p class='success'>✓ Categories table exists</p>\n";
        
        $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM categories");
        $count = $stmt->fetch()['cnt'];
        echo "<p class='info'>Total categories: $count</p>\n";
    } else {
        echo "<p class='error'>✗ Categories table does not exist</p>\n";
    }
} catch (PDOException $e) {
    echo "<p class='error'>✗ Error checking categories table: " . htmlspecialchars($e->getMessage()) . "</p>\n";
}

echo "<h2>4. Test API Call</h2>\n";
try {
    // Simulate GET request
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_GET = [];
    
    $controller = new ApiController();
    
    // Capture output
    ob_start();
    try {
        $controller->getProducts();
        $output = ob_get_clean();
        
        echo "<p class='success'>✓ API call completed</p>\n";
        echo "<h3>Response:</h3>\n";
        echo "<pre>" . htmlspecialchars($output) . "</pre>\n";
        
        // Try to parse JSON
        $json = json_decode($output, true);
        if ($json) {
            if (isset($json['success']) && $json['success']) {
                echo "<p class='success'>✓ JSON response indicates success</p>\n";
                echo "<p class='info'>Products returned: " . (isset($json['data']) ? count($json['data']) : 0) . "</p>\n";
            } else {
                echo "<p class='error'>✗ JSON response indicates failure</p>\n";
                echo "<p class='error'>Error: " . ($json['error'] ?? 'Unknown error') . "</p>\n";
            }
        } else {
            echo "<p class='error'>✗ Response is not valid JSON</p>\n";
        }
    } catch (Exception $e) {
        ob_end_clean();
        echo "<p class='error'>✗ API call threw exception: " . htmlspecialchars($e->getMessage()) . "</p>\n";
        echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>\n";
    }
} catch (Exception $e) {
    echo "<p class='error'>✗ Failed to create ApiController: " . htmlspecialchars($e->getMessage()) . "</p>\n";
}

echo "<h2>5. Test Query Directly</h2>\n";
try {
    $sql = "SELECT p.*, c.name as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            ORDER BY p.created_at DESC 
            LIMIT 5";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p class='success'>✓ Direct query successful</p>\n";
    echo "<p class='info'>Products found: " . count($products) . "</p>\n";
    
    if (count($products) > 0) {
        echo "<h3>Sample Product:</h3>\n";
        echo "<pre>" . print_r($products[0], true) . "</pre>\n";
    }
} catch (PDOException $e) {
    echo "<p class='error'>✗ Direct query failed: " . htmlspecialchars($e->getMessage()) . "</p>\n";
    echo "<pre>SQL: $sql</pre>\n";
}

echo "<h2>6. Admin Session Check</h2>\n";
echo "<p class='info'>Session User ID: " . ($_SESSION['user_id'] ?? 'Not set') . "</p>\n";
echo "<p class='info'>Session Role: " . ($_SESSION['role'] ?? 'Not set') . "</p>\n";

echo "<h2>Diagnostic Complete</h2>\n";
?>
