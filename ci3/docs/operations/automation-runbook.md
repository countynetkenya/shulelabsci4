# Operations Runbook – Shulelabs Automation Script

The `scripts/shulelabs_automation.sh` entry point is designed to be run **after**
bringing a host up to date with the latest application code (for example
following a `git pull`). It performs the dependency, QA, and database flows that
would otherwise be executed manually so you can verify a deployment from a
single command.

## Prerequisites
- PHP 8.2 and Composer available in `PATH`.
- Database credentials available as environment variables (see table below) or
  passed inline when the script is invoked.
- MySQL client utilities (`mysql`, `mysqldump`) installed when database actions
  are enabled.
- Execute from the repository root or export `PROJECT_ROOT=/path/to/shulelabs`.

| Variable | Purpose | Default |
| --- | --- | --- |
| `APPLY_DB` | Enable migrations, seeders, and SQL patch execution | `1` |
| `DB_BACKUP` | Dump the target database before applying changes | `1` |
| `RUN_TESTS` | Execute the legacy PHPUnit suite (`phpunit.xml.dist`) | `1` |
| `RUN_TESTS_CI4` | Execute the CI4 PHPUnit suite (`phpunit.ci4.xml`) | `1` |
| `RUN_STATIC` | Run PHPStan | `1` |
| `RUN_PHPCS` | Run PHPCS | `1` |
| `RUN_SMOKE` | Curl the docker stack (`/` and `/v2` by default) | `0` |
| `RUN_SEEDERS` | Execute `php migrate/seed all` | `0` |
| `RUN_CI4_MIGRATIONS` | Execute `php ci4/bin/migrate/latest` | `1` |
| `RUN_CI4_TASKS` | Run post-migration spark commands | `1` |
| `VERIFY_DB` | Run inventory schema probes after SQL patches | `0` |
| `CHECK_GIT_STATUS` | Summarise the git workspace at runtime | `1` |
| `CHECK_ENV_FILE` | Confirm the environment file exists | `1` |
| `UPDATE_REPO` | Fast-forward pull from the configured remote before QA | `1` |
| `FAIL_FAST` | Abort remaining steps after the first failure | `1` |
| `GIT_REMOTE` | Remote used when `UPDATE_REPO=1` | `origin` |
| `GIT_REF` | Optional branch/ref to check out before pulling | Current branch |
| `ENV_FILE` | Path to the environment file that should exist | `.env` |
| `SQL_PATCH_DIR` | Directory scanned for `*.sql` patches | `database/scripts` |
| `CI4_SPARK_BIN` | Path to the CI4 spark entrypoint | `ci4/spark` |
| `CI4_SPARK_PHP_BIN` | PHP binary used for spark commands | `php` |
| `CI4_SPARK_ENV` | `CI_ENVIRONMENT` value for spark commands | `production` |
| `CI4_SPARK_MIGRATE_ARGS` | Extra args appended to `spark migrate --all` (shell-style quoting supported) | _(empty)_ |
| `CI4_SPARK_TASKS` | Semicolon/newline separated list of spark commands parsed with shell-style quoting | `cache:clear` |

Database connection parameters can be provided with `DB_HOST`, `DB_PORT`,
`DB_USER`, `DB_PASSWORD`, and `DB_NAME`. When `RUN_SMOKE=1`, tweak the HTTP
probe with `SMOKE_BASE_URL` (defaults to `http://localhost:8080` inside the
script) and `SMOKE_ENDPOINTS` (comma-separated list overriding `/,/v2`). Use
`SMOKE_RETRIES`, `SMOKE_RETRY_DELAY`, and `SMOKE_TIMEOUT` to wait for slow
containers, and `SMOKE_EXPECT` to assert that responses contain expected
substrings (e.g. `/=CodeIgniter,/v2=Shulelabs API`).

## Typical deployment flow
```bash
cd /var/www/shulelabs
export DB_HOST=localhost
export DB_USER=shulelabs
export DB_PASSWORD=secret
export DB_NAME=shulelabs

# Optional when UPDATE_REPO=1 (default) – the script will pull for you
git pull --ff-only
./scripts/shulelabs_automation.sh
```

### What the script covers
1. Optionally fast-forwards the local checkout from the configured remote when
   `UPDATE_REPO=1`, aborting if local changes are detected.
2. Summarises the current git branch, head commit, and pending workspace
   changes so you can confirm a clean checkout before files are modified.
3. Verifies that the configured environment file (defaults to `.env`) exists.
4. Installs Composer dependencies and optimises the autoloader.
5. Runs both PHPUnit configurations (legacy and CI4), PHPStan, and PHPCS (respecting the toggle variables above). The script
   sets `CI_ENVIRONMENT=testing` before invoking the CI4 suite so the runtime consistently boots in testing mode regardless of
   the value specified inside the PHPUnit XML files.
6. Optionally curls the docker stack to confirm the CI3 (`/`) and CI4 (`/v2`)
   front controllers respond before database work begins (`RUN_SMOKE=1`).
7. Optionally backs up the configured database, shows migration status, runs
   pending migrations for both stacks (CI3 via `php index.php migrate` and CI4 via
   the portable wrappers in `ci4/bin/migrate/`), executes CI4 spark maintenance tasks (defaults
   to `cache:clear` but configurable through `CI4_SPARK_TASKS`, which accepts
   semicolon- or newline-delimited commands with shell-style quoting), executes seeders,
   and applies SQL patch files
   under `database/scripts/` or paths provided in `SQL_SCRIPTS`.
