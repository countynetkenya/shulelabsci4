# Sprint 1 Plan – Foundation & Tooling

## Scope
- Refresh Composer manifest + lock to include JWT/UUID/Guzzle/Swagger requirements and wire PSR-4 autoload via `index.php`.
- Finalize environment bootstrap: `.env` loader, centralized `shulelabs` config (feature flags, security toggles, external secrets).
- Implement JWT service with leeway handling and guard hook controllable via config.
- Deliver CLI `Migrate` controller with `latest|version|status`, enable timestamped migrations, and add infrastructure tables (`idempotency_keys`, `audit_events`).
- Provide OpenAPI generation workflow (`composer openapi:build`) and ensure payment contract annotations match expected payload shape.
- Author DEV setup and sprint docs to capture usage/testing instructions.

## File Inventory (≤20)
- `composer.json`, `composer.lock`
- `.env.example`
- `mvc/config/{shulelabs.php,hooks.php}`
- `mvc/hooks/JwtMiddleware.php`
- `mvc/libraries/Jwt_service.php`
- `mvc/controllers/Migrate.php`
- `mvc/controllers/api/v10/Payment.php`
- `mvc/migrations/20240501090000_create_idempotency_keys.php`
- `mvc/migrations/20240501090010_create_audit_events.php`
- `docs/sprints/S1-PLAN.md`, `docs/sprints/S1-SPRINT_REPORT.md`
- `docs/DEV_SETUP.md`
- `ci4/docs/openapi/{base.php,sprint1.php}`
- `ci4/docs/openapi.yaml`

## Acceptance Checklist
- `composer install` succeeds with new dependencies; autoloader active in `index.php` (pre-existing wiring verified).
- `.env` loader exposes secrets + guard flag; centralized config surfaces JWT leeway + feature toggles.
- JWT middleware enforces bearer tokens only when `API_JWT_GUARD_ENABLED=true`.
- CLI `php index.php migrate status|latest|version <ts>` operates without fatal errors; migrations create/drop both tables cleanly.
- `composer openapi:build` produces `ci4/docs/openapi.yaml` containing payment endpoint schemas.
- DEV setup doc explains dependency install, OpenAPI build, and migration usage.
