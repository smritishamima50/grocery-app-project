-- Add New Categories and Products for Grocery E-Commerce (FIXED VERSION)
-- This script adds the requested categories and products with proper foreign key handling

-- First, add new categories and get their IDs
INSERT INTO categories (name, description, image) VALUES
('Rice & Grains', 'Various types of rice and grain products', 'https://picsum.photos/300/200?random=30'),
('Cooking', 'Cooking oils, spices, and cooking essentials', 'https://picsum.photos/300/200?random=31'),
('Drinks', 'Tea, coffee, and other beverages', 'https://picsum.photos/300/200?random=32'),
('Baking Needs', 'Flour, baking powder, and baking essentials', 'https://picsum.photos/300/200?random=33'),
('Home Cleaning', 'Cleaning products and household essentials', 'https://picsum.photos/300/200?random=34');

-- Now add products using the correct category IDs
-- We'll use subqueries to get the category IDs by name

-- Add Rice & Grains products
INSERT INTO products (name, description, price, stock_quantity, unit, category_id, image, nutrition_info) VALUES
('Basmati Rice', 'Premium long grain basmati rice', 120.00, 50, 'kg', (SELECT id FROM categories WHERE name = 'Rice & Grains'), 'https://picsum.photos/300/200?random=35', 'High in carbohydrates, gluten-free'),
('Jasmine Rice', 'Fragrant jasmine rice', 100.00, 40, 'kg', (SELECT id FROM categories WHERE name = 'Rice & Grains'), 'https://picsum.photos/300/200?random=36', 'Aromatic rice, good source of energy'),
('Brown Rice', 'Healthy brown rice', 90.00, 30, 'kg', (SELECT id FROM categories WHERE name = 'Rice & Grains'), 'https://picsum.photos/300/200?random=37', 'High in fiber and nutrients'),
('Red Rice', 'Nutritious red rice', 110.00, 25, 'kg', (SELECT id FROM categories WHERE name = 'Rice & Grains'), 'https://picsum.photos/300/200?random=38', 'Rich in antioxidants and minerals'),
('Quinoa', 'Superfood quinoa grains', 200.00, 20, 'kg', (SELECT id FROM categories WHERE name = 'Rice & Grains'), 'https://picsum.photos/300/200?random=39', 'Complete protein source'),
('Barley', 'Whole grain barley', 80.00, 35, 'kg', (SELECT id FROM categories WHERE name = 'Rice & Grains'), 'https://picsum.photos/300/200?random=40', 'High in fiber and vitamins');

-- Add Cooking products (Oil and Masala)
INSERT INTO products (name, description, price, stock_quantity, unit, category_id, image, nutrition_info) VALUES
('Sunflower Oil', 'Pure sunflower cooking oil', 150.00, 40, 'liter', (SELECT id FROM categories WHERE name = 'Cooking'), 'https://picsum.photos/300/200?random=41', 'Rich in vitamin E and healthy fats'),
('Olive Oil', 'Extra virgin olive oil', 300.00, 25, 'liter', (SELECT id FROM categories WHERE name = 'Cooking'), 'https://picsum.photos/300/200?random=42', 'Heart-healthy monounsaturated fats'),
('Coconut Oil', 'Pure coconut cooking oil', 180.00, 30, 'liter', (SELECT id FROM categories WHERE name = 'Cooking'), 'https://picsum.photos/300/200?random=43', 'Medium-chain triglycerides'),
('Mustard Oil', 'Traditional mustard oil', 120.00, 35, 'liter', (SELECT id FROM categories WHERE name = 'Cooking'), 'https://picsum.photos/300/200?random=44', 'Rich in omega-3 fatty acids'),
('Garam Masala', 'Traditional spice blend', 80.00, 50, 'packs', (SELECT id FROM categories WHERE name = 'Cooking'), 'https://picsum.photos/300/200?random=45', 'Aromatic spice mixture'),
('Turmeric Powder', 'Pure turmeric powder', 60.00, 60, 'packs', (SELECT id FROM categories WHERE name = 'Cooking'), 'https://picsum.photos/300/200?random=46', 'Anti-inflammatory properties'),
('Cumin Powder', 'Ground cumin spice', 70.00, 45, 'packs', (SELECT id FROM categories WHERE name = 'Cooking'), 'https://picsum.photos/300/200?random=47', 'Digestive health benefits'),
('Coriander Powder', 'Ground coriander spice', 65.00, 50, 'packs', (SELECT id FROM categories WHERE name = 'Cooking'), 'https://picsum.photos/300/200?random=48', 'Rich in antioxidants'),
('Red Chili Powder', 'Spicy red chili powder', 55.00, 40, 'packs', (SELECT id FROM categories WHERE name = 'Cooking'), 'https://picsum.photos/300/200?random=49', 'Contains capsaicin'),
('Cardamom', 'Whole green cardamom pods', 200.00, 30, 'packs', (SELECT id FROM categories WHERE name = 'Cooking'), 'https://picsum.photos/300/200?random=50', 'Aromatic spice with health benefits');

