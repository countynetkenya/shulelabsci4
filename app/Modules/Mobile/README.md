# Mobile Module

The Mobile module exposes services for Progressive Web App (PWA) and mobile
clients that need offline-first access to operational data. Offline snapshots
are signed and time-bound so that devices can safely operate without a network
connection while respecting tenant scoping and audit requirements.

## Capabilities

- Generate cryptographically signed offline snapshots with configurable TTLs.
- Verify snapshot authenticity and expiry before allowing synchronisation back
to the platform.
- Emit immutable audit events whenever snapshots are issued or verified to keep
ops teams informed about mobile usage patterns.

## Key Services

- `Services\OfflineSnapshotService` – issues and verifies signed snapshot
  payloads using an HMAC signature, manages key rotation, and records audit
  events.
- `Domain\Snapshot` – value object that stores the dataset, validity window, and
  cryptographic metadata required by offline clients.

Future updates will add diff-based delta snapshots and device registration
checks for deeper endpoint hardening.
