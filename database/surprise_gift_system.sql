-- Surprise Gift System Database Schema
-- This file creates tables for the surprise gift system

-- Surprise gifts table (defines available surprise gifts)
CREATE TABLE surprise_gifts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    product_id INT NOT NULL,
    quantity INT DEFAULT 1,
    trigger_type ENUM('order_amount', 'order_count', 'random', 'special_occasion') DEFAULT 'random',
    trigger_value DECIMAL(10,2) DEFAULT 0.00, -- minimum order amount or order count
    probability_percentage INT DEFAULT 10, -- 1-100, chance of being selected
    is_active BOOLEAN DEFAULT TRUE,
    start_date DATE,
    end_date DATE,
    max_uses_per_user INT DEFAULT 1, -- how many times a user can get this gift
    max_total_uses INT DEFAULT NULL, -- total limit across all users
    current_uses INT DEFAULT 0, -- current usage count
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- User surprise gifts table (tracks which users received which gifts)
CREATE TABLE user_surprise_gifts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    order_id INT NOT NULL,
    surprise_gift_id INT NOT NULL,
    quantity INT DEFAULT 1,
    received_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (surprise_gift_id) REFERENCES surprise_gifts(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_gift_per_order (user_id, order_id, surprise_gift_id)
);

-- Add surprise gift tracking to orders table
ALTER TABLE orders ADD COLUMN has_surprise_gift BOOLEAN DEFAULT FALSE AFTER packaging_cost;
ALTER TABLE orders ADD COLUMN surprise_gift_message TEXT AFTER has_surprise_gift;

-- Indexes for better performance
CREATE INDEX idx_surprise_gifts_active ON surprise_gifts(is_active, start_date, end_date);
CREATE INDEX idx_surprise_gifts_trigger ON surprise_gifts(trigger_type, trigger_value);
CREATE INDEX idx_user_surprise_gifts_user ON user_surprise_gifts(user_id);
CREATE INDEX idx_user_surprise_gifts_order ON user_surprise_gifts(order_id);
