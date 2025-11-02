<?php
/**
 * Comprehensive diagnostic script for drivers API issues
 * This will check all possible causes of the "failed to load drivers" error
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html><html><head><title>Drivers API Diagnostic</title>";
echo "<style>body{font-family:Arial;padding:20px;background:#f5f5f5;}";
echo ".section{background:white;padding:20px;margin:10px 0;border-radius:5px;box-shadow:0 2px 4px rgba(0,0,0,0.1);}";
echo ".success{color:green;font-weight:bold;}";
echo ".error{color:red;font-weight:bold;}";
echo ".warning{color:orange;font-weight:bold;}";
echo "pre{background:#f0f0f0;padding:10px;border-radius:3px;overflow:auto;}";
echo "</style></head><body>";
echo "<h1>🔍 Drivers API Diagnostic Tool</h1>";

// 1. Check Database Configuration
echo "<div class='section'>";
echo "<h2>1. Database Configuration</h2>";

require_once 'config/database.php';

if (defined('DB_HOST') && defined('DB_NAME') && defined('DB_USER')) {
    echo "<p class='success'>✅ Database constants are defined</p>";
    echo "<pre>DB_HOST: " . DB_HOST . "\nDB_NAME: " . DB_NAME . "\nDB_USER: " . DB_USER . "\nDB_PASS: " . (DB_PASS ? '[SET]' : '[EMPTY]') . "</pre>";
} else {
    echo "<p class='error'>❌ Database constants are missing!</p>";
}
echo "</div>";

// 2. Check Database Connection
echo "<div class='section'>";
echo "<h2>2. Database Connection</h2>";

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    echo "<p class='success'>✅ Database connection successful!</p>";
    
    // Get MySQL version
    $version = $pdo->query('SELECT VERSION()')->fetchColumn();
    echo "<p>MySQL Version: " . htmlspecialchars($version) . "</p>";
    
} catch (PDOException $e) {
    echo "<p class='error'>❌ Database connection failed!</p>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
    echo "<p><strong>Error Code:</strong> " . $e->getCode() . "</p>";
    echo "</div></body></html>";
    exit;
}
echo "</div>";

// 3. Check if drivers table exists
echo "<div class='section'>";
echo "<h2>3. Drivers Table Check</h2>";

try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'drivers'");
    $tableExists = $stmt->rowCount() > 0;
    
    if ($tableExists) {
        echo "<p class='success'>✅ Drivers table exists!</p>";
        
        // Get table structure
        $stmt = $pdo->query("DESCRIBE drivers");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h3>Table Structure:</h3>";
        echo "<table border='1' cellpadding='5' style='border-collapse:collapse;width:100%;'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        foreach ($columns as $col) {
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
        
        // Count drivers
        $stmt = $pdo->query("SELECT COUNT(*) as total, SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active FROM drivers");
        $counts = $stmt->fetch();
        echo "<p><strong>Total Drivers:</strong> " . $counts['total'] . " | <strong>Active:</strong> " . $counts['active'] . "</p>";
        
        // List all drivers
        $stmt = $pdo->query("SELECT * FROM drivers ORDER BY id");
        $drivers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($drivers) > 0) {
            echo "<h3>All Drivers in Database:</h3>";
            echo "<table border='1' cellpadding='5' style='border-collapse:collapse;width:100%;'>";
            echo "<tr><th>ID</th><th>Name</th><th>Phone</th><th>Email</th><th>Vehicle Type</th><th>Is Active</th></tr>";
            foreach ($drivers as $driver) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($driver['id']) . "</td>";
                echo "<td>" . htmlspecialchars($driver['name']) . "</td>";
                echo "<td>" . htmlspecialchars($driver['phone']) . "</td>";
                echo "<td>" . htmlspecialchars($driver['email'] ?? 'N/A') . "</td>";
                echo "<td>" . htmlspecialchars($driver['vehicle_type']) . "</td>";
                echo "<td>" . ($driver['is_active'] ? 'Yes' : 'No') . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p class='warning'>⚠️ No drivers found in the table!</p>";
        }
        
    } else {
        echo "<p class='error'>❌ Drivers table does NOT exist!</p>";
        echo "<p>Attempting to create the table...</p>";
        
        try {
            $createTableSQL = "
                CREATE TABLE IF NOT EXISTS drivers (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(100) NOT NULL,
                    phone VARCHAR(20) NOT NULL,
                    email VARCHAR(100),
                    vehicle_type ENUM('bike', 'car', 'van') DEFAULT 'bike',
                    license_number VARCHAR(50),
                    is_active BOOLEAN DEFAULT TRUE,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ";
            $pdo->exec($createTableSQL);
            echo "<p class='success'>✅ Drivers table created successfully!</p>";
            
            // Add a sample driver
            $stmt = $pdo->prepare("INSERT INTO drivers (name, phone, email, vehicle_type) VALUES (?, ?, ?, ?)");
            $stmt->execute(['Sample Driver', '1234567890', 'driver@example.com', 'bike']);
            echo "<p class='success'>✅ Sample driver added for testing!</p>";
            
        } catch (PDOException $e) {
            echo "<p class='error'>❌ Failed to create drivers table!</p>";
            echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
        }
    }
    
} catch (PDOException $e) {
    echo "<p class='error'>❌ Error checking drivers table!</p>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
}
echo "</div>";

// 4. Test the API endpoint directly
echo "<div class='section'>";
echo "<h2>4. API Endpoint Test</h2>";

// Start session for admin check
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'admin';
$_SESSION['first_name'] = 'Test';
$_SESSION['last_name'] = 'Admin';

echo "<p>Testing API endpoint...</p>";

// Include autoload
spl_autoload_register(function ($class_name) {
    $paths = [
        'app/models/',
        'app/controllers/',
        'app/helpers/',
        'app/middleware/'
    ];

    foreach ($paths as $path) {
        $file = $path . $class_name . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

try {
    $controller = new ApiController();
    
    // Suppress output for clean test
    ob_start();
    
    $_SERVER['REQUEST_METHOD'] = 'GET';
    
    // Call drivers method
    $controller->drivers();
    
    $output = ob_get_clean();
    
    echo "<h3>API Response:</h3>";
    echo "<pre>" . htmlspecialchars($output) . "</pre>";
    
    // Try to parse as JSON
    $json = json_decode($output, true);
    if ($json) {
        if (isset($json['success']) && $json['success']) {
            echo "<p class='success'>✅ API returned success!</p>";
            echo "<p>Drivers count: " . (isset($json['drivers']) ? count($json['drivers']) : 0) . "</p>";
            
            if (isset($json['drivers']) && count($json['drivers']) > 0) {
                echo "<h3>Drivers returned by API:</h3>";
                echo "<pre>" . htmlspecialchars(json_encode($json['drivers'], JSON_PRETTY_PRINT)) . "</pre>";
            }
        } else {
            echo "<p class='error'>❌ API returned error!</p>";
            echo "<p>Error: " . htmlspecialchars($json['error'] ?? 'Unknown error') . "</p>";
            if (isset($json['error_type'])) {
                echo "<p>Error Type: " . htmlspecialchars($json['error_type']) . "</p>";
            }
        }
    } else {
        echo "<p class='error'>❌ API response is not valid JSON!</p>";
        echo "<p>The response might contain PHP errors or HTML output.</p>";
    }
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Exception occurred!</p>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "\n\nFile: " . $e->getFile() . "\nLine: " . $e->getLine()) . "</pre>";
    echo "<pre>Stack trace:\n" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
} catch (Throwable $t) {
    echo "<p class='error'>❌ Fatal error occurred!</p>";
    echo "<pre>" . htmlspecialchars($t->getMessage()) . "\n\nFile: " . $t->getFile() . "\nLine: " . $t->getLine()) . "</pre>";
    echo "<pre>Stack trace:\n" . htmlspecialchars($t->getTraceAsString()) . "</pre>";
}
echo "</div>";

// 5. Check PHP Error Logs
echo "<div class='section'>";
echo "<h2>5. PHP Error Log Check</h2>";
$errorLogs = [
    'C:\xampp\php\logs\php_error_log',
    'C:\xampp\apache\logs\error.log',
    ini_get('error_log')
];

foreach ($errorLogs as $logPath) {
    if ($logPath && file_exists($logPath)) {
        echo "<h3>Error Log: " . htmlspecialchars($logPath) . "</h3>";
        $lines = file($logPath);
        $recentLines = array_slice($lines, -20); // Last 20 lines
        echo "<pre>" . htmlspecialchars(implode('', $recentLines)) . "</pre>";
        break;
    }
}
echo "</div>";

echo "</body></html>";
?>
