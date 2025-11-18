# Finance Module (CI4)

This module introduces CI4-native finance primitives aligned with the 2025–2026 roadmap. It exposes `/finance` endpoints for
issuing and settling invoices while recording append-only ledger entries and immutable audit events.

## HTTP Endpoints
- `POST /finance/invoices` — issue a new invoice and journal balanced ledger entries.
- `POST /finance/invoices/{number}/settle` — settle an issued invoice, clearing the receivable balance.
- `GET /finance/ping` — lightweight readiness probe for monitoring and smoke checks.

## Domain Overview
- `Domain/Invoice.php` encapsulates invoice state, ledger transaction identifiers, and settlement metadata.
- `Services/InvoiceService.php` orchestrates validation, ledger posting, and audit logging for invoices.
- `Services/InvoiceRepositoryInterface.php` allows persistence adapters; an in-memory implementation is provided for tests and
API stubs.

The service wiring defaults to the foundational ledger and audit services so CI4 deployments inherit the append-only guarantees
and maker-checker posture defined in the implementation plan.
