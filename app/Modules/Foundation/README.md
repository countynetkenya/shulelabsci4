# Foundation Module

The Foundation module seeds the cross-cutting infrastructure required by the ShuleLabs CI4 runtime. It must be installed before any feature slice and is responsible for:

- Shared tenancy context resolution via the TenantResolver service.
- Append-only audit logging with hash-chain sealing.
- Global soft delete orchestration.
- Append-only finance ledgers and period lock governance.
- Integration registry scaffolding and webhook idempotency helpers.
- QR code issuance and verification primitives.
- Maker-checker workflow helpers and approval policies.

## Developer Guidelines

1. **Never bypass the audit service.** All mutating operations must call `AuditService::recordEvent()` before committing.
2. **Soft delete only.** Use `SoftDeleteManager` to mark records with `deleted_at`, `deleted_by`, and `delete_reason`. Hard deletes are forbidden.
3. **Tenant awareness.** Resolve the active tenant by calling `TenantResolver::fromRequest()` or by injecting the resolver into your controller/service constructor.
4. **Ledger integrity.** New financial journals must call `LedgerService::commitTransaction()` with balanced debit/credit entries and use `scheduleReversal()` for controlled rollbacks.
5. **Integration registry.** External API calls should enqueue payload metadata with `IntegrationRegistry` before dispatch and update the record on completion.
6. **QR issuance.** Generate verifiable QR payloads via `QrService::issueToken()` and `QrService::verify()`.
7. **Approvals.** Always submit state transitions through `MakerCheckerService` to enforce four-eyes workflows.

## Quality Gates

- Foundation services ship with a PHPUnit suite under `ci4/tests/Foundation`. Extend these tests when adding behaviour so CI enforces ledger balance, audit hash integrity, QR expiry rules, and tenant scoping.
- GitHub Actions executes linting, PHPStan, PHPUnit, a migration dry-run, audit verification, backup self-tests, and a Docker image build on every push/PR to guard the immutable-audit contract.
- The Docker baseline (`docker-compose.yml`) provisions php-fpm, nginx, MySQL, Redis, worker, and scheduler containers. Use the scheduler to run audit/backup checks and monthly restore drills (`scripts/backup/restore_drill.sh`).

See the service implementations under `Services/` for usage documentation and method level PHPDoc.
