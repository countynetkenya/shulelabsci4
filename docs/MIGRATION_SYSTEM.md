# Migration System Configuration

This document explains how the database migration system is configured in this CodeIgniter 4 application.

## Overview

The application uses CodeIgniter 4's migration system to manage database schema changes. Migrations are organized into two main locations:

1. **App Migrations**: `app/Database/Migrations/` - Core application tables
2. **Module Migrations**: `app/Modules/Foundation/Database/Migrations/` - Foundation module tables

## Migration Discovery Issue and Solution

### The Problem

CodeIgniter 4's migration auto-discovery system (`php spark migrate --all`) only discovers migrations in directories that exactly match registered PSR-4 namespaces. 

With the namespace `Modules\` mapped to `app/Modules/`, CI4 looks for migrations in `app/Modules/Database/Migrations/`, but our actual migrations are in `app/Modules/Foundation/Database/Migrations/`.

CI4 doesn't recursively scan subdirectories, so module migrations were not being discovered.

### The Solution

We implemented a **symbolic link bridge** to make module migrations discoverable:

1. Created `app/Modules/Database/Migrations/` directory
2. Added symbolic links pointing to the actual migration files in Foundation module
3. This allows CI4's FileLocator to find the files under the `Modules\` namespace

### File Structure

```
app/
├── Database/
│   └── Migrations/           # App migrations (actual files)
│       ├── 2024-10-06-000000_CreateCi4MigrationsTable.php
│       ├── 2024-11-19-093500_CreateCi4UsersTable.php
│       └── ...
└── Modules/
    ├── Database/
    │   └── Migrations/       # Symlinks to Foundation migrations
    │       ├── 2024-10-06-000001_CreateAuditTables.php -> ../../Foundation/Database/Migrations/...
    │       └── ...
    └── Foundation/
        └── Database/
            └── Migrations/   # Actual Foundation migration files
                ├── 2024-10-06-000001_CreateAuditTables.php
                ├── 2024-10-06-000002_CreateLedgerTables.php
                └── ...
```

## Running Migrations

### Option 1: Standard CI4 Command (Recommended)

```bash
php spark migrate --all
```

This will now discover and run:
- All App migrations
- All Foundation module migrations (via symlinks)

### Option 2: Custom Command

We also provide a custom command that explicitly runs migrations for all known namespaces:

```bash
php spark migrate:all-modules
```

This command:
1. Runs App migrations
2. Explicitly runs each module's migrations by namespace
3. Provides clearer output about what's being migrated

## Migration Tables Created

When you run migrations, the following tables are created:

### Core Tables (App namespace)
- `migrations` - Migration tracking
- `users` - Normalized user identity
- `roles` - Role definitions
- `user_roles` - User-role assignments

### Foundation Module Tables (Modules namespace)
- `audit_events`, `audit_seals` - Audit logging
- `ledger_transactions`, `ledger_entries`, `ledger_period_locks` - Financial ledger
- `integration_dispatches` - Integration registry
- `qr_tokens`, `qr_scans` - QR code system
- `maker_checker_requests` - Approval workflows
- `tenant_catalog` - Multi-tenancy

## Adding New Module Migrations

If you create a new module with migrations:

1. **Create the migration in the module's Database/Migrations directory:**
   ```
   app/Modules/YourModule/Database/Migrations/YYYY-MM-DD-HHMMSS_YourMigration.php
   ```

2. **Create a symlink in app/Modules/Database/Migrations/:**
   ```bash
   cd app/Modules/Database/Migrations
   ln -s ../../YourModule/Database/Migrations/YYYY-MM-DD-HHMMSS_YourMigration.php .
   ```

3. **Update the MigrateAll command:**
   Add your module namespace to `app/Commands/MigrateAll.php`:
   ```php
   protected $moduleNamespaces = [
       'Modules\\Foundation',
       'Modules\\YourModule',  // Add this
   ];
   ```

4. **Run migrations:**
   ```bash
   php spark migrate --all
   ```

## Migration Status

Check which migrations have been applied:

```bash
# All migrations
php spark migrate:status --all

# Specific namespace
php spark migrate:status -n "Modules\Foundation"
```

## Troubleshooting

### Migrations not being discovered

1. Verify symlinks exist:
   ```bash
   ls -la app/Modules/Database/Migrations/
   ```

2. Check if files are accessible:
   ```bash
   php spark migrate:status --all
   ```

3. Ensure symlink targets are correct (relative paths from the symlink location)

### Tables not being created

1. Check database connection in `.env` or `app/Config/Database.php`
2. Verify database exists and user has CREATE TABLE permissions
3. Check migration file syntax and namespace declaration
4. Review migration output for errors:
   ```bash
   php spark migrate --all 2>&1 | tee migration.log
   ```

## Technical Details

### Why not register each module as a separate namespace?

We tried registering `Modules\Foundation` as a separate PSR-4 namespace alongside `Modules\`, but this creates conflicts:
- PSR-4 tries to load files from both paths
- Classes like `Modules\Foundation\Config\Routes` would be loaded twice
- Results in "Cannot declare class... already in use" errors

### Why not move migrations to app/Modules/Database/Migrations?

Keeping migrations in their respective module directories:
- Maintains module encapsulation
- Makes modules more portable
- Follows standard CI4 module structure
- Keeps related code together

Symlinks provide the best compromise - migrations stay in their modules but are discoverable by CI4's migration system.

## References

- [CodeIgniter 4 Migrations Documentation](https://codeigniter.com/user_guide/dbmgmt/migration.html)
- [CI3 to CI4 Migration Guide](./CI3_TO_CI4_MIGRATION_GUIDE.md)
