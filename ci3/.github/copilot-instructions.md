# GitHub Copilot Instructions for ShuleLabs School OS

## Project Overview

ShuleLabs is a comprehensive School Management System (ERP) that handles billing, inventory, payroll, QuickBooks integration, and student/teacher portals. The system is being rewritten in CodeIgniter 4 as a standalone application, with CI3 serving as a reference implementation.

### Tech Stack

- **Backend:** PHP 8.3 with CodeIgniter 4 (primary) and CodeIgniter 3 (reference only)
- **Database:** MariaDB/MySQL with immutable audit trails (shared between CI3 and CI4)
- **Frontend:** Traditional server-side rendering with jQuery, progressive enhancement
- **Payment Integrations:** M-PESA, Stripe, PayPal (via Omnipay)
- **Third-party APIs:** QuickBooks, Google Drive, SMS gateways
- **Deployment:** Docker containers with nginx, PHP-FPM, Redis, MariaDB
- **Testing:** PHPUnit 10.5+
- **Static Analysis:** PHPStan (level 5+), PHP_CodeSniffer

## Architecture

### Standalone CI4 Application

**CI4 (Active Development - Standalone):**
- Entry point: `public/index.php`
- Application root: `ci4/app/`
- Foundation modules: `ci4/app/Modules/Foundation/`
- Modular HMVC structure under `ci4/app/Modules/`
- Database: Shared with CI3 (read-only reference)
- Sessions: Managed independently by CI4
- Run migrations with: `php spark migrate --all`
- Development server: `php spark serve`

**CI3 (Reference Only - No Active Development):**
- Legacy codebase: `mvc/`, `application/`
- Used only as reference for implementing features in CI4
- Database: Same database as CI4 (for migration purposes)
- **Do not add new features or fix bugs in CI3**
- **Do not create dual-runtime code or shared sessions**
- Only consult CI3 code to understand business logic when porting to CI4

### Autoloading Strategy (CI4)

- PSR-4 namespaces: `App\`, `SchoolOS\`, `Config\`, `Modules\`
- CI4 uses standard Composer autoloading
- All new code must be CI4-compliant with proper namespaces
- Legacy CI3 classmap autoloading is not used in new development

## Coding Guidelines

### General Principles

1. **CI4 Only Development:** All new features and bug fixes go into CI4. CI3 is reference only.
2. **Security First:** All sensitive operations require audit logging, input validation, and CSRF protection.
3. **Database Compatibility:** CI4 uses the same database as CI3 during transition, so maintain schema compatibility.
4. **Database Integrity:** Use transactions, avoid hard deletes (use soft delete), maintain audit trails.
5. **Minimal Changes:** Make surgical, focused changes. Don't refactor working code unless fixing bugs or security issues.

### CI4 Conventions (All New Code)

- Controllers extend `BaseController` or resource controllers
- Models extend `CodeIgniter\Model` with defined properties
- Use namespaced classes: `namespace Modules\Foundation\Controllers;`
- Services registered in `Config\Services` for dependency injection
- Database queries via Query Builder or Entities
- Validation via validation rules arrays
- Use modular HMVC structure under `ci4/app/Modules/`
- Follow PSR-4 autoloading standards

### CI3 Reference (Read-Only)

**IMPORTANT: Do not modify CI3 code. It is reference only.**

When porting features from CI3 to CI4:
1. Review CI3 controller logic in `mvc/controllers/`
2. Review CI3 models in `mvc/models/`
3. Review CI3 views in `mvc/views/`
4. Understand the business logic and data flow
5. Reimplement in CI4 using modern patterns
6. Do not copy-paste CI3 code directly
7. Modernize the implementation (use entities, services, proper validation)

**CI3 patterns to avoid in CI4:**
- Direct `$this->db` usage (use models instead)
- Global functions and helpers (use services)
- Mixed concerns in controllers (use services for business logic)
- Inline SQL queries (use query builder)
- Form validation in controllers (use validation rules)

### Security Requirements

1. **Immutable Audit Trails**
   - Log all mutations through `Modules\Foundation\Services\AuditService`
   - Hash chaining with SHA-256 and daily seals
   - Never modify or delete audit records

2. **Soft Delete Policy**
   - Use `SoftDeleteManager` for deletions
   - Set `deleted_at`, `deleted_by`, `delete_reason`
   - Hard deletes are prohibited

3. **Maker-Checker Workflow**
   - Sensitive operations require dual approval via `MakerCheckerService`
   - Submit → Approve/Reject flow for governance

4. **Tenant Isolation**
   - Resolve tenant context via `TenantResolver`
   - Enforce hierarchical scoping (organisation → school → warehouse)
   - Prevent cross-tenant data leakage

5. **Input Validation**
   - Validate all user input server-side
   - Use CSRF tokens on all forms
   - Sanitize output to prevent XSS
   - Parameterize database queries to prevent SQL injection

## Project Structure

```
/
├── .github/               # GitHub workflows and configuration
├── application/           # CI3 application layer
│   ├── Services/          # Business logic services
│   └── Support/           # Support utilities
├── assets/                # Public assets (CSS, JS, images)
├── ci4/                   # CI4 runtime
│   └── app/
│       ├── Config/        # CI4 configuration
│       └── Modules/       # Modular HMVC structure
│           └── Foundation/ # Cross-cutting services
├── docs/                  # Project documentation
│   ├── API.md             # API documentation
│   ├── SECURITY.md        # Security controls
│   └── SHULELABS_IMPLEMENTATION_PLAN.md
├── mvc/                   # CI3 MVC structure
│   ├── controllers/       # CI3 controllers
│   ├── models/            # CI3 models (classmap)
│   ├── views/             # CI3 views
│   └── migrations/        # CI3 database migrations
├── public/                # Web-accessible files
├── scripts/               # Operational scripts
│   ├── backup/            # Backup and restore scripts
│   └── ci/                # CI/CD scripts
├── storage/               # Runtime storage
│   ├── backups/           # Database backups
│   └── restore-drill/     # Restore testing
└── tests/                 # Test suites
    └── ci4/               # CI4 foundation tests
