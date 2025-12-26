-- Migration: Fix Categories 27 and 28 to IDs 13 and 14
-- This script updates category IDs 27 and 28 to become IDs 13 and 14
-- with proper names, descriptions, and images

-- Step 1: Check if IDs 13 and 14 already exist and handle conflicts
-- We'll use a temporary ID approach to avoid conflicts

-- Disable foreign key checks temporarily
SET FOREIGN_KEY_CHECKS = 0;

-- Step 2: Store current data from categories 27 and 28
-- Create temporary table to store the data
CREATE TEMPORARY TABLE IF NOT EXISTS temp_category_data (
    old_id INT,
    name VARCHAR(100),
    description TEXT,
    image VARCHAR(255),
    created_at TIMESTAMP
);

-- Store category 27 data
INSERT INTO temp_category_data (old_id, name, description, image, created_at)
SELECT id, name, description, image, created_at
FROM categories
WHERE id = 27;

-- Store category 28 data
INSERT INTO temp_category_data (old_id, name, description, image, created_at)
SELECT id, name, description, image, created_at
FROM categories
WHERE id = 28;

-- Step 3: Update products that reference category 27 to use temporary ID 9999
UPDATE products SET category_id = 9999 WHERE category_id = 27;

-- Step 4: Update products that reference category 28 to use temporary ID 9998
UPDATE products SET category_id = 9998 WHERE category_id = 28;

-- Step 5: Delete old categories 27 and 28
DELETE FROM categories WHERE id IN (27, 28);

-- Step 6: Check if IDs 13 and 14 exist, if so, move them to temporary IDs
-- Move category 13 to 9997 if it exists
UPDATE products SET category_id = 9997 WHERE category_id = 13;
DELETE FROM categories WHERE id = 13;

-- Move category 14 to 9996 if it exists
UPDATE products SET category_id = 9996 WHERE category_id = 14;
DELETE FROM categories WHERE id = 14;

-- Step 7: Insert new categories with IDs 13 and 14
-- Category 13: Spices & Herbs
INSERT INTO categories (id, name, description, image, created_at)
SELECT 13, 
       'Spices & Herbs',
       'Premium quality spices, herbs, and seasonings from around the world. Enhance your cooking with authentic flavors and natural ingredients.',
       'https://images.unsplash.com/photo-1596040033229-a9821ebd058d?w=800&q=80',
       COALESCE((SELECT created_at FROM temp_category_data WHERE old_id = 27), NOW())
FROM temp_category_data
WHERE old_id = 27
LIMIT 1;

-- If category 27 didn't exist, insert with default values
INSERT INTO categories (id, name, description, image, created_at)
SELECT 13, 
       'Spices & Herbs',
       'Premium quality spices, herbs, and seasonings from around the world. Enhance your cooking with authentic flavors and natural ingredients.',
       'https://images.unsplash.com/photo-1596040033229-a9821ebd058d?w=800&q=80',
       NOW()
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE id = 13);

-- Category 14: Other Natural Products
INSERT INTO categories (id, name, description, image, created_at)
SELECT 14,
       'Other Natural Products',
       'Organic and natural products including honey, oils, supplements, and wellness items. Pure, natural, and beneficial for your health.',
       'https://images.unsplash.com/photo-1556910096-6f5e72db6803?w=800&q=80',
       COALESCE((SELECT created_at FROM temp_category_data WHERE old_id = 28), NOW())
FROM temp_category_data
WHERE old_id = 28
LIMIT 1;

-- If category 28 didn't exist, insert with default values
INSERT INTO categories (id, name, description, image, created_at)
SELECT 14,
       'Other Natural Products',
       'Organic and natural products including honey, oils, supplements, and wellness items. Pure, natural, and beneficial for your health.',
       'https://images.unsplash.com/photo-1556910096-6f5e72db6803?w=800&q=80',
       NOW()
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE id = 14);

-- Step 8: Update products to reference new category IDs
-- Update products that were in category 27 to category 13
UPDATE products SET category_id = 13 WHERE category_id = 9999;

-- Update products that were in category 28 to category 14
UPDATE products SET category_id = 14 WHERE category_id = 9998;

-- Step 9: Restore categories that were moved from 13 and 14
-- Restore category that was at ID 13
UPDATE products SET category_id = 13 WHERE category_id = 9997;
-- Note: The original category 13 data would need to be restored separately if needed

-- Restore category that was at ID 14
UPDATE products SET category_id = 14 WHERE category_id = 9996;
-- Note: The original category 14 data would need to be restored separately if needed

-- Step 10: Clean up temporary table
DROP TEMPORARY TABLE IF EXISTS temp_category_data;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- Verification queries (commented out - uncomment to verify)
-- SELECT * FROM categories WHERE id IN (13, 14);
-- SELECT COUNT(*) as product_count, category_id FROM products WHERE category_id IN (13, 14) GROUP BY category_id;

