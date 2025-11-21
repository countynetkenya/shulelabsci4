# ShuleLabs 2025–2026 Implementation Plan

This document captures the canonical product and engineering roadmap for the ShuleLabs School OS initiative. It supersedes all prior sprint plans and provides a shared reference for every delivery squad and automation agent.

## 1. Objectives ("Why")
- Deliver a single, modular School OS covering finance, communications, operations, inventory, HR/payroll, transport, library, and analytics.
- Support global-readiness with multi-syllabus curricula (Cambridge, CBC, IB), multi-currency ledgers, and country-specific payroll and tax rules.
- Prioritise prepaid wallets, digitally verifiable documents (QR codes and digital signatures), and paperless operations throughout every module.
- Maintain a modular CodeIgniter 4 (CI4) architecture so that multiple teams and AI agents can ship features in parallel without collisions.

## 2. Architecture ("How")
- **Framework:** CI4 becomes the authoritative runtime while the legacy CI3 stack is kept in read-only mode during cutover.
- **Services:** PHP 8.x, MySQL, Redis queues, Docker-based delivery, and GitHub Actions for CI/CD.
- **Modularity:** `app/Modules/*` encapsulates domain slices (Finance, HR, Inventory, Academics, Library, Transport, Threads, Verification, Integrations, Governance, Analytics).
- **Multi-tenancy:** Hierarchical tenant model (Organisation → School → Warehouse) resolved by a central TenantResolver with shared catalog support.
- **Security:** Auth + CSRF + throttle filters, role permissions, maker-checker approvals, immutable audit logs with hash chains, and enforced soft deletes.
- **Integrations:** QuickBooks Online, MPESA, Visa/PayPal/Google Pay, LMS connectors (Moodle/Canvas/Teams), Google Drive for documents and media.

## 3. CI3 → CI4 Migration Strategy
1. Stand up CI4 alongside CI3 under `/v2/` routes, sharing sessions and authentication.
2. Prioritise cross-cutting CI4 services: AuditService, SoftDelete kernel, Append-Only Ledger with Period Locks, IntegrationRegistry (idempotency + webhooks), QR Service, TenantResolver.
3. Migrate domain slices incrementally (Finance → Inventory → Academics → HR) while legacy CI3 screens remain read-only until feature parity is achieved.
4. Retire CI3 routes only after CI4 modules meet Definition of Done requirements and automated regression suites are green.

## 4. Core Features ("What")
- **Finance & Wallets:** Unified billing (fees, transport, hostel, activities), multi-currency statements, wallet-first POS, controlled reversals, QuickBooks sync.
- **HR/Payroll:** Country-specific compliance (e.g., PAYE/NSSF/SHIF for Kenya) with templates for other countries, effective-dated rates, payslips, compliance exports, approvals, and period locks.
- **Academics:** Multi-syllabus subject/exam engine, AI-assisted report designer, transcripts, and LMS synchronisation (SSO, courses, grades).
- **Inventory & Logistics:** Multi-warehouse flows, PO→GRN→Invoice→Payment lifecycle, QR-enabled transfers with GPS capture and accept/partial/reject decisions, due-to/due-from postings.
- **Library:** Class/subject filters, Google Drive sync, secure downloads, QR labelling.
- **Transport & Attendance:** Routes, GPS tracking, QR/biometric attendance, wallet rules for boarding.
- **Threads & Notifications:** Event-driven message threads, inline approvals (Approve/Receive/Pay), tagging, SMS/email/push notifications.
- **Verification (QR + Signatures):** `/verify/report/{code}` endpoints for reports/certificates, PDF signing (PAdES), employer verification portal, scan logs.
- **Gamification:** Points, badges, House leaderboard, optional wallet rewards.
- **Governance & Compliance:** Role templates, delete-steward model, audit exports, privacy controls (retention, right-to-erasure).

