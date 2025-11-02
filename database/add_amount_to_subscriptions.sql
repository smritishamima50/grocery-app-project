-- Add amount column to subscriptions table
ALTER TABLE subscriptions 
ADD COLUMN amount DECIMAL(10,2) DEFAULT 0 AFTER product_ids;
