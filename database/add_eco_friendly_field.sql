-- Add eco-friendly field to products table
ALTER TABLE products ADD COLUMN is_eco_friendly BOOLEAN DEFAULT FALSE AFTER unit;

-- Update some products to be eco-friendly (examples)
UPDATE products SET is_eco_friendly = TRUE WHERE name LIKE '%organic%' OR name LIKE '%fresh%' OR name LIKE '%local%' OR name LIKE '%natural%';

-- Add some sample stock quantities and units if they don't exist
UPDATE products SET stock_quantity = FLOOR(RAND() * 50) + 10 WHERE stock_quantity = 0;
UPDATE products SET unit = 'kg' WHERE unit IS NULL AND name LIKE '%banana%' OR name LIKE '%apple%' OR name LIKE '%orange%';
UPDATE products SET unit = 'liter' WHERE unit IS NULL AND name LIKE '%milk%' OR name LIKE '%juice%';
UPDATE products SET unit = 'dozen' WHERE unit IS NULL AND name LIKE '%egg%';
UPDATE products SET unit = 'pack' WHERE unit IS NULL AND name LIKE '%bread%' OR name LIKE '%cereal%';
UPDATE products SET unit = 'pcs' WHERE unit IS NULL AND unit IS NULL;
