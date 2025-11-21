# Multi-Table Login Transition

## Overview

The CI4 authentication system supports automatic migration of users from legacy CI3 tables during the signin process. This allows for a seamless transition from CI3 to CI4 without requiring a one-time bulk migration of all users.

## How It Works

### Authentication Flow

1. **User submits signin credentials** (username and password)
2. **CI4 checks ci4_users table** for the username
3. **If user NOT found in ci4_users:**
   - CI4 searches legacy CI3 tables in order:
     - `systemadmin`
     - `user`
     - `teacher`
     - `student`
     - `parents`
4. **If user found in CI3 table:**
   - User data is automatically copied to `ci4_users` table
   - Role is assigned in `ci4_user_roles` based on CI3 usertype
   - Migration is logged in audit trail via `AuditService`
   - Authentication continues with the newly migrated user
5. **If user NOT found in any table:**
   - Display "Unknown username" error
6. **Password verification proceeds** as normal after user is found/migrated

### Automatic Backfill Process

When a user is found in a CI3 table but not in ci4_users:

**Data Migrated:**
- Username
- Email
- Password hash (CI3-compatible SHA-512 hash is preserved)
- Full name
- Photo
- School IDs (comma-separated)
- Active status
- Created/updated timestamps

**Additional Fields Set:**
- `ci3_user_id`: Original user ID from CI3 table
- `ci3_user_table`: Name of the CI3 table the user was migrated from
- Role assignment based on CI3 usertypeID

**Audit Logging:**

Every user migration is logged using `AuditService` with:
- Event type: `user_migrated_from_ci3`
- Event key: `user.migrated.{new_ci4_user_id}`
- After state: Contains new user ID, CI3 table name, CI3 user ID, username
- Metadata: Migration timestamp and source (`automatic_signin`)

Example audit log entry:
```json
{
  "event_type": "user_migrated_from_ci3",
  "event_key": "user.migrated.123",
  "actor_id": "system",
  "after_state": {
    "ci4_user_id": 123,
    "ci3_user_table": "teacher",
    "ci3_user_id": 456,
    "username": "john.teacher"
  },
  "metadata": {
    "migration_timestamp": "2025-11-19 10:30:45",
    "migration_source": "automatic_signin"
  }
}
```

## CI3 Table Mappings

| CI3 Table     | ID Field      | Default Role      | CI3 usertypeID |
|---------------|---------------|-------------------|----------------|
| systemadmin   | systemadminID | Super Admin       | 0              |
| user          | userID        | Admin             | 1              |
| teacher       | teacherID     | Teacher           | 2              |
| student       | studentID     | Student           | 3              |
| parents       | parentsID     | Parent            | 4              |

## Implementation Details

### UserMigrationService

Location: `ci4/app/Services/UserMigrationService.php`

**Key Methods:**

- `findAndMigrateUser(string $username)`: Main entry point for migration
  - Searches all CI3 tables for username
  - Calls `migrateUser()` when found
  - Returns migrated user object or null

- `migrateUser(object $ci3User, string $tableName, array $config)`: Handles the actual migration
  - Inserts into `ci4_users`
  - Assigns role in `ci4_user_roles`
  - Logs migration via `AuditService`
  - Returns newly created user object

### Auth Controller Integration

Location: `ci4/app/Controllers/Auth.php`

**Modified Flow:**

```php
// Find user by username in ci4_users
$user = $this->userModel->findByUsernameAnyStatus($username);

// If not found, check CI3 tables and migrate
if (!$user) {
    log_message('info', 'User not found in ci4_users, checking CI3 tables');
    $user = $this->userMigrationService->findAndMigrateUser($username);
    
    if (!$user) {
        // User truly doesn't exist
        return 'Unknown username' error;
    }
    
    log_message('info', 'User migrated from CI3, proceeding with authentication');
}

// Continue with password verification...
```

## Password Compatibility

The system maintains **full CI3 password compatibility**:

- CI3 password hashes use SHA-512 with encryption key
- These hashes are **preserved** during migration
- `HashCompat` library ensures passwords work identically in CI4
- No password reset required after migration

## Advantages of This Approach

1. **Zero Downtime**: Users can continue working during migration period
2. **Self-Service**: Users migrate themselves by simply signing in
3. **No Coordination Required**: No need to schedule bulk migration
4. **Audit Trail**: Every migration is logged for compliance
5. **Gradual Migration**: Only active users are migrated
6. **Fallback Safety**: CI3 tables remain untouched as reference

## Testing the Migration

### Manual Test Cases

**Test Case 1: New CI4 User**
- User exists in `ci4_users`
- Expected: Normal signin, no migration

**Test Case 2: CI3 User First Signin**
- User exists in `teacher` table, not in `ci4_users`
- Expected: User migrated, audit log created, signin succeeds

**Test Case 3: Duplicate Username**
- User exists in both `ci4_users` and CI3 table
- Expected: Uses `ci4_users` entry, no migration

**Test Case 4: Unknown User**
- User doesn't exist in any table
- Expected: "Unknown username" error

### Checking Audit Logs

Query to find user migrations:

