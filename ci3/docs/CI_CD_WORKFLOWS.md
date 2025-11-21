# CI/CD Workflows

The ShuleLabs School OS CI pipeline is orchestrated through GitHub Actions (`.github/workflows/ci.yml`). Every push and pull request against `main` or `work` executes the following quality gates:

1. **Dependency install** – Composer dependencies are restored in a PHP 8.2 environment with required extensions (`intl`, `gd`, `bcmath`, `mysqli`, `pdo_mysql`, `zip`).
2. **Static checks** – `composer lint` (PHP_CodeSniffer) and `composer phpstan` enforce the coding standards and static analysis baseline across the CI3 legacy stack and the `/v2` CI4 modules.
3. **Unit tests** – `composer test` runs PHPUnit, including the foundation service coverage under `ci4/tests/Foundation`.
4. **Migration dry-run** – `php ci4/bin/migrate/latest --pretend` validates all migrations apply cleanly without mutating the database.
5. **Audit guard** – `php scripts/ci/audit-guard.php` verifies the append-only audit hash chain remains intact.
6. **Backup automation** – `php scripts/backup/run_backup.php --self-test` creates a compressed archive and `scripts/backup/restore_drill.sh` ensures restore drills are functional.
7. **Container build** – `docker build -t shulelabs-app -f docker/php-fpm/Dockerfile .` confirms the production image compiles with the pinned dependencies.
8. **Release publishing** – Tag pushes trigger `.github/workflows/deploy.yml`, materialise the production `.env` from GitHub secrets, export the database credentials for validation, replay migrations against an ephemeral MySQL instance seeded with those secrets, and publish a hardened image to GitHub Container Registry (`ghcr.io`) only after the Trivy security scan reports no high or critical findings.

Pipelines fail fast if any gate reports violations, blocking merges until issues are resolved.

## Local Execution

Run the same checks locally via the helper Composer script:

```bash
composer ci
```

For targeted runs:

```bash
composer lint
composer phpstan
composer test
php ci4/bin/migrate/latest --pretend
php scripts/ci/audit-guard.php
php scripts/backup/run_backup.php --self-test
bash scripts/backup/restore_drill.sh
```

## Release Checklist

- Verify the latest CI run passed on the release branch.
- Ensure Docker images are rebuilt and pushed to the deployment registry (GitHub Container Registry tag + `latest`).
- Confirm the release Trivy scan in `.github/workflows/deploy.yml` completed with no high/critical findings before the push step.
- Confirm the most recent backup archive is available and a restore drill has succeeded within the last 30 days.
- Verify `OPERATIONS_ALERT_WEBHOOK` is configured and receiving scheduler failure notifications in the target environment.
- Capture the CI run ID and backup artefact checksum in the deployment log for auditability.
