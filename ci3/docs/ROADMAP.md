# Roadmap Status Snapshot

The canonical roadmap is maintained in `docs/SHULELABS_IMPLEMENTATION_PLAN.md`. This document summarises the currently delivered artefacts and outstanding work per phase.

## Phase 0 – Foundations (Complete)

Delivered:

- CI4 runtime scaffolded under `/v2` with shared sessions.
- Foundation services implemented with migrations (audit, ledger, integration registry, QR, maker-checker, tenant catalog).
- Docker baseline with php-fpm, nginx, MySQL, Redis, worker, and scheduler containers (`docker-compose.yml`).
- Automated backups (`scripts/backup/run_backup.php`) and restore drills (`scripts/backup/restore_drill.sh`).
- GitHub Actions CI pipeline covering lint, PHPStan, PHPUnit, migration dry-run, audit guard, backup self-test, and container build.
- PHPUnit coverage for all foundation services (`ci4/tests/Foundation`).

Operational guardrails now remain in place while Phase 1 builds on top of the foundations.

## Phase 1 – Finance & Resilience (Complete)

Delivered:

- CI4 Finance module under `ci4/app/Modules/Finance` with feature-flagged routes, invoice and wallet services, and maker-checker permissions seeded.
- Wallet-first POS checkout/refund APIs with append-only ledger enforcement and reconciliation-safe receipts.
- QuickBooks Online export queue with idempotent retries, resumable tokens, and audit trail coverage.
- Nightly reconciliation and anomaly alerting jobs with dashboards feeding the operations webhook.
- Backup and restore automation updated to include finance tables plus documented drills signed off by operations.

Follow-up:

- Monitor QuickBooks export health metrics and ledger reconciliation dashboards while Phase 2 squads ramp.

## Phase 2 – Operations & Engagement (Complete)

Delivered:

- CI4 Inventory module with QR-enabled transfers, maker-checker approvals, and audit hooks.
- Library document catalog synced to Drive with QR issuance for printed artefacts.
- Threads collaboration bus with contextual metadata, message APIs, and event dispatching.
- Gamification listener wiring recognitions to points, badges, and audit trails.

Follow-up:

- Bind production repositories/adapters for inventory persistence, Drive storage, and leaderboard storage.
- Kick off Phase 4 analytics squad staffing and migration readiness using the event bus groundwork.

## Phase 3 – People Systems & Learning

Complete:

- CI4 HR module with the Kenyan payroll template, maker-checker approvals, and `/v2/hr/payroll/*` endpoints (JSON + UI) for finance and HR leads.
- Learning module with Moodle grade/enrolment synchronisation wired through the integration registry, surfaced via `/v2/learning/moodle/*` APIs and scheduler-driven dispatch runners.
- Mobile offline snapshot service issuing signed, time-bound datasets for PWA clients, backed by `/v2/mobile/*` endpoints and telemetry exposed to operations.

Focus shifts to promoting production Moodle credentials, monitoring payroll approval throughput, and finalising telemetry thresholds while the analytics squad spins up for Phase 4.

## Phase 4 – Analytics & Global Scale

Pending earlier phases. Will enable InsightEngine analytics, BI connectors, predictive risk scoring, and advanced compliance dashboards.
