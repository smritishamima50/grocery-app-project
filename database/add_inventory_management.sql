-- Add Inventory Management Support to Products Table
-- This migration adds columns needed for comprehensive inventory management

-- Add low stock threshold column
ALTER TABLE products ADD COLUMN low_stock_threshold INT DEFAULT 10 AFTER stock_quantity;

-- Add restock ETA column (datetime for when restocking is expected)
ALTER TABLE products ADD COLUMN restock_eta DATETIME NULL AFTER low_stock_threshold;

-- Add is_frozen column for frozen products
ALTER TABLE products ADD COLUMN is_frozen BOOLEAN DEFAULT FALSE AFTER restock_eta;

-- Add is_active column to enable/disable products
ALTER TABLE products ADD COLUMN is_active BOOLEAN DEFAULT TRUE AFTER is_frozen;

-- Update existing products with random low stock thresholds (5-20)
UPDATE products SET low_stock_threshold = FLOOR(RAND() * 16) + 5 WHERE low_stock_threshold IS NULL;

-- Set some products as frozen (products with 'frozen' in name)
UPDATE products SET is_frozen = TRUE WHERE name LIKE '%frozen%' OR name LIKE '%Frozen%';

-- Set some products as eco-friendly (products with organic, fresh, local, natural in name)
UPDATE products SET is_eco_friendly = TRUE WHERE name LIKE '%organic%' OR name LIKE '%fresh%' OR name LIKE '%local%' OR name LIKE '%natural%';

-- Set random restock dates for some products (next 1-14 days)
UPDATE products SET restock_eta = DATE_ADD(NOW(), INTERVAL FLOOR(RAND() * 14) + 1 DAY) 
WHERE stock_quantity < low_stock_threshold AND restock_eta IS NULL;

-- Add some sample restock dates for low stock items
UPDATE products SET restock_eta = DATE_ADD(NOW(), INTERVAL FLOOR(RAND() * 7) + 1 DAY) 
WHERE stock_quantity <= low_stock_threshold AND restock_eta IS NULL;

-- Create index for better performance on inventory queries
CREATE INDEX idx_products_stock_status ON products(stock_quantity, low_stock_threshold, is_active);
CREATE INDEX idx_products_restock ON products(restock_eta);
CREATE INDEX idx_products_frozen ON products(is_frozen);
CREATE INDEX idx_products_eco_friendly ON products(is_eco_friendly);
