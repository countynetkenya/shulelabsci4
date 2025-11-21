# ShuleLabs CodeIgniter 4 Runtime

This directory contains the CodeIgniter 4 application extracted from the legacy
monorepo. It can run in two modes:

1. **Embedded** – the folder remains inside the `shulelabs` monorepo and reuses
   the top-level Composer dependencies.
2. **Standalone** – copy the contents of `ci4/` into a fresh repository and run
   Composer from that location.

The helper scripts below are scoped so they work in both layouts.

## Initial setup

1. Install PHP 8.3 and the required extensions (`intl`, `mbstring`, `sqlite3`,
   `openssl`, `curl`).
2. If you are working with the standalone runtime, run Composer inside the new
   repository root:

   ```bash
   composer install
   ```

3. Copy the default environment file and adjust credentials:

   ```bash
   php scripts/setup-directories.php
   # or manually:
   cp .env.example .env
   ```

   The setup script also ensures the writable folders exist with the correct
   permissions.

## Database migrations & seeders

All migration entry points live under `bin/migrate`:

```bash
php bin/migrate/status    # Show pending migrations
php bin/migrate/latest    # Apply all pending migrations (passes --all)
php bin/migrate/version 2024-10-06-000006  # Jump to a specific version
php bin/migrate/rollback  # Roll back the last batch (uses --all)
```

These wrappers call the `spark` CLI internally so the same commands work whether
`ci4/` is embedded or standalone. Feel free to use `php spark …` directly if you
prefer.

**Note:** Module migrations (like Foundation) are discovered via symbolic links in
`app/Modules/Database/Migrations/`. See [docs/MIGRATION_SYSTEM.md](docs/MIGRATION_SYSTEM.md)
for technical details about the migration system configuration.

## Running the test suite

Use the portable test runner to execute the PHPUnit suite:

```bash
bash scripts/run-tests.sh
```

The script will reuse the monorepo's vendor directory when present, otherwise it
runs `composer install` locally before invoking PHPUnit.

## OpenAPI specification

All OpenAPI annotations live in `docs/openapi`. Run the standard Composer script
from the repository root (embedded mode) or from the standalone checkout:

```bash
composer openapi:build
```

The command writes `docs/openapi.yaml` in place.

## Next steps when creating a new repository

- Add CI/CD automation (GitHub Actions, GitLab CI, etc.) that runs
  `composer ci` or the equivalent composer scripts you rely on.
- Publish container images or Docker Compose manifests mirroring your target
  deployment.
- Configure application secrets (.env values) for each environment and make sure
  they are stored securely (Vault, Doppler, etc.).
