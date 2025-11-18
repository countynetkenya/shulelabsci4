# CI4 Standalone Login & Cutover Plan

This note summarizes how authentication behaves now that the CodeIgniter 4
runtime lives in its own repository, the implementation-plan gates that must
remain green before CI4 is treated as the only runtime, the `/v2` cleanup that
has already landed, and the activation checklist for running CI4 independently.

## Authentication & session defaults in the standalone runtime

* **CI4 owns its own session contract.** Authentication now relies on the native
  CI4 session cookie (`ci4_session`) backed by the `ci4_sessions` database table.
  The default configuration (`app/Config/Session.php`) uses the database session
  handler and the `default` connection. Update the `.env` `database.default.*`
  values per environment so CI4 can create and read this table without any
  external dependencies.
* **New migrations & seeders.** Run `php bin/migrate/latest` (or the equivalent
  CI pipeline step) so the `2024-10-18-000001_CreateCi4SessionsTable` and
  `2024-10-18-000002_CreateUsersTable` migrations create the `ci4_sessions` and
  `users` tables automatically. For local smoke tests seed an administrator with
  `php spark db:seed AdminUserSeeder` (admin/admin123) before logging in.
* **Login flow.** Unauthenticated requests are redirected to `/login` (served by
  `App\Controllers\LoginController`), which renders the form and handles
  `POST /login` submissions. The controller now validates credentials via the
  `users` table, checks `password_hash` values, and writes the `ci4_session`
  cookie before redirecting to the dashboard.

## Outstanding implementation-plan gates

CI4 should only be declared the authoritative runtime after the original
implementation plan’s gating items stay healthy in production:

1. **Do-now services** – Audit log ingestion, append-only ledgers with period
   locks, centralized soft-delete, encrypted backup/restore drills, and the
   Docker/CI baselines. Confirm each service runs cleanly in the standalone
   repository.
2. **Feature migration cadence** – Finance first, followed by Inventory, then
   HR/Learning/Mobile, and finally Analytics. Each slice must meet its
   Definition of Done before the legacy stack is retired. Validate every
   module’s maker-checker flow, ledger integration, and audit coverage.
3. **Feature flags & consumers** – Freeze new feature work on the legacy
   runtime, remove the `/v2` feature-flag shims, and ensure no consumers (mobile
   apps, partners, or jobs) are pinned to `/v2/...` URLs. Communicate the
   cutover schedule well in advance so clients can adjust.

## Solo-run activation checklist

Follow these steps (in order) when promoting CI4 to the primary runtime in its
own repository:

1. **Front controller & base URL** – Update nginx/Apache to point the public web
   root to `public/index.php` directly (the legacy `public/v2.php` shim is no
   longer used). Set `app.baseURL` in `.env` to the production host (for example
   `https://app.shulelabs.cloud/`) and restart PHP-FPM so helpers stop generating
   `/v2` URLs.
2. **Session store** – Run migrations so `ci4_sessions` exists, then verify the
   new cookie (`ci4_session`) is issued on login. Keep the handler set to the
   database driver unless you have a replicated alternative (Redis, etc.).
3. **Cross-cutting services** – Ensure AuditService, the append-only ledgers
   (with period locks), SoftDelete, IntegrationRegistry, QR, and the tenant
   resolver are wired via the standalone bootstrap. These were the “Do Now” items
   and are prerequisites for running CI4 solo.
4. **Feature readiness** – Confirm each module (finance → inventory →
   HR/learning/mobile → analytics) meets its Definition of Done and no longer
   relies on the legacy runtime. Maker-checker flows, audit logging, and ledger
   integration must all be validated here.
5. **Documentation & clients** – Keep the documentation, automation scripts, and
   front-end assets pointing at root-relative routes (no `/v2` prefix). Review
   payroll approvals, module READMEs, runbooks, and any downstream jobs that call
   CI4 endpoints.
6. **Feature flags & external consumers** – Remove obsolete `/v2` feature flags
   and coordinate with mobile apps, integrations, or partners that cached the old
   URLs.

Once each item above is satisfied you can remove the `/v2` reverse proxy from
legacy deployments and treat this repository as the authoritative runtime.

## Module smoke-test workflow

After deploying the standalone runtime, exercise each module end to end:

1. **Foundation / health** – Hit `GET /system/health` to verify dependencies.
   Load `GET /operations/dashboard` (HTML) or `GET /operations/mobile-snapshots`
   (JSON) to confirm telemetry renders.
2. **Mobile snapshots feeding telemetry** – `POST /mobile/snapshots` issues a
   snapshot, `POST /mobile/snapshots/verify` records the result, and
   `GET /mobile/telemetry/snapshots` exposes the aggregated data consumed by the
   dashboard.
3. **Finance** – `POST /finance/invoices` issues an invoice and
   `POST /finance/invoices/{number}/settle` closes it. Supply the standard
   headers (`X-Tenant-ID`, `X-Actor-ID`, `X-Currency`, etc.) so the audit and
   ledger contexts are populated.
4. **Inventory** – `POST /inventory/transfers` initiates a stock transfer and
   `POST /inventory/transfers/{id}/complete` records maker/checker decisions.
5. **HR** – Load `/hr/payroll/approvals` in the browser. Its JavaScript polls
   `/hr/payroll/approvals/pending` and sends approve/reject mutations; validate
   each path now that the fetch helpers no longer include `/v2`.
6. **Learning** – Exercise `POST /learning/moodle/grades` and
   `/learning/moodle/enrollments` with representative JSON payloads to confirm
   Moodle sync hooks survived the repo split.
7. **Library** – `POST /library/documents` registers Drive/QR assets and writes
   to the audit trail.
8. **Threads & gamification** – `GET /threads` lists threads, `POST /threads`
   creates one, and `POST /threads/{id}/messages` appends replies. Ensure the
   event bus still emits `Recognition.Awarded` events for gamification.

Capture these smoke tests in your CI/CD pipeline (or in a shared runbook) so the
standalone deployment covers the same workflows that existed when CI4 was
mounted under `/v2`.

## `/v2` removal checklist (completed)

* `App::$baseURL` now reads from `app.baseURL` and defaults to
  `http://localhost:8080/`, eliminating the baked-in `/v2/` suffix.
* All module route groups (Foundation, Finance, HR, Learning, Inventory,
  Library, Mobile, Threads) were re-homed to root-relative paths.
* UI assets such as the payroll approvals page now build `/hr/...` URLs instead
  of `/v2/hr/...`.
* Module READMEs and the operational docs that previously referenced `/v2`
  endpoints have been refreshed to describe the new entry points.
* The legacy nginx `public/v2.php` shim is no longer required; CI4 can be served
  directly from the web root once the environment points to this repository.

With these changes CI4 is fully decoupled from the `/v2` namespace and owns its
own authentication/session store. Complete the outstanding implementation-plan
milestones (and the solo-run activation checklist) before declaring CI4 the sole
production runtime.
