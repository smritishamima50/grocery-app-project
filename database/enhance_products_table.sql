-- Enhance Products Table for Advanced Features
-- Adds missing columns for allergens, halal_certified, images array, and price history

-- Add allergens column (JSON array: ["peanut","lactose","gluten"])
ALTER TABLE products ADD COLUMN IF NOT EXISTS allergens JSON NULL AFTER diet_tags;

-- Add halal_certified column
ALTER TABLE products ADD COLUMN IF NOT EXISTS halal_certified BOOLEAN DEFAULT FALSE AFTER allergens;

-- Add images column (JSON array for multiple images)
ALTER TABLE products ADD COLUMN IF NOT EXISTS images JSON NULL AFTER image;

-- Add price_current column (for tracking current price separately)
ALTER TABLE products ADD COLUMN IF NOT EXISTS price_current DECIMAL(10,2) NULL AFTER price;

-- Update price_current to match price for existing records
UPDATE products SET price_current = price WHERE price_current IS NULL;

-- Create product_price_history table for price tracking
CREATE TABLE IF NOT EXISTS product_price_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    changed_by_admin_id INT NULL,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (changed_by_admin_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_product_price_history_product (product_id),
    INDEX idx_product_price_history_date (changed_at)
);

-- Create products_backup table if it doesn't exist (for backup functionality)
CREATE TABLE IF NOT EXISTS products_backup (
    id INT AUTO_INCREMENT PRIMARY KEY,
    original_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    brand VARCHAR(255),
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    price_current DECIMAL(10,2),
    unit_size VARCHAR(50),
    stock_quantity INT DEFAULT 0,
    low_stock_threshold INT DEFAULT 10,
    unit VARCHAR(50),
    category_id INT,
    image VARCHAR(255),
    images JSON,
    nutrition_info TEXT,
    diet_tags JSON,
    allergens JSON,
    halal_certified BOOLEAN DEFAULT FALSE,
    is_eco_friendly BOOLEAN DEFAULT FALSE,
    is_frozen BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    backed_up_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    backed_up_by_admin_id INT NULL,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    FOREIGN KEY (backed_up_by_admin_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_products_backup_original (original_id),
    INDEX idx_products_backup_date (backed_up_at)
);