```

## Development Workflow

### Setup

```bash
# Install dependencies
composer install

# Configure environment
cp .env.example .env
# Edit .env with DB credentials and secrets

# Run CI4 migrations
php spark migrate --all

# Start development server
php spark serve
# or use Docker (see below)
```

### Quality Checks

```bash
# Run all checks (lint + phpstan + tests)
composer ci

# Individual checks
composer lint        # PHP_CodeSniffer
composer phpstan     # Static analysis
composer test        # PHPUnit tests
```

### Database Migrations

**CI4 Migrations (All new migrations):**
```bash
# Create new migration
php spark make:migration AddNewTable

# Run migrations
php spark migrate --all

# Check migration status
php spark migrate:status

# Rollback last batch
php spark migrate:rollback

# Rollback specific version
php spark migrate:rollback -b <batch_number>
```

**Important:** 
- All new migrations must be CI4 migrations
- Maintain compatibility with existing database schema from CI3
- Test both up and down migrations
- Never modify existing CI3 migration files

### Docker Development

```bash
make up          # Start containers
make down        # Stop containers
make logs        # View logs
make bash        # Shell into app container
make reset-db    # Reset database
```

## Testing Strategy

- **Unit Tests:** Test individual methods/services in isolation
- **Integration Tests:** Test database interactions, API calls
- **Feature Tests:** Test complete user workflows
- **Location:** `tests/ci4/` for CI4 foundation tests
- **Run:** `composer test` or `vendor/bin/phpunit`
- **Coverage:** Aim for high coverage on business logic and services

## Common Patterns

### Creating New CI4 Modules

1. Create module directory: `ci4/app/Modules/YourModule/`
2. Add controller: `Controllers/YourController.php`
3. Add model: `Models/YourModel.php`
4. Add entities: `Entities/YourEntity.php` (recommended)
5. Add services: `Services/YourService.php` (for business logic)
6. Add migrations: `Database/Migrations/`
7. Register routes in `ci4/app/Config/Routes.php`
8. Add tests in `tests/ci4/YourModule/`

### Porting Features from CI3 to CI4

**Step-by-step process:**

1. **Analyze CI3 Implementation**
   - Read CI3 controller: `mvc/controllers/YourController.php`
   - Read CI3 model: `mvc/models/Your_model.php`
   - Read CI3 view: `mvc/views/your_view.php`
   - Document business rules and data flow

2. **Design CI4 Implementation**
   - Plan module structure under `ci4/app/Modules/`
   - Define entity classes for data objects
   - Design service layer for business logic
   - Plan RESTful routes if applicable

3. **Implement in CI4**
   - Create CI4 controller with proper namespace
   - Create CI4 model extending `CodeIgniter\Model`
   - Create entity class if needed
   - Implement service layer for complex logic
   - Create modern views using CI4 view syntax
   - Add proper validation rules
   - Implement error handling

4. **Test**
   - Write unit tests for models and services
   - Write feature tests for controllers
   - Test database operations
   - Verify business logic matches CI3 behavior

5. **Document**
   - Update API documentation if applicable
   - Add inline comments for complex logic
   - Update module README if exists

### Database Queries (CI4)

```php
// Using Query Builder
$builder = $this->db->table('users');
$users = $builder->select('*')
                 ->where('active', 1)
                 ->get()
                 ->getResultArray();

