-- Migration: Add Diet Profile Support
-- This migration adds user_diet_profiles table and nutrition fields to products

-- Add user_diet_profiles table
CREATE TABLE IF NOT EXISTS user_diet_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    diet_goal ENUM('weight_loss', 'muscle_gain', 'diabetes_friendly', 'low_sodium', 'vegetarian', 'general') DEFAULT 'general',
    calorie_target INT NOT NULL DEFAULT 2000,
    dietary_preferences JSON,
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_active_diet (user_id, active)
);

-- Add nutrition fields to products table (checking if they exist first)
SET @dbname = DATABASE();
SET @tablename = "products";
SET @columnname = "calories_per_unit";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 1",
  "ALTER TABLE products ADD COLUMN calories_per_unit DECIMAL(10,2) DEFAULT 0"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

SET @columnname = "protein_per_unit";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE table_name = 'products' AND table_schema = DATABASE() AND column_name = 'protein_per_unit'
  ) > 0,
  "SELECT 1",
  "ALTER TABLE products ADD COLUMN protein_per_unit DECIMAL(10,2) DEFAULT 0"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

ALTER TABLE products ADD COLUMN IF NOT EXISTS carbs_per_unit DECIMAL(10,2) DEFAULT 0;
ALTER TABLE products ADD COLUMN IF NOT EXISTS fat_per_unit DECIMAL(10,2) DEFAULT 0;
ALTER TABLE products ADD COLUMN IF NOT EXISTS fiber_per_unit DECIMAL(10,2) DEFAULT 0;
ALTER TABLE products ADD COLUMN IF NOT EXISTS sodium_per_unit DECIMAL(10,2) DEFAULT 0;
ALTER TABLE products ADD COLUMN IF NOT EXISTS is_vegetarian BOOLEAN DEFAULT TRUE;
ALTER TABLE products ADD COLUMN IF NOT EXISTS is_diabetes_friendly BOOLEAN DEFAULT FALSE;
ALTER TABLE products ADD COLUMN IF NOT EXISTS is_weight_loss_friendly BOOLEAN DEFAULT FALSE;
ALTER TABLE products ADD COLUMN IF NOT EXISTS is_muscle_gain_friendly BOOLEAN DEFAULT FALSE;

