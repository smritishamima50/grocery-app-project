-- Dummy Data for Grocery E-Commerce Database
-- This file inserts sample data into all tables

-- Insert users (customers and admins)
INSERT INTO users (email, phone, password_hash, role, first_name, last_name) VALUES
('admin@grocery.com', '+8801712345678', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'Admin', 'User'),
('customer1@grocery.com', '+8801712345679', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', 'John', 'Doe'),
('customer2@grocery.com', '+8801712345680', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', 'Jane', 'Smith'),
('customer3@grocery.com', '+8801712345681', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', 'Bob', 'Johnson');

-- Insert categories
INSERT INTO categories (name, description, image) VALUES
('Fruits & Vegetables', 'Fresh fruits and vegetables', 'https://picsum.photos/300/200?random=1'),
('Dairy & Eggs', 'Milk, cheese, eggs and dairy products', 'https://picsum.photos/300/200?random=2'),
('Meat & Poultry', 'Fresh meat and poultry products', 'https://picsum.photos/300/200?random=3'),
('Bakery', 'Bread, cakes and bakery items', 'https://picsum.photos/300/200?random=4'),
('Beverages', 'Soft drinks, juices and beverages', 'https://picsum.photos/300/200?random=5'),
('Snacks', 'Chips, cookies and snack items', 'https://picsum.photos/300/200?random=6'),
('Frozen Food', 'Frozen meals, vegetables and meat products', 'https://picsum.photos/300/200?random=23');

-- Insert products
INSERT INTO products (name, description, price, stock_quantity, unit, category_id, image, nutrition_info) VALUES
('Organic Apples', 'Fresh organic red apples', 150.00, 100, 'kg', 1, 'https://picsum.photos/300/200?random=7', 'Rich in fiber and vitamin C'),
('Banana', 'Yellow bananas', 50.00, 200, 'dozen', 1, 'https://picsum.photos/300/200?random=8', 'Good source of potassium'),
('Milk', 'Fresh cow milk', 80.00, 50, 'liter', 2, 'https://picsum.photos/300/200?random=9', 'Calcium rich'),
('Eggs', 'Farm fresh eggs', 120.00, 30, 'dozen', 2, 'https://picsum.photos/300/200?random=10', 'High in protein'),
('Chicken Breast', 'Fresh chicken breast', 250.00, 25, 'kg', 3, 'https://picsum.photos/300/200?random=11', 'Lean protein source'),
('White Bread', 'Fresh white bread loaf', 40.00, 20, 'pcs', 4, 'https://picsum.photos/300/200?random=12', 'Carbohydrate source'),
('Coca Cola', 'Carbonated soft drink', 35.00, 100, 'bottles', 5, 'https://picsum.photos/300/200?random=13', 'Refreshing beverage'),
('Potato Chips', 'Crispy potato chips', 25.00, 50, 'packs', 6, 'https://picsum.photos/300/200?random=14', 'Snack food'),
('Orange Juice', 'Fresh orange juice', 60.00, 30, 'bottles', 5, 'https://picsum.photos/300/200?random=15', 'Vitamin C rich'),
('Chocolate Cookies', 'Delicious chocolate cookies', 45.00, 40, 'packs', 6, 'https://picsum.photos/300/200?random=16', 'Sweet treat'),
('Tomatoes', 'Fresh red tomatoes', 80.00, 80, 'kg', 1, 'https://picsum.photos/300/200?random=17', 'Rich in lycopene'),
('Cheese', 'Cheddar cheese block', 300.00, 15, 'kg', 2, 'https://picsum.photos/300/200?random=18', 'Calcium and protein rich'),
('Beef Steak', 'Premium beef steak', 450.00, 20, 'kg', 3, 'https://picsum.photos/300/200?random=19', 'High protein'),
('Croissant', 'Butter croissant', 30.00, 25, 'pcs', 4, 'https://picsum.photos/300/200?random=20', 'Flaky pastry'),
('Green Tea', 'Organic green tea', 150.00, 10, 'packs', 5, 'https://picsum.photos/300/200?random=21', 'Antioxidant rich'),
('Mixed Nuts', 'Assorted nuts mix', 200.00, 15, 'packs', 6, 'https://picsum.photos/300/200?random=22', 'Healthy fats'),
('Frozen Chicken Nuggets', 'Breaded chicken nuggets', 180.00, 40, 'packs', 7, 'https://picsum.photos/300/200?random=24', 'Pre-cooked chicken nuggets'),
('Frozen French Fries', 'Crispy frozen fries', 120.00, 35, 'packs', 7, 'https://picsum.photos/300/200?random=25', 'Ready to cook fries'),
('Frozen Mixed Vegetables', 'Assorted frozen vegetables', 90.00, 50, 'packs', 7, 'https://picsum.photos/300/200?random=26', 'Mixed frozen veggies'),
('Frozen Ice Cream', 'Vanilla ice cream', 150.00, 30, 'tubs', 7, 'https://picsum.photos/300/200?random=27', 'Creamy vanilla ice cream'),
('Frozen Pizza', 'Margherita frozen pizza', 280.00, 25, 'pcs', 7, 'https://picsum.photos/300/200?random=28', 'Ready to bake pizza');

-- Insert user addresses
INSERT INTO user_addresses (user_id, address_type, address_line1, address_line2, city, state, zip_code, country, is_default) VALUES
(2, 'home', '123 Main Street', 'Apt 4B', 'Dhaka', 'Dhaka', '1205', 'Bangladesh', TRUE),
(2, 'office', '456 Business Ave', 'Floor 5', 'Dhaka', 'Dhaka', '1212', 'Bangladesh', FALSE),
(3, 'home', '789 Residential Road', NULL, 'Chittagong', 'Chittagong', '4000', 'Bangladesh', TRUE),
(4, 'home', '321 Village Lane', NULL, 'Khulna', 'Khulna', '9100', 'Bangladesh', TRUE);

-- Insert cart items
INSERT INTO cart_items (user_id, product_id, quantity) VALUES
(2, 1, 2),
(2, 3, 1),
(3, 5, 1),
(3, 7, 3),
(4, 2, 1),
(4, 9, 2);

-- Insert orders
INSERT INTO orders (user_id, total_amount, status, delivery_address_id, delivery_slot, payment_method, payment_status) VALUES
(2, 380.00, 'delivered', 1, '2023-10-15 10:00-12:00', 'cash_on_delivery', 'completed'),
(3, 785.00, 'shipped', 3, '2023-10-16 14:00-16:00', 'bkash', 'completed'),
(4, 160.00, 'packed', 4, '2023-10-17 09:00-11:00', 'card', 'completed');

-- Insert order items
INSERT INTO order_items (order_id, product_id, quantity, unit_price, total_price) VALUES
(1, 1, 2, 150.00, 300.00),
(1, 3, 1, 80.00, 80.00),
(2, 5, 1, 250.00, 250.00),
(2, 7, 3, 35.00, 105.00),
(2, 12, 1, 300.00, 300.00),
(2, 14, 2, 30.00, 60.00),
(3, 2, 1, 50.00, 50.00),
(3, 9, 2, 60.00, 120.00),
(3, 10, 1, 45.00, 45.00);

-- Insert coupons
INSERT INTO coupons (code, discount_type, discount_value, min_order_amount, expiry_date, usage_limit) VALUES
('WELCOME10', 'percentage', 10.00, 500.00, '2023-12-31', 100),
('FLAT50', 'flat', 50.00, 200.00, '2023-11-30', 50),
('SAVE20', 'percentage', 20.00, 1000.00, '2023-12-15', 25);

-- Insert wishlists
INSERT INTO wishlists (user_id, product_id) VALUES
(2, 6),
(2, 11),
(3, 13),
(3, 15),
(4, 4),
(4, 16);

-- Insert subscriptions
INSERT INTO subscriptions (user_id, frequency, product_ids, start_date, end_date, status) VALUES
(2, 'weekly', '[1,3,4]', '2023-10-01', '2023-12-31', 'active'),
(3, 'monthly', '[2,9,10]', '2023-10-01', '2024-01-31', 'active');

-- Insert payments
INSERT INTO payments (order_id, amount, payment_method, transaction_id, status) VALUES
(1, 380.00, 'cash_on_delivery', NULL, 'completed'),
(2, 785.00, 'bkash', 'BK123456789', 'completed'),
(3, 160.00, 'card', 'CARD987654321', 'completed');

-- Insert notifications
INSERT INTO notifications (user_id, title, message, type, is_read) VALUES
(2, 'Order Delivered', 'Your order #1 has been successfully delivered', 'order_update', TRUE),
(3, 'Order Shipped', 'Your order #2 has been shipped and is on the way', 'order_update', FALSE),
(4, 'Order Packed', 'Your order #3 has been packed and ready for delivery', 'order_update', FALSE),
(2, 'Special Offer', 'Get 20% off on your next order with code SAVE20', 'promotion', FALSE),
(3, 'Welcome Bonus', 'Welcome to our grocery store! Use WELCOME10 for 10% off', 'promotion', TRUE);

-- Insert delivery updates
INSERT INTO delivery_updates (order_id, status, message) VALUES
(1, 'Order Placed', 'Your order has been placed successfully'),
(1, 'Order Confirmed', 'Your order has been confirmed by our team'),
(1, 'Order Packed', 'Your order has been packed and ready for delivery'),
(1, 'Order Shipped', 'Your order has been shipped'),
(1, 'Order Delivered', 'Your order has been delivered successfully'),
(2, 'Order Placed', 'Your order has been placed successfully'),
(2, 'Order Confirmed', 'Your order has been confirmed by our team'),
(2, 'Order Packed', 'Your order has been packed and ready for delivery'),
(2, 'Order Shipped', 'Your order has been shipped'),
(3, 'Order Placed', 'Your order has been placed successfully'),
(3, 'Order Confirmed', 'Your order has been confirmed by our team'),
(3, 'Order Packed', 'Your order has been packed and ready for delivery');