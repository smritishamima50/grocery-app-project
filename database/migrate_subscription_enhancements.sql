-- Migration: Enhance Subscriptions for Bi-Weekly and Payment Methods
-- This migration adds bi-weekly frequency and payment method support to subscriptions

-- Alter subscriptions table to add bi-weekly frequency
ALTER TABLE subscriptions 
MODIFY COLUMN frequency ENUM('weekly', 'bi_weekly', 'monthly') NOT NULL;

-- Add payment method column to subscriptions
ALTER TABLE subscriptions 
ADD COLUMN payment_method ENUM('pre_paid', 'cash_on_delivery') DEFAULT 'cash_on_delivery' AFTER frequency;

-- Add delivery address to subscriptions
ALTER TABLE subscriptions 
ADD COLUMN delivery_address_id INT AFTER payment_method;

-- Add delivery slot preference to subscriptions
ALTER TABLE subscriptions 
ADD COLUMN delivery_slot_preference VARCHAR(100) AFTER delivery_address_id;

-- Add next delivery date to subscriptions
ALTER TABLE subscriptions 
ADD COLUMN next_delivery_date DATE AFTER delivery_slot_preference;

-- Add last order date to subscriptions
ALTER TABLE subscriptions 
ADD COLUMN last_order_date DATE AFTER next_delivery_date;

-- Add updated at timestamp
ALTER TABLE subscriptions 
ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at;

-- Add foreign key constraint for delivery address
ALTER TABLE subscriptions 
ADD CONSTRAINT fk_subscription_address 
FOREIGN KEY (delivery_address_id) REFERENCES user_addresses(id) ON DELETE SET NULL;

-- Add indexes for better performance
CREATE INDEX idx_subscriptions_user ON subscriptions(user_id);
CREATE INDEX idx_subscriptions_status ON subscriptions(status);
CREATE INDEX idx_subscriptions_next_delivery ON subscriptions(next_delivery_date);
