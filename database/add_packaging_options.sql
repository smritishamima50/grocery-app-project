-- Add packaging options to orders table
ALTER TABLE orders ADD COLUMN packaging_option ENUM('standard', 'eco_friendly', 'reusable_bag') DEFAULT 'standard' AFTER payment_method;

-- Add packaging cost for reusable bag option
ALTER TABLE orders ADD COLUMN packaging_cost DECIMAL(10,2) DEFAULT 0.00 AFTER packaging_option;
