-- Complete Fix for Subscriptions Table
-- This will recreate the table with proper JSON handling

-- Drop the table if it exists
DROP TABLE IF EXISTS subscriptions;

-- Create subscriptions table with all required columns
CREATE TABLE subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    frequency ENUM('weekly', 'bi_weekly', 'monthly') NOT NULL,
    payment_method ENUM('pre_paid', 'cash_on_delivery') DEFAULT 'cash_on_delivery',
    delivery_address_id INT,
    delivery_slot_preference VARCHAR(100),
    product_ids JSON, -- JSON array of product IDs: [1, 2, 3]
    amount DECIMAL(10,2) DEFAULT 0, -- Subscription amount (200, 500, 1000)
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

-- Insert test subscription with correct JSON format
INSERT INTO subscriptions (user_id, frequency, amount, payment_method, status, start_date, next_delivery_date, product_ids)
VALUES (
    1,                              -- user_id
    'monthly',                      -- frequency
    1000,                          -- amount (৳1000)
    'cash_on_delivery',            -- payment method
    'active',                       -- status
    CURDATE(),                     -- start_date
    DATE_ADD(CURDATE(), INTERVAL 1 MONTH), -- next_delivery_date
    '[]'                           -- product_ids as empty JSON array
);

-- Add more test subscriptions
INSERT INTO subscriptions (user_id, frequency, amount, payment_method, status, start_date, next_delivery_date, product_ids)
VALUES (
    1,                              -- user_id
    'weekly',                       -- frequency
    200,                           -- amount (৳200)
    'cash_on_delivery',            -- payment method
    'active',                       -- status
    CURDATE(),                     -- start_date
    DATE_ADD(CURDATE(), INTERVAL 1 WEEK), -- next_delivery_date
    '[]'                           -- product_ids as empty JSON array
),
(
    1,                              -- user_id
    'bi_weekly',                    -- frequency
    500,                           -- amount (৳500)
    'pre_paid',                    -- payment method
    'active',                       -- status
    CURDATE(),                     -- start_date
    DATE_ADD(CURDATE(), INTERVAL 2 WEEK), -- next_delivery_date
    '[]'                           -- product_ids as empty JSON array
);

