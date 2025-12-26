-- Comprehensive Nutrition Data Update for All Products
-- This script updates all products with accurate nutrition information
-- Values are per 100g (or per unit where specified) based on standard nutrition databases

-- Ensure all nutrition columns exist
ALTER TABLE products 
    ADD COLUMN IF NOT EXISTS calories_per_unit DECIMAL(10,2) DEFAULT 0,
    ADD COLUMN IF NOT EXISTS protein_per_unit DECIMAL(10,2) DEFAULT 0,
    ADD COLUMN IF NOT EXISTS carbs_per_unit DECIMAL(10,2) DEFAULT 0,
    ADD COLUMN IF NOT EXISTS fat_per_unit DECIMAL(10,2) DEFAULT 0,
    ADD COLUMN IF NOT EXISTS fiber_per_unit DECIMAL(10,2) DEFAULT 0,
    ADD COLUMN IF NOT EXISTS sodium_per_unit DECIMAL(10,2) DEFAULT 0;

-- Fruits and Vegetables (per 100g)
UPDATE products SET 
    calories_per_unit = 52, protein_per_unit = 0.3, carbs_per_unit = 14, fat_per_unit = 0.2, fiber_per_unit = 2.4, sodium_per_unit = 1,
    is_vegetarian = TRUE, is_diabetes_friendly = TRUE, is_weight_loss_friendly = TRUE
WHERE name LIKE '%Apple%' OR name LIKE '%apples%';

UPDATE products SET 
    calories_per_unit = 89, protein_per_unit = 1.1, carbs_per_unit = 23, fat_per_unit = 0.3, fiber_per_unit = 2.6, sodium_per_unit = 1,
    is_vegetarian = TRUE, is_diabetes_friendly = TRUE, is_weight_loss_friendly = FALSE
WHERE name LIKE '%Banana%' OR name LIKE '%bananas%';

UPDATE products SET 
    calories_per_unit = 47, protein_per_unit = 0.9, carbs_per_unit = 12, fat_per_unit = 0.1, fiber_per_unit = 2.4, sodium_per_unit = 0,
    is_vegetarian = TRUE, is_diabetes_friendly = TRUE, is_weight_loss_friendly = TRUE
WHERE name LIKE '%Orange%' OR name LIKE '%oranges%';

UPDATE products SET 
    calories_per_unit = 18, protein_per_unit = 0.9, carbs_per_unit = 3.9, fat_per_unit = 0.2, fiber_per_unit = 1.2, sodium_per_unit = 5,
    is_vegetarian = TRUE, is_diabetes_friendly = TRUE, is_weight_loss_friendly = TRUE
WHERE name LIKE '%Tomato%' OR name LIKE '%tomatoes%';

UPDATE products SET 
    calories_per_unit = 16, protein_per_unit = 0.7, carbs_per_unit = 3.6, fat_per_unit = 0.2, fiber_per_unit = 1.6, sodium_per_unit = 5,
    is_vegetarian = TRUE, is_diabetes_friendly = TRUE, is_weight_loss_friendly = TRUE
WHERE name LIKE '%Cucumber%' OR name LIKE '%cucumbers%';

UPDATE products SET 
    calories_per_unit = 25, protein_per_unit = 1.0, carbs_per_unit = 5, fat_per_unit = 0.1, fiber_per_unit = 2.2, sodium_per_unit = 2,
    is_vegetarian = TRUE, is_diabetes_friendly = TRUE, is_weight_loss_friendly = TRUE
WHERE name LIKE '%Carrot%' OR name LIKE '%carrots%';

UPDATE products SET 
    calories_per_unit = 22, protein_per_unit = 2.9, carbs_per_unit = 3.6, fat_per_unit = 0.2, fiber_per_unit = 2.0, sodium_per_unit = 15,
    is_vegetarian = TRUE, is_diabetes_friendly = TRUE, is_weight_loss_friendly = TRUE
WHERE name LIKE '%Spinach%' OR name LIKE '%spinach%';

