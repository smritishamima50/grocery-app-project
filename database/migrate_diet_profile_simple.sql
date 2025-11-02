-- Migration: Add Diet Profile Support (Compatible with older MySQL)
-- Run this file to add diet profile functionality

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

-- Add nutrition fields to products table
ALTER TABLE products ADD COLUMN calories_per_unit DECIMAL(10,2) DEFAULT 0;
ALTER TABLE products ADD COLUMN protein_per_unit DECIMAL(10,2) DEFAULT 0;
ALTER TABLE products ADD COLUMN carbs_per_unit DECIMAL(10,2) DEFAULT 0;
ALTER TABLE products ADD COLUMN fat_per_unit DECIMAL(10,2) DEFAULT 0;
ALTER TABLE products ADD COLUMN fiber_per_unit DECIMAL(10,2) DEFAULT 0;
ALTER TABLE products ADD COLUMN sodium_per_unit DECIMAL(10,2) DEFAULT 0;
ALTER TABLE products ADD COLUMN is_vegetarian BOOLEAN DEFAULT TRUE;
ALTER TABLE products ADD COLUMN is_diabetes_friendly BOOLEAN DEFAULT FALSE;
ALTER TABLE products ADD COLUMN is_weight_loss_friendly BOOLEAN DEFAULT FALSE;
ALTER TABLE products ADD COLUMN is_muscle_gain_friendly BOOLEAN DEFAULT FALSE;

