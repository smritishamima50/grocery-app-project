-- Admin Orders Management Enhancement
-- This file enhances the orders system for admin management

-- 1. Add missing columns to orders table
ALTER TABLE orders 
ADD COLUMN subtotal DECIMAL(10,2) DEFAULT 0.00 AFTER total_amount,
ADD COLUMN discount_amount DECIMAL(10,2) DEFAULT 0.00 AFTER subtotal,
ADD COLUMN eco_friendly_delivery BOOLEAN DEFAULT FALSE AFTER packaging_option,
ADD COLUMN assigned_driver VARCHAR(100) NULL AFTER eco_friendly_delivery,
ADD COLUMN delivery_slot_date DATE NULL AFTER delivery_slot,
ADD COLUMN delivery_slot_start TIME NULL AFTER delivery_slot_date,
ADD COLUMN delivery_slot_end TIME NULL AFTER delivery_slot_start,
ADD COLUMN is_urgent BOOLEAN DEFAULT FALSE AFTER assigned_driver,
ADD COLUMN admin_notes TEXT NULL AFTER is_urgent;

-- 2. Update order status enum to match requirements
ALTER TABLE orders 
MODIFY COLUMN status ENUM('pending', 'confirmed', 'packed', 'out_for_delivery', 'delivered', 'cancelled') DEFAULT 'pending';

-- 3. Update payment method enum to match requirements
ALTER TABLE orders 
MODIFY COLUMN payment_method ENUM('bkash', 'cod', 'nagad', 'card', 'wallet') DEFAULT 'cod';

-- 4. Create order status history table for audit trail
CREATE TABLE order_status_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    old_status VARCHAR(50),
    new_status VARCHAR(50) NOT NULL,
    changed_by_admin_id INT,
    admin_name VARCHAR(100),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (changed_by_admin_id) REFERENCES users(id) ON DELETE SET NULL
);

-- 5. Create drivers table
CREATE TABLE drivers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(100),
    vehicle_type ENUM('bike', 'car', 'van') DEFAULT 'bike',
    license_number VARCHAR(50),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 6. Create delivery slots table
CREATE TABLE delivery_slots (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    max_orders INT DEFAULT 50,
    current_orders INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_slot (date, start_time, end_time)
);

