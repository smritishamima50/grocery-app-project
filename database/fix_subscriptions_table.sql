-- Fix/Create Subscriptions Table
-- This will create or alter the subscriptions table with all necessary columns

-- Drop the table if it exists to recreate it properly
DROP TABLE IF EXISTS subscriptions;

-- Create subscriptions table with all required columns
CREATE TABLE subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    frequency ENUM('weekly', 'bi_weekly', 'monthly') NOT NULL,
    payment_method ENUM('pre_paid', 'cash_on_delivery') DEFAULT 'cash_on_delivery',
    delivery_address_id INT,
    delivery_slot_preference VARCHAR(100),
    product_ids JSON,
    next_delivery_date DATE,
    last_order_date DATE,
    start_date DATE NOT NULL,
    end_date DATE,
    status ENUM('active', 'paused', 'cancelled') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (delivery_address_id) REFERENCES user_addresses(id) ON DELETE SET NULL
);

-- Add indexes for better performance
CREATE INDEX idx_subscriptions_user ON subscriptions(user_id);
CREATE INDEX idx_subscriptions_status ON subscriptions(status);
CREATE INDEX idx_subscriptions_next_delivery ON subscriptions(next_delivery_date);
