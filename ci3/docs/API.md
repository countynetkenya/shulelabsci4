# API Surface – Foundation Runtime

The CI4 `/v2` runtime exposes foundational endpoints and service contracts required by downstream modules.

## Health Check

`GET /v2/foundation/health`

- Returns a JSON payload containing the application version, database connectivity status, Redis reachability, and audit hash-chain status.
- Use this endpoint for container liveness probes and smoke tests.

Example response:

```json
{
  "status": "ok",
  "timestamp": "2024-10-06T12:00:00Z",
  "database": true,
  "redis": true,
  "audit_chain_intact": true
}
```

## Audit Service

- `AuditService::recordEvent(string $eventKey, string $eventType, array $context, ?array $before, ?array $after, array $metadata = [])`
- `AuditService::sealDay(?Time $day = null)` – triggers daily hash sealing.
- `AuditService::verifyIntegrity()` – returns `true` when the hash chain is intact.

## Ledger Service

- `LedgerService::commitTransaction(string $transactionKey, array $entries, array $context, array $metadata = [])`
  - `entries` must contain balanced debit/credit movements with a minimum scale of 4 decimal places.
- `LedgerService::scheduleReversal(int $transactionId, array $context, string $reason)` creates controlled reversal entries.

## Maker-Checker Workflows

- `MakerCheckerService::submit(string $actionKey, array $payload, array $context)`
- `MakerCheckerService::approve(int $requestId, array $context)`
- `MakerCheckerService::reject(int $requestId, array $context, string $reason)`

## QR Verification

- `QrService::issueToken(string $resourceType, string $resourceId, array $context, ?int $ttlSeconds = null)` returns the token, PNG bytes, and verification URL.
- `QrService::verify(string $token, array $context)` validates the token and records a scan event.

## Tenant Resolution

- `TenantResolver::fromRequest(IncomingRequest $request)` resolves tenant context from headers/query parameters.
- `TenantResolver::fromIdentifiers(array $ids)` loads tenant metadata from `tenant_catalog`.

These services underpin every subsequent roadmap module. Extend them cautiously and accompany changes with unit and integration tests.

## HR Payroll

- `POST /v2/hr/payroll/payslips` – generates a payslip using the active country template (Kenya by default), records an audit event, and opens a maker-checker approval. Requires `employee_id`, `employee_name`, `period`, `payout_date`, and `base_salary` in the JSON body.
- `GET /v2/hr/payroll/approvals` – returns a rendered HTML dashboard summarising pending payroll approvals for the finance/HR teams.
- `GET /v2/hr/payroll/approvals/pending` – JSON summary of pending approvals grouped by tenant, including statutorily calculated breakdowns.
- `POST /v2/hr/payroll/approvals/{id}/approve` and `POST /v2/hr/payroll/approvals/{id}/reject` – closes maker-checker requests with the supplied actor context; rejection requires a JSON body containing a `reason` field.

## Learning – Moodle Synchronisation

- `POST /v2/learning/moodle/grades` – accepts a `course` object and `grades[]` array, registers an idempotent dispatch in the integration registry, calls the configured Moodle client, and returns the downstream response with HTTP 202.
- `POST /v2/learning/moodle/enrollments` – mirrors the grade flow for enrolment updates and surfaces the Moodle response.
- Scheduled runners (`moodle-grade-sync`, `moodle-enrollment-sync`) replay pending dispatches via the integration registry, ensuring retries respect idempotency and failure telemetry.

## Mobile Offline Snapshots

- `POST /v2/mobile/snapshots` – issues a signed offline dataset for mobile/PWA clients. The JSON body must supply a `dataset` object, optional `ttl_seconds`, and optional `version`. Returns the full snapshot envelope (ID, checksum, signature, expiry).
- `POST /v2/mobile/snapshots/verify` – accepts a snapshot envelope and validates signature, expiry, and tenant ownership using the configured signing keys. Returns `{ "verified": true|false }`.
- `GET /v2/mobile/telemetry/snapshots?hours=24` – aggregates recent audit events into issued/verified/failure counts per tenant along with the latest failure details to guide operational monitoring.