-- Add Drinks products (Tea and Coffee)
INSERT INTO products (name, description, price, stock_quantity, unit, category_id, image, nutrition_info) VALUES
('Green Tea', 'Premium green tea leaves', 120.00, 40, 'packs', (SELECT id FROM categories WHERE name = 'Drinks'), 'https://picsum.photos/300/200?random=51', 'Rich in antioxidants and catechins'),
('Black Tea', 'Classic black tea', 80.00, 50, 'packs', (SELECT id FROM categories WHERE name = 'Drinks'), 'https://picsum.photos/300/200?random=52', 'Natural caffeine source'),
('White Tea', 'Delicate white tea', 150.00, 25, 'packs', (SELECT id FROM categories WHERE name = 'Drinks'), 'https://picsum.photos/300/200?random=53', 'Minimal processing, high antioxidants'),
('Oolong Tea', 'Traditional oolong tea', 130.00, 30, 'packs', (SELECT id FROM categories WHERE name = 'Drinks'), 'https://picsum.photos/300/200?random=54', 'Partially fermented tea'),
('Coffee Beans', 'Premium arabica coffee beans', 250.00, 35, 'kg', (SELECT id FROM categories WHERE name = 'Drinks'), 'https://picsum.photos/300/200?random=55', 'Rich in caffeine and antioxidants'),
('Instant Coffee', 'Quick instant coffee', 180.00, 45, 'packs', (SELECT id FROM categories WHERE name = 'Drinks'), 'https://picsum.photos/300/200?random=56', 'Convenient coffee solution'),
('Decaf Coffee', 'Decaffeinated coffee', 200.00, 20, 'packs', (SELECT id FROM categories WHERE name = 'Drinks'), 'https://picsum.photos/300/200?random=57', 'Coffee without caffeine'),
('Herbal Tea', 'Chamomile herbal tea', 90.00, 40, 'packs', (SELECT id FROM categories WHERE name = 'Drinks'), 'https://picsum.photos/300/200?random=58', 'Caffeine-free, calming properties');

-- Add Baking Needs products (Maida and Atta)
INSERT INTO products (name, description, price, stock_quantity, unit, category_id, image, nutrition_info) VALUES
('All Purpose Flour (Maida)', 'Fine white flour for baking', 60.00, 50, 'kg', (SELECT id FROM categories WHERE name = 'Baking Needs'), 'https://picsum.photos/300/200?random=59', 'Refined wheat flour'),
('Whole Wheat Flour (Atta)', 'Nutritious whole wheat flour', 70.00, 45, 'kg', (SELECT id FROM categories WHERE name = 'Baking Needs'), 'https://picsum.photos/300/200?random=60', 'High in fiber and nutrients'),
('Baking Powder', 'Leavening agent for baking', 40.00, 60, 'packs', (SELECT id FROM categories WHERE name = 'Baking Needs'), 'https://picsum.photos/300/200?random=61', 'Chemical leavening agent'),
('Baking Soda', 'Sodium bicarbonate for baking', 35.00, 55, 'packs', (SELECT id FROM categories WHERE name = 'Baking Needs'), 'https://picsum.photos/300/200?random=62', 'Natural leavening agent'),
('Yeast', 'Active dry yeast', 50.00, 40, 'packs', (SELECT id FROM categories WHERE name = 'Baking Needs'), 'https://picsum.photos/300/200?random=63', 'Biological leavening agent'),
('Corn Flour', 'Fine corn flour', 80.00, 30, 'kg', (SELECT id FROM categories WHERE name = 'Baking Needs'), 'https://picsum.photos/300/200?random=64', 'Gluten-free flour option'),
('Rice Flour', 'Fine rice flour', 90.00, 25, 'kg', (SELECT id FROM categories WHERE name = 'Baking Needs'), 'https://picsum.photos/300/200?random=65', 'Gluten-free alternative'),
('Sugar', 'Granulated white sugar', 55.00, 40, 'kg', (SELECT id FROM categories WHERE name = 'Baking Needs'), 'https://picsum.photos/300/200?random=66', 'Sweetening agent');

