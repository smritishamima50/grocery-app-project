-- Migration: Create family_member_profiles table
-- This table stores individual diet profiles for each family member

CREATE TABLE IF NOT EXISTS family_member_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    member_type ENUM('child', 'teenager', 'adolescent', 'adult') NOT NULL DEFAULT 'adult',
    member_count INT NOT NULL DEFAULT 1 COMMENT 'Number of family members of this type',
    diet_goal VARCHAR(50) DEFAULT 'general',
    calorie_target INT DEFAULT NULL,
    current_weight DECIMAL(5,2) DEFAULT NULL,
    target_weight DECIMAL(5,2) DEFAULT NULL,
    height DECIMAL(5,2) DEFAULT NULL,
    age INT DEFAULT NULL,
    activity_level VARCHAR(50) DEFAULT 'moderately_active',
    bmi DECIMAL(4,1) DEFAULT NULL,
    dietary_preferences JSON DEFAULT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add comment to table
ALTER TABLE family_member_profiles COMMENT = 'Stores individual diet profiles for each family member type';

SELECT 'Migration completed: family_member_profiles table created' AS result;

