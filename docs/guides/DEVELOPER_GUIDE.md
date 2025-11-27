# Developer Guide - ShuleLabs CI4

**Version:** 1.0.0  
**Last Updated:** November 23, 2025  
**Audience:** Developers, Contributors

---

## Table of Contents

1. [Getting Started](#getting-started)
2. [Project Structure](#project-structure)
3. [Development Workflow](#development-workflow)
4. [Coding Standards](#coding-standards)
5. [Testing Guide](#testing-guide)
6. [Module Development](#module-development)
7. [Database Migrations](#database-migrations)
8. [API Development](#api-development)
9. [Security Best Practices](#security-best-practices)
10. [Debugging](#debugging)
11. [Contributing Guidelines](#contributing-guidelines)
12. [Troubleshooting](#troubleshooting)

---

## Getting Started

### Prerequisites

**Required:**
- PHP 8.3+
- Composer 2.x
- Git

**Optional:**
- MySQL 8.0+ (SQLite works for development)
- Redis 7.0+
- Docker & Docker Compose

### Installation

```bash
# 1. Clone repository
git clone https://github.com/countynetkenya/shulelabsci4.git
cd shulelabsci4

# 2. Install dependencies
composer install

# 3. Setup environment
cp env .env

# 4. Generate encryption key
php spark key:generate

# 5. Run migrations
php spark migrate

# 6. Seed test data
php spark db:seed CompleteDatabaseSeeder

# 7. Start development server
php spark serve
```

**Access**: http://localhost:8080  
**Login**: admin@shulelabs.local / Admin@123456

### IDE Setup

**VS Code (Recommended)**

**.vscode/settings.json:**
```json
{
  "php.validate.executablePath": "/usr/bin/php8.3",
  "php.suggest.basic": false,
  "intelephense.environment.phpVersion": "8.3.0",
  "intelephense.files.associations": [
    "*.php",
    "*.phtml"
  ],
  "files.associations": {
    "*.php": "php"
  },
  "editor.formatOnSave": true,
  "editor.tabSize": 4,
  "editor.insertSpaces": true
}
```

**Recommended Extensions:**
- PHP Intelephense
- PHP Debug (Xdebug)
- EditorConfig for VS Code
- GitLens
- Better Comments

**PHPStorm**

1. Open project
2. Settings → PHP → Set PHP 8.3 interpreter
3. Enable CodeIgniter 4 plugin
4. Configure PHPUnit: `phpunit.ci4.xml`
5. Configure PHP_CodeSniffer: PSR-12 standard

---

## Project Structure

```
shulelabsci4/
├── app/                        # Application code
│   ├── Config/                 # Configuration files
│   │   ├── App.php            # Application config
│   │   ├── Database.php       # Database config
│   │   ├── Routes.php         # Route definitions
│   │   └── ...
│   ├── Controllers/           # Base controllers
│   ├── Models/                # Core models
│   ├── Modules/               # Feature modules
│   │   ├── Finance/
│   │   │   ├── Config/
│   │   │   ├── Controllers/
│   │   │   │   └── Api/       # API controllers
│   │   │   ├── Models/
│   │   │   ├── Services/      # Business logic
│   │   │   ├── Repositories/  # Data access
│   │   │   ├── Database/
│   │   │   │   ├── Migrations/
│   │   │   │   └── Seeds/
│   │   │   └── Tests/
│   │   ├── Learning/
│   │   ├── Library/
│   │   └── ...
│   ├── Filters/               # Middleware
│   ├── Helpers/               # Helper functions
│   ├── Libraries/             # Custom libraries
│   ├── Services/              # Global services
│   └── Views/                 # Templates
├── public/                    # Web root
│   ├── index.php             # Entry point
│   └── assets/               # Static files
├── writable/                  # Logs, cache, sessions
│   ├── cache/
│   ├── logs/
│   ├── sessions/
│   └── uploads/
├── tests/                     # Test suite
│   ├── Finance/
│   ├── Learning/
│   └── ...
├── vendor/                    # Composer dependencies
├── docs/                      # Documentation
├── deployment/                # Deployment configs
├── composer.json              # Dependencies
├── phpunit.ci4.xml           # PHPUnit config
├── .env                       # Environment config
└── spark                      # CLI tool
```

### Key Directories

| Directory | Purpose | Modify? |
|-----------|---------|---------|
| `app/Modules/` | Feature modules | ✅ Yes |
| `app/Config/` | Configuration | ✅ Yes |
| `app/Services/` | Global services | ✅ Yes |
| `app/Models/` | Base models | ⚠️ Carefully |
| `vendor/` | Dependencies | ❌ No |
| `writable/` | Runtime files | ❌ No |
| `public/` | Web-accessible | ✅ Yes (assets) |

---

## Development Workflow

### Branch Strategy (Git Flow)

```
main                   # Production-ready code
  ├── develop          # Integration branch
  │   ├── feature/*    # New features
  │   ├── bugfix/*     # Bug fixes
  │   └── hotfix/*     # Urgent production fixes
  └── release/*        # Release preparation
```

### Creating a Feature

```bash
# 1. Create feature branch from develop
git checkout develop
git pull origin develop
git checkout -b feature/invoice-bulk-import

# 2. Develop feature
# ... write code ...

# 3. Run tests
composer test

# 4. Code quality checks
composer cs:check
composer phpstan

# 5. Commit changes
git add .
git commit -m "feat(finance): add bulk invoice import"

# 6. Push to remote
git push origin feature/invoice-bulk-import

# 7. Create pull request (GitHub)
# - Target: develop
# - Reviewers: Team leads
# - Labels: enhancement

# 8. After approval, merge to develop
```

### Commit Message Convention

Follow [Conventional Commits](https://www.conventionalcommits.org/):

```
<type>(<scope>): <subject>

<body>

<footer>
```

**Types:**
- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation
- `style`: Formatting
- `refactor`: Code restructuring
- `test`: Adding tests
- `chore`: Maintenance

**Examples:**
```
feat(finance): add bulk invoice import
fix(learning): correct GPA calculation
docs(api): update authentication guide
test(library): add borrowing service tests
```

---

## Coding Standards

### PSR-12 Extended Coding Style

ShuleLabs follows [PSR-12](https://www.php-fig.org/psr/psr-12/) with CI4 conventions.

**Class Structure:**
```php
<?php

declare(strict_types=1);

namespace Modules\Finance\Services;

use Modules\Finance\Repositories\InvoiceRepository;
use Modules\Foundation\Services\AuditService;

/**
 * InvoiceService.
 *
 * Handles invoice business logic
 *
 * @version 1.0.0
 */
class InvoiceService
{
    /**
     * Invoice repository.
     */
    protected InvoiceRepository $repository;

    /**
     * Audit service.
     */
    protected AuditService $auditService;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->repository = new InvoiceRepository();
        $this->auditService = new AuditService();
    }

    /**
     * Create a new invoice.
     *
     * @param array $data Invoice data
     * @param int   $schoolId School ID
     *
     * @return int Invoice ID
     */
    public function createInvoice(array $data, int $schoolId): int
    {
        // Validate
        $errors = $this->validate($data);
        if (!empty($errors)) {
            throw new \InvalidArgumentException('Validation failed');
        }

        // Business logic
        $invoiceNumber = $this->generateInvoiceNumber($schoolId);
        $data['invoice_number'] = $invoiceNumber;
        $data['school_id'] = $schoolId;

        // Create
        $id = $this->repository->create($data);

        // Audit
        $this->auditService->log('invoice.created', $id);

        return $id;
    }

    /**
     * Generate unique invoice number.
     *
     * @param int $schoolId School ID
     *
     * @return string Invoice number
     */
    private function generateInvoiceNumber(int $schoolId): string
    {
        $year = date('Y');
        $count = $this->repository->countBySchool($schoolId, $year);

        return sprintf('INV-%d-%04d', $year, $count + 1);
    }
}
```

### Naming Conventions

**Classes:**
- PascalCase
- Singular nouns
- Descriptive names

```php
class InvoiceService {}
class StudentRepository {}
class PaymentController {}
```

**Methods:**
- camelCase
- Verb + noun
- Descriptive

```php
public function createInvoice() {}
public function getStudentsByClass() {}
private function calculateTotalAmount() {}
```

**Variables:**
- camelCase
- Descriptive

```php
$studentId = 10;
$invoiceNumber = 'INV-2025-001';
$totalAmount = 50000.00;
```

**Constants:**
- UPPER_SNAKE_CASE

```php
const MAX_UPLOAD_SIZE = 5242880; // 5MB
const DEFAULT_CURRENCY = 'KES';
```

**Database Tables:**
- snake_case
- Plural nouns
- Prefixed with module name (optional)

```sql
students
invoices
invoice_items
fee_structures
```

### Code Organization

**Separation of Concerns:**

```
Controller → Service → Repository → Model → Database
```

**Controller (Thin):**
```php
public function create()
{
    $data = $this->request->getJSON(true);
    $tenantContext = $this->tenantResolver->fromRequest($this->request);
    
    $id = $this->service->createInvoice($data, $tenantContext['school']['id']);
    
    return $this->respondCreated(['id' => $id]);
}
```

**Service (Business Logic):**
```php
public function createInvoice(array $data, int $schoolId): int
{
    // Validate
    $this->validate($data);
    
    // Business logic
    $data['invoice_number'] = $this->generateInvoiceNumber($schoolId);
    $data['school_id'] = $schoolId;
    
    // Persist
    $id = $this->repository->create($data);
    
    // Side effects
    $this->auditService->log('invoice.created', $id);
    
    return $id;
}
```

**Repository (Data Access):**
```php
public function create(array $data): int
{
    return $this->model->insert($data);
}
```

### Type Hints

Always use type hints for parameters and return values:

```php
// ✅ Good
public function calculateTotal(int $invoiceId): float
{
    // ...
}

// ❌ Bad
public function calculateTotal($invoiceId)
{
    // ...
}
```

### DocBlocks

Required for all classes and public methods:

```php
/**
 * Calculate invoice total.
 *
 * Sums all invoice items and applies discounts
 *
 * @param int $invoiceId Invoice ID
 * @param bool $includeVAT Include VAT in calculation
 *
 * @return float Total amount
 *
 * @throws InvoiceNotFoundException If invoice not found
 */
public function calculateTotal(int $invoiceId, bool $includeVAT = true): float
{
    // ...
}
```

### Error Handling

```php
// Use specific exceptions
throw new InvoiceNotFoundException("Invoice {$id} not found");
throw new ValidationException('Invalid student ID');

// Catch and handle appropriately
try {
    $invoice = $this->service->createInvoice($data, $schoolId);
} catch (ValidationException $e) {
    return $this->failValidationErrors($e->getErrors());
} catch (\Exception $e) {
    log_message('error', 'Invoice creation failed: ' . $e->getMessage());
    return $this->failServerError('Unable to create invoice');
}
```

### Security Best Practices

**1. Input Validation:**
```php
// Validate all input
$validation = service('validation');
$validation->setRules([
    'student_id' => 'required|integer|is_not_unique[students.id]',
    'amount' => 'required|decimal|greater_than[0]',
    'due_date' => 'required|valid_date',
]);

if (!$validation->run($data)) {
    throw new ValidationException($validation->getErrors());
}
```

**2. SQL Injection Prevention:**
```php
// ✅ Use Query Builder (automatic escaping)
$students = $this->db->table('students')
    ->where('school_id', $schoolId)
    ->where('name LIKE', "%{$search}%")
    ->get()
    ->getResultArray();

// ❌ Never use raw SQL with user input
$sql = "SELECT * FROM students WHERE name = '{$search}'"; // DANGEROUS!
```

**3. XSS Prevention:**
```php
// Escape output in views
<?= esc($studentName) ?>
<?= esc($description, 'html') ?>
```

**4. Tenant Isolation:**
```php
// ALWAYS scope queries by tenant
$invoices = $this->db->table('invoices')
    ->where('school_id', $schoolId)  // CRITICAL!
    ->findAll();
```

---

## Testing Guide

### Running Tests

```bash
# Run all tests
composer test

# Run specific module
php vendor/bin/phpunit tests/Finance/

# Run with coverage
php vendor/bin/phpunit --coverage-html coverage/

# Run single test
php vendor/bin/phpunit --filter testCreateInvoice
```

### Writing Tests

**Unit Test Example:**

```php
<?php

namespace Tests\Finance;

use CodeIgniter\Test\CIUnitTestCase;
use Modules\Finance\Services\InvoiceService;

class InvoiceServiceTest extends CIUnitTestCase
{
    protected InvoiceService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new InvoiceService();
    }

    public function testCreateInvoice(): void
    {
        $data = [
            'student_id' => 1,
            'amount' => 50000.00,
            'due_date' => '2025-12-31',
        ];

        $id = $this->service->createInvoice($data, 1);

        $this->assertIsInt($id);
        $this->assertGreaterThan(0, $id);
    }

    public function testValidateInvoiceData(): void
    {
        $invalidData = ['student_id' => 'invalid'];

        $this->expectException(\InvalidArgumentException::class);
        $this->service->createInvoice($invalidData, 1);
    }
}
```

**Integration Test Example:**

```php
<?php

namespace Tests\Finance\Api;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

class InvoiceApiTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $seed = 'Tests\\Support\\Database\\Seeds\\TestDatabaseSeeder';

    public function testCreateInvoiceApi(): void
    {
        $data = [
            'student_id' => 1,
            'amount' => 50000.00,
            'due_date' => '2025-12-31',
        ];

        $result = $this->withSession(['user_id' => 1])
            ->withHeaders(['X-Tenant-ID' => '1'])
            ->post('api/v1/finance/invoices', $data);

        $result->assertStatus(201);
        $result->assertJSONFragment(['status' => 'success']);
    }
}
```

### Test Data

Use seeders for test data:

```php
<?php

namespace Tests\Support\Database\Seeds;

use CodeIgniter\Database\Seeder;

class InvoiceSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'invoice_number' => 'INV-2025-001',
                'student_id' => 1,
                'school_id' => 1,
                'amount' => 50000.00,
                'status' => 'pending',
                'due_date' => '2025-12-31',
            ],
        ];

        $this->db->table('invoices')->insertBatch($data);
    }
}
```

---

## Module Development

### Creating a New Module

```bash
# 1. Create module structure
mkdir -p app/Modules/NewModule/{Config,Controllers/Api,Models,Services,Repositories,Database/Migrations,Database/Seeds,Tests}

# 2. Create controller
touch app/Modules/NewModule/Controllers/Api/ItemsApiController.php

# 3. Create service
touch app/Modules/NewModule/Services/ItemService.php

# 4. Create repository
touch app/Modules/NewModule/Repositories/ItemRepository.php

# 5. Create model
touch app/Modules/NewModule/Models/ItemModel.php

# 6. Create migration
php spark make:migration create_items_table --namespace Modules\\NewModule
```

**Example Module: Items**

**Controller:**
```php
<?php

namespace Modules\NewModule\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use Modules\NewModule\Services\ItemService;

class ItemsApiController extends ResourceController
{
    protected $format = 'json';
    protected ItemService $service;

    public function __construct()
    {
        $this->service = new ItemService();
    }

    public function index(): \CodeIgniter\HTTP\ResponseInterface
    {
        $page = (int) ($this->request->getGet('page') ?? 1);
        $items = $this->service->paginate($page);
        return $this->respond($items);
    }

    public function create(): \CodeIgniter\HTTP\ResponseInterface
    {
        $data = $this->request->getJSON(true);
        $id = $this->service->create($data);
        return $this->respondCreated(['id' => $id]);
    }
}
```

**Add Routes:**

```php
// app/Config/Routes.php
$routes->group('api/v1/newmodule', ['filter' => 'auth'], function ($routes) {
    $routes->resource('items', ['controller' => 'Modules\NewModule\Controllers\Api\ItemsApiController']);
});
```

---

## Database Migrations

### Creating Migrations

```bash
# Create migration
php spark make:migration create_items_table

# Create with namespace
php spark make:migration create_items_table --namespace Modules\\NewModule
```

**Migration Template:**

```php
<?php

namespace Modules\NewModule\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateItemsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'school_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'price' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0.00,
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['active', 'inactive'],
                'default' => 'active',
            ],
            'created_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
            'deleted_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('school_id');
        $this->forge->addKey('deleted_at');
        
        $this->forge->createTable('items');
    }

    public function down(): void
    {
        $this->forge->dropTable('items');
    }
}
```

### Running Migrations

```bash
# Run all migrations
php spark migrate

# Run specific namespace
php spark migrate --namespace Modules\\NewModule

# Rollback
php spark migrate:rollback

# Refresh (rollback + migrate)
php spark migrate:refresh
```

---

## API Development

### REST API Standards

**Endpoint Naming:**
```
GET    /api/v1/invoices          # List invoices
GET    /api/v1/invoices/{id}     # Get invoice
POST   /api/v1/invoices          # Create invoice
PUT    /api/v1/invoices/{id}     # Update invoice
DELETE /api/v1/invoices/{id}     # Delete invoice
```

**Response Format:**

```json
{
  "status": "success",
  "data": {},
  "message": "Operation completed",
  "meta": {
    "page": 1,
    "total": 100
  }
}
```

**Error Response:**

```json
{
  "status": "error",
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "Validation failed",
    "details": {
      "email": ["Email is required"]
    }
  }
}
```

---

## Debugging

### Xdebug Setup

**Install Xdebug:**
```bash
sudo pecl install xdebug
```

**Configure (`/etc/php/8.3/cli/php.ini`):**
```ini
[xdebug]
zend_extension=xdebug.so
xdebug.mode=debug
xdebug.start_with_request=yes
xdebug.client_host=localhost
xdebug.client_port=9003
```

**VS Code (`launch.json`):**
```json
{
    "version": "0.2.0",
    "configurations": [
        {
            "name": "Listen for Xdebug",
            "type": "php",
            "request": "launch",
            "port": 9003,
            "pathMappings": {
                "/var/www/html": "${workspaceFolder}"
            }
        }
    ]
}
```

### Logging

```php
// Debug logging
log_message('debug', 'Processing invoice: ' . $invoiceId);

// Error logging
log_message('error', 'Payment failed: ' . $e->getMessage());

// Custom logging
log_message('info', 'Student enrolled', ['student_id' => $id]);
```

**View logs:**
```bash
tail -f writable/logs/log-$(date +%Y-%m-%d).log
```

---

## Contributing Guidelines

### Pull Request Process

1. Fork repository
2. Create feature branch
3. Write code + tests
4. Run quality checks
5. Commit with conventional message
6. Push and create PR
7. Address review feedback
8. Merge after approval

### Code Review Checklist

- [ ] Code follows PSR-12
- [ ] All tests pass
- [ ] Code coverage ≥ 80%
- [ ] No security vulnerabilities
- [ ] Documentation updated
- [ ] Commit messages follow convention

---

## Troubleshooting

### Common Issues

**Issue**: Database connection failed  
**Fix**: Check `.env` database credentials

**Issue**: Session not persisting  
**Fix**: Create sessions table: `php spark migrate`

**Issue**: Permission denied on writable/  
**Fix**: `chmod -R 777 writable/`

**Issue**: Class not found  
**Fix**: `composer dump-autoload`

---

**Happy Coding!** 🚀
