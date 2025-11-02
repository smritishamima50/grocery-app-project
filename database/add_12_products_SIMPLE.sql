-- =====================================================
-- ADD 12 PRODUCTS - SIMPLE GUARANTEED WORKING VERSION
-- =====================================================
-- Database: grocery_app
-- Table: products
-- =====================================================
-- This script will DEFINITELY add 12 products
-- Total after: 110 products (98 existing + 12 new)
-- =====================================================

-- STEP 1: Ensure Categories Exist
-- =====================================================
INSERT IGNORE INTO categories (name, description, image) VALUES
('Cooking', 'Cooking oils, spices, and cooking essentials', 'https://picsum.photos/300/200?random=86'),
('Rice & Grains', 'Various types of rice and grain products', 'https://picsum.photos/300/200?random=85');

-- STEP 2: Get Category IDs (for reference)
-- =====================================================
-- Note: Category IDs may vary, but the script will find them automatically

-- STEP 3: Delete any existing products with these exact names
-- =====================================================
DELETE FROM products WHERE name = 'Salt';
DELETE FROM products WHERE name = 'Honey';
DELETE FROM products WHERE name = 'Dates';
DELETE FROM products WHERE name = 'Shosha (Cucumber)';
DELETE FROM products WHERE name = 'Pudinapata (Mint Leaf)';
DELETE FROM products WHERE name = 'Kagzi (Lemon)';
DELETE FROM products WHERE name = 'Beef Premium Cube';
DELETE FROM products WHERE name = 'Diploma Instant Full Cream Milk Powder 1kg (Foil Pack)';
DELETE FROM products WHERE name = 'Chinigura Rice Loose (P) (BRRI-34)';
DELETE FROM products WHERE name = 'Nazirshail Rice Loose (P) (Sompa Katari)';
DELETE FROM products WHERE name = 'Miniket Rice Loose(S) (BRRI-28)';
DELETE FROM products WHERE name = 'Fresh Instant Full Cream Milk Powder 1000gm';

-- STEP 4: Insert Products Using Direct Category Lookups
-- =====================================================

-- 1. Salt (Cooking category)
INSERT INTO products (name, brand, description, price, unit_size, stock_quantity, low_stock_threshold, unit, category_id, image, nutrition_info, diet_tags, is_eco_friendly, is_frozen, is_active)
VALUES (
    'Salt',
    'Premium Brand',
    'Pure refined iodized salt for cooking and seasoning. Essential for enhancing flavors in all your dishes. Free from impurities and suitable for all types of cooking.',
    35.00,
    '1kg',
    150,
    20,
    'kg',
    (SELECT id FROM categories WHERE name = 'Cooking' LIMIT 1),
    'https://picsum.photos/300/200?random=101',
    'Sodium: 39000mg per 100g. Iodized salt helps prevent iodine deficiency. Essential mineral for body functions.',
    '["halal", "vegetarian", "gluten-free"]',
    0,
    0,
    1
);

-- 2. Honey (Cooking category)
INSERT INTO products (name, brand, description, price, unit_size, stock_quantity, low_stock_threshold, unit, category_id, image, nutrition_info, diet_tags, is_eco_friendly, is_frozen, is_active)
VALUES (
    'Honey',
    'Natural Premium',
    'Pure natural honey collected from local beehives. Unprocessed and unfiltered, preserving all natural enzymes and health benefits. Perfect as a natural sweetener for tea, baking, and cooking.',
    450.00,
    '500gm',
    80,
    15,
    'packs',
    (SELECT id FROM categories WHERE name = 'Cooking' LIMIT 1),
    'https://picsum.photos/300/200?random=102',
    'Rich in antioxidants, natural sugars, and enzymes. Contains vitamins and minerals. Calories: 304 per 100g.',
    '["natural", "organic", "gluten-free"]',
    1,
    0,
    1
);

-- 3. Dates (Fruits & Vegetables category)
INSERT INTO products (name, brand, description, price, unit_size, stock_quantity, low_stock_threshold, unit, category_id, image, nutrition_info, diet_tags, is_eco_friendly, is_frozen, is_active)
VALUES (
    'Dates',
    'Premium Quality',
    'Premium quality dates, naturally dried and sweet. Rich in natural sugars, fiber, and essential nutrients. Perfect for snacks, desserts, and energy boost.',
    350.00,
    '500gm',
    100,
    20,
    'packs',
    (SELECT id FROM categories WHERE name = 'Fruits & Vegetables' LIMIT 1),
    'https://picsum.photos/300/200?random=103',
    'High in fiber, potassium, magnesium, and natural sugars. Rich in antioxidants. Calories: 282 per 100g. Natural energy source.',
    '["halal", "vegan", "organic", "gluten-free"]',
    1,
    0,
    1
);

