-- Run this to check actual columns in menus table
SELECT column_name, data_type
FROM information_schema.columns
WHERE table_name = 'menus'
ORDER BY ordinal_position;

-- Check sample data
SELECT * FROM menus LIMIT 3;