UPDATE products SET 
    calories_per_unit = 25, protein_per_unit = 1.1, carbs_per_unit = 5.8, fat_per_unit = 0.2, fiber_per_unit = 1.8, sodium_per_unit = 6,
    is_vegetarian = TRUE, is_diabetes_friendly = TRUE, is_weight_loss_friendly = TRUE
WHERE name LIKE '%Broccoli%' OR name LIKE '%broccoli%';

UPDATE products SET 
    calories_per_unit = 20, protein_per_unit = 1.5, carbs_per_unit = 4.0, fat_per_unit = 0.1, fiber_per_unit = 2.0, sodium_per_unit = 4,
    is_vegetarian = TRUE, is_diabetes_friendly = TRUE, is_weight_loss_friendly = TRUE
WHERE name LIKE '%Lettuce%' OR name LIKE '%lettuce%';

UPDATE products SET 
    calories_per_unit = 33, protein_per_unit = 1.5, carbs_per_unit = 7.0, fat_per_unit = 0.2, fiber_per_unit = 2.1, sodium_per_unit = 5,
    is_vegetarian = TRUE, is_diabetes_friendly = TRUE, is_weight_loss_friendly = TRUE
WHERE name LIKE '%Onion%' OR name LIKE '%onions%';

UPDATE products SET 
    calories_per_unit = 32, protein_per_unit = 1.3, carbs_per_unit = 7.0, fat_per_unit = 0.2, fiber_per_unit = 2.6, sodium_per_unit = 6,
    is_vegetarian = TRUE, is_diabetes_friendly = TRUE, is_weight_loss_friendly = TRUE
WHERE name LIKE '%Potato%' OR name LIKE '%potatoes%' AND name NOT LIKE '%Frozen%';

-- Dairy Products (per 100g or per unit)
UPDATE products SET 
    calories_per_unit = 61, protein_per_unit = 3.2, carbs_per_unit = 4.8, fat_per_unit = 3.3, fiber_per_unit = 0, sodium_per_unit = 44,
    is_vegetarian = TRUE, is_diabetes_friendly = TRUE, is_weight_loss_friendly = TRUE
WHERE name LIKE '%Milk%' AND name NOT LIKE '%Chocolate%' AND name NOT LIKE '%Flavored%';

UPDATE products SET 
    calories_per_unit = 113, protein_per_unit = 7.0, carbs_per_unit = 0.9, fat_per_unit = 9.0, fiber_per_unit = 0, sodium_per_unit = 190,
    is_vegetarian = TRUE, is_diabetes_friendly = FALSE, is_weight_loss_friendly = FALSE
WHERE name LIKE '%Cheese%' OR name LIKE '%cheese%';

UPDATE products SET 
    calories_per_unit = 59, protein_per_unit = 10, carbs_per_unit = 3.6, fat_per_unit = 0.4, fiber_per_unit = 0, sodium_per_unit = 108,
    is_vegetarian = TRUE, is_diabetes_friendly = TRUE, is_weight_loss_friendly = TRUE
WHERE name LIKE '%Yogurt%' OR name LIKE '%yogurt%' AND name NOT LIKE '%Flavored%';

UPDATE products SET 
    calories_per_unit = 155, protein_per_unit = 13, carbs_per_unit = 1.1, fat_per_unit = 11, fiber_per_unit = 0, sodium_per_unit = 124,
    is_vegetarian = FALSE, is_diabetes_friendly = TRUE, is_weight_loss_friendly = TRUE, is_muscle_gain_friendly = TRUE
WHERE name LIKE '%Egg%' OR name LIKE '%eggs%';

-- Meat Products (per 100g)
UPDATE products SET 
    calories_per_unit = 165, protein_per_unit = 31, carbs_per_unit = 0, fat_per_unit = 3.6, fiber_per_unit = 0, sodium_per_unit = 74,
    is_vegetarian = FALSE, is_diabetes_friendly = TRUE, is_weight_loss_friendly = TRUE, is_muscle_gain_friendly = TRUE
