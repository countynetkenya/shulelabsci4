-- Script: Delete studenttranscriptreport permissions and related mappings
-- Purpose: Safely remove permissions named like '%studenttranscriptreport%'
-- Usage: Review SELECT output, then allow DELETE statements to run within the same transaction.
-- Rollback Instructions: If the DELETE statements should not persist, execute ROLLBACK; before COMMIT.

START TRANSACTION;

SET @target_pattern := '%studenttranscriptreport%';
SET @db_name := DATABASE();

-- Step 1: Review affected permission records
SELECT *
FROM permissions
WHERE name LIKE @target_pattern;

-- Step 2: Delete role-to-permission relationships referencing the target permissions (skip if table missing)
SET @sql := IF(
    EXISTS (
        SELECT 1
        FROM information_schema.tables
        WHERE table_schema = @db_name AND table_name = 'role_permission'
    ),
    CONCAT(
        'DELETE rp FROM role_permission AS rp ',
        'INNER JOIN permissions AS p ON p.permissionID = rp.permission_id ',
        'WHERE p.name LIKE ', QUOTE(@target_pattern), ';'
    ),
    'SELECT "Skipped role_permission cleanup (table not found)" AS info;'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Step 3: Delete user-to-permission relationships referencing the target permissions
DELETE up
FROM user_permission AS up
INNER JOIN permissions AS p ON p.permissionID = up.permission_id
WHERE p.name LIKE @target_pattern;

-- Step 4: Delete generic permission relationships referencing the target permissions (skip if table missing)
SET @sql := IF(
    EXISTS (
        SELECT 1
        FROM information_schema.tables
        WHERE table_schema = @db_name AND table_name = 'permission_relationships'
    ),
    CONCAT(
        'DELETE pr FROM permission_relationships AS pr ',
        'INNER JOIN permissions AS p ON p.permissionID = pr.permission_id ',
        'WHERE p.name LIKE ', QUOTE(@target_pattern), ';'
    ),
    'SELECT "Skipped permission_relationships cleanup (table not found)" AS info;'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Step 5: Remove the target permissions themselves
DELETE FROM permissions
WHERE name LIKE @target_pattern;

-- Step 6: Verify that no matching permissions remain
SELECT *
FROM permissions
WHERE name LIKE @target_pattern;

COMMIT;
