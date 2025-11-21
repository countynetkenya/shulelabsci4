# Learning Module

The Learning module provides CI4 services for synchronising courses and grades
with external LMS platforms. The initial integration targets Moodle and
emphasises idempotent sync jobs, webhook-safe retries, and immutable audit
records.

## Capabilities

- Register idempotent dispatches before invoking LMS APIs to guarantee safe
  retries and traceability.
- Push grade updates and enrolment changes to Moodle via the
  `MoodleSyncService`, which records audit events and handles success/failure
  callbacks in the integration registry.
- Surface detailed metadata to downstream workers so they can replay payloads or
  investigate failures without re-computing data extracts.

## Key Services

- `Services\MoodleSyncService` – orchestrates Moodle API calls, leverages the
  integration registry for idempotency, and captures audit events.
- `Services\MoodleClientInterface` – abstraction around the underlying HTTP
  client, enabling test doubles and future connectors (Canvas, Teams, etc.).

Additional connectors can implement `MoodleClientInterface` or a sibling
interface and be registered in the service container while reusing the same
integration workflow.