// Using Models (Preferred)
$userModel = new UserModel();
$users = $userModel->where('active', 1)->findAll();

// Using Entities (Recommended for complex objects)
$userModel = new UserModel();
$user = $userModel->find($userId); // Returns UserEntity
$user->email = 'new@email.com';
$userModel->save($user);

// Transactions
$this->db->transStart();
try {
    $userModel->save($userData);
    $profileModel->save($profileData);
    $this->db->transComplete();
    
    if ($this->db->transStatus() === false) {
        throw new \RuntimeException('Transaction failed');
    }
} catch (\Exception $e) {
    $this->db->transRollback();
    log_message('error', 'Transaction error: ' . $e->getMessage());
    throw $e;
}

// Bad: Direct database queries (avoid)
// $this->db->query("SELECT * FROM users WHERE id = $id"); // SQL injection risk!
```

### Audit Logging (CI4)

```php
use Modules\Foundation\Services\AuditService;

$audit = service('audit');
$audit->log([
    'event' => 'user.updated',
    'entity_type' => 'user',
    'entity_id' => $userId,
    'changes' => $changes,
    'user_id' => $currentUserId
]);
```

### Soft Delete (CI4)

```php
use Modules\Foundation\Services\SoftDeleteManager;

$softDelete = service('softDelete');
$softDelete->delete('users', $userId, [
    'deleted_by' => $currentUserId,
    'delete_reason' => 'User requested account closure'
]);
```

## Code Review Guidelines

This document describes how GitHub Copilot (or a similar AI assistant) should perform code reviews for this project. The goal is to deliver concise, actionable feedback that helps contributors merge high-quality changes quickly while respecting the team's existing workflows.

### Review Principles

1. **Understand the Context**
   - Read the pull request description, linked issues, and commit messages to understand the intention behind the changes.
   - Skim the modified files to capture the overall scope before diving into line-level comments.
   - Identify risk areas (security-sensitive code, business-critical flows, database migrations) before deep-diving.

2. **Prioritize High-Impact Feedback**
   - Focus on issues that affect correctness, security, performance, maintainability, and user-facing behaviour.
   - Highlight regressions or breaking changes with clear explanations of the potential impact.
   - Point out deviations from project conventions or missing test coverage when they materially affect confidence in the change.

3. **Be Precise and Actionable**
   - Reference exact files and line numbers when pointing out issues.
   - Suggest specific fixes or alternatives whenever possible and mention relevant project utilities or helper functions.
   - When flagging uncertainties, explain the assumptions and invite clarification from the author.
   - Offer quick follow-up steps (e.g., running a specific script or adding a targeted assertion) so authors can resolve the feedback efficiently.

4. **Recognize Good Practices**
   - Call out positive patterns such as improved tests, documentation, or refactoring that increases clarity.
   - Reinforce behaviors the team should continue.

5. **Stay Consistent with Project Standards**
   - Apply the coding conventions documented in `TESTING.md`, `README.md`, and other repository guides.
   - Ensure any new documentation follows the tone and formatting used throughout this repository.
   - When uncertain about a guideline, ask for confirmation rather than blocking the change.

6. **Watch for Merge-Readiness**
   - Confirm the branch merges cleanly or call out conflicts the author must resolve before approval.
   - Recommend rebasing or updating lockfiles when the diff suggests outdated dependencies.
   - Note required follow-up actions (e.g., database migration sequencing) that must happen prior to deployment.

### Review Workflow

1. **Initial Pass**
   - Verify that the project builds or passes tests when feasible. When tooling is unavailable, explain the limitation and suggest the author run the checks.
   - Confirm that new features include appropriate tests and documentation.
   - Look for obvious merge conflicts (e.g., conflict markers, outdated snapshots) and highlight them early.
   - Run static analysis (PHPStan, ESLint, etc.) or unit tests from the repository's tooling to surface regressions automatically. Summarize failures with actionable pointers.

2. **Detailed Review**
   - Inspect each file for logical errors, edge cases, and adherence to patterns.
   - Check security-sensitive code paths for input validation and error handling.
   - Ensure database migrations, API changes, and configuration updates are backward compatible.
   - Validate that dependency updates and configuration changes align with environment-specific constraints documented in the repo.
   - Trace the execution path using existing tests, manual reasoning, or lightweight sandbox scripts to verify data flow and error handling.
   - When Copilot proposes a fix, double-check that the patch compiles, passes relevant tests, and aligns with surrounding code conventions before recommending it.

3. **Summary Comment**
   - Provide a concise summary highlighting the overall assessment and confidence level.
   - Enumerate any blocking issues and suggestions for improvement, grouping related comments when possible.
   - Approve the PR if no blocking issues remain; otherwise, request changes with a clear rationale and reference to specific comments.

### Providing Recommendations and Fixes

- When identifying an issue, include a short explanation of the risk, the affected code path, and the expected behaviour. Reference specific files, functions, or database migrations so the author can find the context quickly. Use a consistent mini-template such as `Risk:` (impact/severity), `Scope:` (file or function), and `Expectation:` (correct behaviour) so authors can scan the rationale at a glance.
- Offer at least one concrete remediation strategy. This may include code snippets, pointing to helper utilities, or recommending configuration updates.
- If Copilot can draft a fix, share the snippet inline or attach it as a suggested change. Highlight any assumptions so the author can validate them.
- Encourage the author to run the appropriate test suite (unit, integration, end-to-end) after applying the fix. When possible, provide the exact command from the project's tooling.
- For recurring issues, suggest preventive follow-ups such as adding regression tests, updating documentation, or refactoring the relevant module.

### Tone and Communication

- Be professional, constructive, and empathetic.
- Use concise language, avoiding jargon when a simpler explanation suffices.
- Encourage collaboration by thanking the author for their work and inviting dialogue on open questions.
- Avoid definitive statements when evidence is incomplete; invite the author to confirm assumptions.

### Automation and Tooling

- Leverage automated tooling (linters, tests, static analysis) to validate findings when possible.
- Reference tooling outputs in feedback to support conclusions.
- Surface flaky or failing checks that need reruns or investigation. Check the repository's CI dashboard (e.g., GitHub Checks tab) to review historical runs, identify patterns of flakiness, and capture links or log excerpts that illustrate the failure mode when reporting it.
- Do not rely solely on automated results—use human judgment to interpret their implications.

### Handling Merge Conflicts

- If conflict markers (`<<<<<<<`, `=======`, `>>>>>>>`) appear in the diff, request that the author resolve them before continuing the review.
- When possible, describe the conflicting areas and suggest which side likely reflects the intended behaviour. Mention concrete techniques—such as running `git diff <base>..<branch>` locally, comparing `git log --stat` entries, or inspecting the pull request history—to justify the recommendation.
- Remind the author to rerun tests after resolving conflicts to ensure no regressions were introduced.

### Closure Checklist

Before approving a change, confirm that:

1. All blocking comments are resolved or addressed with a follow-up plan.
2. The branch merges cleanly and automated checks are green or have documented justifications.
3. Release notes, migrations, or configuration changes have clear rollout instructions when needed.

## Performance Considerations

### Database Optimization

- Use database indexes on frequently queried columns
- Avoid N+1 queries - use joins or eager loading
- Limit result sets with pagination
- Use database query caching where appropriate
- Profile slow queries with `EXPLAIN` before optimizing

### Caching Strategy

- Use Redis for session storage and frequently accessed data
- Cache expensive computations and API responses
- Invalidate cache when data changes
- Set appropriate TTL (Time To Live) for cached items
- Never cache sensitive user data without encryption

### Code Optimization

- Minimize file I/O operations
- Batch database operations when possible
- Use lazy loading for large datasets
- Optimize asset loading (combine/minify CSS/JS)
- Profile code with Xdebug when performance issues arise

## Error Handling and Logging

### Error Handling Patterns

**CI4 Transaction Handling:**
```php
$db = \Config\Database::connect();
$db->transStart();

