<?php
/**
 * Direct test script for drivers API endpoint
 * This will help diagnose any errors by calling the API directly
 */

// Start session
session_start();

// Include database config
require_once 'config/database.php';

// Set test admin session
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'admin';
$_SESSION['first_name'] = 'Test';
$_SESSION['last_name'] = 'Admin';

// Include autoload
spl_autoload_register(function ($class_name) {
    $paths = [
        'app/models/',
        'app/controllers/',
        'app/helpers/'
    ];

    foreach ($paths as $path) {
        $file = $path . $class_name . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Suppress errors for clean output
ini_set('display_errors', '0');
error_reporting(E_ALL);

echo "<h2>Drivers API Test</h2>";
echo "<hr>";

try {
    $controller = new ApiController();
    
    echo "<h3>Testing GET /api/admin/drivers</h3>";
    
    // Capture output
    ob_start();
    
    // Simulate GET request
    $_SERVER['REQUEST_METHOD'] = 'GET';
    
    // Call drivers method
    $controller->drivers();
    
    $output = ob_get_clean();
    
    echo "<h4>Response:</h4>";
    echo "<pre style='background: #f0f0f0; padding: 10px; border: 1px solid #ccc;'>";
    echo htmlspecialchars($output);
    echo "</pre>";
    
    // Try to parse as JSON
    $json = json_decode($output, true);
    if ($json) {
        echo "<h4>Parsed JSON:</h4>";
        echo "<pre style='background: #e8f5e9; padding: 10px; border: 1px solid #4caf50;'>";
        print_r($json);
        echo "</pre>";
        
        if (isset($json['success']) && $json['success']) {
            echo "<p style='color: green;'><strong>✅ SUCCESS:</strong> API returned success response</p>";
            echo "<p>Drivers count: " . (isset($json['drivers']) ? count($json['drivers']) : 0) . "</p>";
        } else {
            echo "<p style='color: red;'><strong>❌ ERROR:</strong> " . ($json['error'] ?? 'Unknown error') . "</p>";
        }
    } else {
        echo "<p style='color: red;'><strong>❌ ERROR:</strong> Response is not valid JSON!</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'><strong>❌ EXCEPTION:</strong> " . $e->getMessage() . "</p>";
    echo "<pre style='background: #ffebee; padding: 10px; border: 1px solid #f44336;'>";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString();
    echo "</pre>";
} catch (Throwable $t) {
    echo "<p style='color: red;'><strong>❌ FATAL ERROR:</strong> " . $t->getMessage() . "</p>";
    echo "<pre style='background: #ffebee; padding: 10px; border: 1px solid #f44336;'>";
    echo "File: " . $t->getFile() . "\n";
    echo "Line: " . $t->getLine() . "\n";
    echo "Stack trace:\n" . $t->getTraceAsString();
    echo "</pre>";
}

echo "<hr>";
echo "<h3>Check Error Logs</h3>";
echo "<p>Check your PHP error log (usually in C:\\xampp\\php\\logs\\php_error_log or C:\\xampp\\apache\\logs\\error.log) for detailed error messages.</p>";
?>