## 5. P0 Hard Prerequisites (Do Now)
1. **AuditService + Audit Log:** Append-only log capturing before/after JSON, request metadata, IP, and hash chain with daily sealing.
2. **Append-Only Finance + Period Lock:** Immutable ledgers with reversal-based corrections and locked accounting periods.
3. **Central Soft Delete:** No hard deletes; `deleted_at/by/reason` enforced via shared services and DB triggers.
4. **Backups & Restore Drills:** AES-256 encrypted backups, checksum verification, monthly test restores to S3/Drive.
5. **Docker Baseline & CI Gates:** php-fpm, nginx, Redis, MySQL, worker/cron containers with automated lint/tests/migration dry-runs/audit guard/AI self-check in CI.

## 6. P1 High-Value Enhancements
1. **QR Service:** `qr_tokens` + `qr_scans` tables, QR issuance for IDs, invoices, reports, assets.
2. **Integration Registry:** Idempotency, retries, and logging for QuickBooks, MPESA, LMS connectors.
3. **Tenant Resolver & Shared Catalogs:** Central context resolution and cross-school price lists, permissions, and catalog management.
4. **Wallet Reconciliation:** Daily automated reconciliation jobs with reports and anomaly alerts.

## 7. P2 Medium-Term Goals
- LMS two-way sync (Moodle SSO, nightly grade/course updates).
- Gamification to wallet tie-ins and rewards.
- InsightEngine for predictive risk, collections, and analytics.
- PWA and Flutter offline endpoints plus push notifications.

## 8. DevOps & Quality Expectations
- **CI/CD:** GitHub Actions pipelines for linting, PHPStan/Psalm, PHPUnit, migration/seed dry-runs, container builds, staging gates (backup dry-run, audit seal check), production pre-backup + auto-rollback.
- **Monitoring:** Health/readiness probes, structured logs, login P95, backup success %, audit seal integrity, webhook retry depth.
- **Documentation:** Maintain `/docs/API.md`, `/docs/CI_CD_WORKFLOWS.md`, `/docs/SECURITY.md`, and this implementation plan as living references.

## 9. Delivery Phases (Indicative)
- **Phase 0 (Weeks 1–2):** CI4 bootstrap, Docker stack, filters, AuditService, SoftDelete, Append-Only ledger, IntegrationRegistry skeleton, QR Service scaffold.
- **Phase 1 (Weeks 3–6):** Finance APIs (invoices/statements/wallets), QuickBooks idempotent sync, backup/restore drills, wallet reconciliation workers.
- **Phase 2 (Weeks 7–10):** Inventory with QR-enabled transfers, Library QR/Drive, Threads event bus, Gamification MVP.
- **Phase 3 (Weeks 11–14):** HR/Payroll (Kenya template + sandbox), LMS (Moodle SSO/sync), Mobile/PWA endpoints.
- **Phase 4 (Week 15+):** InsightEngine analytics, BI connectors, global payroll templates, advanced compliance dashboards.

## 10. Definition of Done
A module is considered production-ready when:
- All CI4 routes, services, migrations, tests, permissions, and audit hooks are implemented.
- Finance flows honour append-only ledgers and period locks with no hard deletes (CI guard passes).
- Backups succeed with logged monthly restore drills and verified checksums.
- `/verify/report/{code}` validates printed reports/certificates, and the employer portal is live.
- CI/CD pipelines are green with monitoring covering P95 latency, audit seals, reconciliation status, and integration retry queues.

## 11. Implementation Recommendations
- Freeze new CI3 feature development once CI4 scaffolding is live; restrict legacy stack to bug fixes until migration completes.
- Use feature flags to control `/v2` cutovers and provide rollback paths during phased rollouts.
- Enforce tenant scoping via middleware and service-level guards from the start to avoid retrofitting later.
- Automate reconciliation, QR verification, and audit seal checks with dashboards surfaced in Threads for rapid incident response.
- Document module-specific standards (naming, testing, audit hooks) within each `app/Modules/*/README.md` so AI agents can onboard quickly.

For historical records predating this plan, see `docs/archive/legacy-sprint-plans/`.
