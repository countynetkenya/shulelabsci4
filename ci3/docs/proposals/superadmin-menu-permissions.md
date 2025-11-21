# Super Admin Menu & Permission Recommendations

## 1. Normalize Menu Definitions
- Consolidate sidebar configuration into a single source of truth (e.g., `config/sidebar.php`) that enumerates each menu item with:
  - `slug`, `route`, `label_key`, `permission`, `feature_flag`, and `children` metadata.
  - `visibility` rules (e.g., `superadmin_only`, `school_scope`, `legacy`).
- Replace duplicated arrays in `admin_sidebar_pages.php`, migrations, and seeders with helper functions that read from the config and build the correct structures for each context.

**Benefits**
- Eliminates drift between sidebar config, menu overrides, migrations, and smoke tests.
- Enables automated scripts to iterate over the config and validate that every declared view has a menu entry.

## 2. Permission-driven Rendering
- Introduce a `MenuAuthorizer` helper that accepts the normalized menu config and the current user’s permission set, returning only allowed links.
- For super admins, surface *all* items by default, but retain the ability to hide experimental features behind feature flags or environment toggles.
- For school-scoped admins, pass the filtered list so they only see items they can access.

**Benefits**
- Stops hard-coded `if (permissionChecker(...))` checks scattered across views.
- Reduces regressions when permissions are renamed or refactored.

## 3. Menu Override Lifecycle
- Update the `menu_overrides` migration and seeder to diff against the canonical menu config:
  - Insert missing entries.
  - Update links whose route changed.
  - Soft-delete obsolete rows, keeping an audit trail.
- Provide an artisan/CLI command (e.g., `php index.php tools/sync_sidebar`) to rerun the sync without redeploying migrations.

**Benefits**
- Guarantees the database mirror of the sidebar stays aligned with code.
- Simplifies smoke tests and production debugging.

## 4. Auto-register New Views
- Add a CI check (or local script) that scans `mvc/views/` for top-level modules and ensures each has a corresponding menu entry in the normalized config.
- Allow opt-outs via annotations (e.g., `@menu-exempt`) for partials or modal-only views.

**Benefits**
- Prevents newly built screens from being unreachable because they are missing from the sidebar.

## 5. Super Admin “All Access” Overlay
- Layer a super-admin specific menu section at the top of the sidebar/dashboard with high-priority links:
  - Global settings
  - CMS management (posts, pages, menus)
  - Integrations (email settings, backups, social links)
  - System utilities (migration status, error logs)
- Use the normalized config to tag these links (`superadmin_featured: true`) so the dashboard can highlight them without duplicating URLs.

**Benefits**
- Maintains a curated experience for super admins while reusing the same route metadata.

## 6. Permission Auditing Tooling
- Build an admin-only report (or CLI) that lists every permission, the routes/views that rely on it, and which user roles currently possess it.
- Integrate with the menu config to flag menu items that reference undefined permissions or roles.

**Benefits**
- Exposes orphaned permissions and misconfigured roles early.
- Supports governance and compliance reviews.

## 7. Documentation & Onboarding
- Document the new menu workflow in `docs/architecture/sidebar.md`, covering:
  - How to register a new menu item.
  - How to associate permissions.
  - How to run the sync and smoke scripts.
- Provide examples for common patterns (single link, nested menus, feature-flagged links).

**Benefits**
- Reduces ramp-up time for new developers.
- Ensures consistent implementations across teams.

---

Adopting these steps will keep the super admin menus comprehensive, permission-aware, and easier to evolve while preventing regressions in other roles’ navigation.