-- 7. Add indexes for better performance (only if they don't exist)
CREATE INDEX IF NOT EXISTS idx_orders_assigned_driver ON orders(assigned_driver);
CREATE INDEX IF NOT EXISTS idx_orders_delivery_date ON orders(delivery_slot_date);
CREATE INDEX IF NOT EXISTS idx_orders_urgent ON orders(is_urgent);
CREATE INDEX IF NOT EXISTS idx_order_status_history_order ON order_status_history(order_id);
CREATE INDEX IF NOT EXISTS idx_order_status_history_admin ON order_status_history(changed_by_admin_id);
CREATE INDEX IF NOT EXISTS idx_drivers_active ON drivers(is_active);
CREATE INDEX IF NOT EXISTS idx_delivery_slots_date ON delivery_slots(date, is_active);

-- 8. Insert sample drivers
INSERT INTO drivers (name, phone, email, vehicle_type, license_number) VALUES
('Rahim Ahmed', '+8801712345678', 'rahim@example.com', 'bike', 'BIKE123456'),
('Karim Uddin', '+8801712345679', 'karim@example.com', 'car', 'CAR123456'),
('Salam Khan', '+8801712345680', 'salam@example.com', 'van', 'VAN123456'),
('Nurul Islam', '+8801712345681', 'nurul@example.com', 'bike', 'BIKE123457'),
('Mohammad Ali', '+8801712345682', 'ali@example.com', 'car', 'CAR123457');

-- 9. Insert sample delivery slots for next 7 days
INSERT INTO delivery_slots (date, start_time, end_time, max_orders) VALUES
(CURDATE(), '09:00:00', '12:00:00', 50),
(CURDATE(), '12:00:00', '15:00:00', 50),
(CURDATE(), '15:00:00', '18:00:00', 50),
(CURDATE(), '18:00:00', '21:00:00', 30),
(DATE_ADD(CURDATE(), INTERVAL 1 DAY), '09:00:00', '12:00:00', 50),
(DATE_ADD(CURDATE(), INTERVAL 1 DAY), '12:00:00', '15:00:00', 50),
(DATE_ADD(CURDATE(), INTERVAL 1 DAY), '15:00:00', '18:00:00', 50),
(DATE_ADD(CURDATE(), INTERVAL 1 DAY), '18:00:00', '21:00:00', 30),
(DATE_ADD(CURDATE(), INTERVAL 2 DAY), '09:00:00', '12:00:00', 50),
(DATE_ADD(CURDATE(), INTERVAL 2 DAY), '12:00:00', '15:00:00', 50),
(DATE_ADD(CURDATE(), INTERVAL 2 DAY), '15:00:00', '18:00:00', 50),
(DATE_ADD(CURDATE(), INTERVAL 2 DAY), '18:00:00', '21:00:00', 30),
(DATE_ADD(CURDATE(), INTERVAL 3 DAY), '09:00:00', '12:00:00', 50),
(DATE_ADD(CURDATE(), INTERVAL 3 DAY), '12:00:00', '15:00:00', 50),
(DATE_ADD(CURDATE(), INTERVAL 3 DAY), '15:00:00', '18:00:00', 50),
(DATE_ADD(CURDATE(), INTERVAL 3 DAY), '18:00:00', '21:00:00', 30),
(DATE_ADD(CURDATE(), INTERVAL 4 DAY), '09:00:00', '12:00:00', 50),
(DATE_ADD(CURDATE(), INTERVAL 4 DAY), '12:00:00', '15:00:00', 50),
(DATE_ADD(CURDATE(), INTERVAL 4 DAY), '15:00:00', '18:00:00', 50),
(DATE_ADD(CURDATE(), INTERVAL 4 DAY), '18:00:00', '21:00:00', 30),
(DATE_ADD(CURDATE(), INTERVAL 5 DAY), '09:00:00', '12:00:00', 50),
(DATE_ADD(CURDATE(), INTERVAL 5 DAY), '12:00:00', '15:00:00', 50),
(DATE_ADD(CURDATE(), INTERVAL 5 DAY), '15:00:00', '18:00:00', 50),
(DATE_ADD(CURDATE(), INTERVAL 5 DAY), '18:00:00', '21:00:00', 30),
(DATE_ADD(CURDATE(), INTERVAL 6 DAY), '09:00:00', '12:00:00', 50),
(DATE_ADD(CURDATE(), INTERVAL 6 DAY), '12:00:00', '15:00:00', 50),
(DATE_ADD(CURDATE(), INTERVAL 6 DAY), '15:00:00', '18:00:00', 50),
(DATE_ADD(CURDATE(), INTERVAL 6 DAY), '18:00:00', '21:00:00', 30);

-- 10. Update existing orders to have proper structure
UPDATE orders SET 
    subtotal = total_amount,
    discount_amount = 0.00,
    eco_friendly_delivery = FALSE,
    delivery_slot_date = CURDATE(),
    delivery_slot_start = '12:00:00',
    delivery_slot_end = '15:00:00'
WHERE subtotal IS NULL;

-- 11. Create order items with product name snapshots (for historical data)
ALTER TABLE order_items 
ADD COLUMN product_name_snapshot VARCHAR(255) NULL AFTER product_id,
ADD COLUMN product_image_snapshot VARCHAR(255) NULL AFTER product_name_snapshot;

-- 12. Update existing order items with product snapshots
UPDATE order_items oi 
JOIN products p ON oi.product_id = p.id 
SET oi.product_name_snapshot = p.name,
    oi.product_image_snapshot = p.image;

-- 13. Create order statistics view for admin dashboard
CREATE VIEW order_statistics AS
SELECT 
    COUNT(*) as total_orders,
    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_orders,
    COUNT(CASE WHEN status = 'confirmed' THEN 1 END) as confirmed_orders,
    COUNT(CASE WHEN status = 'packed' THEN 1 END) as packed_orders,
    COUNT(CASE WHEN status = 'out_for_delivery' THEN 1 END) as out_for_delivery_orders,
    COUNT(CASE WHEN status = 'delivered' THEN 1 END) as delivered_orders,
    COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled_orders,
    SUM(CASE WHEN status = 'delivered' THEN total_amount ELSE 0 END) as total_revenue,
    AVG(CASE WHEN status = 'delivered' THEN total_amount ELSE NULL END) as avg_order_value,
    COUNT(CASE WHEN is_urgent = TRUE THEN 1 END) as urgent_orders,
    COUNT(CASE WHEN eco_friendly_delivery = TRUE THEN 1 END) as eco_friendly_orders
FROM orders;
