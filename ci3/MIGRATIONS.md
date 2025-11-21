# Shulelabs Migrations & Seeding

This repository contains both a legacy CodeIgniter 3 application and a newer CodeIgniter 4 application, each with their own migration system.

## CI4 Table Naming Convention

**All CI4 tables use the `ci4_` prefix** to clearly distinguish them from legacy CI3 tables. This makes it easy to identify and manage tables during the transition period. Once the migration from CI3 to CI4 is complete and CI3 is no longer needed, non-prefixed tables can be safely removed.

## Migration History Tables

**IMPORTANT:** CI3 and CI4 use **separate** migration history tables:

- **CI3** uses the `migrations` table (structure: version, name)
- **CI4** uses the `ci4_migrations` table (structure: id, version, class, group, namespace, time, batch)

These tables are **incompatible** and must remain separate. CI4's MigrationRunner requires an `id` column that CI3's table does not have.

## CodeIgniter 3 Migrations

The CodeIgniter 3 application stores all migrations in `mvc/migrations/` and seeders in `mvc/migrations/seeders/`. The CLI controller `Migrate` is the supported entrypoint for managing schema changes and initial data.

## CodeIgniter 4 Migrations

When you are working on the CodeIgniter 4 runtime (embedded under `ci4/` or extracted into its own repository) you can use either:

**Option 1: From repository root (portable wrappers)**
```bash
php ci4/bin/migrate/status
php ci4/bin/migrate/latest
php ci4/bin/migrate/version 2024-10-06-000006
php ci4/bin/migrate/rollback
```

These scripts proxy to `php spark` internally and automatically pass `--all` so module namespaces are included.

**Option 2: From inside ci4/ directory (direct spark usage)**
```bash
cd ci4
php spark migrate:status --all
php spark migrate --all
php spark migrate:rollback
```

Both approaches work identically and use the `ci4_migrations` table for tracking history.

## Prerequisites

### CI3 Prerequisites

* Configure database credentials via environment variables (preferred):
  * `DB_HOST`, `DB_USERNAME`, `DB_PASSWORD`, `DB_DATABASE`
  * Optional: `DB_PORT`, `DB_DRIVER`, `DB_DSN`, `DB_ALLOW_EMPTY_PASSWORD=1` (only when an empty password is intentional)
* Alternatively, populate `mvc/config/{environment}/database.php`. Missing credentials halt the bootstrap with a clear error to prevent accidental unauthenticated connections.
* Ensure the migrations directory exists (created automatically when loading the config).

### CI4 Prerequisites

* Configure database credentials in `.env` (at repository root):
  * `database.default.hostname`, `database.default.username`, `database.default.password`, `database.default.database`
  * Or use environment variables as with CI3
* The `ci4_migrations` table will be created automatically by the first migration when you run `php spark migrate --all`

## CLI commands

### CI3 Commands

Run all CI3 commands from the project root:

```bash
php index.php migrate/status            # Show configured path, current version, and pending migrations
php index.php migrate/latest            # Apply all pending migrations
php index.php migrate/version <TS>      # Migrate up/down to a target timestamp
php index.php migrate/version <TS> --dry-run=1  # Preview the migration/rollback plan only
php index.php migrate/seed all          # Run all registered seeders in a stable order
php index.php migrate/seed menu_overrides  # Run a specific seeder (names: menu_overrides, audit_events)
```

Add `--verbose=1` to `status`, `latest`, `version`, or `seed` to stream SQL summaries and extra diagnostics to STDOUT and `application/logs/`.

### CI4 Commands

Run CI4 commands from the project root or from inside the `ci4/` directory:

**From repository root:**
```bash
php ci4/bin/migrate/status    # Show migration status for all namespaces
php ci4/bin/migrate/latest    # Run all pending migrations
php ci4/bin/migrate/rollback  # Rollback the last migration batch
php ci4/bin/migrate/version 2024-10-06-000001  # Migrate to specific version
```

**From inside ci4/ directory:**
```bash
cd ci4
php spark migrate:status --all   # Show migration status (--all includes modules)
php spark migrate --all          # Run all pending migrations
php spark migrate:rollback       # Rollback the last migration batch
```

**Note:** The `--all` flag is important to ensure migrations from module namespaces (e.g., `Modules\Foundation`) are included.

### Seeder behaviour

**CI3 Seeders:**
* `menu_overrides` synchronises the admin sidebar overrides defined in `mvc/config/admin_sidebar_pages.php` and only inserts missing rows.
* `audit_events` inserts a single system bootstrap event if the table is empty to validate audit logging.
* Seeders are idempotent—re-running them will skip existing rows.

**CI4 Seeders:**
* Run from ci4 directory: `php spark db:seed SeederClassName`
* CI4 seeders are located in `ci4/app/Database/Seeds/` and module-specific seeders in `ci4/app/Modules/*/Database/Seeds/`

## Troubleshooting

### CI3 Troubleshooting

* **Credential errors:** Verify the environment variables are exported for both PHP-FPM and CLI sessions or update the appropriate `mvc/config/{environment}/database.php`. The CLI aborts when credentials are incomplete.
* **`log_bin_trust_function_creators` errors:** Some migrations may define stored routines. When MySQL binary logging is enabled, grant a privileged user or enable `log_bin_trust_function_creators=1` before re-running.
* **Routine SQL inspection:** Re-run migrations with `--verbose=1` to capture executed SQL in `application/logs/` for auditing.
* **Repeated runs:** Executing `php index.php migrate/latest` or `php index.php migrate/seed all` multiple times is safe—no-op runs report that everything is current.

### CI4 Troubleshooting

* **"Unknown column 'id' in 'order clause'" error:** This occurs if CI4 tries to use the CI3 `migrations` table. Ensure `ci4/app/Config/Migrations.php` has `$table = 'ci4_migrations'`. The first migration will create this table automatically.
* **Migrations not found:** Ensure you're using the `--all` flag when running migrations to include module namespaces: `php spark migrate --all`
* **Table already exists errors:** The bootstrap migration (`2024-10-06-000000_CreateCi4MigrationsTable.php`) uses `createTable(..., true)` for idempotency. If you still see errors, the migration may have partially run—check the database manually.
* **Credential errors:** Verify database settings in `.env` at the repository root. CI4 reads from the `.env` file for database configuration.

### CI3/CI4 Isolation

The two frameworks maintain **completely separate** migration histories:
* CI3 uses the `migrations` table
* CI4 uses the `ci4_migrations` table
* Neither system interferes with the other
* You can run migrations in both systems independently without conflicts

## Optional hardening

To reduce bot noise (e.g., `/robots.txt`, `/swagger*`, `.env` probes), serve a permissive `robots.txt` and deny common exploit paths at the web server layer. For Nginx:

```nginx
location = /robots.txt { return 200 "User-agent: *\nDisallow:/\n"; }
location ~* /(\.env|swagger|\.git|\.DS_Store) { return 404; }
```

Reload Nginx after applying the snippet to shield PHP from unnecessary 404 spam.
