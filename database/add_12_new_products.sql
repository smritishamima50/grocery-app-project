-- Add 12 New Products for Grocery E-Commerce
-- This script adds the requested 12 products with proper categories and details

-- First, ensure all required categories exist
-- Note: If categories already exist, they won't be duplicated (assuming unique name constraint)
-- If your database doesn't have unique constraint on category name, manually verify categories exist before running

-- Check and insert 'Rice & Grains' category if it doesn't exist
INSERT INTO categories (name, description, image)
SELECT 'Rice & Grains', 'Various types of rice and grain products', 'https://picsum.photos/300/200?random=85'
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE name = 'Rice & Grains');

-- Check and insert 'Cooking' category if it doesn't exist
INSERT INTO categories (name, description, image)
SELECT 'Cooking', 'Cooking oils, spices, and cooking essentials', 'https://picsum.photos/300/200?random=86'
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE name = 'Cooking');

-- Add the 12 products with detailed information

-- 1. Salt - Cooking Essentials
INSERT INTO products (name, brand, description, price, unit_size, stock_quantity, low_stock_threshold, unit, category_id, image, nutrition_info, diet_tags, is_eco_friendly, is_frozen, is_active) VALUES
(
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
    FALSE,
    FALSE,
    TRUE
);

-- 2. Honey - Natural Sweetener
INSERT INTO products (name, brand, description, price, unit_size, stock_quantity, low_stock_threshold, unit, category_id, image, nutrition_info, diet_tags, is_eco_friendly, is_frozen, is_active) VALUES
(
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
    TRUE,
    FALSE,
    TRUE
);

-- 3. Dates - Dried Fruits
INSERT INTO products (name, brand, description, price, unit_size, stock_quantity, low_stock_threshold, unit, category_id, image, nutrition_info, diet_tags, is_eco_friendly, is_frozen, is_active) VALUES
(
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
    TRUE,
    FALSE,
    TRUE
);

-- 4. Shosha (Cucumber) - Fresh Vegetables
INSERT INTO products (name, brand, description, price, unit_size, stock_quantity, low_stock_threshold, unit, category_id, image, nutrition_info, diet_tags, is_eco_friendly, is_frozen, is_active) VALUES
(
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
    TRUE,
    FALSE,
    TRUE
);

-- 5. Pudinapata (Mint Leaf) - Fresh Herbs
INSERT INTO products (name, brand, description, price, unit_size, stock_quantity, low_stock_threshold, unit, category_id, image, nutrition_info, diet_tags, is_eco_friendly, is_frozen, is_active) VALUES
(
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
    TRUE,
    FALSE,
    TRUE
);

-- 6. Kagzi (Lemon) - Fresh Fruits
INSERT INTO products (name, brand, description, price, unit_size, stock_quantity, low_stock_threshold, unit, category_id, image, nutrition_info, diet_tags, is_eco_friendly, is_frozen, is_active) VALUES
(
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
    TRUE,
    FALSE,
    TRUE
);

-- 7. Beef Premium Cube - Meat Products
INSERT INTO products (name, brand, description, price, unit_size, stock_quantity, low_stock_threshold, unit, category_id, image, nutrition_info, diet_tags, is_eco_friendly, is_frozen, is_active) VALUES
(
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
    FALSE,
    TRUE,
    TRUE
);

-- 8. Diploma Instant Full Cream Milk Powder 1kg (Foil Pack) - Dairy Products
INSERT INTO products (name, brand, description, price, unit_size, stock_quantity, low_stock_threshold, unit, category_id, image, nutrition_info, diet_tags, is_eco_friendly, is_frozen, is_active) VALUES
(
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
    FALSE,
    FALSE,
    TRUE
);

-- 9. Chinigura Rice Loose (P) (BRRI-34) - Rice & Grains
INSERT INTO products (name, brand, description, price, unit_size, stock_quantity, low_stock_threshold, unit, category_id, image, nutrition_info, diet_tags, is_eco_friendly, is_frozen, is_active) VALUES
(
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
    TRUE,
    FALSE,
    TRUE
);

-- 10. Nazirshail Rice Loose (P) (Sompa Katari) - Rice & Grains
INSERT INTO products (name, brand, description, price, unit_size, stock_quantity, low_stock_threshold, unit, category_id, image, nutrition_info, diet_tags, is_eco_friendly, is_frozen, is_active) VALUES
(
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
    TRUE,
    FALSE,
    TRUE
);

-- 11. Miniket Rice Loose(S) (BRRI-28) - Rice & Grains
INSERT INTO products (name, brand, description, price, unit_size, stock_quantity, low_stock_threshold, unit, category_id, image, nutrition_info, diet_tags, is_eco_friendly, is_frozen, is_active) VALUES
(
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
    TRUE,
    FALSE,
    TRUE
);

-- 12. Fresh Instant Full Cream Milk Powder 1000gm - Dairy Products
INSERT INTO products (name, brand, description, price, unit_size, stock_quantity, low_stock_threshold, unit, category_id, image, nutrition_info, diet_tags, is_eco_friendly, is_frozen, is_active) VALUES
(
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
    FALSE,
    FALSE,
    TRUE
);

-- Verify products were added successfully
SELECT 'Products added successfully!' as status;
SELECT COUNT(*) as total_new_products FROM products WHERE name IN (
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
);

