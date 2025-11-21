# Sprint 4 Plan – People Ops Collaboration & Recognition

## Overview
Sprint 4 focuses on deepening People Operations collaboration tools and tightening recognition-driven engagement. We are expanding the conversation threads domain to support richer context, introducing a unifying event dispatch service, and documenting external surfaces that unlock feedback, recognition, and digest automation.

## Goals
- Enrich conversation threads with contextual metadata that links discussions back to recognitions and organisational artefacts.
- Standardise event propagation so recognition flows, CFR (Conversation, Feedback, Recognition) activity, and gamification milestones remain in sync.
- Expose clear REST endpoints for threads, feedback sentiment, recognition issuance, and digest summaries so the frontend and integrations can ship in parallel.
- Establish retention and privacy policies for historical CFR data while keeping OKR records intact for reporting.

## Scope & Deliverables
1. **Threads Domain Enhancements**
   - Extend the `threads` model to capture `context_type`, `context_id`, and `is_cfr` flags so conversations can be anchored to recognitions, OKRs, or external resources.
   - Introduce a `recognition_events` table for storing immutable recognition lifecycle activity tied back to the originating thread.
   - Document migration ordering and backfill approach for legacy rows lacking contextual metadata.
2. **Event Service & Recognition Flow**
   - Build a central `Event_service` responsible for dispatching domain events and managing subscriber registration.
   - Emit a `Recognition.Awarded` event from the `Cfr_service` whenever a recognition is granted or modified.
   - Create a `Gamification_listener` subscriber that reacts to `Recognition.Awarded` to award points, badges, or streak updates.
   - Outline testing strategy for the dispatcher (unit coverage of fan-out and failure isolation).
3. **Public API Surfaces**
   - Add REST endpoints for:
     - `GET /threads` and `POST /threads` with filtering by `context_type`, `context_id`, and CFR flag.
     - `POST /feedback` capturing qualitative feedback plus sentiment analysis payload (score, magnitude, provider metadata).
     - `POST /recognition` and `GET /recognition/{id}` covering lifecycle events and links to award artefacts.
     - `GET /summary` delivering aggregated recognition + feedback stats for dashboards.
     - `GET /digest` returning weekly digest payloads for notification jobs.
   - Update OpenAPI documentation once payload contracts are finalised, including error states for sentiment enrichment failures.
4. **Automation & Jobs**
   - Schedule cron tasks for `weekly_digest` generation and `sync_gamification` reconciliation so recognition metrics stay aligned with gamification leaderboards.
   - Capture retry/backoff strategy and logging requirements for both jobs.
5. **Data Retention & Compliance**
   - Enforce anonymisation of CFR `actor_id` values after two years while retaining aggregate statistics.
   - Clarify that OKR logs remain fully preserved for auditability even when CFR data is anonymised.
   - Define migration/backfill scripts plus privacy impact assessment checkpoints.

## Timeline & Milestones
| Week | Deliverable |
| ---- | ----------- |
| 1 | Threads schema migration, recognition events table, and migration playbook validated in staging. |
| 2 | Event service dispatcher with CFR → Recognition.Awarded wiring and gamification listener integration tests. |
| 3 | Threads, feedback, recognition, summary, and digest API endpoints with OpenAPI drafts. |
| 4 | Cron automation for weekly digest & gamification sync, retention policy enforcement, and documentation sign-off. |

## Risks & Mitigations
- **Sentiment analysis dependency drift**: Provide fallbacks when external NLP services degrade; surface warning codes to callers.
- **Event dispatch overload**: Introduce queue backpressure and retry logic in the `Event_service` to isolate slow subscribers.
- **Data privacy regressions**: Add automated checks ensuring anonymisation jobs exclude OKR logs and only target CFR actor identifiers.
- **Digest delivery latency**: Pre-compute summaries and cache recognition statistics to keep cron executions under the SLA.

## Success Criteria
- Threads API supports filtering by contextual metadata and correctly flags CFR-linked discussions.
- Recognition events trigger gamification updates within the same processing cycle via the new dispatcher.
- Feedback sentiment payloads are persisted and surfaced in summary/digest responses.
- Automated retention job anonymises CFR actor IDs older than two years without impacting OKR reporting extracts.