-- Update products with basic nutrition information
UPDATE products SET calories_per_unit = 52.00, protein_per_unit = 0.3, carbs_per_unit = 14.00, fat_per_unit = 0.2, fiber_per_unit = 2.4, sodium_per_unit = 1.00, is_vegetarian = TRUE, is_diabetes_friendly = TRUE, is_weight_loss_friendly = TRUE WHERE name LIKE '%Apple%';
UPDATE products SET calories_per_unit = 89.00, protein_per_unit = 1.1, carbs_per_unit = 23.00, fat_per_unit = 0.3, fiber_per_unit = 2.6, sodium_per_unit = 1.00, is_vegetarian = TRUE, is_diabetes_friendly = TRUE, is_weight_loss_friendly = FALSE WHERE name LIKE '%Banana%';
UPDATE products SET calories_per_unit = 61.00, protein_per_unit = 3.2, carbs_per_unit = 4.8, fat_per_unit = 3.3, fiber_per_unit = 0.0, sodium_per_unit = 44.00, is_vegetarian = TRUE, is_diabetes_friendly = TRUE, is_weight_loss_friendly = TRUE WHERE name LIKE '%Milk%';
UPDATE products SET calories_per_unit = 155.00, protein_per_unit = 13.00, carbs_per_unit = 1.1, fat_per_unit = 11.00, fiber_per_unit = 0.0, sodium_per_unit = 124.00, is_vegetarian = FALSE, is_diabetes_friendly = TRUE, is_weight_loss_friendly = TRUE WHERE name LIKE '%Egg%';
UPDATE products SET calories_per_unit = 165.00, protein_per_unit = 31.00, carbs_per_unit = 0.0, fat_per_unit = 3.6, fiber_per_unit = 0.0, sodium_per_unit = 74.00, is_vegetarian = FALSE, is_diabetes_friendly = TRUE, is_weight_loss_friendly = TRUE WHERE name LIKE '%Chicken%';
UPDATE products SET calories_per_unit = 265.00, protein_per_unit = 9.0, carbs_per_unit = 49.00, fat_per_unit = 3.2, fiber_per_unit = 2.7, sodium_per_unit = 491.00, is_vegetarian = TRUE, is_diabetes_friendly = FALSE, is_weight_loss_friendly = FALSE WHERE name LIKE '%Bread%';
UPDATE products SET calories_per_unit = 42.00, protein_per_unit = 0.0, carbs_per_unit = 10.6, fat_per_unit = 0.0, fiber_per_unit = 0.0, sodium_per_unit = 4.00, is_vegetarian = TRUE, is_diabetes_friendly = FALSE, is_weight_loss_friendly = FALSE WHERE name LIKE '%Cola%';
UPDATE products SET calories_per_unit = 536.00, protein_per_unit = 7.0, carbs_per_unit = 53.0, fat_per_unit = 35.0, fiber_per_unit = 4.2, sodium_per_unit = 536.00, is_vegetarian = TRUE, is_diabetes_friendly = FALSE, is_weight_loss_friendly = FALSE WHERE name LIKE '%Chips%';
UPDATE products SET calories_per_unit = 45.00, protein_per_unit = 0.7, carbs_per_unit = 10.4, fat_per_unit = 0.2, fiber_per_unit = 0.2, sodium_per_unit = 1.00, is_vegetarian = TRUE, is_diabetes_friendly = TRUE, is_weight_loss_friendly = TRUE WHERE name LIKE '%Juice%';
UPDATE products SET calories_per_unit = 485.00, protein_per_unit = 6.0, carbs_per_unit = 71.0, fat_per_unit = 20.0, fiber_per_unit = 2.0, sodium_per_unit = 380.00, is_vegetarian = TRUE, is_diabetes_friendly = FALSE, is_weight_loss_friendly = FALSE WHERE name LIKE '%Cookie%';
UPDATE products SET calories_per_unit = 18.00, protein_per_unit = 0.9, carbs_per_unit = 3.9, fat_per_unit = 0.2, fiber_per_unit = 1.2, sodium_per_unit = 5.00, is_vegetarian = TRUE, is_diabetes_friendly = TRUE, is_weight_loss_friendly = TRUE WHERE name LIKE '%Tomato%';
UPDATE products SET calories_per_unit = 113.00, protein_per_unit = 7.0, carbs_per_unit = 0.9, fat_per_unit = 9.0, fiber_per_unit = 0.0, sodium_per_unit = 190.00, is_vegetarian = TRUE, is_diabetes_friendly = FALSE, is_weight_loss_friendly = FALSE WHERE name LIKE '%Cheese%';
UPDATE products SET calories_per_unit = 250.00, protein_per_unit = 26.0, carbs_per_unit = 0.0, fat_per_unit = 17.0, fiber_per_unit = 0.0, sodium_per_unit = 55.00, is_vegetarian = FALSE, is_diabetes_friendly = TRUE, is_weight_loss_friendly = FALSE WHERE name LIKE '%Beef%';
UPDATE products SET calories_per_unit = 406.00, protein_per_unit = 8.2, carbs_per_unit = 45.0, fat_per_unit = 21.0, fiber_per_unit = 2.6, sodium_per_unit = 371.00, is_vegetarian = TRUE, is_diabetes_friendly = FALSE, is_weight_loss_friendly = FALSE WHERE name LIKE '%Croissant%';
UPDATE products SET calories_per_unit = 2.00, protein_per_unit = 0.0, carbs_per_unit = 0.0, fat_per_unit = 0.0, fiber_per_unit = 0.0, sodium_per_unit = 3.00, is_vegetarian = TRUE, is_diabetes_friendly = TRUE, is_weight_loss_friendly = TRUE WHERE name LIKE '%Tea%';
UPDATE products SET calories_per_unit = 607.00, protein_per_unit = 20.0, carbs_per_unit = 21.0, fat_per_unit = 54.0, fiber_per_unit = 7.0, sodium_per_unit = 1.00, is_vegetarian = TRUE, is_diabetes_friendly = TRUE, is_weight_loss_friendly = FALSE WHERE name LIKE '%Nuts%';
UPDATE products SET calories_per_unit = 290.00, protein_per_unit = 14.0, carbs_per_unit = 18.0, fat_per_unit = 17.0, fiber_per_unit = 1.0, sodium_per_unit = 600.00, is_vegetarian = FALSE, is_diabetes_friendly = FALSE, is_weight_loss_friendly = FALSE, is_muscle_gain_friendly = TRUE WHERE name LIKE '%Frozen Chicken Nuggets%';
UPDATE products SET calories_per_unit = 312.00, protein_per_unit = 3.4, carbs_per_unit = 41.0, fat_per_unit = 15.0, fiber_per_unit = 3.0, sodium_per_unit = 260.00, is_vegetarian = TRUE, is_diabetes_friendly = FALSE, is_weight_loss_friendly = FALSE, is_muscle_gain_friendly = FALSE WHERE name LIKE '%Frozen French Fries%';
UPDATE products SET calories_per_unit = 64.00, protein_per_unit = 2.8, carbs_per_unit = 10.0, fat_per_unit = 0.5, fiber_per_unit = 3.0, sodium_per_unit = 50.00, is_vegetarian = TRUE, is_diabetes_friendly = TRUE, is_weight_loss_friendly = TRUE, is_muscle_gain_friendly = FALSE WHERE name LIKE '%Frozen Mixed Vegetables%';
UPDATE products SET calories_per_unit = 207.00, protein_per_unit = 3.5, carbs_per_unit = 24.0, fat_per_unit = 11.0, fiber_per_unit = 0.7, sodium_per_unit = 80.00, is_vegetarian = TRUE, is_diabetes_friendly = FALSE, is_weight_loss_friendly = FALSE, is_muscle_gain_friendly = FALSE WHERE name LIKE '%Frozen Ice Cream%';
UPDATE products SET calories_per_unit = 266.00, protein_per_unit = 11.0, carbs_per_unit = 33.0, fat_per_unit = 10.0, fiber_per_unit = 1.5, sodium_per_unit = 550.00, is_vegetarian = TRUE, is_diabetes_friendly = FALSE, is_weight_loss_friendly = FALSE, is_muscle_gain_friendly = TRUE WHERE name LIKE '%Frozen Pizza%';
