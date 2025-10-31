-- Fix menus without menu_group_id
UPDATE menus
SET menu_group_id = (SELECT id FROM menu_groups WHERE key = 'default' LIMIT 1)
WHERE menu_group_id IS NULL;

-- Check result
SELECT id, key, label, menu_group_id FROM menus ORDER BY id;
