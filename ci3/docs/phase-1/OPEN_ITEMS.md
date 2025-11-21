# Phase 1 Open Items Log

| ID | Question / Decision | Owner | Target Resolution | Status | Resolution |
| --- | --- | --- | --- | --- | --- |
| FI-001 | Determine QR embedding strategy for POS receipts (reuse QR service vs inline generation). | Product + Design | Week 1 discovery readout | Resolved | Reuse shared QR service so receipts reference ledger transactions and existing `/verify` flows. |
| FI-002 | Confirm multi-currency wallet requirements for launch scope. | Finance | Week 1 finance workshop | Resolved | Launch with single-currency wallets per tenant; capture currency metadata for future expansion. |
| FI-003 | Validate compliance-driven invoice numbering constraints (CRA/KRA, etc.). | Legal + Finance | Week 2 before migration finalisation | Resolved | Adopt per-tenant legal prefixes with audit log retention; documented in finance runbooks. |

Update this table as decisions are made; link supporting documents or tickets in an additional column if needed.
