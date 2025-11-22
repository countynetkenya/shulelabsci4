# Inventory Module

The Inventory module introduces QR-enabled warehouse transfers on CI4. Transfers
are created with maker-checker approvals, verifiable QR labels, and append-only
audit logging.

## Capabilities

- Create inter-warehouse transfer requests with per-item quantities.
- Issue QR codes for each transfer so receiving teams can scan and verify the
  payload prior to acknowledging receipt.
- Enforce maker-checker approvals for every transfer before items move.
- Record transfer lifecycle events (initiation, completion) in the immutable
audit log via the shared `AuditService`.

## Key Services

- `Services\TransferService` – orchestrates transfer initiation and completion
  while integrating with QR issuance, audit logging, and approvals.
- `Services\TransferRepositoryInterface` – persistence contract for transfer
  aggregates. An in-memory implementation ships for demos/tests.
- `Domain\Transfer` – value object representing the transfer aggregate.

## Routing

Routes are exposed under `/inventory`:

- `POST /inventory/transfers` – create a transfer.
- `POST /inventory/transfers/{id}/complete` – accept or reject a transfer
  after scanning.

Integrators should bind `inventoryTransferRepository` in the service container
with a database-backed implementation for production deployments.
