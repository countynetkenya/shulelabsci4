# CI4 Foundations Delivery Log

This document tracks the cross-cutting services landed as part of Phase 0 of the ShuleLabs School OS roadmap.

## Runtime Bootstrapping
- **CI4 Entry Point:** `/public/v2.php` proxies to the CI4 front controller under `ci4/public/index.php`.
- **Shared Sessions:** CI4 now uses the existing `school_sessions` database table with the `school` cookie, allowing /v2 routes to honour CI3 logins during the cutover period.
- **Autoloading:** Composer is configured with the `SchoolOS\` namespace (pointing at `ci4/app/`) and CodeIgniter module discovery for `app/Modules/*`.

## Cross-Cutting Services
- `Modules/Foundation/Services/AuditService` — append-only audit log with hash chaining and daily seal support.
- `Modules/Foundation/Services/SoftDeleteManager` — global soft delete orchestrator with audit hooks.
- `Modules/Foundation/Services/LedgerService` — append-only ledger helper enforcing balanced journals and period locks.
- `Modules/Foundation/Services/IntegrationRegistry` — idempotent integration dispatch tracking with success/failure telemetry.
- `Modules/Foundation/Services/QrService` — QR token issuance and verification with scan logging.
- `Modules/Foundation/Services/TenantResolver` — resolves hierarchical tenant context from headers or query parameters.
- `Modules/Foundation/Services/MakerCheckerService` — approval workflow enforcement for sensitive transitions.

All services are registered through `Config\Services` for DI/Service Locator access (`service('audit')`, `service('ledger')`, etc.).

## Database Migrations
The Foundation module ships migrations for audit logs, ledgers, integration registry, QR tokens/scans, maker-checker requests, and tenant catalog tables. Run them with:

```bash
php spark migrate --all
```

Ensure `DB_*` environment variables are defined to reuse the CI3 datasource.

## Next Steps
- Monitor scheduler output and hook into the observability stack for alerting when audit/backup checks fail.
- Prepare container registry automation (image tagging, vulnerability scanning) before Phase 0 exit.
- Continue hardening the foundation test suite as new cross-cutting services arrive (e.g. notification bus, feature flags).
