-- Restore departments that were deleted
-- This script will insert departments 1 and 2 back if they don't exist

INSERT INTO sys_departments (id, key, label, sort_order, is_active, is_default, created_at, updated_at)
VALUES
    (1, 'system', 'ระบบ', 1, true, true, NOW(), NOW()),
    (2, 'bplus', 'Bplus', 2, true, true, NOW(), NOW())
ON CONFLICT (id) DO UPDATE SET
    key = EXCLUDED.key,
    label = EXCLUDED.label,
    sort_order = EXCLUDED.sort_order,
    is_active = EXCLUDED.is_active,
    is_default = EXCLUDED.is_default,
    updated_at = NOW();

-- Reset sequence if needed
SELECT setval('sys_departments_id_seq', (SELECT MAX(id) FROM sys_departments));