8. Optionally executes the inventory schema verification probe (enable via
   `VERIFY_DB=1`).
9. Streams output to `var/log/automation_<timestamp>.log` for later review,
   reports any failed or skipped steps in the summary footer, and exits early on
   the first failure unless `FAIL_FAST=0`.

### Follow-up tasks that remain manual
- Restarting PHP-FPM, queue workers, or other long-running daemons when code or
  configuration changes require it.
- Rebuilding front-end assets or vendor bundles (for example running `npm` or
  `yarn` in the asset sub-packages) when a release modifies JavaScript or CSS
  tooling.
- Importing or exporting large data sets beyond the curated SQL patch directory.
- Validating application behaviour through browser smoke tests or product
  walkthroughs once automation completes.

Document the outcome of those manual steps alongside the log file generated by
this script so reviewers can see the full deployment picture.

## Drop-in task: select the CI4 environment file during deploy

When promoting a new release you can extend the surrounding deployment
automation (whether Ansible, a shell script, or CI/CD YAML) with the following
task to ensure CodeIgniter 4 serves verbose error pages on staging/dev while
remaining quiet in production.

### Objective
Ensure ShuleLabs (CI4) shows detailed error pages on non-prod (staging/dev) and
hides them on production by selecting the correct `.env` per environment during
deploy.

### Inputs
- `ENV_NAME` – expected values: `production`, `staging`, `development`, `test`
- `RELEASE_DIR` – absolute path to the release being deployed (for example,
  `/var/www/shulelabs/releases/<timestamp>`)
- `SHARED_ENV_DIR` – absolute path to environment templates (for example,
  `/var/www/shulelabs/shared/env`)
- `CI4_DIR` – derived as `${RELEASE_DIR}/ci4`

### Pre-conditions
- Two template files exist in `SHARED_ENV_DIR`:
  - `.env.production` with `CI_ENVIRONMENT = production`
  - `.env.staging` with `CI_ENVIRONMENT = development`
- The deploy user can read from `SHARED_ENV_DIR` and write or symlink into
  `CI4_DIR`
- PHP-FPM service name is known if a reload is required (optional)

### Steps
1. Determine the target environment from `ENV_NAME`.
2. Map the environment to its template:
   - `production` → `.env.production`
   - any other (`staging`, `development`, `test`) → `.env.staging`
3. Validate that the chosen template exists and that its `CI_ENVIRONMENT`
   matches the intended value (`production` for prod, `development` otherwise).
4. Remove any existing `.env` in `CI4_DIR`.
5. Copy or symlink the template to `CI4_DIR/.env`.
6. Optionally reload PHP-FPM so the new configuration is applied immediately.
7. Continue with the normal step that flips the `current` symlink to
   `RELEASE_DIR`.

### Acceptance criteria
- On staging/dev, an error route renders the CI4 debug page with a stack trace.
- On production, errors render without exposing stack traces.
- The task is idempotent and safe to run repeatedly.

### Safety and guardrails
- Never modify `.env.production`; abort if its `CI_ENVIRONMENT` is not
  `production`.
- For non-prod templates, auto-correct `CI_ENVIRONMENT` to `development` if it
  differs.
- Abort if `CI4_DIR` is missing or not a directory.
- Log the resolved template path and final `CI_ENVIRONMENT` value.

### Rollback
Re-run the task with the previous release’s `RELEASE_DIR` and the appropriate
template, then flip the `current` symlink back to that release.

### Observability
- Emit a one-line summary noting the template used, the target `.env` path, and
  the resulting `CI_ENVIRONMENT` value.
- Optionally issue an HTTP health check after the symlink swap to confirm the
  application is serving traffic.

## Additional automation recommendations

These patterns help keep deployments reproducible and reduce manual follow-up
after the base automation script finishes.

### Normalize writable directories
- Ensure `application/cache`, `application/logs`, and `ci4/public/uploads` exist
  with the correct ownership (typically `www-data:www-data`) and permissions
  (`775`). Seed them with placeholder files so the directories remain tracked.
- Add a preflight check that fails the deployment if any writable path is
  missing or not writable by the web user.

### Align CI3 environment toggles
- Mirror the CI4 `.env` selection for the legacy CI3 stack. For projects that
  rely on the `ENVIRONMENT` constant in `index.php`, template the file based on
  `ENV_NAME` so production never boots with debug mode enabled.

### Restart dependent services
- Capture restart commands for PHP-FPM, queue workers, and websocket daemons in
  variables (for example `PHP_FPM_SERVICE`) and trigger them automatically when
  code or configuration changes require a reload.
- Gate service restarts behind toggles (e.g. `RESTART_SERVICES=1`) to allow dry
  runs without disrupting traffic.

### Post-deploy verification
- Curl critical routes (API, admin) after flipping the `current` symlink and
  assert on known substrings to catch routing or configuration regressions.
- Emit a structured summary to Slack/Teams or your logging stack with the git
  SHA, environment template applied, restart results, and verification status.

### Database safety rails
- When `APPLY_DB=1`, create timestamped `mysqldump` backups in shared storage
  and surface the path in release notes for rapid rollback.
- Run `php index.php migrate status` (CI3) and `php ci4/bin/migrate/status`
  before and after migrations, recording the output alongside the automation
  log to prove schema convergence.

### Notes
- Mirror this behaviour for the legacy CI3 stack by toggling its `ENVIRONMENT`
  value between `development` (non-prod) and `production` (prod).
- Keep secrets and credentials out of the committed templates; store the real
  files in a secured shared location with restricted permissions.