try {
    // Database operations
    $userModel->save($userData);
    $profileModel->save($profileData);
    
    $db->transComplete();
    
    if ($db->transStatus() === false) {
        throw new \RuntimeException('Transaction failed');
    }
    
    return $this->respond(['success' => true]);
    
} catch (\Exception $e) {
    $db->transRollback();
    log_message('error', 'Transaction error: ' . $e->getMessage());
    return $this->fail('An error occurred while processing your request');
}
```

**CI4 Exception Handling:**
```php
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\Exceptions\HTTPException;

class StudentController extends BaseController {
    public function show($id) {
        try {
            $student = $this->studentModel->find($id);
            
            if (!$student) {
                throw PageNotFoundException::forPageNotFound("Student not found");
            }
            
            return view('students/show', ['student' => $student]);
            
        } catch (PageNotFoundException $e) {
            log_message('error', 'Student lookup failed: ' . $e->getMessage());
            return $this->failNotFound($e->getMessage());
            
        } catch (\Exception $e) {
            log_message('error', 'Unexpected error: ' . $e->getMessage());
            return $this->failServerError('An unexpected error occurred');
        }
    }
}
```

**API Response Helpers (CI4):**
```php
// Success responses
return $this->respond(['data' => $result], 200);
return $this->respondCreated(['data' => $newRecord]);
return $this->respondNoContent();

