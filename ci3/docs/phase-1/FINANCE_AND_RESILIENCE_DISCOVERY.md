# Phase 1 Discovery – Finance & Resilience

Phase 1 introduces the Finance module on CI4 while hardening operational resilience. The discovery blueprint below captured scope, deliverables, sequencing, and operational guardrails so squads could implement without blocking each other. The delivery has now been completed; the summary and sign-off details are documented first, followed by the original blueprint for archival reference.

## Delivery Summary
- `ci4/app/Modules/Finance` landed with feature-gated routes, controllers, domain services, and maker-checker permissions seeded across tenants.
- Invoice, wallet, and POS flows ship with append-only ledger enforcement, audit trails, and contract/integration tests under `ci4/tests/Finance`.
- QuickBooks Online integration runs via queue + CLI workflows with resumable tokens stored in CI4 repositories and dashboarded success metrics.
- Nightly reconciliation, invoice ageing, and integration retry commands execute from scheduler/cron with alerts routed to the operations webhook.
- Backup/restore automation includes finance tables with rehearsal scripts (`scripts/backup/restore_drill.sh`) extended to validate ledger and wallet balances post-restore.

## Operational Sign-off
- **Telemetry:** Grafana dashboards for finance latency, reconciliation drift, and QuickBooks retry depth published to operations.
- **Runbooks:** Updated procedures for POS incident response, QuickBooks export reruns, and reconciliation mismatch triage committed to the shared ops knowledge base.
- **Security & Compliance:** Invoice numbering rules, QR receipt embedding, and multi-currency handling validated with product, finance, and legal stakeholders.
- **Release:** Feature flag `FINANCE_V2` enabled for pilot tenants with rollback plan documented; wider rollout scheduled alongside Phase 2 start.

## Artefact Checklist
| Area | Artefact | Status |
| --- | --- | --- |
| Module | `ci4/app/Modules/Finance` routes, controllers, config | ✅ Merged |
| Data | Migrations for wallet/invoice/reconciliation tables + legacy backfill commands | ✅ Applied & rehearsed |
| Integrations | QuickBooks client, queue workers, CLI command | ✅ Deployed with sandbox verification |
| Automation | Nightly reconciliation + alerting dashboards | ✅ Live |
| Backups | Finance coverage in backup/restore scripts with SQL smoke tests | ✅ Signed off by operations |

## Scope & Outcomes
- **Finance APIs:** CI4-native endpoints for invoices, statements, wallet operations, and POS flows that respect append-only ledger guarantees from `ci4/app/Modules/Foundation/Services/LedgerService.php`.
- **Wallet-First POS:** Cashier workflows that default to prepaid wallets while supporting cash/bank/card fallbacks with identical ledger discipline.
- **QuickBooks Sync:** Idempotent exports built on the Integration Registry (`ci4/app/Modules/Foundation/Services/IntegrationRegistry.php`) with resumable retries.
- **Reconciliation Automation:** Scheduled jobs and dashboards that keep wallets, ledger, and external integrations aligned.
- **Backup Discipline:** Documented restore drills and scripts that include the new finance tables in daily backups.

### Out of Scope
- Payroll and HR payments (slated for Phase 3).
- Third-party PSP onboarding beyond MPESA/Visa channels already prototyped in Phase 0.
- Real-time analytics dashboards (Phase 4 scope).

## Workstreams & Deliverables
| Workstream | Key Deliverables | Acceptance Criteria |
| --- | --- | --- |
| CI4 Finance Module | Module skeleton under `ci4/app/Modules/Finance`, routes, feature flag (`FINANCE_V2`), baseline controllers. | Routes registered, module enabled via feature flag, smoke tests pass on `/v2/finance/ping`. |
| Domain & Data Model | Entities/repositories for invoices, ledger postings, wallet accounts, reconciliation logs. Migrations for ledger alignment and wallet schema. | PHPUnit coverage for repositories, migrations replay cleanly, append-only invariants enforced. |
| POS Experience | `/v2/finance/pos/checkout` and `/v2/finance/pos/refund` endpoints with wallet + fallback payment orchestration. | Integration tests showing atomic ledger + wallet updates and receipt payloads. |
| QuickBooks Integration | `Modules/Finance/Integrations/QuickBooksClient`, export queue, CLI commands for sync. | Idempotent retry proven via job logs, sandbox export demonstrates double-run safety. |
| Reconciliation & Monitoring | Cron-safe commands for wallet vs ledger reconciliation, invoice ageing, integration retry sweeps. Ops webhook alerts on severe mismatches. | Nightly job runbook with thresholds, Grafana/Prometheus dashboard updated. |
| Backup & Restore Hardening | Updated `scripts/backup/run_backup.php`, restore drills including finance tables, documentation in `docs/operations/database-backup.md`. | Restore rehearsal signed off by operations with finance-specific SQL validation snippets. |

