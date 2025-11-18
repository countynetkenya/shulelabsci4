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

### Turnkey Ubuntu bootstrap

On fresh Ubuntu 22.04+ hosts you can automate the full stack (APT packages,
Composer dependencies, MySQL provisioning, and CI4 migrations) with:

```bash
bash scripts/setup-ubuntu.sh
```

Run the script as a regular sudo-enabled user (not root). Override the database
defaults by exporting `DB_NAME`, `DB_USER`, `DB_PASS`, or `DB_HOST` before
launching it. The script ensures the database exists, installs Composer
dependencies, copies `.env` if needed, and finally executes
`php bin/migrate/latest` so an existing schema is upgraded to the latest
migrations automatically.

### Login & logging defaults

- Authenticated sessions rely on the CI4-native session handler backed by the
  `ci4_sessions` table. Run `php bin/migrate/latest` and (optionally)
  `php spark db:seed AdminUserSeeder` to provision an initial `admin/admin123`
  account locally.
- Unauthenticated visitors are redirected to `/login`, which is served by
  `App\Controllers\LoginController`. The controller validates credentials,
  verifies password hashes, and stores the authenticated identity in the
  session before redirecting users to the dashboard.
- Log files are written to `writable/logs/`. The logger threshold defaults to 9
  for non-production environments and 4 in production via
  `app/Config/Logger.php`. Ensure the `writable/` directory remains writable by
  the web server user in every environment.

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
- Review `docs/ci4-login-cutover.md` for the latest CI4-only deployment
  guidance: authentication/session defaults, the remaining
  implementation-plan gates before CI4 becomes the sole runtime, the `/v2`
  cleanup checklist, and the solo-run activation + module smoke-test
  workflows that apply to new deployments.
