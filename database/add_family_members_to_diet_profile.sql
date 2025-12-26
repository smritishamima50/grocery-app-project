-- Migration: Add family_members column to user_diet_profiles table
-- This adds support for capturing approximate total family members in diet profile

-- Check if column already exists before adding
SET @dbname = DATABASE();
SET @tablename = "user_diet_profiles";
SET @columnname = "family_members";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (COLUMN_NAME = @columnname)
  ) > 0,
  "SELECT 'Column family_members already exists in user_diet_profiles' AS result;",
  CONCAT("ALTER TABLE ", @tablename, " ADD COLUMN ", @columnname, " INT DEFAULT NULL COMMENT 'Approximate total family members (2, 5, 10, 15, etc.)';")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Add index for better query performance if needed
-- CREATE INDEX idx_family_members ON user_diet_profiles(family_members);

SELECT 'Migration completed: family_members column added to user_diet_profiles table' AS result;

