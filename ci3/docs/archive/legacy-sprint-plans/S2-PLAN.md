# Sprint 2 Plan

## Goals
- Introduce append-only financial ledgers for invoices, payments, and general ledger activity.
- Deliver a unified Student Statement experience with API-driven UI and large export support.
- Provide an idempotent QuickBooks export skeleton to prepare for future integrations.
- Capture outcomes and decisions for transparency.

## Scope
1. **Database migrations**
   - Create append-only tables for `invoices`, `invoice_lines`, `payments`, `gl_journal`, and `gl_lines`.
   - Add safeguards that block updates and deletes to guarantee the audit trail.
2. **Student Statement modernisation**
   - Build reusable service layer and JSON API.
   - Refresh UI to consume the API, add term/month/day filters, and support 10k+ row CSV/PDF exports.
3. **QuickBooks export skeleton**
   - Implement idempotent request handling using the idempotency keys table.
   - Log queued work for observability and future expansion.
4. **Documentation**
   - Record sprint plan and outcomes in `/docs/sprints`.

## Timeline & Milestones
| Week | Deliverable |
| ---- | ----------- |
| 1 | Finalise database schema and append-only safeguards. |
| 2 | Ship Student Statement API + UI updates with export endpoints. |
| 3 | Deliver QuickBooks export skeleton and documentation. |

## Risks & Mitigations
- **Large export performance**: stream CSV output and reuse PDF templates to avoid memory spikes.
- **Idempotency correctness**: leverage new `idempotency_keys` table to store request/response hashes.
- **UI regression**: keep legacy dynamic dropdown behaviour and rely on service integration tests.

## Success Criteria
- Migrations execute without touching existing mutable tables.
- Student Statement renders via API, supports filters, and downloads ≥10k row CSV without timeout.
- QuickBooks export endpoint returns consistent response when repeated with the same key.
