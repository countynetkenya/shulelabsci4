# 🔧 Complete Database Table Reference Refactor - AI Agent Prompt

**Date**: November 27, 2025  
**Issue**: Multiple files still reference legacy `ci4_*` prefixed tables that no longer exist  
**Impact**: Runtime errors: "Unable to prepare statement: no such table: ci4_user_roles"  
**Required Action**: Replace ALL occurrences of `ci4_users`, `ci4_roles`, and `ci4_user_roles` with unprefixed versions

---

## 📋 Context & Background

### What Happened
1. **Original Schema**: Database used `ci4_users`, `ci4_roles`, `ci4_user_roles` tables
2. **Migration Applied**: Created migration `2025-11-26-132500_ResetUnprefixedAuthTables.php` that:
   - Drops old `ci4_*` tables
   - Creates new unprefixed tables: `users`, `roles`, `user_roles`
3. **Partial Fix**: Updated some files (UserModel.php, CompleteDatabaseSeeder.php) but MISSED many controllers
4. **Current Issue**: 100+ references to `ci4_*` tables remain in codebase causing database errors

### Current Database Schema
```sql
-- ACTUAL TABLES (exist):
- users            (was ci4_users)
- roles            (was ci4_roles)
- user_roles       (was ci4_user_roles)

-- LEGACY TABLES (DO NOT EXIST):
- ci4_users        ❌ DELETED
- ci4_roles        ❌ DELETED
- ci4_user_roles   ❌ DELETED
```

---

## 🎯 Mission: Complete Codebase Refactor

### Objective
**Replace ALL occurrences** of the following table references across the ENTIRE codebase:

| Old Reference (DELETE) | New Reference (USE) |
|------------------------|---------------------|
| `ci4_users`            | `users`             |
| `ci4_roles`            | `roles`             |
| `ci4_user_roles`       | `user_roles`        |

### Scope
- **ALL PHP files** in `app/` directory
- **Controllers** (Admin, Teacher, Student, SuperAdmin, Parent Portal)
- **Models** (though UserModel already fixed)
- **Database Seeders** (though CompleteDatabaseSeeder already fixed)
- **Filters**, **Helpers**, **Libraries**, **Services**
- **DO NOT TOUCH** migration files (they reference old names intentionally for dropping)

---

## 🔍 Files Requiring Changes (Confirmed via grep)

### High Priority (Controllers - Active Runtime Errors)

1. **app/Controllers/Admin/Dashboard.php** (10 occurrences)
   - Lines 136-141: Role verification query
   
2. **app/Controllers/Admin/Schools.php** (10 occurrences)
   - Lines 205-210: SuperAdmin role check

3. **app/Controllers/Admin/Users.php** (25+ occurrences)
   - Lines 33-39: User listing with roles
   - Lines 81-82: Validation rules
   - Lines 108, 113: User creation
   - Lines 162, 197-198, 228, 232-233: User editing
   - Lines 287: Role listing
   - Lines 318-323: Role verification

4. **app/Controllers/Admin.php** (4 occurrences)
   - Lines 86-87, 112, 306

5. **app/Controllers/ParentPortal.php** (18 occurrences) ⚠️ **CRITICAL - PARENT USER TESTING**
   - Lines 91, 93: Grade queries joining users table
   - Lines 188-193: Children listing with user details
   - Lines 210-214: Child detail view
   - **Impact**: Parent dashboard and child management completely broken

6. **app/Modules/Admin/Controllers/Classes.php** (12 occurrences)
   - Lines 192-199: Teacher listing query

7. **app/Modules/Admin/Controllers/Teachers.php** (14 occurrences) ⚠️ **MISSED FILE**
   - Lines 35-43: Teacher listing with roles
   - Lines 84-85: Validation rules
   - Lines 110, 114, 118: Teacher creation
   - Lines 183-184: Teacher editing validation

8. **app/Modules/Admin/Controllers/Students.php** (14 occurrences) ⚠️ **MISSED FILE**
   - Lines 35-43: Student listing with roles
   - Lines 84-85: Validation rules
   - Lines 110, 114, 118: Student creation
   - Lines 194-195: Student editing validation

9. **app/Modules/Student/Controllers/Dashboard.php** (4 occurrences)
   - Lines 52-56: Student info query

