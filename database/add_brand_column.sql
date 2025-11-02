-- Migration: Add brand column to products table if it doesn't exist
-- This fixes the "Unknown column 'brand' in 'field list'" error

-- Add brand column if it doesn't exist
ALTER TABLE products ADD COLUMN IF NOT EXISTS brand VARCHAR(255) AFTER name;

-- If the above doesn't work (older MySQL versions), use this instead:
-- Check if column exists before adding (MySQL 5.7+)
SET @dbname = DATABASE();
SET @tablename = "products";
SET @columnname = "brand";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 1",
  "ALTER TABLE products ADD COLUMN brand VARCHAR(255) AFTER name"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

