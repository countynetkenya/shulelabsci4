# CI3 to CI4 Database Migration Guide

## Table of Contents
1. [Overview](#overview)
2. [User Authentication Migration](#user-authentication-migration)
3. [Migration Strategies](#migration-strategies)
4. [Pre-Migration Checklist](#pre-migration-checklist)
5. [Step-by-Step Migration Process](#step-by-step-migration-process)
6. [Table Prefix Strategy](#table-prefix-strategy)
7. [Data Validation & Backfill](#data-validation--backfill)
8. [Rollback Procedures](#rollback-procedures)
9. [Common Issues & Solutions](#common-issues--solutions)
10. [Post-Migration Tasks](#post-migration-tasks)

---

## Overview

This guide covers the complete process for migrating a ShuleLabs CI3 database to support CI4 with **independent user authentication**.

### Important Changes in CI4 User Authentication

**CI4 now uses its own normalized user schema and is fully independent from CI3 user tables.**

**What Changed:**
- **Before:** CI4 authenticated against CI3 tables (student, teacher, parents, user, systemadmin)
- **Now:** CI4 uses its own `ci4_users`, `ci4_roles`, and `ci4_user_roles` tables
- **Migration:** Automatic one-time backfill from CI3 tables to CI4 tables during initial migration
- **Independence:** After migration, CI4 authentication operates independently of CI3

### Key Principles
- **Zero Downtime**: CI3 and CI4 can run simultaneously during migration
- **Independent Authentication**: CI4 has its own user schema, not shared with CI3
- **Automatic Backfill**: CI4 migrations automatically populate user data from CI3 tables
- **Non-Destructive**: Only adds new tables/columns, never removes existing CI3 data
- **Reversible**: Can rollback CI4 without affecting CI3
- **Password Compatibility**: CI4 preserves CI3 password hashes for seamless login

---

## User Authentication Migration

### CI4 User Schema Overview

CI4 introduces three new tables for user management:

**1. `ci4_users`** - Main user identity table
- Replaces multi-table CI3 approach (student, teacher, parents, user, systemadmin)
- Contains: username, email, password_hash, full_name, photo, schoolID, is_active
- Tracks original CI3 source via `ci3_user_id` and `ci3_user_table` fields
- One user record per person, regardless of role

**2. `ci4_roles`** - Role definitions
- 8 pre-seeded roles: super_admin, admin, teacher, student, parent, accountant, librarian, receptionist
- Maps to CI3 usertypeID for backward compatibility
- Enables future role expansion beyond CI3's fixed types

**3. `ci4_user_roles`** - User-to-role assignments
- Many-to-many relationship between users and roles
- Allows users to have multiple roles (future enhancement)

### Migration Process

When you run `php spark migrate --all` on a database with existing CI3 user tables:

1. **Create CI4 Tables**
   - Creates `ci4_users`, `ci4_roles`, `ci4_user_roles`
   - Adds proper indexes and foreign key relationships

2. **Seed Roles**
   - Populates `ci4_roles` with 8 default roles
   - Maps each role to corresponding CI3 usertypeID (0-7)

3. **Backfill Users** (Automatic)
   - Scans CI3 tables: systemadmin, user, teacher, student, parents
   - Copies each user to `ci4_users` with:
     - Original username and password hash (unchanged)
     - User metadata (name, email, photo, schoolID, active status)
     - CI3 source tracking (ci3_user_id, ci3_user_table)
   - Assigns appropriate role based on usertypeID
   - Skips duplicates and handles missing data gracefully

4. **Result**
   - All CI3 users can now log in via CI4
   - CI4 authenticates against `ci4_users` only
   - CI3 tables remain unchanged and functional
   - Both systems can operate independently

### Backward Compatibility

- **Passwords:** CI4 uses CI3-compatible SHA-512 + ENCRYPTION_KEY hashing
- **Sessions:** Both can share `school_sessions` table (optional)
- **User Mapping:** CI4 tracks original CI3 user ID and table for reference
- **Existing Credentials:** All CI3 usernames and passwords work in CI4 after migration

### What Happens to CI3 Tables

**During migration:**
- CI3 user tables (student, teacher, etc.) remain **unchanged**
- They are used as read-only sources for backfill
- No data is removed or modified in CI3 tables

**After migration:**
- CI4 authenticates exclusively from `ci4_users`
- CI3 can still use its original tables if running in parallel
- No runtime synchronization between CI3 and CI4 user tables
- Changes in CI3 won't automatically appear in CI4 (one-time migration only)

**Post-migration options:**
- Keep CI3 tables for CI3 compatibility during transition
- Eventually drop CI3 user tables once CI3 is decommissioned
- CI4 will continue working independently

---

## Migration Strategies

### Strategy 1: In-Place Migration (Recommended)

**Best for**: Most deployments with existing data

**Approach**:
- Add CI4-specific tables alongside existing CI3 tables
- Share critical tables like `school_sessions` for seamless transition
- Gradually migrate features from CI3 to CI4
- Decommission CI3 once migration is complete

**Advantages**:
- ✅ No data duplication
- ✅ No synchronization needed
- ✅ Lower deployment complexity
- ✅ Gradual, controlled migration
- ✅ Easy rollback

**Disadvantages**:
- ⚠️ Requires careful schema compatibility
- ⚠️ Mixed CI3/CI4 tables in same database

**Tools Required**:
- `php spark db:audit` - Check compatibility
- `php spark db:upgrade` - Add missing schema elements
- `php spark db:backfill` - Fix data issues

---

### Strategy 2: New Database with Migration

**Best for**: Fresh installations or major redesigns

**Approach**:
- Create new database with optimized CI4 schema
- Migrate data from CI3 to CI4 database
- Run both systems in parallel during transition
- Use data synchronization or read-only mode on CI3

**Advantages**:
- ✅ Clean separation
- ✅ Optimal CI4 schema design
- ✅ No legacy baggage

**Disadvantages**:
- ⚠️ Complex data migration
- ⚠️ Requires data synchronization during transition
- ⚠️ More deployment complexity
- ⚠️ Higher risk

**Note**: This strategy is **not recommended** for most deployments. Use Strategy 1 unless you have specific requirements for database separation.

---

### Strategy 3: Hybrid with Table Prefix

**Best for**: Organizations wanting clear CI4 table separation

**Approach**:
- Use in-place migration (Strategy 1)
- Add `ci4_` prefix to all new CI4-specific tables
- Share critical tables like `school_sessions` without prefix
- Clear visual separation between CI3 and CI4 tables

**Advantages**:
- ✅ All benefits of in-place migration
- ✅ Clear table ownership
- ✅ Easier to identify CI4 tables
- ✅ Simplified cleanup after full migration

**Implementation**:
```bash
# Audit with prefix
php spark db:audit --prefix=ci4_

# Upgrade with prefix
php spark db:upgrade --apply --prefix=ci4_
```

---

## Pre-Migration Checklist

### 1. Database Backup

**CRITICAL**: Always backup before migration!

```bash
# Create timestamped backup
mysqldump -u root -p shulelabs > backup_$(date +%Y%m%d_%H%M%S).sql

# Verify backup
mysql -u root -p -e "SOURCE backup_20241119_120000.sql" test_db

# Store backup securely
cp backup_*.sql /secure/backup/location/
```

### 2. Environment Preparation

- [ ] PHP 8.3+ installed
- [ ] Composer installed
- [ ] CI4 dependencies installed (`composer install`)
- [ ] `.env` configured with correct database credentials
- [ ] Encryption key matches CI3 (for password compatibility)
- [ ] Database user has CREATE/ALTER privileges

### 3. Audit Current State

```bash
# Check what's missing
php spark db:audit --format=json > audit-before.json

# Review the audit report
cat audit-before.json | jq .
```

### 4. Test Environment Setup

- [ ] Set up staging environment identical to production
- [ ] Run full migration on staging first
- [ ] Validate all features work in staging
- [ ] Document any issues encountered

---

## Step-by-Step Migration Process

### Phase 1: Schema Compatibility Check

**Duration**: 5-10 minutes

```bash
# Step 1: Audit database
php spark db:audit

# Expected output:
# - Missing tables: audit_events, idempotency_keys, etc.
# - Missing columns: (varies by deployment)
# - Missing indexes: (varies by deployment)

# Step 2: Review findings
# - Critical: Missing required tables
# - Important: Missing columns in existing tables
# - Optional: Missing indexes (performance impact only)

# Step 3: Preview upgrade plan
php spark db:upgrade --dry-run

# Review SQL statements to be executed
```

### Phase 2: Schema Migration

**Duration**: 5-15 minutes

```bash
# Step 1: Backup (again, just before changes)
mysqldump -u root -p shulelabs > backup_before_upgrade_$(date +%Y%m%d_%H%M%S).sql

# Step 2: Apply schema changes
php spark db:upgrade --apply

# Confirm changes:
# - "Are you sure you want to continue?" -> y

# Step 3: Verify upgrade
php spark db:audit

# Expected: "✓ Database schema is fully compatible!"
```

### Phase 3: Data Validation

**Duration**: 5-10 minutes

```bash
# Step 1: Check for data issues
php spark db:audit --validate-data

# Common issues found:
# - NULL timestamps in school_sessions
# - NULL created_at in audit tables
# - Missing hash values in audit_events

# Step 2: Preview backfill operations
php spark db:backfill --dry-run

# Review SQL that will fix data issues

# Step 3: Apply backfill (if needed)
php spark db:backfill --apply
```

### Phase 4: Verification

**Duration**: 10-30 minutes

```bash
# Schema verification
php spark db:audit
# Should show: "✓ Database schema is fully compatible!"

# Data verification
php spark db:audit --validate-data
# Should show: "✓ No data validation issues found"

# Application testing
# 1. Test super admin login (the original issue this fixes)
# 2. Test CI4 features (audit logging, sessions, etc.)
# 3. Verify CI3 still works (if running in parallel)
# 4. Check application logs for errors
```

### Phase 5: CI4 Migration Execution

**Duration**: Varies by project

```bash
# Run CI4 migrations (Foundation modules)
php spark migrate --all

# Verify migrations
php spark migrate:status

# Test CI4 runtime
# Visit: http://yoursite.com/v2/ (or CI4 entry point)
```

---

## Table Prefix Strategy

### When to Use Prefixes

Use table prefixes (`ci4_`) when:
- You want clear separation between CI3 and CI4 tables
- Multiple developers need to identify table ownership
- Planning to archive/remove CI3 tables later
- Organizational policy requires it

### Implementation

#### Option 1: With Prefix (Recommended for new deployments)

```bash
# Audit with prefix
php spark db:audit --prefix=ci4_

# Upgrade with prefix
php spark db:upgrade --apply --prefix=ci4_

# Results in tables like:
# - ci4_audit_events
# - ci4_idempotency_keys
# - ci4_menu_overrides
# - school_sessions (shared, no prefix)
```

#### Option 2: Without Prefix (Default)

```bash
# Standard commands (no prefix)
php spark db:audit
php spark db:upgrade --apply

# Results in tables like:
# - audit_events
# - idempotency_keys
# - menu_overrides
# - school_sessions
```

### Shared Tables (Never Prefixed)

These tables are shared between CI3 and CI4:
- `school_sessions` - For session sharing during transition
- `user` - User authentication data
- `setting` - School configuration

---

## Data Validation & Backfill

### Common Data Issues

#### 1. NULL Timestamps

**Issue**: `school_sessions` with `timestamp = 0` or `NULL`

**Impact**: Sessions may not expire correctly

**Fix**:
```sql
UPDATE school_sessions 
SET timestamp = UNIX_TIMESTAMP() 
WHERE timestamp = 0 OR timestamp IS NULL;
```

**Automated**: `php spark db:backfill --apply`

#### 2. Missing Created Timestamps

**Issue**: Audit tables with `created_at IS NULL`

**Impact**: Cannot track when events occurred

**Fix**:
```sql
UPDATE audit_events 
SET created_at = NOW() 
WHERE created_at IS NULL;
```

**Automated**: `php spark db:backfill --apply`

#### 3. Invalid Hash Values

**Issue**: `audit_events` with `hash_value` NULL or empty

**Impact**: **HIGH SEVERITY** - Breaks audit chain integrity

**Fix**: Manual review required. Cannot auto-generate hash values for historical data.

```bash
# Identify affected records
SELECT id, event_key, created_at 
FROM audit_events 
WHERE hash_value IS NULL OR hash_value = '';

# Options:
# 1. Delete invalid records (if acceptable)
# 2. Mark as legacy/unverified
# 3. Generate new hash (breaks chain but better than NULL)
```

### Validation Process

```bash
# 1. Full validation scan
php spark db:audit --validate-data --format=json > validation-report.json

# 2. Review issues
cat validation-report.json | jq '.data_issues'

# 3. Generate backfill plan
php spark db:backfill --dry-run

# 4. Review and approve
# Read each SQL statement carefully

# 5. Apply backfill
php spark db:backfill --apply

# 6. Re-validate
php spark db:audit --validate-data
```

---

## Rollback Procedures

### Scenario 1: Schema Migration Failed

```bash
# Stop application
systemctl stop nginx  # or apache2

# Restore from backup
mysql -u root -p shulelabs < backup_before_upgrade_20241119_120000.sql

# Verify restoration
mysql -u root -p shulelabs -e "SHOW TABLES;"

# Restart application
systemctl start nginx
```

### Scenario 2: CI4 Not Working, CI3 Fine

```bash
# No rollback needed!
# CI3 continues to work with existing tables
# CI4 tables don't affect CI3 operation

# Option 1: Fix CI4 issues and try again
# Option 2: Remove CI4 tables if no longer needed
php spark db:cleanup --ci4-only  # (if implemented)
```

### Scenario 3: Data Backfill Issues

```bash
# Backfill operations are transaction-wrapped
# Failed operations automatically rollback

# If data corrupted:
# 1. Restore from backup taken before backfill
mysql -u root -p shulelabs < backup_before_backfill.sql

# 2. Review backfill SQL
# 3. Fix manually or retry with corrections
```

---

## Common Issues & Solutions

### Issue 1: "Unknown column 'settingID'"

**Symptom**: Super admin login fails with database error

**Cause**: CI3 `setting` table lacks `settingID` column

**Solution**: Already fixed in SiteModel! No action needed.

**Verification**:
```bash
# Test login as super admin
# Should work regardless of settingID presence
```

---

### Issue 2: Permission Denied on Schema Changes

**Symptom**: `ERROR 1142: ALTER command denied to user`

**Cause**: Database user lacks CREATE/ALTER privileges

**Solution**:
```sql
-- Grant privileges (as root)
GRANT CREATE, ALTER, INDEX ON shulelabs.* TO 'your_user'@'localhost';
FLUSH PRIVILEGES;
```

---

### Issue 3: Table Already Exists

**Symptom**: `CREATE TABLE` fails with "table already exists"

**Cause**: Partial migration or previous attempt

**Solution**:
```bash
# Skip existing tables (automatic in db:upgrade)
# Or use prefix to separate
php spark db:upgrade --apply --prefix=ci4_
```

---

### Issue 4: Foreign Key Constraints

**Symptom**: Cannot create table due to foreign key issues

**Cause**: Referenced tables don't exist yet

**Solution**: Migration order handled automatically, but if manual fixes needed:
```sql
-- Temporarily disable foreign key checks
SET FOREIGN_KEY_CHECKS = 0;

-- Run migrations
-- ...

-- Re-enable checks
SET FOREIGN_KEY_CHECKS = 1;
```

---

## Post-Migration Tasks

### 1. Verify Application Functionality

- [ ] Super admin login works
- [ ] Regular user login works
- [ ] Session persistence across CI3/CI4
- [ ] Audit logging working
- [ ] No database errors in logs

### 2. Performance Optimization

```bash
# Analyze tables for query optimization
mysql -u root -p shulelabs -e "ANALYZE TABLE audit_events, idempotency_keys;"

# Check for missing indexes (beyond audit recommendations)
# Review slow query log
```

### 3. Documentation Updates

- [ ] Document migration date and version
- [ ] Update team wiki with new tables
- [ ] Note any customizations or deviations
- [ ] Archive migration logs

### 4. Monitoring Setup

- [ ] Set up alerts for database errors
- [ ] Monitor CI4 table sizes
- [ ] Track session table growth
- [ ] Schedule regular backups

### 5. Cleanup (Optional)

Once fully migrated to CI4 and CI3 decommissioned:

```bash
# Identify CI3-only tables
mysql -u root -p shulelabs -e "SHOW TABLES;" > all_tables.txt

# Review and archive unused tables
# DO NOT delete until certain CI3 won't be reactivated

# Archive old data
mysqldump -u root -p shulelabs ci3_old_table > archive/ci3_old_table.sql

# Drop archived tables (CAREFUL!)
# mysql -u root -p shulelabs -e "DROP TABLE ci3_old_table;"
```

---

## Migration Checklist Summary

### Pre-Migration
- [ ] Database backed up
- [ ] Staging environment tested
- [ ] Audit completed and reviewed
- [ ] Team notified of maintenance window

### During Migration
- [ ] Schema audit completed
- [ ] Schema upgrade applied
- [ ] Data validation completed
- [ ] Data backfill applied (if needed)
- [ ] Verification passed

### Post-Migration
- [ ] Application tested
- [ ] Performance verified
- [ ] Monitoring enabled
- [ ] Documentation updated
- [ ] Team trained on new commands

---

## Need Help?

If you encounter issues not covered in this guide:

1. **Check logs**: `writable/logs/log-*.php`
2. **Review audit output**: `php spark db:audit --format=json`
3. **Test in staging first**: Never experiment in production
4. **Ask for help**: Include error messages and audit results

---

## Appendix: Quick Command Reference

```bash
# Audit Commands
php spark db:audit                              # Basic audit
php spark db:audit --format=json                # JSON output
php spark db:audit --prefix=ci4_                # With table prefix
php spark db:audit --validate-data              # Include data validation
php spark db:audit --include-experimental       # Include OKR tables

# Upgrade Commands
php spark db:upgrade --dry-run                  # Preview changes
php spark db:upgrade --apply                    # Apply changes
php spark db:upgrade --apply --prefix=ci4_      # With prefix
php spark db:upgrade --migrations               # Generate migration files

# Backfill Commands
php spark db:backfill --dry-run                 # Preview data fixes
php spark db:backfill --apply                   # Apply data fixes
php spark db:backfill --prefix=ci4_             # With prefix

# Standalone Script (no Spark)
php ci4/scripts/ci3-db-upgrade.php \
  --dsn="mysql:host=localhost;dbname=shulelabs" \
  --user=root --pass=secret --apply
```

---

**Document Version**: 1.0  
**Last Updated**: 2024-11-19  
**Maintained By**: ShuleLabs DevOps Team
