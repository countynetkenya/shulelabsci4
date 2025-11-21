# Threads Module

The Threads module delivers the event-driven collaboration backbone powering CFR
(Conversations, Feedback, Recognition) and operational notifications.

## Capabilities

- Create contextualised threads linked to recognitions, OKRs, or operational
  artefacts.
- Post messages while maintaining immutable audit trails.
- Broadcast lifecycle events (thread created, message posted) via the shared
  `EventBus` so downstream services can react in real time.

## Key Services

- `Services\ThreadService` – core orchestration for creating and updating
  threads.
- `Services\EventBus` – synchronous publisher for dispatching engagement events
  to subscribers such as Gamification or digest jobs.
- `Services\ThreadRepositoryInterface` – persistence contract with an in-memory
  implementation for tests.

## Routing

- `GET /v2/threads` – list threads with optional filtering by context.
- `POST /v2/threads` – create a thread (with optional initial message).
- `POST /v2/threads/{id}/messages` – append a message to an existing thread.

Bind `threadsRepository` and `threadsEventBus` to production-ready services to
persist and dispatch events at scale.