WHERE name LIKE '%Chicken%' AND name NOT LIKE '%Frozen%' AND name NOT LIKE '%Nuggets%';

UPDATE products SET 
    calories_per_unit = 250, protein_per_unit = 26, carbs_per_unit = 0, fat_per_unit = 17, fiber_per_unit = 0, sodium_per_unit = 55,
    is_vegetarian = FALSE, is_diabetes_friendly = TRUE, is_weight_loss_friendly = FALSE, is_muscle_gain_friendly = TRUE
WHERE name LIKE '%Beef%' OR name LIKE '%beef%';

UPDATE products SET 
    calories_per_unit = 180, protein_per_unit = 25, carbs_per_unit = 0, fat_per_unit = 8, fiber_per_unit = 0, sodium_per_unit = 60,
    is_vegetarian = FALSE, is_diabetes_friendly = TRUE, is_weight_loss_friendly = TRUE, is_muscle_gain_friendly = TRUE
WHERE name LIKE '%Fish%' OR name LIKE '%fish%' OR name LIKE '%Salmon%' OR name LIKE '%Tuna%';

-- Bakery Products (per 100g or per slice/piece)
UPDATE products SET 
    calories_per_unit = 265, protein_per_unit = 9, carbs_per_unit = 49, fat_per_unit = 3.2, fiber_per_unit = 2.7, sodium_per_unit = 491,
    is_vegetarian = TRUE, is_diabetes_friendly = FALSE, is_weight_loss_friendly = FALSE
WHERE name LIKE '%Bread%' OR name LIKE '%bread%' AND name NOT LIKE '%Croissant%';

UPDATE products SET 
    calories_per_unit = 406, protein_per_unit = 8.2, carbs_per_unit = 45, fat_per_unit = 21, fiber_per_unit = 2.6, sodium_per_unit = 371,
    is_vegetarian = TRUE, is_diabetes_friendly = FALSE, is_weight_loss_friendly = FALSE
WHERE name LIKE '%Croissant%' OR name LIKE '%croissant%';

UPDATE products SET 
    calories_per_unit = 485, protein_per_unit = 6, carbs_per_unit = 71, fat_per_unit = 20, fiber_per_unit = 2.0, sodium_per_unit = 380,
    is_vegetarian = TRUE, is_diabetes_friendly = FALSE, is_weight_loss_friendly = FALSE
WHERE name LIKE '%Cookie%' OR name LIKE '%cookies%' OR name LIKE '%Biscuit%' OR name LIKE '%biscuits%';

UPDATE products SET 
    calories_per_unit = 339, protein_per_unit = 8, carbs_per_unit = 55, fat_per_unit = 10, fiber_per_unit = 2.3, sodium_per_unit = 230,
    is_vegetarian = TRUE, is_diabetes_friendly = FALSE, is_weight_loss_friendly = FALSE
WHERE name LIKE '%Cake%' OR name LIKE '%cake%';

-- Beverages (per 100ml)
UPDATE products SET 
    calories_per_unit = 42, protein_per_unit = 0, carbs_per_unit = 10.6, fat_per_unit = 0, fiber_per_unit = 0, sodium_per_unit = 4,
    is_vegetarian = TRUE, is_diabetes_friendly = FALSE, is_weight_loss_friendly = FALSE
WHERE name LIKE '%Cola%' OR name LIKE '%Soda%' OR name LIKE '%Soft Drink%';

UPDATE products SET 
    calories_per_unit = 45, protein_per_unit = 0.7, carbs_per_unit = 10.4, fat_per_unit = 0.2, fiber_per_unit = 0.2, sodium_per_unit = 1,
    is_vegetarian = TRUE, is_diabetes_friendly = TRUE, is_weight_loss_friendly = TRUE
WHERE name LIKE '%Juice%' OR name LIKE '%juice%' AND name NOT LIKE '%Cola%';

UPDATE products SET 
    calories_per_unit = 2, protein_per_unit = 0, carbs_per_unit = 0.3, fat_per_unit = 0, fiber_per_unit = 0, sodium_per_unit = 3,
    is_vegetarian = TRUE, is_diabetes_friendly = TRUE, is_weight_loss_friendly = TRUE
