<?php
/**
 * Check and Create Drivers Table
 * Run this script to ensure the drivers table exists in your database
 */

require_once 'config/database.php';

try {
    // Check if drivers table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'drivers'");
    $tableExists = $stmt->fetch();
    
    if ($tableExists) {
        echo "✅ Drivers table already exists.\n";
        
        // Show table structure
        $stmt = $pdo->query("DESCRIBE drivers");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "\nTable structure:\n";
        echo str_repeat("-", 60) . "\n";
        foreach ($columns as $column) {
            echo sprintf("%-20s %-20s %s\n", 
                $column['Field'], 
                $column['Type'], 
                $column['Null'] === 'YES' ? 'NULL' : 'NOT NULL'
            );
        }
        
        // Count drivers
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM drivers WHERE is_active = 1");
        $result = $stmt->fetch();
        echo "\nActive drivers: " . $result['count'] . "\n";
        
    } else {
        echo "⚠️ Drivers table does not exist. Creating it now...\n";
        
        $sql = "
        CREATE TABLE drivers (
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
        
        $pdo->exec($sql);
        echo "✅ Drivers table created successfully!\n";
    }
    
    echo "\n✅ All done! You can now use the drivers feature in the admin panel.\n";
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Error Code: " . $e->getCode() . "\n";
    exit(1);
}