-- Add Snacks products (Noodles and Pasta) - Note: These will be added to existing Snacks category
INSERT INTO products (name, description, price, stock_quantity, unit, category_id, image, nutrition_info) VALUES
('Instant Noodles', 'Quick cooking instant noodles', 25.00, 100, 'packs', (SELECT id FROM categories WHERE name = 'Snacks'), 'https://picsum.photos/300/200?random=67', 'Quick meal option'),
('Ramen Noodles', 'Japanese style ramen noodles', 35.00, 80, 'packs', (SELECT id FROM categories WHERE name = 'Snacks'), 'https://picsum.photos/300/200?random=68', 'Traditional ramen noodles'),
('Spaghetti Pasta', 'Italian spaghetti pasta', 45.00, 60, 'packs', (SELECT id FROM categories WHERE name = 'Snacks'), 'https://picsum.photos/300/200?random=69', 'Durum wheat pasta'),
('Penne Pasta', 'Tube-shaped penne pasta', 40.00, 55, 'packs', (SELECT id FROM categories WHERE name = 'Snacks'), 'https://picsum.photos/300/200?random=70', 'Versatile pasta shape'),
('Macaroni Pasta', 'Elbow macaroni pasta', 35.00, 70, 'packs', (SELECT id FROM categories WHERE name = 'Snacks'), 'https://picsum.photos/300/200?random=71', 'Classic macaroni shape'),
('Fettuccine Pasta', 'Flat ribbon pasta', 50.00, 40, 'packs', (SELECT id FROM categories WHERE name = 'Snacks'), 'https://picsum.photos/300/200?random=72', 'Wide flat pasta'),
('Lasagna Sheets', 'Flat lasagna pasta sheets', 55.00, 30, 'packs', (SELECT id FROM categories WHERE name = 'Snacks'), 'https://picsum.photos/300/200?random=73', 'For making lasagna'),
('Rice Noodles', 'Thin rice noodles', 30.00, 50, 'packs', (SELECT id FROM categories WHERE name = 'Snacks'), 'https://picsum.photos/300/200?random=74', 'Gluten-free noodle option');

-- Add Home Cleaning products
INSERT INTO products (name, description, price, stock_quantity, unit, category_id, image, nutrition_info) VALUES
('Detergent Powder', 'Heavy duty laundry detergent', 120.00, 40, 'kg', (SELECT id FROM categories WHERE name = 'Home Cleaning'), 'https://picsum.photos/300/200?random=75', 'Effective cleaning power'),
('Liquid Detergent', 'Concentrated liquid detergent', 150.00, 35, 'liter', (SELECT id FROM categories WHERE name = 'Home Cleaning'), 'https://picsum.photos/300/200?random=76', 'Easy to use liquid form'),
('Air Freshener', 'Room air freshener spray', 80.00, 50, 'bottles', (SELECT id FROM categories WHERE name = 'Home Cleaning'), 'https://picsum.photos/300/200?random=77', 'Eliminates odors'),
('Dish Cleaner', 'Dishwashing liquid', 60.00, 60, 'bottles', (SELECT id FROM categories WHERE name = 'Home Cleaning'), 'https://picsum.photos/300/200?random=78', 'Gentle on hands'),
('Glass Cleaner', 'Streak-free glass cleaner', 70.00, 45, 'bottles', (SELECT id FROM categories WHERE name = 'Home Cleaning'), 'https://picsum.photos/300/200?random=79', 'Crystal clear results'),
('Floor Cleaner', 'Multi-surface floor cleaner', 90.00, 40, 'bottles', (SELECT id FROM categories WHERE name = 'Home Cleaning'), 'https://picsum.photos/300/200?random=80', 'Safe for all floors'),
('Toilet Cleaner', 'Bathroom toilet cleaner', 65.00, 50, 'bottles', (SELECT id FROM categories WHERE name = 'Home Cleaning'), 'https://picsum.photos/300/200?random=81', 'Powerful cleaning action'),
('All Purpose Cleaner', 'Versatile cleaning solution', 75.00, 45, 'bottles', (SELECT id FROM categories WHERE name = 'Home Cleaning'), 'https://picsum.photos/300/200?random=82', 'Multi-surface cleaning'),
('Fabric Softener', 'Clothes fabric softener', 100.00, 35, 'bottles', (SELECT id FROM categories WHERE name = 'Home Cleaning'), 'https://picsum.photos/300/200?random=83', 'Softens and freshens clothes'),
('Bleach', 'Chlorine bleach', 55.00, 40, 'bottles', (SELECT id FROM categories WHERE name = 'Home Cleaning'), 'https://picsum.photos/300/200?random=84', 'Whitening and disinfecting');

-- Update existing categories to better organize them
UPDATE categories SET name = 'Beverages & Drinks' WHERE name = 'Beverages';
UPDATE categories SET name = 'Snacks & Pasta' WHERE name = 'Snacks';