WHERE name LIKE '%Tea%' OR name LIKE '%tea%' AND name NOT LIKE '%Biscuit%';

UPDATE products SET 
    calories_per_unit = 1, protein_per_unit = 0.1, carbs_per_unit = 0, fat_per_unit = 0, fiber_per_unit = 0, sodium_per_unit = 2,
    is_vegetarian = TRUE, is_diabetes_friendly = TRUE, is_weight_loss_friendly = TRUE
WHERE name LIKE '%Coffee%' OR name LIKE '%coffee%' AND name NOT LIKE '%Biscuit%';

UPDATE products SET 
    calories_per_unit = 43, protein_per_unit = 0.5, carbs_per_unit = 10, fat_per_unit = 0.1, fiber_per_unit = 0, sodium_per_unit = 4,
    is_vegetarian = TRUE, is_diabetes_friendly = FALSE, is_weight_loss_friendly = FALSE
WHERE name LIKE '%Water%' AND name LIKE '%Flavored%';

-- Snacks (per 100g)
UPDATE products SET 
    calories_per_unit = 536, protein_per_unit = 7, carbs_per_unit = 53, fat_per_unit = 35, fiber_per_unit = 4.2, sodium_per_unit = 536,
    is_vegetarian = TRUE, is_diabetes_friendly = FALSE, is_weight_loss_friendly = FALSE
WHERE name LIKE '%Chips%' OR name LIKE '%chips%' OR name LIKE '%Crisps%';

UPDATE products SET 
    calories_per_unit = 607, protein_per_unit = 20, carbs_per_unit = 21, fat_per_unit = 54, fiber_per_unit = 7, sodium_per_unit = 1,
    is_vegetarian = TRUE, is_diabetes_friendly = TRUE, is_weight_loss_friendly = FALSE, is_muscle_gain_friendly = TRUE
WHERE name LIKE '%Nuts%' OR name LIKE '%nuts%' OR name LIKE '%Almond%' OR name LIKE '%Peanut%';

UPDATE products SET 
    calories_per_unit = 520, protein_per_unit = 12, carbs_per_unit = 58, fat_per_unit = 27, fiber_per_unit = 3, sodium_per_unit = 290,
    is_vegetarian = TRUE, is_diabetes_friendly = FALSE, is_weight_loss_friendly = FALSE
WHERE name LIKE '%Crackers%' OR name LIKE '%crackers%';

-- Frozen Foods (per 100g)
UPDATE products SET 
    calories_per_unit = 290, protein_per_unit = 14, carbs_per_unit = 18, fat_per_unit = 17, fiber_per_unit = 1.0, sodium_per_unit = 600,
    is_vegetarian = FALSE, is_diabetes_friendly = FALSE, is_weight_loss_friendly = FALSE, is_muscle_gain_friendly = TRUE
WHERE name LIKE '%Frozen Chicken Nuggets%' OR name LIKE '%Chicken Nuggets%';

UPDATE products SET 
    calories_per_unit = 312, protein_per_unit = 3.4, carbs_per_unit = 41, fat_per_unit = 15, fiber_per_unit = 3.0, sodium_per_unit = 260,
    is_vegetarian = TRUE, is_diabetes_friendly = FALSE, is_weight_loss_friendly = FALSE
WHERE name LIKE '%Frozen French Fries%' OR name LIKE '%French Fries%';

UPDATE products SET 
    calories_per_unit = 64, protein_per_unit = 2.8, carbs_per_unit = 10, fat_per_unit = 0.5, fiber_per_unit = 3.0, sodium_per_unit = 50,
    is_vegetarian = TRUE, is_diabetes_friendly = TRUE, is_weight_loss_friendly = TRUE
WHERE name LIKE '%Frozen Mixed Vegetables%' OR name LIKE '%Mixed Vegetables%';