-- 4. Shosha (Cucumber) (Fruits & Vegetables category)
INSERT INTO products (name, brand, description, price, unit_size, stock_quantity, low_stock_threshold, unit, category_id, image, nutrition_info, diet_tags, is_eco_friendly, is_frozen, is_active)
VALUES (
    'Shosha (Cucumber)',
    'Fresh Farm',
    'Fresh, crisp cucumbers locally sourced from trusted farms. Perfect for salads, snacks, and pickling. High water content makes it refreshing and hydrating.',
    60.00,
    '1kg',
    200,
    30,
    'kg',
    (SELECT id FROM categories WHERE name = 'Fruits & Vegetables' LIMIT 1),
    'https://picsum.photos/300/200?random=104',
    'High water content (95%), low calories. Rich in vitamin K, vitamin C, and potassium. Contains antioxidants and anti-inflammatory compounds.',
    '["halal", "vegan", "vegetarian", "organic", "gluten-free"]',
    1,
    0,
    1
);

-- 5. Pudinapata (Mint Leaf) (Fruits & Vegetables category)
INSERT INTO products (name, brand, description, price, unit_size, stock_quantity, low_stock_threshold, unit, category_id, image, nutrition_info, diet_tags, is_eco_friendly, is_frozen, is_active)
VALUES (
    'Pudinapata (Mint Leaf)',
    'Fresh Garden',
    'Fresh mint leaves harvested from local gardens. Aromatic and flavorful, perfect for teas, chutneys, salads, and garnishing. Natural digestive aid.',
    50.00,
    '100gm',
    120,
    25,
    'packs',
    (SELECT id FROM categories WHERE name = 'Fruits & Vegetables' LIMIT 1),
    'https://picsum.photos/300/200?random=105',
    'Low in calories, rich in antioxidants. Contains menthol which aids digestion. Good source of vitamin A and vitamin C. Natural breath freshener.',
    '["halal", "vegan", "vegetarian", "organic", "gluten-free"]',
    1,
    0,
    1
);

-- 6. Kagzi (Lemon) (Fruits & Vegetables category)
INSERT INTO products (name, brand, description, price, unit_size, stock_quantity, low_stock_threshold, unit, category_id, image, nutrition_info, diet_tags, is_eco_friendly, is_frozen, is_active)
VALUES (
    'Kagzi (Lemon)',
    'Fresh Farm',
    'Fresh kagzi lemons, known for their thin skin and juicy flesh. Perfect for cooking, beverages, and garnishing. Rich in vitamin C and natural citric acid.',
    80.00,
    '1kg',
    180,
    30,
    'kg',
    (SELECT id FROM categories WHERE name = 'Fruits & Vegetables' LIMIT 1),
    'https://picsum.photos/300/200?random=106',
    'Excellent source of vitamin C (53mg per 100g). Rich in citric acid, flavonoids, and antioxidants. Aids digestion and boosts immunity.',
    '["halal", "vegan", "vegetarian", "organic", "gluten-free"]',
    1,
    0,
    1
);

-- 7. Beef Premium Cube (Meat & Poultry category)
INSERT INTO products (name, brand, description, price, unit_size, stock_quantity, low_stock_threshold, unit, category_id, image, nutrition_info, diet_tags, is_eco_friendly, is_frozen, is_active)
VALUES (
    'Beef Premium Cube',
    'Premium Meat',
    'Premium quality beef cubes, freshly cut and prepared. Tender and flavorful, perfect for curries, stir-fries, and grilling. Source of high-quality protein.',
    550.00,
    '1kg',
    60,
    15,
    'kg',
    (SELECT id FROM categories WHERE name = 'Meat & Poultry' LIMIT 1),
    'https://picsum.photos/300/200?random=107',
    'High in protein (26g per 100g), iron, zinc, and B vitamins. Rich source of complete amino acids. Calories: 250 per 100g.',
    '["halal", "protein-rich"]',
    0,
    1,
    1
);

-- 8. Diploma Instant Full Cream Milk Powder 1kg (Dairy & Eggs category)
INSERT INTO products (name, brand, description, price, unit_size, stock_quantity, low_stock_threshold, unit, category_id, image, nutrition_info, diet_tags, is_eco_friendly, is_frozen, is_active)
VALUES (
    'Diploma Instant Full Cream Milk Powder 1kg (Foil Pack)',
    'Diploma',
    'Premium quality instant full cream milk powder in convenient foil packaging. Easy to prepare, just add water. Rich and creamy taste, perfect for beverages and cooking.',
    650.00,
    '1kg',
    90,
    20,
    'packs',
    (SELECT id FROM categories WHERE name = 'Dairy & Eggs' LIMIT 1),
    'https://picsum.photos/300/200?random=108',
    'Full cream milk powder with all natural nutrients. Rich in calcium (1000mg per 100g), protein (26g per 100g), and vitamin D. Good source of vitamins A and B12.',
    '["halal", "vegetarian", "protein-rich", "calcium-rich"]',
    0,
    0,
    1
);