-- Update products with nutrition information
UPDATE products SET 
    calories_per_unit = CASE 
        WHEN name LIKE '%Apple%' THEN 52.00
        WHEN name LIKE '%Banana%' THEN 89.00
        WHEN name LIKE '%Milk%' THEN 61.00
        WHEN name LIKE '%Egg%' THEN 155.00
        WHEN name LIKE '%Chicken%' THEN 165.00
        WHEN name LIKE '%Bread%' THEN 265.00
        WHEN name LIKE '%Cola%' THEN 42.00
        WHEN name LIKE '%Chips%' THEN 536.00
        WHEN name LIKE '%Juice%' THEN 45.00
        WHEN name LIKE '%Cookie%' THEN 485.00
        WHEN name LIKE '%Tomato%' THEN 18.00
        WHEN name LIKE '%Cheese%' THEN 113.00
        WHEN name LIKE '%Beef%' THEN 250.00
        WHEN name LIKE '%Croissant%' THEN 406.00
        WHEN name LIKE '%Tea%' THEN 2.00
        WHEN name LIKE '%Nuts%' THEN 607.00
        WHEN name LIKE '%Frozen Chicken Nuggets%' THEN 290.00
        WHEN name LIKE '%Frozen French Fries%' THEN 312.00
        WHEN name LIKE '%Frozen Mixed Vegetables%' THEN 64.00
        WHEN name LIKE '%Frozen Ice Cream%' THEN 207.00
        WHEN name LIKE '%Frozen Pizza%' THEN 266.00
        ELSE 200.00
    END,
    protein_per_unit = CASE
        WHEN name LIKE '%Chicken%' THEN 31.00
        WHEN name LIKE '%Beef%' THEN 26.00
        WHEN name LIKE '%Egg%' THEN 13.00
        WHEN name LIKE '%Nuts%' THEN 20.00
        WHEN name LIKE '%Milk%' THEN 3.2
        WHEN name LIKE '%Bread%' THEN 9.0
        WHEN name LIKE '%Frozen Chicken Nuggets%' THEN 14.0
        WHEN name LIKE '%Frozen French Fries%' THEN 3.4
        WHEN name LIKE '%Frozen Mixed Vegetables%' THEN 2.8
        WHEN name LIKE '%Frozen Ice Cream%' THEN 3.5
        WHEN name LIKE '%Frozen Pizza%' THEN 11.0
        ELSE 5.0
    END,
    carbs_per_unit = CASE
        WHEN name LIKE '%Chips%' THEN 53.0
        WHEN name LIKE '%Cookie%' THEN 71.0
        WHEN name LIKE '%Bread%' THEN 49.00
        WHEN name LIKE '%Banana%' THEN 23.00
        WHEN name LIKE '%Apple%' THEN 14.00
        WHEN name LIKE '%Milk%' THEN 4.8
        WHEN name LIKE '%Frozen Chicken Nuggets%' THEN 18.0
        WHEN name LIKE '%Frozen French Fries%' THEN 41.0
        WHEN name LIKE '%Frozen Mixed Vegetables%' THEN 10.0
        WHEN name LIKE '%Frozen Ice Cream%' THEN 24.0
        WHEN name LIKE '%Frozen Pizza%' THEN 33.0
        ELSE 15.0
    END,
    fat_per_unit = CASE
        WHEN name LIKE '%Chips%' THEN 35.0
        WHEN name LIKE '%Nuts%' THEN 54.0
        WHEN name LIKE '%Cookie%' THEN 20.0
        WHEN name LIKE '%Beef%' THEN 17.0
        WHEN name LIKE '%Frozen Chicken Nuggets%' THEN 17.0
        WHEN name LIKE '%Frozen French Fries%' THEN 15.0
        WHEN name LIKE '%Frozen Mixed Vegetables%' THEN 0.5
        WHEN name LIKE '%Frozen Ice Cream%' THEN 11.0
        WHEN name LIKE '%Frozen Pizza%' THEN 10.0
        ELSE 5.0
    END,
    fiber_per_unit = CASE
        WHEN name LIKE '%Nuts%' THEN 7.0
        WHEN name LIKE '%Apple%' THEN 2.4
        WHEN name LIKE '%Banana%' THEN 2.6
        WHEN name LIKE '%Bread%' THEN 2.7
        WHEN name LIKE '%Frozen Chicken Nuggets%' THEN 1.0
        WHEN name LIKE '%Frozen French Fries%' THEN 3.0
        WHEN name LIKE '%Frozen Mixed Vegetables%' THEN 3.0
        WHEN name LIKE '%Frozen Ice Cream%' THEN 0.7
        WHEN name LIKE '%Frozen Pizza%' THEN 1.5
        ELSE 1.0
    END,
    sodium_per_unit = CASE
        WHEN name LIKE '%Chips%' THEN 536.00
        WHEN name LIKE '%Cookie%' THEN 380.00
        WHEN name LIKE '%Bread%' THEN 491.00
        WHEN name LIKE '%Frozen Chicken Nuggets%' THEN 600.00
        WHEN name LIKE '%Frozen French Fries%' THEN 260.00
        WHEN name LIKE '%Frozen Mixed Vegetables%' THEN 50.00
        WHEN name LIKE '%Frozen Ice Cream%' THEN 80.00
        WHEN name LIKE '%Frozen Pizza%' THEN 550.00
        ELSE 50.00
    END,
    is_vegetarian = CASE
        WHEN name LIKE '%Chicken%' OR name LIKE '%Beef%' OR name LIKE '%Egg%' OR name LIKE '%Frozen Chicken Nuggets%' THEN FALSE
        ELSE TRUE
    END,
    is_diabetes_friendly = CASE
        WHEN name LIKE '%Chips%' OR name LIKE '%Cookie%' OR name LIKE '%Cola%' OR name LIKE '%Frozen French Fries%' OR name LIKE '%Frozen Ice Cream%' OR name LIKE '%Frozen Pizza%' THEN FALSE
        ELSE TRUE
    END,
    is_weight_loss_friendly = CASE
        WHEN name LIKE '%Apple%' OR name LIKE '%Tomato%' OR name LIKE '%Milk%' OR name LIKE '%Tea%' OR name LIKE '%Frozen Mixed Vegetables%' THEN TRUE
        WHEN name LIKE '%Chips%' OR name LIKE '%Cookie%' OR name LIKE '%Nuts%' OR name LIKE '%Croissant%' OR name LIKE '%Beef%' OR name LIKE '%Frozen French Fries%' OR name LIKE '%Frozen Ice Cream%' OR name LIKE '%Frozen Pizza%' OR name LIKE '%Frozen Chicken Nuggets%' THEN FALSE
        ELSE TRUE
    END,
    is_muscle_gain_friendly = CASE
        WHEN name LIKE '%Chicken%' OR name LIKE '%Beef%' OR name LIKE '%Egg%' OR name LIKE '%Nuts%' OR name LIKE '%Milk%' OR name LIKE '%Frozen Chicken Nuggets%' OR name LIKE '%Frozen Pizza%' THEN TRUE
        ELSE FALSE
    END;
