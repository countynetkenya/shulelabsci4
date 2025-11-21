# Sprint 1 Report – Foundation & Tooling

## Completed
- Captured sprint objectives in the legacy `S1-PLAN.md` (now archived under `docs/archive/legacy-sprint-plans/`) and documented setup steps in `docs/DEV_SETUP.md`.
- Synced Composer manifest/lock with JWT, UUID, Guzzle, and Swagger dependencies; verified autoload bootstrap in `index.php`.
- Hardened env bootstrap plus centralized `shulelabs` config with feature flags, JWT leeway, and guard toggle sourced from `.env`.
- Implemented `Jwt_service` leeway support and `JwtMiddleware` guard honoring the `API_JWT_GUARD_ENABLED` flag.
- Delivered CLI-only `Migrate` controller supporting `latest`, `version`, and `status` commands with consistent error handling.
- Authored timestamped migrations for `idempotency_keys` and `audit_events` with reversible `down()` definitions.
- Added top-level API docs scaffold plus dedicated Sprint 1 annotation stub, refreshed payment Swagger for `save_payment` student item arrays, and regenerated `ci4/docs/openapi.yaml` via `composer openapi:build`.

## Pending / Risks
- Database credentials unavailable in container, so migrations were not executed; see DEV setup for manual verification commands.
- JWT guard currently enforces only when enabled; future sprints should align per-module access control with `PERMISSIONS_V1` flag rollout.
- OpenAPI generation currently scans `mvc/controllers/api` only; expand coverage once remaining modules gain Swagger annotations.