10. **app/Modules/Teacher/Controllers/Dashboard.php** (5 occurrences)
    - Lines 51-55: Teacher info query
    - Lines 115-116: Grades query

11. **app/Modules/Teacher/Controllers/Gradebook.php** (9 occurrences)
    - Lines 58-62: Student grades query

12. **app/Modules/Teacher/Controllers/Attendance.php** (6 occurrences) ⚠️ **MISSED FILE**
    - Lines 60-63: Student attendance listing

### Medium Priority (Seeders - Already Partially Fixed)

10. **app/Database/Seeds/MultiSchoolUserSeeder.php** (10 occurrences)
    - Lines 83-90: Role ID lookups
    - Line 226: User insert

11. **app/Database/Seeds/Ci4DefaultSuperadminSeeder.php** (6 occurrences)
    - Lines 25-26, 61, 65, 72

### Low Priority (Verification Only)

12. **app/Database/Migrations/2025-11-26-132500_ResetUnprefixedAuthTables.php**
    - ⚠️ **DO NOT CHANGE** - References old names to drop them

---

## 🛠️ Refactoring Instructions

### Step 1: Automated Global Search & Replace

Execute the following replacements **in order** (order matters due to substring matching):

```bash
# Pattern 1: Replace ci4_user_roles (longest first to avoid partial matches)
find app/ -type f -name "*.php" ! -path "*/Migrations/*" -exec sed -i 's/ci4_user_roles/user_roles/g' {} +

# Pattern 2: Replace ci4_users
find app/ -type f -name "*.php" ! -path "*/Migrations/*" -exec sed -i 's/ci4_users/users/g' {} +

# Pattern 3: Replace ci4_roles
find app/ -type f -name "*.php" ! -path "*/Migrations/*" -exec sed -i 's/ci4_roles/roles/g' {} +
```

**IMPORTANT**: Exclude migration files from replacement!

### Step 2: Manual Review Required Files

Some files may need manual review due to complex SQL or string concatenation:

1. **Validation Rules**: Update `is_unique[]` rules
   ```php
   // OLD:
   'email' => 'required|valid_email|is_unique[ci4_users.email]'
   
   // NEW:
   'email' => 'required|valid_email|is_unique[users.email]'
   ```

2. **Complex Queries**: Check JOIN statements
   ```php
   // OLD:
   ->join('ci4_user_roles', 'ci4_users.id = ci4_user_roles.user_id')
   
   // NEW:
   ->join('user_roles', 'users.id = user_roles.user_id')
   ```

3. **Table Aliases**: Update if ci4_ prefix in aliases
   ```php
   // OLD:
   ->table('ci4_user_roles ur')
   
   // NEW:
   ->table('user_roles ur')
   ```

### Step 3: Verification Commands

After refactoring, verify no ci4_* references remain:

```bash
# Should return 0 matches (except migrations):
grep -r "ci4_users" app/ --include="*.php" | grep -v "Migrations/"
grep -r "ci4_roles" app/ --include="*.php" | grep -v "Migrations/"
grep -r "ci4_user_roles" app/ --include="*.php" | grep -v "Migrations/"
```

### Step 4: Test Database Connectivity

Run these tests to verify fixes:

```bash
# 1. Clear routes cache
php spark cache:clear

# 2. Test database connection
php spark db:table users --limit=3

# 3. Run overnight testing
php scripts/overnight-web-testing.php

# 4. Check for errors
tail -f writable/logs/log-*.log
```

### Step 5: Manual Web Testing as Parent User

**CRITICAL**: Test the system as a **Parent user** via web browser to verify ParentPortal functionality:

1. **Start dev server** (if not running):
   ```bash
   php spark serve --host=0.0.0.0 --port=8080 &
   ```