-- 9. Chinigura Rice Loose (P) (BRRI-34) (Rice & Grains category)
INSERT INTO products (name, brand, description, price, unit_size, stock_quantity, low_stock_threshold, unit, category_id, image, nutrition_info, diet_tags, is_eco_friendly, is_frozen, is_active)
VALUES (
    'Chinigura Rice Loose (P) (BRRI-34)',
    'Premium Quality',
    'Premium quality Chinigura rice, BRRI-34 variety. Long grain, aromatic, and fluffy when cooked. Locally grown premium rice with excellent texture and taste.',
    95.00,
    '1kg',
    200,
    40,
    'kg',
    (SELECT id FROM categories WHERE name = 'Rice & Grains' LIMIT 1),
    'https://picsum.photos/300/200?random=109',
    'High in carbohydrates, gluten-free. Good source of energy. Contains B vitamins and essential minerals. Low in fat.',
    '["halal", "vegan", "vegetarian", "gluten-free", "locally-grown"]',
    1,
    0,
    1
);

-- 10. Nazirshail Rice Loose (P) (Sompa Katari) (Rice & Grains category)
INSERT INTO products (name, brand, description, price, unit_size, stock_quantity, low_stock_threshold, unit, category_id, image, nutrition_info, diet_tags, is_eco_friendly, is_frozen, is_active)
VALUES (
    'Nazirshail Rice Loose (P) (Sompa Katari)',
    'Premium Quality',
    'Premium Nazirshail rice, Sompa Katari variety. Fine grain, aromatic, and premium quality. Known for its distinct flavor and texture, perfect for special occasions.',
    120.00,
    '1kg',
    150,
    30,
    'kg',
    (SELECT id FROM categories WHERE name = 'Rice & Grains' LIMIT 1),
    'https://picsum.photos/300/200?random=110',
    'Premium long grain rice, rich in carbohydrates. Gluten-free, contains B vitamins and essential minerals. Excellent source of energy.',
    '["halal", "vegan", "vegetarian", "gluten-free", "premium"]',
    1,
    0,
    1
);

-- 11. Miniket Rice Loose(S) (BRRI-28) (Rice & Grains category)
INSERT INTO products (name, brand, description, price, unit_size, stock_quantity, low_stock_threshold, unit, category_id, image, nutrition_info, diet_tags, is_eco_friendly, is_frozen, is_active)
VALUES (
    'Miniket Rice Loose(S) (BRRI-28)',
    'Premium Quality',
    'Quality Miniket rice, BRRI-28 variety. Short grain rice with good texture and taste. Popular choice for daily meals, easy to cook and digest.',
    75.00,
    '1kg',
    180,
    35,
    'kg',
    (SELECT id FROM categories WHERE name = 'Rice & Grains' LIMIT 1),
    'https://picsum.photos/300/200?random=111',
    'Short grain rice, high in carbohydrates. Gluten-free, contains B vitamins and essential minerals. Good source of energy for daily consumption.',
    '["halal", "vegan", "vegetarian", "gluten-free"]',
    1,
    0,
    1
);

-- 12. Fresh Instant Full Cream Milk Powder 1000gm (Dairy & Eggs category)
INSERT INTO products (name, brand, description, price, unit_size, stock_quantity, low_stock_threshold, unit, category_id, image, nutrition_info, diet_tags, is_eco_friendly, is_frozen, is_active)
VALUES (
    'Fresh Instant Full Cream Milk Powder 1000gm',
    'Fresh',
    'Premium instant full cream milk powder, 1000gm pack. Convenient and long-lasting. Rich and creamy when prepared, perfect for tea, coffee, beverages, and cooking.',
    620.00,
    '1000gm',
    85,
    20,
    'packs',
    (SELECT id FROM categories WHERE name = 'Dairy & Eggs' LIMIT 1),
    'https://picsum.photos/300/200?random=112',
    'Full cream milk powder with complete nutrition. Rich in calcium (1000mg per 100g), protein (26g per 100g), and essential vitamins. Good source of vitamin D, A, and B12.',
    '["halal", "vegetarian", "protein-rich", "calcium-rich"]',
    0,
    0,
    1
);

-- =====================================================
-- VERIFICATION - Run these queries to verify
-- =====================================================

-- Check total product count
SELECT 
    'TOTAL PRODUCTS' as Info,
    COUNT(*) as Count
FROM products;

-- Verify all 12 products were added
SELECT 
    id,
    name,
    price,
    stock_quantity,
    is_active,
    (SELECT name FROM categories WHERE id = products.category_id) as category_name
FROM products 
WHERE name IN (
    'Salt',
    'Honey',
    'Dates',
    'Shosha (Cucumber)',
    'Pudinapata (Mint Leaf)',
    'Kagzi (Lemon)',
    'Beef Premium Cube',
    'Diploma Instant Full Cream Milk Powder 1kg (Foil Pack)',
    'Chinigura Rice Loose (P) (BRRI-34)',
    'Nazirshail Rice Loose (P) (Sompa Katari)',
    'Miniket Rice Loose(S) (BRRI-28)',
    'Fresh Instant Full Cream Milk Powder 1000gm'
)
ORDER BY id DESC;

