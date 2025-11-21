# Sidebar and Super Admin Navigation

> **Roadmap reference:** For the full 2025–2026 delivery strategy, see [`docs/SHULELABS_IMPLEMENTATION_PLAN.md`](../SHULELABS_IMPLEMENTATION_PLAN.md). This sidebar guide assumes modules follow the CI4 migration approach outlined there.

This document explains how the unified sidebar configuration and super admin navigation tooling works.

## Canonical configuration

All menu metadata lives in [`mvc/config/sidebar.php`](../../mvc/config/sidebar.php). The file exports a single array with an `items` key. Each item includes:

- `name`, `menu_name`, and `menu_label_key` for display.
- `link`/`route`, `controller`, and `method` for routing.
- `contexts` describing where the entry should surface (`admin_sidebar`, `superadmin_dashboard`, etc.).
- Optional metadata for feature flags, permissions, icons, priorities, and super-admin highlighting.
- `sync_managed` to opt into automated database synchronisation and CLI tooling.

### Adding a new entry

1. Add a new entry to `sidebar.php` and populate the relevant fields.
2. Include `contexts` so the item renders in the correct surfaces.
3. Set `sync_managed` to `true` if the record should be mirrored into `menu_overrides`.
4. Run the sidebar sync CLI (`php index.php tools/sidebar/sync`) to publish the change.

## Rendering and authorisation

The [`SidebarRegistry`](../../mvc/libraries/SidebarRegistry.php) helper exposes convenience methods to fetch items by context, super-admin featured entries, and syncable records. Rendering code should ask the registry for a context-specific list and pass it through [`MenuAuthorizer`](../../mvc/libraries/MenuAuthorizer.php) to enforce feature-flag and permission checks before displaying links.

## Database synchronisation

Migrations and seeders rely on the shared configuration to insert or update rows in the `menu_overrides` table. When a link is renamed, the sync process automatically rewrites matching legacy `admin/...` links and deactivates retired entries.

- `php index.php tools/sidebar/sync` replays the sync logic and logs create/update/retire operations.
- The CLI command and migration only touch rows stamped with `managed_by = sidebar_config` metadata to avoid clobbering manual overrides.

## Auditing utilities

Two additional CLI helpers make it easier to keep menus healthy:

- `php index.php tools/sidebar/audit` validates that each configured item points at an existing controller/method and that the referenced permission exists in the database.
- `php index.php tools/sidebar/checkviews` scans the top-level view directories and reports folders without a matching sidebar entry (skipping layout/partial directories). Use this command in CI to catch orphaned modules.

## Smoke test

`scripts/smoke_menu_visibility.php` now reads from the shared configuration, ensuring automated smoke checks stay in sync with the menu definition. The script validates controller availability, explicit route mappings, and seeded `menu_overrides` links for every sync-managed item.

## Super admin dashboard

The super admin dashboard controller and view consume `SidebarRegistry::featuredSuperadminItems()` so the landing page automatically reflects the latest quick links without duplicating configuration. Highlight new high-priority admin utilities by setting `superadmin_featured` to `true` in the config entry.
