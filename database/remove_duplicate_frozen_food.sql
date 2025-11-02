-- Remove duplicate Frozen Food categories
-- This script removes extra Frozen Food categories and keeps only one

DELETE FROM categories 
WHERE name = 'Frozen Food' 
AND id NOT IN (
    SELECT MIN(id) FROM (
        SELECT id FROM categories WHERE name = 'Frozen Food'
    ) AS temp
);
