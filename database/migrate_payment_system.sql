-- Payment System Migration
-- Run these statements to align the orders table and create payment_transactions

-- 1) Update orders table payment fields and add transaction/total
ALTER TABLE orders
    MODIFY COLUMN payment_method ENUM('bkash','nagad','card','cod') DEFAULT 'cod',
    MODIFY COLUMN payment_status ENUM('unpaid','pending','paid','failed','refunded') DEFAULT 'unpaid';

-- Add columns if they do not already exist (MySQL 8+ safe pattern)
SET @col_exists := (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'orders' AND COLUMN_NAME = 'transaction_id'
);
SET @qry := IF(@col_exists = 0, 'ALTER TABLE orders ADD COLUMN transaction_id TEXT NULL AFTER payment_status;', 'DO 0');
PREPARE stmt FROM @qry; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists := (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'orders' AND COLUMN_NAME = 'total_payable'
);
SET @qry := IF(@col_exists = 0, 'ALTER TABLE orders ADD COLUMN total_payable DECIMAL(10,2) NULL AFTER total_amount;', 'DO 0');
PREPARE stmt FROM @qry; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 2) Create payment_transactions table
CREATE TABLE IF NOT EXISTS payment_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    method ENUM('bkash','nagad','card') NOT NULL,
    status ENUM('initiated','pending_customer_action','success','failed') NOT NULL DEFAULT 'initiated',
    gateway_reference TEXT,
    amount DECIMAL(10,2) NOT NULL,
    raw_response JSON NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);