```sql
SELECT 
    event_key,
    event_type,
    JSON_EXTRACT(after_state, '$.username') as username,
    JSON_EXTRACT(after_state, '$.ci3_user_table') as source_table,
    created_at
FROM ci4_audit_events
WHERE event_type = 'user_migrated_from_ci3'
ORDER BY created_at DESC;
```

### Application Logs

Migration events are logged at INFO level:

```
INFO - UserMigrationService::findAndMigrateUser() - Found user in teacher, attempting migration
INFO - UserMigrationService::migrateUser() - Successfully migrated user john.teacher from teacher to ci4_users (new ID: 123)
INFO - Auth::processSignin() - User migrated from CI3, proceeding with authentication: john.teacher
```

## Multi-School Session Variables

The authentication system now properly supports multi-school staff with separate session variables:

### Session Variables

**User Identity:**
- `loginuserID`: User's CI4 user ID
- `username`: User's username
- `name`: User's full name
- `email`: User's email
- `usertypeID`: User's CI3-compatible usertype ID
- `usertype`: User's role name (e.g., "Admin", "Teacher")

**School Access:**
- `available_school_ids`: **Array** of school IDs the user has access to
- `schoolID`: Currently active school ID (single integer)
- `schools`: Legacy comma-separated school IDs (for CI3 compatibility)

**School Context:**
- `defaultschoolyearID`: Active school year for current school
- `lang`: Language setting for current school

### How It Works

1. **During Login** (`Auth::setUserSession()`):
   - `available_school_ids` is set to array of all accessible school IDs
   - `schoolID` and `schools` remain as comma-separated string initially

2. **After School Selection** (`School::processSelection()` or `Auth::redirectAfterSignin()`):
   - `schoolID` is updated to the selected school ID (integer)
   - `available_school_ids` remains unchanged
   - School-specific context (`defaultschoolyearID`, `lang`) is set

3. **Returning to School Selector** (`/school/select`):
   - Uses `available_school_ids` to show all accessible schools
   - Displays current active school from `schoolID`
   - User can switch schools without logging out

### Code Example

```php
// Get list of schools user can access (for school selector)
$availableSchoolIDs = session()->get('available_school_ids'); // [1, 2, 3]

// Get currently active school
$currentSchoolID = session()->get('schoolID'); // 2

// Check if user has access to a specific school
if (in_array($targetSchoolID, $availableSchoolIDs)) {
    // Grant access
}
```

### Benefits

- **Persistent Access List**: User's full school access list is preserved in session
- **Easy School Switching**: Staff can change active school via `/school/select`
- **Clear Separation**: Active school vs. available schools are distinct
- **CI3 Compatibility**: Legacy `schools` variable still present for older code

## Security Considerations

### Transaction Safety

All migrations use database transactions:
- If any step fails, entire migration is rolled back
- Database integrity is maintained
- Partial migrations cannot occur

### Duplicate Prevention

The service checks for existing usernames before migration:
- Prevents duplicate users in `ci4_users`
- Skips migration if username already exists
- Logs warnings for debugging

### CI3 Table Preservation

- CI3 tables are **read-only** during migration
- Original data is never modified
- Tables remain as reference/fallback

### Password Security

- Passwords are never logged
- Hash format preserved from CI3
- No plaintext password exposure

## Future Considerations

### Complete Migration Check

To verify all active users have been migrated:

```sql
-- Count users in CI3 tables not yet in ci4_users
SELECT 
    'systemadmin' as source_table,
    COUNT(*) as unmigrated_count
FROM systemadmin sa
LEFT JOIN ci4_users cu ON cu.username = sa.username
WHERE cu.id IS NULL AND sa.active = 1

UNION ALL

SELECT 'user', COUNT(*)
FROM user u
LEFT JOIN ci4_users cu ON cu.username = u.username
WHERE cu.id IS NULL AND u.active = 1

-- ... repeat for teacher, student, parents
```

### Disabling Migration

Once migration is complete, you can disable automatic migration:

```php
// In Auth::processSignin(), comment out migration code:
// if (!$user) {
//     $user = $this->userMigrationService->findAndMigrateUser($username);
// }
```

### CI3 Table Retirement

After confirming all active users are migrated:
1. Disable automatic migration (see above)
2. Keep CI3 tables as historical reference
3. Optional: Archive CI3 tables to separate database
4. Never delete CI3 tables (audit/compliance requirement)

## Troubleshooting

### Migration Fails

**Symptoms:** User not migrated despite existing in CI3 table

**Check:**
1. Database logs for errors
2. Application logs for transaction failures
3. Verify CI3 table exists and is accessible
4. Check for missing required fields

### Duplicate Username Error

**Symptoms:** Migration skipped, user in both tables

**Solution:**
- This is expected behavior
- CI4 will use the `ci4_users` entry
- No action needed

### Missing Role

**Symptoms:** User migrated but can't sign in

**Check:**
1. Verify `ci4_roles` table has entry for user's usertypeID
2. Check `ci4_user_roles` table for role assignment
3. Manually assign role if missing:

```sql
INSERT INTO ci4_user_roles (user_id, role_id, created_at)
SELECT cu.id, cr.id, NOW()
FROM ci4_users cu, ci4_roles cr
WHERE cu.username = 'problematic.user'
  AND cr.ci3_usertype_id = 2; -- Teacher role
```

---

**Last Updated:** November 2025  
**Version:** 1.0