## Architecture Decisions
1. **Domain Layer:** Implement repositories/entities under `ci4/app/Modules/Finance/Domain/*`. Ledger persistence delegates to `LedgerService`; audits go through `AuditService`.
2. **HTTP Layer:** Expose controllers under `/v2/finance/*` with routes defined in `ci4/app/Modules/Finance/Config/Routes.php`. Use `TenantResolver` for tenancy context and consistent error formatting from Phase 0 base controllers.
3. **Feature Flags:** Gate new functionality with `FINANCE_V2` in `ci4/app/Config/Feature.php` mirroring existing `Foundation` toggles to enable per-tenant rollout.
4. **Permissions:** Declare capabilities in `ci4/app/Modules/Finance/Config/Permissions.php` and seed via migrations to support maker-checker flows.
5. **Observability:** Standardise structured logs (JSON) for finance events. Extend the operations webhook payloads to include ledger transaction IDs and tenant codes.

## Data Migration & Schema Plan
- **Ledger Alignment:** Backfill legacy finance tables (`invoice`, `payment`, `income`, `expense`, `credit_memo`) into append-only `ledger_transactions` / `ledger_entries`. Provide a CLI rehearsal script under `ci4/app/Modules/Finance/Commands/BackfillLegacyFinance.php`.
- **Wallet Schema:** Create `wallet_accounts`, `wallet_transactions`, and `wallet_balances` (tenant + student/guardian keys). Capture `source_type`, `source_id`, currency, and metadata for reversals.
- **Invoices & Receipts:** Normalize headers/items into `finance_invoices` and `finance_invoice_items`. Receipts/payments reference invoice IDs and ledger transactions.
- **Reconciliation Logs:** Persist job outcomes in `finance_reconciliation_runs` with JSON mismatch payloads for forensic review.
- **Seed Data:** Provide chart-of-accounts seeders for tuition, transport, hostel, discounts, and taxes per tenant to accelerate onboarding.

### Migration Execution Steps
1. Generate migrations for schema creation with reversible down methods.
2. Author seeder classes under `ci4/app/Database/Seeds/Finance` for baseline accounts and permissions.
3. Implement backfill commands guarded behind `--confirm` flags to avoid accidental production runs.
4. Dry-run migrations/backfills against sanitized snapshots before production deployment.

## API Surface (Draft)
| Endpoint | Method | Description | Dependencies |
| --- | --- | --- | --- |
| `/v2/finance/invoices` | GET/POST | List/create invoices, optionally auto-posting ledger entries. | `LedgerService`, tenant resolver |
| `/v2/finance/invoices/{id}` | GET/PATCH | Retrieve/settle invoices; PATCH triggers wallet or fallback payments. | Wallet + ledger services |
| `/v2/finance/wallets/{account}/top-up` | POST | Apply wallet credits (MPESA, card, cash) with append-only journal. | Integration Registry hooks |
| `/v2/finance/wallets/{account}/charge` | POST | Debit wallet for POS sale or invoice settlement. | POS workflows |
| `/v2/finance/statements` | GET | Render statements filtered by tenant, term, or guardian. | Statement builder, QR service |
| `/v2/finance/quickbooks/export` | POST | Queue idempotent QuickBooks sync jobs; returns export batch ID. | Integration Registry + job queue |
| `/v2/finance/reconciliation/runs` | GET | List recent reconciliation batches and outcomes. | Reconciliation jobs |

All endpoints must honour tenancy context, emit audit trails, and share the error envelope introduced in Phase 0 (`ci4/app/Modules/Foundation/Http/Responses/ApiErrorResponse.php`).

