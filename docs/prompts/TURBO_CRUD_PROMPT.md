# 🚀 Turbo CRUD Orchestration Prompt v3.1 (Comprehensive)

**Trigger Command:**
`@Copilot ACTIVATE TURBO CRUD v3 FOR MODULES: Inventory, Library, POS, Scheduler`

## Context & Persona
- **Role:** ShuleLabs CI4 Lead Architect & UI-First Developer.
- **Philosophy:** "The Interface IS the Specification."
- **Architecture:** Modular HMVC (`app/Modules/{Module}`).
- **Constraint:** All data is tenant-scoped by `school_id`.
- **Security:** Role-Based Access Control (RBAC) is mandatory.

## ⚠️ Critical Pre-Flight Checks (Do These FIRST)

### Route File Format (MANDATORY)
All module `Routes.php` files **MUST** use this class-based pattern:
```php
<?php

namespace Modules\{Module}\Config;

use CodeIgniter\Router\RouteCollection;

class Routes
{
    public static function map(RouteCollection $routes): void
    {
        // routes here
    }
}
```
**NEVER** use bare `$routes->group()` at file level - it breaks PHPUnit!

### Model Limitations
- CI4 Model does NOT have `distinct()` method
- Use: `->select('DISTINCT column as alias', false)` instead

### View Layout
- All views should extend `layouts/main` (NOT `layouts/app`)
- Check existing modules if unsure

### SQLite Migrations
- SQLite does NOT support `ALTER TABLE DROP COLUMN`
- Use table recreation pattern for column modifications

## Execution Protocol (Per Module)

**Complete one module fully before moving to the next.**

### 1. 📝 Spec & Schema Sync (The Foundation)
- **Read:** `docs/specs/{XX}-{MODULE}_SPEC.md` and `app/Modules/{Module}/Database/Migrations/`.
- **Verify:** Does the migration match the spec? If not, trust the **Migration** as the current reality, but **Update the Spec** to match the code.
- **Output:** List the table columns you are building for.

### 2. 🧠 Service Layer (The Logic)
- **File:** `app/Modules/{Module}/Services/{Module}Service.php`
- **Dependencies:** Inject `AuditService` (from Foundation) if possible.
- **Methods:** `getAll($schoolId)`, `getById($id, $schoolId)`, `create(array $data)`, `update($id, array $data)`, `delete($id)`.
- **Constraint:** EVERY query must include `where('school_id', $schoolId)`.
- **Audit:** Log critical actions (`create`, `update`, `delete`) if `AuditService` is available.

### 3. 🎮 Web Controller (The Traffic Cop)
- **File:** `app/Modules/{Module}/Controllers/Web/{Module}Controller.php`
- **Parent:** `extends \App\Controllers\BaseController` (or `AdminController` if available).
- **Security:** 
  - Add `if (! session()->get('is_admin') && ! service('auth')->can('{module}.view')) return redirect()->to('/login');`
- **Handling Conflicts:** If the file exists and `create_file` fails, use `run_in_terminal` with `rm {path}` to delete it first, then recreate it.
- **Methods:** `index`, `create`, `store`, `edit`, `update`, `delete`.
- **Validation:** Use `$this->validate([])` with strict rules.

### 4. 🎨 UI Implementation (The Face)
- **Location:** `app/Modules/{Module}/Views/` (Keep views inside the module for portability).
- **Layout:** `<?= $this->extend('layouts/main') ?>` - NOT `layouts/app`!
- **Files:**
  1.  `index.php`: Data table with "Edit" and "Delete" buttons. Handle empty states (e.g., "No items found").
  2.  `create.php`: Form with CSRF token `<?= csrf_field() ?>` and validation errors.
  3.  `edit.php`: Pre-filled form.
- **Style:** Bootstrap 4 (SB Admin 2 compatible).
- **UX:** Add a "Back" button on forms. Use Bootstrap 4 badges for status fields.

### 5. 🧭 Navigation (The Missing Link)
- **File:** `app/Views/components/sidebar.php`
- **Action:** Use `replace_string_in_file` to inject the list item.
- **Pattern:** Find the last module link and append after it:
  ```php
  <li style="padding: 10px 20px;">
      <a href="<?= base_url('{module}') ?>" style="color: #fff; text-decoration: none; display: block; padding: 10px; border-radius: 5px;">
          <i class="fa fa-{icon}"></i> {Module Name}
      </a>
  </li>
  ```

### 6. 🌱 Seeding (Instant Life)
- **File:** `app/Modules/{Module}/Database/Seeds/{Module}Seeder.php`
- **Action:** Create a seeder that inserts 5 realistic dummy records (scoped to a test school_id).
- **Run:** `php spark db:seed App\Modules\{Module}\Database\Seeds\{Module}Seeder`

### 7. 🛣️ Routes (Class-Based Format!)
- **File:** `app/Modules/{Module}/Config/Routes.php`
- **Format:** MUST be class-based (see Critical Pre-Flight Checks above)
```php
<?php

namespace Modules\{Module}\Config;

use CodeIgniter\Router\RouteCollection;

class Routes
{
    public static function map(RouteCollection $routes): void
    {
        $routes->group('{module_slug}', ['namespace' => 'App\Modules\{Module}\Controllers\Web'], static function($routes) {
            $routes->get('/', '{Module}Controller::index');
            $routes->get('create', '{Module}Controller::create');
            $routes->post('store', '{Module}Controller::store');
            $routes->get('edit/(:num)', '{Module}Controller::edit/$1');
            $routes->post('update/(:num)', '{Module}Controller::update/$1');
            $routes->get('delete/(:num)', '{Module}Controller::delete/$1');
        });
    }
}
```

### 8. 🧪 Feature Tests
- **File:** `tests/Feature/{Module}/{Module}CrudTest.php`
- **Traits:** Use `TenantTestTrait` for authentication context
- **Setup:** Call `$this->setupTenantContext()` in `setUp()`
- **Session:** Use `$this->withSession($this->getAdminSession())` for authenticated requests

### 9. 📚 Documentation
- **Action:** Update `docs/reports/LATEST_STATUS.md`.
- **Action:** Check off items in `docs/specs/{XX}-{MODULE}_SPEC.md`.

## Module Specifics

### A. Inventory ✅ (Completed)
- **Focus:** Items, Categories, Stock Levels.
- **Key Fields:** `name`, `sku`, `quantity`, `reorder_level`.

### B. Library ✅ (Completed)
- **Focus:** Books, Authors, ISBN.
- **Key Fields:** `title`, `author`, `isbn`, `category`, `total_copies`, `available_copies`.

### C. POS (Point of Sale)
- **Focus:** Sales, Cart (Session-based or DB), Transactions.
- **Dependency:** Uses Inventory items.

### D. Scheduler
- **Focus:** Background Jobs, Cron Expressions.
- **Key Fields:** `name`, `command`, `schedule`, `last_run`, `status`.

---

## Common Pitfalls to Avoid

| Pitfall | Solution |
|---------|----------|
| `Undefined variable $routes` in tests | Use class-based Routes.php format |
| `distinct()` method not found | Use `select('DISTINCT col', false)` |
| Views not rendering | Check layout extends `layouts/main` |
| Tests failing with 403 | Ensure TenantTestTrait is used |
| SQLite migration errors | Avoid DROP COLUMN, use table recreation |
| Namespace mismatch in routes | Match import statement in app/Config/Routes.php |

---
**Next Module: POS**
