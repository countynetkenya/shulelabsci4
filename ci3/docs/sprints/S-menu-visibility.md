# Sprint Note – Admin Sidebar Visibility Refresh

## Phase 0 – Environment Discovery

### Application environment & database
- `index.php` bootstraps `.env` via `mvc/config/env.php`, defaults to `development` unless the request originates outside the VPN ranges; production traffic sets `ENVIRONMENT=production`.
- Active database group resolves through `mvc/config/database.php`, sourcing credentials from `ENV` (or `mvc/config/production/database.php` when flags missing). The live DSN targets the `shulelabs_staging` schema with no table prefix configured.

### Migration configuration
- Migrations are enabled and configured for timestamped filenames with the canonical path `FCPATH.'mvc/migrations/'`. The migration table remains `migrations` and auto-run stays disabled so deployments advance versions explicitly.

### Feature flag loader
- `mvc/config/shulelabs.php` is autoloaded during bootstrap and exposes `UNIFIED_STATEMENT`, `PAYROLL_V2`, `PERMISSIONS_V1`, `OKR_V1`, and `CFR_V1` under `$this->config['shulelabs']['feature_flags']`. Each flag defers to `FLAG_*` entries in `.env`, defaulting to `true` for `UNIFIED_STATEMENT` and `false` for the others when unset.

### Sidebar & routing pipeline
1. `mvc/config/sidebar.php` is the canonical menu registry; context-specific adapters (such as `admin_sidebar_pages.php`) filter the definition for runtime use.
2. Seeder `20240701000030_seed_sidebar_pages.php` and the `tools/sidebar/sync` CLI republish managed entries into `menu_overrides`, update legacy `admin/...` links, and retire stale rows tagged with `managed_by = sidebar_config`.
3. `mvc/config/routes.php` iterates the filtered page map to register prefixless URIs (for example `okr`, `cfr`, `finance/statement`, and `payroll`) when controllers and methods exist, caching menu trees per session under `dbMenus`.

### Controller inventory prior to sprint
- `Okr` controller already exists (flag gated).
- `Cfr`, `FinanceStatement`, and `Payroll` controllers were absent, so sidebar seeding deferred to future deployments and routes were skipped at runtime.

## Implementation Summary
- Normalised `mvc/config/migration.php` to the canonical timestamp configuration expected by deploy tooling.
- Regenerated the early OKR and menu override migrations so they use instance methods and idempotent index creation compatible with PHP 8.
- Introduced shared database config shims in `application/config/` so CLI utilities resolve `mvc/config/database.php` regardless of entry point.
- Added production-specific logging config to raise log verbosity during the stabilization sweep.
- Delivered feature-gated admin controllers (`Cfr`, `FinanceStatement`, `Payroll`) with placeholder views that render inside `_layout_main`.
- Ensured `cfr`, `finance/statement`, and `payroll` map explicitly even if dynamic registration is bypassed.
- Authored `scripts/smoke_menu_visibility.php` to verify flags, controller presence, menu overrides, and route registration.

## Verification & Diagnostics
Commands executed locally:
- `php -l` over `mvc` and `application` trees (syntax safety sweep).
- `php index.php migrate/status` and `php index.php migrate/latest` (no pending migrations reported).
- `php scripts/smoke_menu_visibility.php` (post-feature smoke validation).

## Operations Notes
1. Toggle features by exporting in `/var/www/besha/.env`:
   ```bash
   FLAG_OKR_V1=true
   FLAG_CFR_V1=true
   FLAG_UNIFIED_STATEMENT=true
   FLAG_PAYROLL_V2=true
   FLAG_PERMISSIONS_V1=true
   ```
2. Re-run the sidebar seeder when deploying fresh environments:
   ```bash
   php index.php migrate version 20240701000030
   php index.php migrate latest
   ```
3. Clear cached menus after flag or seed changes by removing `dbMenus` from the session store (log out/in or truncate `ci_sessions`).
4. Review `application/logs/` after enabling the modules; the elevated log threshold in production captures errors, info, and debug rows during the sweep.

## Outstanding Follow-ups
- Backfill translations for the new placeholders (`cfr_placeholder_copy`, `finance_statement_placeholder`, `payroll_placeholder_copy`).
- Replace stub views with production-ready modules once feature work completes.
