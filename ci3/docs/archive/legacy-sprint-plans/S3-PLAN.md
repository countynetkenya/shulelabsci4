# Sprint 3 Plan – Payroll & Permissions Enhancements

## Overview
Sprint 3 focuses on rounding out payroll compliance for the Kenyan market, tightening payroll document exports, and delivering the first iteration of the fine-grained permissions management module. A supporting objective is to harden the digital library so document distribution is auditable and controlled for at least six months.

## Goals
- Provide up-to-date statutory tables to guarantee accurate statutory deductions and employer contributions in payroll runs.
- Deliver compliant employee artefacts (payslips, P9 forms, and eCitizen submissions) in reusable formats.
- Ship Permissions v1 so administrators can manage access at scale and exchange role definitions programmatically.
- Enhance the library module with secure, traceable file distribution features.

## Deliverables
### 1. Kenyan Statutory Tables
- Capture 2024 rates for SHIF, NSSF Tier I & II, PAYE brackets, Housing Levy, and NITA levies.
- Centralise lookup tables under `database/seeds/payroll_statutory_tables.php` with effective dates.
- Build regression tests that compare computed deductions against KRA reference examples.
- Provide an Artisan-style CLI task (`php index.php payroll:refresh_tables`) for refreshing tables from seed data.

### 2. Payroll Artefacts & Exports
- Generate branded PDF payslips for individual pay periods via MPDF with download + email delivery.
- Produce annual P9 statements per employee with audit history and regeneration support.
- Offer eCitizen-ready ZIP exports that package XML/CSV payloads per the platform’s payroll upload template.
- Extend `ci4/docs/openapi/payroll.php` to document the new endpoints for payslip retrieval and export triggers.

### 3. Permissions v1
- Introduce grouped role management UI with search, bulk grant/revoke, and JSON import/export.
- Store permission bundles as JSONB (or MySQL JSON) in `permissions_groups` with validation guards.
- Include role assignment history logging to keep an audit trail of bulk operations.
- Publish REST endpoints with pagination and filtering to back the new UI.
- Seed default role groups (Admin, Finance, HR, Academics, Library) and document customisation steps.

### 4. Library Hardening
- Add signed download URLs (configurable TTL) backed by short-lived JWT tokens.
- Persist download events for at least 180 days with filters by user, asset, and time window.
- Expose CSV export of download logs for compliance reviews.
- Update the frontend to surface download counts and latest access timestamps.

## Milestones
1. **Week 1:** Statutory table ingestion + validation tests; signed link backend service prototype.
2. **Week 2:** Payroll artefact generation flows and CLI wiring; permissions schema + API foundations.
3. **Week 3:** Permissions UI interactions, bulk operations, and JSON exchange; library download reporting views.
4. **Week 4:** Integration testing, documentation, and deployment readiness checklist.

## Risks & Mitigations
- **Changing regulations:** Track KRA circulars; encapsulate rates behind effective-date tables to simplify updates.
- **PDF generation performance:** Cache rendered templates and batch background jobs for mass exports.
- **Permissions misconfiguration:** Implement dry-run previews before applying bulk changes; store snapshots for rollback.
- **Audit data growth:** Archive download logs older than 180 days into cold storage with scheduled jobs.

## Testing Strategy
- Unit tests for payroll calculators covering each statutory deduction.
- Snapshot tests for payslip/P9 templates to detect layout regressions.
- API integration tests for permissions workflows, including import/export of JSON definitions.
- End-to-end tests for signed library links validating expiry and logging.

## Documentation & Training
- Update the Administrator Guide with new payroll export instructions and permissions workflows.
- Provide a short Loom/video walkthrough for HR and Finance teams demonstrating the new artefacts.
- Schedule knowledge-transfer sessions with customer support to handle permissions-related queries.