UPDATE products SET 
    calories_per_unit = 207, protein_per_unit = 3.5, carbs_per_unit = 24, fat_per_unit = 11, fiber_per_unit = 0.7, sodium_per_unit = 80,
    is_vegetarian = TRUE, is_diabetes_friendly = FALSE, is_weight_loss_friendly = FALSE
WHERE name LIKE '%Frozen Ice Cream%' OR name LIKE '%Ice Cream%';

UPDATE products SET 
    calories_per_unit = 266, protein_per_unit = 11, carbs_per_unit = 33, fat_per_unit = 10, fiber_per_unit = 1.5, sodium_per_unit = 550,
    is_vegetarian = TRUE, is_diabetes_friendly = FALSE, is_weight_loss_friendly = FALSE, is_muscle_gain_friendly = TRUE
WHERE name LIKE '%Frozen Pizza%' OR name LIKE '%Pizza%';

-- Grains and Cereals (per 100g)
UPDATE products SET 
    calories_per_unit = 379, protein_per_unit = 13, carbs_per_unit = 73, fat_per_unit = 7, fiber_per_unit = 10, sodium_per_unit = 1,
    is_vegetarian = TRUE, is_diabetes_friendly = TRUE, is_weight_loss_friendly = FALSE
WHERE name LIKE '%Rice%' OR name LIKE '%rice%' AND name NOT LIKE '%Crispy%';

UPDATE products SET 
    calories_per_unit = 389, protein_per_unit = 16, carbs_per_unit = 66, fat_per_unit = 7, fiber_per_unit = 11, sodium_per_unit = 2,
    is_vegetarian = TRUE, is_diabetes_friendly = TRUE, is_weight_loss_friendly = FALSE
WHERE name LIKE '%Oats%' OR name LIKE '%oatmeal%' OR name LIKE '%Oatmeal%';

UPDATE products SET 
    calories_per_unit = 340, protein_per_unit = 12, carbs_per_unit = 64, fat_per_unit = 4, fiber_per_unit = 12, sodium_per_unit = 3,
    is_vegetarian = TRUE, is_diabetes_friendly = TRUE, is_weight_loss_friendly = FALSE
WHERE name LIKE '%Wheat%' OR name LIKE '%wheat%' AND name NOT LIKE '%Bread%';

-- Spices and Condiments (per 100g - typically used in small amounts)
-- Note: Salt is handled separately below due to its unique nutritional profile

UPDATE products SET 
    calories_per_unit = 295, protein_per_unit = 0.5, carbs_per_unit = 73, fat_per_unit = 0.3, fiber_per_unit = 2.6, sodium_per_unit = 3,
    is_vegetarian = TRUE, is_diabetes_friendly = FALSE, is_weight_loss_friendly = FALSE
WHERE name LIKE '%Sugar%' OR name LIKE '%sugar%';

UPDATE products SET 
    calories_per_unit = 884, protein_per_unit = 0, carbs_per_unit = 0, fat_per_unit = 100, fiber_per_unit = 0, sodium_per_unit = 0,
    is_vegetarian = TRUE, is_diabetes_friendly = TRUE, is_weight_loss_friendly = FALSE
WHERE name LIKE '%Oil%' OR name LIKE '%oil%' AND name NOT LIKE '%Fish%';

-- Non-food items (cleaning products, household items) - set to 0 calories
UPDATE products SET 
    calories_per_unit = 0, protein_per_unit = 0, carbs_per_unit = 0, fat_per_unit = 0, fiber_per_unit = 0, sodium_per_unit = 0,
    is_vegetarian = TRUE, is_diabetes_friendly = TRUE, is_weight_loss_friendly = TRUE, is_muscle_gain_friendly = FALSE
WHERE name LIKE '%Detergent%' OR name LIKE '%Cleaner%' OR name LIKE '%Bleach%' OR name LIKE '%Freshener%' 
   OR name LIKE '%Fabric Softener%' OR name LIKE '%Baking Soda%' OR name LIKE '%Household%';