## Integration Strategy – QuickBooks Online
- Port OAuth flow and export logic from `mvc/controllers/Quickbooks.php` into `Modules/Finance/Integrations/QuickBooksClient` with token storage in CI4 repositories.
- Store tokens, batch metadata, and logs to allow resumable retries. Employ Integration Registry fingerprints to guard against duplicate exports.
- Provide CLI command `QuickBooksSync` under `ci4/app/Modules/Finance/Commands` and a queue worker handler to drive incremental sync.
- Add smoke tests targeting the QuickBooks sandbox to validate read/write scopes and rate-limit behaviour.

## Reconciliation & Automation
- Implement cron-safe commands in `ci4/app/Modules/Finance/Commands` for:
  - Wallet vs ledger balance reconciliation (threshold-configurable, pushes mismatches to operations webhook).
  - Invoice ageing reports and anomaly alerts for long-outstanding balances.
  - Integration retry sweeps (QuickBooks, MPESA callbacks) with exponential backoff limits.
- Extend `scripts/cron/` definitions or GitHub Actions workflows to schedule nightly smoke checks with reporting into the operations Slack channel.

## Backup & Resilience Hardening
- Update `scripts/backup/run_backup.php` to include finance tables and verify dumps via checksum metadata.
- Amend `scripts/backup/restore_drill.sh` to replay finance migrations and run smoke SQL (ledger totals, wallet balances) post-restore.
- Document restore validation steps in `docs/operations/database-backup.md`, including acceptance thresholds for finance data.
- Ensure infrastructure IaC (Terraform) references new secrets/keys (QuickBooks, MPESA) with rotation guidance.

## Quality Gates
- PHPUnit feature tests under `ci4/tests/Finance` covering invoice issuance, wallet debits, POS flows, and reconciliation commands.
- Contract tests using `CodeIgniter\Test\FeatureTestTrait` for `/v2/finance/*` endpoints.
- PHPStan level max on new module namespaces; update baseline as necessary.
- API examples appended to `ci4/docs/openapi.yaml` with request/response samples for finance endpoints.
- Load testing scripts (k6 or Artillery) targeting high-volume wallet transactions to validate lock contention handling.

## Timeline & Checkpoints
1. **Week 1:** Merge module skeleton, feature flag, and baseline migrations. Deliver backfill rehearsal plan.
2. **Week 2:** Implement invoice & wallet domain models, ledger posting flows, and `/v2/finance/invoices` + `/wallets` endpoints.
3. **Week 3:** Ship POS workflows, wallet reconciliation job, and associated PHPUnit coverage.
4. **Week 4:** Port QuickBooks integration, queue orchestration, and sandbox verification.
5. **Week 5:** Harden backup scripts, document restore drills, and ship dashboards/alerts for reconciliation status.
6. **Week 6:** UAT in staging with data migration rehearsal, runbook sign-off, and Go/No-Go readiness review for CI3 feature freeze.

## Risks, Dependencies & Mitigations
- **Chart of Accounts Alignment:** Requires finance/ops agreement per country. Mitigation: schedule design workshop in Week 1, capture template decisions as seed data PR.
- **Payment Provider Readiness:** MPESA/Visa webhook contracts must be confirmed. Mitigation: coordinate with Integrations squad, reuse Phase 0 sandbox credentials, add contract tests.
- **Migration Safety:** Risk of double-posting legacy transactions. Mitigation: read-only backfill rehearsals with checksum comparison and `--dry-run` CLI options.
- **Resource Contention:** Finance squad shares backend engineers with Operations. Mitigation: publish sprint commitments and block dedicated QA bandwidth early.

## Open Questions (Resolved)
1. Should POS receipts embed QR codes via existing QR service or generate inline?
   - **Resolution:** Reuse the central QR service to ensure consistency with existing verification flows; receipts include QR tokens referencing ledger transactions.
2. Do we require multi-currency wallet balances at launch or defer to post-MVP?
   - **Resolution:** Launch with single-currency wallets per tenant but preserve currency metadata for future multi-currency expansion in Phase 3.
3. Are there compliance requirements (e.g., CRA, KRA) that impact invoice numbering sequences?
   - **Resolution:** Adopt legal-compliant, per-tenant prefixes with audit trail storage; numbering policy documented in finance runbooks.

Resolutions are mirrored in `docs/phase-1/OPEN_ITEMS.md` for traceability.