// Error responses
return $this->fail('Validation failed', 400);
return $this->failNotFound('Resource not found');
return $this->failUnauthorized('Authentication required');
return $this->failForbidden('Access denied');
return $this->failServerError('Internal server error');
```

### Logging Conventions

- **Error logs:** Use `log_message('error', ...)` for exceptions and failures
- **Debug logs:** Use `log_message('debug', ...)` for development debugging (disable in production)
- **Info logs:** Use `log_message('info', ...)` for important business events
- **Audit logs:** Always use `AuditService` for sensitive operations
- Include context: user ID, entity ID, operation type
- Never log sensitive data (passwords, credit cards, API keys)

## Git Workflow and Branching

### Branch Naming Conventions

- **Feature branches:** `feature/short-description` or `copilot/short-description`
- **Bug fixes:** `bugfix/issue-number-description`
- **Hotfixes:** `hotfix/critical-fix-description`
- **CI4 migration work:** `ci4/module-name` or `ci4/feature-name`

### Commit Message Format

```
<type>: <short summary in present tense>

<optional detailed description>

<optional footer with issue references>
```

**Types:**
- `feat:` New feature
- `fix:` Bug fix
- `docs:` Documentation changes
- `refactor:` Code refactoring
- `test:` Adding or updating tests
- `chore:` Maintenance tasks
- `security:` Security fixes

**Examples:**
```
feat: Add soft delete support to inventory module

Implement SoftDeleteManager for inventory items to maintain
audit trail and comply with data retention policies.

Closes #123
```

### Pull Request Guidelines

- Keep PRs focused and small (< 400 lines if possible)
- Reference the issue number in PR description
- Include testing instructions
- Update relevant documentation
- Ensure all CI checks pass before requesting review
- Add screenshots for UI changes

## Common Anti-Patterns to Avoid

### Database Anti-Patterns

❌ **DON'T:**
```php
// N+1 query problem (CI4)
foreach ($students as $student) {
    $fees = $db->table('fees')
               ->where('student_id', $student->id)
               ->get()
               ->getResult();
}

// SQL injection vulnerability
$db->query("SELECT * FROM users WHERE username = '$username'");

// Hard delete without audit trail
$db->table('users')->delete(['id' => $userId]);

// Using CI3 patterns in CI4
$this->db->where('id', $id)->get('users'); // Wrong framework!
```

✅ **DO:**
```php
// Use JOIN to avoid N+1 (CI4)
$builder = $db->table('students');
$results = $builder->select('students.*, fees.*')
                   ->join('fees', 'fees.student_id = students.id')
                   ->get()
                   ->getResult();

// Or use model relationships (preferred)
$students = $studentModel->with('fees')->findAll();

// Use query builder for safety
$db->table('users')->where('username', $username)->get();

// Use soft delete with audit
$softDelete->delete('users', $userId, [
    'deleted_by' => $currentUserId,
    'delete_reason' => 'User requested'
]);
```

### Security Anti-Patterns

❌ **DON'T:**
```php
// Trusting user input (CI4)
$data = $this->request->getPost();
$userModel->insert($data); // Mass assignment vulnerability!

// Exposing sensitive data in logs
log_message('debug', 'Password: ' . $password);

// Missing CSRF protection
<form method="post" action="/update">

// Storing passwords in plain text
$data['password'] = $this->request->getPost('password');
```

✅ **DO:**
```php
// Validate and sanitize input (CI4)
$rules = [
    'email' => 'required|valid_email',
    'name'  => 'required|min_length[3]'
];