2. **Create a Parent user** (if doesn't exist):
   ```bash
   # Add parent role to roles table if missing
   php spark db:query "INSERT OR IGNORE INTO roles (role_name, role_slug, ci3_usertype_id, description, created_at) VALUES ('Parent', 'parent', 5, 'Parent/Guardian role', datetime('now'))"
   
   # Create parent user
   php spark db:seed ParentUserSeeder  # Create this seeder or manually insert
   ```

3. **Test Parent Portal workflows**:
   - [ ] Login as parent user at `http://localhost:8080/login`
   - [ ] Access Parent Dashboard at `/parent/dashboard`
   - [ ] View children list at `/parent/children`
   - [ ] View child grades (should use `users` table, not `ci4_users`)
   - [ ] View child attendance
   - [ ] Check all page loads without database errors
   - [ ] Verify no "ci4_user_roles" errors in browser console or logs

4. **Verify Parent-specific database queries**:
   ```bash
   # Check writable/logs/log-*.log for any ci4_* table errors
   grep -i "ci4_users\|ci4_roles\|ci4_user_roles" writable/logs/log-*.log
   ```

5. **Test all user roles** (comprehensive):
   - [ ] SuperAdmin: `admin@shulelabs.local` / `Admin@123456`
   - [ ] School Admin: `schooladmin1@shulelabs.local` / `Admin@123`
   - [ ] Teacher: `teacher1@shulelabs.local` / `Teacher@123`
   - [ ] Student: `student1@shulelabs.local` / `Student@123`
   - [ ] **Parent**: `parent1@shulelabs.local` / `Parent@123` ⚠️ **MUST TEST**

**Why Parent Testing is Critical**:
- `app/Controllers/ParentPortal.php` has **18 occurrences** of `ci4_*` table references
- Parent workflows involve complex joins across users, students, grades, and classes
- Most likely to expose missed database reference errors

---

## 📊 Expected Changes Summary

| File Type | Estimated Changes | Priority | Parent User Impact |
|-----------|-------------------|----------|--------------------|
| ParentPortal.php | 18 occurrences | **CRITICAL** | 🔴 **BLOCKING** |
| Teachers.php (Module) | 14 occurrences | **CRITICAL** | ⚠️ **NEW FIND** |
| Students.php (Module) | 14 occurrences | **CRITICAL** | ⚠️ **NEW FIND** |
| Attendance.php (Module) | 6 occurrences | **HIGH** | ⚠️ **NEW FIND** |
| Other Controllers | 60+ occurrences | HIGH | ⚠️ Partial |
| Seeders | 16 occurrences | MEDIUM | - |
| Models | 0 (already fixed) | - | - |
| Migrations | 0 (DO NOT TOUCH) | - | - |
| **TOTAL** | **128+ occurrences** | - | - |

**Note**: We initially found 116 occurrences but discovered 3 additional critical files in `app/Modules/` (Teachers.php, Students.php, Attendance.php) bringing total to **128+ occurrences**.

---

## ✅ Success Criteria

After executing this refactor, you should achieve:

1. ✅ **Zero database errors** - No "no such table: ci4_*" errors
2. ✅ **All tests passing** - Overnight testing at 100% success rate
3. ✅ **No grep matches** - `grep -r "ci4_users" app/ --include="*.php" | grep -v Migrations/` returns 0
4. ✅ **All logins work** - All 18 test users can authenticate (including Parent role)
5. ✅ **All dashboards load** - SuperAdmin, Admin, Teacher, Student, **Parent** dashboards accessible
6. ✅ **All CRUD operations** - Create/Read/Update/Delete for users, roles working
7. ✅ **Teacher/Student CRUD tested** - Can create/edit teachers and students via admin panel
8. ✅ **Attendance marking works** - Teachers can mark attendance without errors
9. ✅ **Parent Portal tested** - Parent can view children, grades, attendance without errors
10. ✅ **No errors in logs** - `writable/logs/log-*.log` contains zero ci4_* table errors

---

## 🚨 What NOT to Change

**DO NOT modify these**:
1. Migration files in `app/Database/Migrations/` (they reference old names to drop them)
2. Documentation/comments that reference historical table names
3. CI3 compatibility fields like `ci3_user_id`, `ci3_user_table` (these are column names, not table names)

---

## 📝 Implementation Checklist

Use this checklist to track progress:

- [ ] **Phase 1**: Run automated search/replace (exclude migrations)
- [ ] **Phase 2**: Manually review validation rules in all Controllers
- [ ] **Phase 3**: Manually review JOIN statements in complex queries
- [ ] **Phase 4**: Verify grep returns 0 matches for ci4_* tables
- [ ] **Phase 5**: Clear all caches (`php spark cache:clear`)
- [ ] **Phase 6**: Test database connectivity (`php spark db:table users`)
- [ ] **Phase 7**: Run overnight testing suite
- [ ] **Phase 8**: Verify all 18 users can login
- [ ] **Phase 9**: Check all dashboards load without errors
- [ ] **Phase 10**: **Test Teacher/Student CRUD operations** (create new teacher, create new student, edit teacher, edit student)
- [ ] **Phase 11**: **Test Attendance marking** (mark attendance for a class as teacher)
- [ ] **Phase 12**: **Manual web test as Parent user** (access ParentPortal, view children, grades)
- [ ] **Phase 13**: Verify no ci4_* errors in logs (`grep -i "ci4_users\|ci4_roles\|ci4_user_roles" writable/logs/log-*.log`)
- [ ] **Phase 14**: Commit changes with message: "fix: replace all ci4_* table references with unprefixed versions"

---

## 🎯 Autonomous Agent Instructions

**If you are an AI coding agent executing this refactor**:

1. **Read this entire document** before starting
2. **Use multi_replace_string_in_file** for efficiency (batch edits)
3. **Execute in this order**:
   - **ParentPortal.php** (FIRST - most critical, 18 occurrences)
   - **Modules/Admin/Controllers/Teachers.php** (14 occurrences - teacher CRUD broken)
   - **Modules/Admin/Controllers/Students.php** (14 occurrences - student CRUD broken)
   - **Modules/Teacher/Controllers/Attendance.php** (6 occurrences - attendance broken)
   - Other Controllers (remaining files with ci4_* references)
   - Seeders (medium priority)
   - Any other files found via grep
4. **After each file**:
   - Verify syntax is valid
   - Check for partial replacements (e.g., don't leave `ci4_user` if it was `ci4_users`)
5. **Test after completion**:
   - Run `php spark serve`
   - Execute overnight testing
   - Verify 0 database errors in logs
6. **Commit once** with comprehensive message listing all files changed

---

## 📖 Example Transformations

### Example 1: Simple Table Reference
```php
// BEFORE:
$users = $db->table('ci4_users')->get();

// AFTER:
$users = $db->table('users')->get();
```

### Example 2: JOIN Statement
```php
// BEFORE:
$db->table('ci4_users')
    ->select('ci4_users.*, ci4_roles.name as role_name')
    ->join('ci4_user_roles', 'ci4_users.id = ci4_user_roles.user_id')
    ->join('ci4_roles', 'ci4_user_roles.role_id = ci4_roles.id')

// AFTER:
$db->table('users')
    ->select('users.*, roles.name as role_name')
    ->join('user_roles', 'users.id = user_roles.user_id')
    ->join('roles', 'user_roles.role_id = roles.id')
```

### Example 3: Validation Rule
```php
// BEFORE:
'username' => 'required|is_unique[ci4_users.username]'

// AFTER:
'username' => 'required|is_unique[users.username]'
```

### Example 4: Table Alias
```php
// BEFORE:
$this->db->table('ci4_user_roles ur')
    ->join('ci4_roles r', 'r.id = ur.role_id')

// AFTER:
$this->db->table('user_roles ur')
    ->join('roles r', 'r.id = ur.role_id')
```

---

## 🔗 Related Files & Context

**Migration that created the issue**:
- `app/Database/Migrations/2025-11-26-132500_ResetUnprefixedAuthTables.php`

**Files already fixed** (reference for pattern):
- `app/Models/UserModel.php` ✅
- `app/Database/Seeds/CompleteDatabaseSeeder.php` ✅

**Testing script** (to validate fixes):
- `scripts/overnight-web-testing.php`

**Database location**:
- `/workspaces/shulelabsci4/writable/database.db` (SQLite3)

---

## 🚀 Ready to Execute?

**Command for AI Agent**:
```
@Copilot Execute COMPLETE_DATABASE_REFACTOR_PROMPT.md - Replace ALL ci4_* table references with unprefixed versions across entire codebase (except migrations). Verify 0 database errors after completion.
```

---

**Version**: 1.0  
**Status**: Ready for Execution  
**Estimated Time**: 15-20 minutes  
**Risk Level**: Low (search/replace with verification)  
**Impact**: HIGH - Resolves all database errors
