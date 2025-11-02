<?php
/**
 * Fix Missing Brand Column in Products Table
 * This script adds the brand column if it doesn't exist
 */

require_once 'config/database.php';

header('Content-Type: text/plain');
echo "=== Fix Products Table - Add Brand Column ===\n\n";

try {
    // Check if brand column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM products LIKE 'brand'");
    $columnExists = $stmt->rowCount() > 0;
    
    if ($columnExists) {
        echo "✅ Brand column already exists in products table.\n";
    } else {
        echo "❌ Brand column NOT found. Adding it now...\n\n";
        
        // Try to add the column
        try {
            // Get current columns to find where to insert
            $stmt = $pdo->query("SHOW COLUMNS FROM products");
            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Find position after 'name'
            $foundName = false;
            $insertAfter = 'name';
            
            $pdo->exec("ALTER TABLE products ADD COLUMN brand VARCHAR(255) NULL AFTER name");
            echo "✅ Brand column added successfully!\n\n";
            
            // Verify it was added
            $stmt = $pdo->query("SHOW COLUMNS FROM products LIKE 'brand'");
            if ($stmt->rowCount() > 0) {
                echo "✅ Verification: Brand column now exists.\n";
            } else {
                echo "⚠️  Warning: Column may not have been added correctly.\n";
            }
            
        } catch (PDOException $e) {
            echo "❌ Error adding brand column: " . $e->getMessage() . "\n";
            echo "\nTrying alternative method...\n";
            
            // Alternative: Use information_schema check
            try {
                $pdo->exec("
                    SET @dbname = DATABASE();
                    SET @tablename = 'products';
                    SET @columnname = 'brand';
                    SET @preparedStatement = (SELECT IF(
                      (
                        SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
                        WHERE
                          (table_name = @tablename)
                          AND (table_schema = @dbname)
                          AND (column_name = @columnname)
                      ) > 0,
                      'SELECT 1',
                      CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' VARCHAR(255) NULL AFTER name')
                    ));
                    PREPARE alterIfNotExists FROM @preparedStatement;
                    EXECUTE alterIfNotExists;
                    DEALLOCATE PREPARE alterIfNotExists;
                ");
                echo "✅ Brand column added using alternative method!\n";
            } catch (PDOException $e2) {
                echo "❌ Alternative method also failed: " . $e2->getMessage() . "\n";
                echo "\nPlease run this SQL manually:\n";
                echo "ALTER TABLE products ADD COLUMN brand VARCHAR(255) NULL AFTER name;\n";
            }
        }
    }
    
    // Show current table structure
    echo "\n=== Current Products Table Columns ===\n";
    $stmt = $pdo->query("SHOW COLUMNS FROM products");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $col) {
        echo "  - " . $col['Field'] . " (" . $col['Type'] . ")\n";
    }
    
    echo "\n=== Done ===\n";
    
} catch (PDOException $e) {
    echo "❌ Database Error: " . $e->getMessage() . "\n";
    echo "\nPlease check your database connection.\n";
}

