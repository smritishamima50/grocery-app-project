-- Update user_surprise_gifts table to allow NULL order_id for cart selections
-- This allows users to select surprise gifts before placing an order
-- The order_id will be updated when the order is created

-- Modify order_id column to allow NULL
ALTER TABLE user_surprise_gifts MODIFY COLUMN order_id INT NULL;

-- Update the unique constraint to allow multiple NULL order_id entries per user
-- Remove the old unique constraint if it exists
ALTER TABLE user_surprise_gifts DROP INDEX IF EXISTS unique_user_gift_per_order;

-- Add new unique constraint that allows NULL order_id (MySQL treats NULL as distinct)
-- This allows one gift selection per user before order creation
ALTER TABLE user_surprise_gifts 
ADD UNIQUE KEY unique_user_gift_cart (user_id, surprise_gift_id, order_id);

-- Add index for faster lookups of cart selections (NULL order_id)
CREATE INDEX idx_user_surprise_gifts_cart ON user_surprise_gifts(user_id, order_id);

-- Note: The foreign key constraint on order_id will need to be handled carefully
-- If your MySQL version supports it, you can modify the foreign key to allow NULL:
-- ALTER TABLE user_surprise_gifts DROP FOREIGN KEY IF EXISTS user_surprise_gifts_ibfk_2;
-- ALTER TABLE user_surprise_gifts 
-- ADD CONSTRAINT user_surprise_gifts_ibfk_2 
-- FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE;