if ($this->validate($rules)) {
    $data = [
        'email' => $this->request->getPost('email'),
        'name'  => $this->request->getPost('name')
    ];
    $userModel->insert($data);
}

// Never log sensitive data
log_message('debug', 'Login attempt for user: ' . $username);

// Always include CSRF token
<?= form_open('update') ?>
<?= csrf_field() ?>

// Hash passwords
$data['password'] = password_hash($this->request->getPost('password'), PASSWORD_DEFAULT);
```

### Code Organization Anti-Patterns

❌ **DON'T:**
```php
// Business logic in controllers (CI4)
namespace App\Controllers;

class StudentController extends BaseController {
    public function update() {
        // 200+ lines of business logic here
        // Calculating fees, sending emails, updating multiple tables
        // This is wrong!
    }
}

// God objects with too many responsibilities
class UtilityHelper {
    // 50+ unrelated methods
    public function sendEmail() {}
    public function calculateFees() {}
    public function generateReport() {}
    public function processPayment() {}
    // Too many responsibilities!
}

// Copying CI3 patterns directly into CI4
class OldStyleController extends BaseController {
    public function index() {
        $this->load->model('User_model'); // This is CI3, not CI4!
        $this->load->view('header');      // Wrong!
    }
}
```

✅ **DO:**
```php
// Use services for business logic (CI4)
namespace App\Controllers;

use App\Services\StudentService;

class StudentController extends BaseController {
    protected $studentService;
    
    public function __construct() {
        $this->studentService = service('studentService');
    }
    
    public function update($id) {
        $data = $this->request->getPost();
        $result = $this->studentService->updateStudent($id, $data);
        return $this->respond($result);
    }
}

// Single responsibility classes
namespace App\Services;

class EmailService {
    public function sendWelcomeEmail($user) { /* ... */ }
    public function sendPasswordReset($user) { /* ... */ }
}

class SmsService {
    public function sendOTP($phone, $code) { /* ... */ }
    public function sendNotification($phone, $message) { /* ... */ }
}

// Modern CI4 patterns
class ModernController extends BaseController {
    public function index() {
        $userModel = model('UserModel'); // CI4 way
        return view('users/index', [     // CI4 way
            'users' => $userModel->findAll()
        ]);
    }
}
```

## Dependency Management

### Adding New Dependencies

1. **Check for vulnerabilities** before adding any package
2. **Prefer stable versions** over dev versions
3. **Review package maintenance** - last update, issue count, community size
4. **Check license compatibility** with project license
5. **Consider package size** and performance impact
6. **Document why** the dependency was added

### Updating Dependencies

```bash
# Update specific package
composer update vendor/package

# Update all dependencies (with caution)
composer update

# Always run tests after updating
composer ci
```

- Review CHANGELOG before updating major versions
- Test thoroughly in development before deploying
- Update one dependency at a time for easier rollback
- Document breaking changes in PR description

## Documentation

- **Main README:** Project overview, setup, Docker usage
- **TESTING.md:** Manual testing scenarios and QA procedures
- **docs/API.md:** API endpoint documentation
- **docs/SECURITY.md:** Security controls and compliance
- **docs/DEV_SETUP.md:** Developer environment setup
- **docs/CI4_FOUNDATIONS.md:** CI4 migration progress
- **docs/SHULELABS_IMPLEMENTATION_PLAN.md:** Product roadmap

## Important Notes

1. **CI4 Only Development** - All new code goes in CI4. CI3 is reference only.
2. **Never modify CI3 code** - CI3 is frozen. Only read it for reference.
3. **Never hard delete data** - Always use soft delete with audit trail
4. **Always log sensitive operations** - Use AuditService for compliance
5. **Test database migrations** - Both up and down migrations must work
6. **Database compatibility** - CI4 shares database with CI3 during transition
7. **Security first** - Input validation, CSRF protection, parameterized queries
8. **Use modern CI4 patterns** - Entities, services, proper dependency injection
9. **Document breaking changes** - Update relevant docs and migration guides
10. **Follow existing patterns** - Consistency is more important than personal preference

## Getting Help

- Check `docs/` directory for architectural decisions and guides
- Review `TESTING.md` for manual verification procedures
- Examine existing code in the same module for patterns
- Consult `composer.json` scripts for automation commands
- Reference `docs/SHULELABS_IMPLEMENTATION_PLAN.md` for roadmap alignment
