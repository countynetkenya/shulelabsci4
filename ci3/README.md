# shulelabs
Shulelabs

## 2025–2026 Implementation Plan

The authoritative roadmap for the platform is documented in [`docs/SHULELABS_IMPLEMENTATION_PLAN.md`](docs/SHULELABS_IMPLEMENTATION_PLAN.md). All new features, migrations, and compliance milestones should align with that plan.

### CI4 Foundations (`/v2` runtime)

- The `/public/v2.php` front controller boots the CI4 runtime located under `ci4/` alongside the legacy CI3 stack.
- Shared sessions are enabled via the existing `school_sessions` database table so `/v2` routes honour CI3 logins during migration.
- Cross-cutting services (audit logging, soft deletes, append-only ledgers, integration registry, QR issuance, maker-checker approvals, tenant resolution) live in `ci4/app/Modules/Foundation` with accompanying migrations.
- Run `php spark migrate --all` to install the foundational schema additions before exercising `/v2` endpoints.

## Run in GitHub Codespaces

[![Open in GitHub Codespaces](https://github.com/codespaces/badge.svg)](https://codespaces.new/github.com/ShuleLabs/shulelabs)

1. Create a Codespace with the button above or open an existing devcontainer with `make up`.
2. Wait for the post-create automation to finish:
   - `composer install`
   - If a CI4 `spark` binary exists it runs `php spark migrate --all` (CI3 projects simply skip this step).
   - Writable directories (`application/cache`, `application/logs`) are created with group write access.
3. Visit the forwarded ports:
   - Application: http://localhost:80/
   - Database: port 3306 (MySQL 8)
   - phpMyAdmin: http://localhost:8081/ (login with `shulelabs` / `shulelabs`).
4. Import or reset your schema with `make reset-db` as needed, then log in to the application.

### Docker baseline (`/v2` runtime)

The production-aligned stack lives in `docker-compose.yml` and ships the following services:

- `app` – PHP 8.3 FPM container built from `docker/php-fpm/Dockerfile` with Composer pre-installed and the required PHP extensions (`intl`, `gd`, `zip`, `bcmath`, `mysqli`, `pdo_mysql`, `exif`).
- `web` – nginx proxy that serves `ci4/public` as the web root and forwards PHP traffic to the `app` service.
- `db` – MySQL 8.0 with a persistent data volume and health check.
- `redis` – optional cache/session backend.
- `mailhog` – optional SMTP sink exposed on http://localhost:8025/ for local email testing.

To boot the stack on a clean machine:

```bash
cp .env.example .env              # only required the first time
docker compose build --no-cache   # rebuilds the PHP image with extensions
docker compose up -d              # start containers in the background
docker compose logs -f            # follow logs during the first boot
```

Once the containers are up, confirm everything is healthy with `scripts/docker/smoke.sh`. The script ensures Composer dependencies are installed in the `app` service, waits for the `app`, `web`, and `db` containers to report healthy, and verifies that `http://localhost:8080/index.php` returns HTTP 200. Backups land under `storage/backups` using `scripts/backup/run_backup.php`; restore drills unpack into `storage/restore-drill`.

After the smoke script succeeds you should be able to:

- Open http://localhost:8080/ in a browser and load the CI4 frontend.
- Inspect container status with `docker compose ps` or `docker ps`.
- Connect to the database with `docker compose exec db mysql -h db -u root -p$DB_ROOT_PASSWORD`.
- Review runtime logs with `docker compose logs -f app web db`.

### Environment defaults

```
DB_HOST=db
DB_DATABASE=shulelabs
DB_USERNAME=shulelabs
DB_PASSWORD=shulelabs
```

These values are wired through `.env.example` / `.env` and injected into the PHP container so both CI3 configuration loaders and framework helpers receive consistent credentials. Update `.env` if you override the database credentials or enable optional services such as Mailhog.

### Helpful make targets

```
make up        # start the devcontainer stack locally
make down      # stop and remove containers
make logs      # follow the Apache/PHP logs
make bash      # open a shell in the app container
make reset-db  # recreate the development database
```

### Troubleshooting

- Port 80 in Codespaces must be marked as public to access the ERP externally.
- If Apache serves a directory index, ensure the repo root is mounted at `/workspace/shulelabs` and that `index.php` exists.
- Should migrations fail, re-run `composer install` followed by the migration command inside the container.
- Run `composer ci` locally to mirror the GitHub Actions pipeline (lint, PHPStan, PHPUnit).
- Run `composer test` to execute the CI4 suite via `phpunit.ci4.xml` (the helper script wraps `vendor/bin/phpunit -c phpunit.ci4.xml`).
- For CI3 modules that rely on `application/config/database.php`, mirror the credentials from `.env` to keep CLI jobs and background workers connected.


## Menu overrides management

The admin sidebar can be re-arranged without editing PHP by visiting **Administrator → Menu Overrides** (`/menuoverrides`). The interface supports:

1. Listing current custom nodes and relocation rules along with their database-backed configuration.
2. Creating or editing entries, including priority, parent, icon, and optional permission bypass flags.
3. Supplying optional placeholder JSON when `Create Parent Placeholder` is enabled so missing parents are auto-generated.
4. Removing overrides that are no longer required.

Whenever an override is saved, updated, or deleted the cached `dbMenus` session value is cleared so the next request rebuilds the full menu tree.

Menu labels are still localised through the `mvc/language/<locale>/topbar_menu_lang.php` files. For every override `menuName` ensure the corresponding `menu_<menuName>` key exists (for example `menu_main_examreport`).

### Migration

Run the latest migrations after deploying to create and seed the new `menu_overrides` table:

```bash
php index.php migrate/latest
```

For convenience the repository now also ships shortcut wrappers so the more
compact syntax below works as well:

```bash
php migrate/latest
```

The CLI-only `migrate` controller ensures deployments can execute migrations without exposing them over HTTP.

The migration copies the legacy `mvc/config/menu_overrides.php` values into the database so the sidebar structure matches the previous configuration on first run.

### Verification

Developers can quickly syntax check the new components with:

```bash
php -l mvc/migrations/20240221000000_create_menu_overrides.php
php -l mvc/models/Menu_override_m.php
php -l mvc/controllers/Menuoverrides.php
php -l mvc/views/menuoverrides/index.php
php -l mvc/views/menuoverrides/form.php
```

Perform a manual walkthrough by adding a temporary override in the UI, verifying it appears in the sidebar, and confirming translated labels resolve correctly after updating the relevant language files.

## Objectives & Key Results (OKR)

The OKR module ships behind the `FLAG_OKR_V1` feature flag. Enable it by setting `FLAG_OKR_V1=true` in your environment and running the migrations:

```bash
php index.php migrate/latest
```

Once enabled you can manage objectives from **Administration → OKRs**. Progress can be recomputed manually in the UI or programmatically through the API at `/api/v10/okr`.

To refresh progress in bulk schedule the CLI command:

```bash
php index.php cron okr_update_progress            # all schools
php index.php cron okr_update_progress <schoolID> # single school
```

Progress is aggregated from linked data sources (exams, attendance, gamification, finance) via the `Okr_progress_service` library.

## Deployment automation

Operators can run `scripts/shulelabs_automation.sh` after pulling the latest code to
install Composer dependencies, execute the QA toolchain, and apply pending
database migrations or SQL patches. When `UPDATE_REPO=1` (the default) it also
performs a fast-forward `git pull` so servers without automation tooling can be
updated from a single command. The command logs to `var/log/automation_*`,
summarises any failures, and stops on the first error unless `FAIL_FAST=0`. See the
[operations runbook](docs/operations/automation-runbook.md) for environment
variables, execution order, and remaining manual follow-up tasks.

### Ubuntu quick start

On a fresh Ubuntu host you only need to install the runtime prerequisites once;
afterwards the automation script handles day-to-day updates:

```bash
sudo apt update
sudo apt install php8.2-cli php8.2-mbstring php8.2-xml php8.2-mysql php8.2-curl mysql-client unzip git
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

git clone git@github.com:countynetkenya/shulelabs.git
cd shulelabs

# Optional: export DB_HOST/DB_USER/DB_PASSWORD/DB_NAME if they differ from defaults
./scripts/shulelabs_automation.sh
```

Subsequent deployments on the same machine are as simple as running the script
again (optionally with overrides such as `UPDATE_REPO=0` when the workspace is
already up to date or `RUN_TESTS=0` to skip the legacy suite). No additional
manual Composer or PHPUnit commands are required.

By default the script now exercises both PHPUnit suites: the legacy CI3 tests and
the CI4 module tests via `phpunit.ci4.xml`. Toggle them independently with
`RUN_TESTS=0` or `RUN_TESTS_CI4=0` when you only need a subset. The automation
exports `CI_ENVIRONMENT=testing` before launching PHPUnit so the runtime always
boots in the expected mode, regardless of the value embedded in the XML
configuration.

When database work is enabled (`APPLY_DB=1`) the script also executes the CI4
migration stack through the portable wrappers under `ci4/bin/migrate/` (which
proxy to `php spark ... --all`) followed by a configurable
set of spark maintenance commands (defaults to `cache:clear`). Control those
behaviours with `RUN_CI4_MIGRATIONS=0` and `RUN_CI4_TASKS=0`, or override the
spark binary/env/flags/tasks via `CI4_SPARK_BIN`, `CI4_SPARK_PHP_BIN`,
`CI4_SPARK_ENV`, `CI4_SPARK_MIGRATE_ARGS`, and `CI4_SPARK_TASKS`. Arguments and
tasks accept shell-style quoting (single or double quotes) and can be delimited
with either newlines or semicolons when specifying multiple commands.

Set `RUN_SMOKE=1` to curl the dockerised stack via `scripts/docker/smoke.sh`
before touching the database; it ensures Composer dependencies are installed,
waits for the core services (`app`, `web`, `db`) to report healthy, and curls
`http://localhost:8080/index.php`. Override the base URL with `BASE_URL` or tweak
retry behaviour with `WAIT_ATTEMPTS`, `WAIT_DELAY`, and `CURL_TIMEOUT`.
