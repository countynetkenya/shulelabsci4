# Security & Compliance Controls

The ShuleLabs School OS enforces security controls aligned with the implementation roadmap and the immutable audit requirements.

## Immutable Audit Trails

- All mutating operations must log through `Modules\Foundation\Services\AuditService`.
- The audit trail uses SHA-256 hash chaining and daily seals stored in `audit_seals`.
- `scripts/ci/audit-guard.php` and the scheduler container continuously verify the chain integrity.

## Soft Delete & Maker-Checker Policies

- Hard deletes are blocked; entities are marked with `deleted_at`, `deleted_by`, and `delete_reason` using `SoftDeleteManager`.
- Sensitive operations require dual approval via `MakerCheckerService` (submit → approve/reject) to satisfy four-eyes governance.

## Tenant Isolation

- `TenantResolver` enforces hierarchical scoping (organisation → school → warehouse) sourced from the `tenant_catalog` table.
- Controllers must resolve the tenant context and pass the resolved IDs to downstream services to avoid cross-tenant data leakage.

## Financial Integrity

- Finance features must call `LedgerService` to persist append-only, balanced journals.
- Period locks (`ledger_period_locks`) prevent back-dated edits after books are closed.

## Backup & Restore

- `scripts/backup/run_backup.php` executes encrypted (gzipped) dumps, storing artefacts under `storage/backups`.
- Restore drills (`scripts/backup/restore_drill.sh`) unpack the latest archive into `storage/restore-drill`; the scheduler runs these checks daily.
- Operators must replicate the archives to an offsite location after each run.

## Secrets & Environment

- Database credentials, Redis endpoints, and feature flags are injected via environment variables (`DB_*`, `REDIS_*`, etc.).
- The Docker images avoid bundling secrets and rely on runtime injection.
- GitHub Actions uses ephemeral secrets; no credentials are committed to the repository.

## Monitoring & Alerting

- Queue workers emit heartbeat audit events (`worker:heartbeat`) every five minutes for observability.
- Scheduler runs record audit events (`scheduler:*`) for boot, heartbeat, audit guard, and backup tasks and now forward failures and threshold breaches to the operations webhook (`OPERATIONS_ALERT_WEBHOOK`) so alerts reach the on-call channel immediately.
- Backup and audit guard logs are surfaced through the scheduler container logs; integrate them with your logging stack for alerting on failure.
- Container releases are blocked unless the Trivy scan in `.github/workflows/deploy.yml` passes with no outstanding `HIGH`/`CRITICAL` vulnerabilities, ensuring registry automation enforces the security baseline.

For additional architectural mandates refer to `docs/SHULELABS_IMPLEMENTATION_PLAN.md`.
