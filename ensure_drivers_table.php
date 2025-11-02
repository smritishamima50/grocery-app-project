<?php
/**
 * Script to ensure the drivers table exists and has the correct structure
 * Run this script if drivers are not loading
 */

require_once 'config/database.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Ensure Drivers Table</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .success { color: green; font-weight: bold; padding: 10px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 5px; margin: 10px 0; }
        .error { color: red; font-weight: bold; padding: 10px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 5px; margin: 10px 0; }
        .info { color: #0c5460; padding: 10px; background: #d1ecf1; border: 1px solid #bee5eb; border-radius: 5px; margin: 10px 0; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 3px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>🔧 Drivers Table Setup</h1>

<?php
try {
    // Check connection
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    echo "<div class='success'>✅ Database connection successful!</div>";
    
    // Check if table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'drivers'");
    $tableExists = $stmt->rowCount() > 0;
    
    if (!$tableExists) {
        echo "<div class='info'>⚠️ Drivers table does not exist. Creating it now...</div>";
        
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
        echo "<div class='success'>✅ Drivers table created successfully!</div>";
        
        // Add sample drivers
        $sampleDrivers = [
            ['Rahim Ahmed', '+8801712345678', 'rahim@example.com', 'bike', 'BIKE123456'],
            ['Karim Uddin', '+8801712345679', 'karim@example.com', 'car', 'CAR123456'],
            ['Salam Khan', '+8801712345680', 'salam@example.com', 'van', 'VAN123456']
        ];
        
        $stmt = $pdo->prepare("INSERT INTO drivers (name, phone, email, vehicle_type, license_number) VALUES (?, ?, ?, ?, ?)");
        
        foreach ($sampleDrivers as $driver) {
            try {
                $stmt->execute($driver);
            } catch (PDOException $e) {
                // Driver might already exist, skip
            }
        }
        
        echo "<div class='success'>✅ Sample drivers added!</div>";
        
    } else {
        echo "<div class='success'>✅ Drivers table already exists!</div>";
        
        // Check table structure
        $stmt = $pdo->query("DESCRIBE drivers");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<div class='info'><h3>Table Structure:</h3><pre>";
        foreach ($columns as $col) {
            echo sprintf("%-20s %-30s %-5s %-5s %-10s %s\n",
                $col['Field'],
                $col['Type'],
                $col['Null'],
                $col['Key'],
                $col['Default'] ?? 'NULL',
                $col['Extra']
            );
        }
        echo "</pre></div>";
        
        // Check driver count
        $stmt = $pdo->query("SELECT COUNT(*) as total, SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active FROM drivers");
        $counts = $stmt->fetch();
        
        echo "<div class='info'>";
        echo "<strong>Total Drivers:</strong> " . $counts['total'] . "<br>";
        echo "<strong>Active Drivers:</strong> " . $counts['active'];
        echo "</div>";
        
        if ($counts['total'] == 0) {
            echo "<div class='info'>⚠️ No drivers in table. Adding sample drivers...</div>";
            
            $sampleDrivers = [
                ['Rahim Ahmed', '+8801712345678', 'rahim@example.com', 'bike', 'BIKE123456'],
                ['Karim Uddin', '+8801712345679', 'karim@example.com', 'car', 'CAR123456'],
                ['Salam Khan', '+8801712345680', 'salam@example.com', 'van', 'VAN123456']
            ];
            
            $stmt = $pdo->prepare("INSERT INTO drivers (name, phone, email, vehicle_type, license_number) VALUES (?, ?, ?, ?, ?)");
            
            foreach ($sampleDrivers as $driver) {
                try {
                    $stmt->execute($driver);
                } catch (PDOException $e) {
                    // Skip if error
                }
            }
            
            echo "<div class='success'>✅ Sample drivers added!</div>";
        }
        
        // Test query
        echo "<div class='info'><h3>Testing Query:</h3>";
        $stmt = $pdo->prepare("SELECT * FROM drivers WHERE is_active = 1 ORDER BY name ASC");
        $stmt->execute();
        $drivers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($drivers) > 0) {
            echo "<p class='success'>✅ Query successful! Found " . count($drivers) . " active driver(s)</p>";
            echo "<pre>";
            foreach ($drivers as $driver) {
                echo json_encode($driver, JSON_PRETTY_PRINT) . "\n";
            }
            echo "</pre>";
        } else {
            echo "<p class='error'>❌ Query returned no active drivers</p>";
        }
        echo "</div>";
    }
    
    echo "<div class='success'><h2>✅ Setup Complete!</h2>";
    echo "<p>You can now try accessing the Drivers management page in the admin panel.</p>";
    echo "<p><a href='/admin/drivers'>Go to Drivers Management</a></p>";
    echo "</div>";
    
} catch (PDOException $e) {
    echo "<div class='error'>";
    echo "<h2>❌ Database Error</h2>";
    echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Error Code:</strong> " . $e->getCode() . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    echo "</div>";
} catch (Exception $e) {
    echo "<div class='error'>";
    echo "<h2>❌ Error</h2>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}
?>

</body>
</html>