-- Salt - has sodium but no calories (used in tiny amounts)
-- Standard table salt is approximately 39% sodium by weight
-- Per 100g of salt: ~39,000 mg sodium (39g sodium)
UPDATE products SET 
    calories_per_unit = 0, 
    protein_per_unit = 0, 
    carbs_per_unit = 0, 
    fat_per_unit = 0, 
    fiber_per_unit = 0, 
    sodium_per_unit = 39000, -- per 100g (salt is approximately 39g sodium per 100g)
    is_vegetarian = TRUE, 
    is_diabetes_friendly = TRUE, 
    is_weight_loss_friendly = FALSE, 
    is_muscle_gain_friendly = FALSE
WHERE name LIKE '%Salt%' AND name NOT LIKE '%Low%';

-- Default values for food products that don't match any pattern
-- Set reasonable defaults based on common grocery items
UPDATE products SET 
    calories_per_unit = CASE 
        WHEN calories_per_unit = 0 OR calories_per_unit IS NULL THEN 100 
        ELSE calories_per_unit 
    END,
    protein_per_unit = CASE 
        WHEN protein_per_unit = 0 OR protein_per_unit IS NULL THEN 5 
        ELSE protein_per_unit 
    END,
    carbs_per_unit = CASE 
        WHEN carbs_per_unit = 0 OR carbs_per_unit IS NULL THEN 15 
        ELSE carbs_per_unit 
    END,
    fat_per_unit = CASE 
        WHEN fat_per_unit = 0 OR fat_per_unit IS NULL THEN 3 
        ELSE fat_per_unit 
    END,
    fiber_per_unit = CASE 
        WHEN fiber_per_unit = 0 OR fiber_per_unit IS NULL THEN 2 
        ELSE fiber_per_unit 
    END,
    sodium_per_unit = CASE 
        WHEN sodium_per_unit = 0 OR sodium_per_unit IS NULL THEN 50 
        ELSE sodium_per_unit 
    END,
    is_vegetarian = COALESCE(is_vegetarian, TRUE),
    is_diabetes_friendly = COALESCE(is_diabetes_friendly, TRUE),
    is_weight_loss_friendly = COALESCE(is_weight_loss_friendly, TRUE),
    is_muscle_gain_friendly = COALESCE(is_muscle_gain_friendly, FALSE)
WHERE (calories_per_unit = 0 OR calories_per_unit IS NULL) 
  AND name NOT LIKE '%Detergent%' AND name NOT LIKE '%Cleaner%' AND name NOT LIKE '%Bleach%' 
  AND name NOT LIKE '%Freshener%' AND name NOT LIKE '%Fabric Softener%' AND name NOT LIKE '%Baking Soda%'
  AND name NOT LIKE '%Salt%' AND name NOT LIKE '%Household%';

-- Ensure unit is set for products
UPDATE products SET unit = 'kg' WHERE unit IS NULL AND (name LIKE '%kg%' OR name LIKE '%kilogram%');
UPDATE products SET unit = 'g' WHERE unit IS NULL AND (name LIKE '%g%' AND name NOT LIKE '%kg%');
UPDATE products SET unit = 'liter' WHERE unit IS NULL AND (name LIKE '%liter%' OR name LIKE '%L%' OR name LIKE '%ml%');
UPDATE products SET unit = 'piece' WHERE unit IS NULL AND (name LIKE '%pcs%' OR name LIKE '%piece%' OR name LIKE '%each%');
UPDATE products SET unit = 'pack' WHERE unit IS NULL AND (name LIKE '%pack%' OR name LIKE '%package%');
UPDATE products SET unit = 'bottle' WHERE unit IS NULL AND (name LIKE '%bottle%');
UPDATE products SET unit = 'box' WHERE unit IS NULL AND (name LIKE '%box%');
UPDATE products SET unit = 'pack' WHERE unit IS NULL;

-- Verify: Show products with missing or zero calories
SELECT id, name, calories_per_unit, protein_per_unit, carbs_per_unit, fat_per_unit, fiber_per_unit, sodium_per_unit, unit 
FROM products 
WHERE calories_per_unit = 0 OR calories_per_unit IS NULL 
ORDER BY id;

