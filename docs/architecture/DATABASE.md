# 📊 Database Schema & Architecture

**Last Updated**: 2025-11-22  
**Status**: Active

## Overview

Complete database schema documentation for ShuleLabs CI4, including multi-tenant data modeling and architectural patterns.

## Table of Contents

- [Overview](#overview)
- [Database Design Principles](#database-design-principles)
- [Multi-Tenant Data Modeling](#multi-tenant-data-modeling)
- [Schema Layers](#schema-layers)
- [Tenant Catalog](#tenant-catalog)
- [Tenant-Scoped Tables](#tenant-scoped-tables)
- [Global Tables](#global-tables)
- [Naming Conventions](#naming-conventions)
- [Migration Strategy](#migration-strategy)
- [References](#references)

### Inventory V2 Tables

#### `inventory_locations`
Stores/Warehouses.
- `id` (PK)
- `name`
- `description`
- `is_default` (Boolean)

#### `inventory_categories`
Item categories.
- `id` (PK)
- `name`

#### `inventory_items`
Product catalog.
- `id` (PK)
- `category_id` (FK)
- `name`
- `sku` (Unique)
- `type` (Enum: 'physical', 'service', 'bundle')
- `unit_cost`
- `is_billable`

#### `inventory_stock`
Current stock levels per location.
- `id` (PK)
- `item_id` (FK)
- `location_id` (FK)
- `quantity`

#### `inventory_transfers`
Stock movement records.
- `id` (PK)
- `item_id` (FK)
- `from_location_id` (FK)
- `to_location_id` (FK)
- `quantity`
- `status` (Enum: 'pending', 'completed', 'cancelled')
- `initiated_by` (FK)

## Database Design Principles

ShuleLabs follows these core database design principles:

1. **Normalization**: Minimum 3NF (Third Normal Form) to reduce redundancy
2. **Referential Integrity**: Foreign keys enforced for data consistency
3. **Soft Deletes**: `deleted_at` column pattern for recoverability
4. **Audit Columns**: Standard timestamp and user tracking columns
5. **Multi-Tenant Isolation**: Row-level `tenant_id` scoping for tenant-specific data
6. **UUID Support**: Optional UUID primary keys for distributed systems (future)

### Standard Audit Columns

Every table includes these columns for tracking:

```sql
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
created_by INT,  -- User ID who created the record
updated_by INT,  -- User ID who last updated the record
deleted_at TIMESTAMP NULL  -- Soft delete (NULL = active, timestamp = deleted)
```

## Multi-Tenant Data Modeling

### Row-Level Isolation Strategy

ShuleLabs uses **row-level isolation** with `tenant_id` columns:

- **Shared Database**: Single database for all tenants
- **Shared Schema**: Single set of tables for all tenants
- **Row-Level Scoping**: `tenant_id` (or `school_id`, `organisation_id`) on tenant-specific tables
- **Application Enforcement**: Queries automatically scoped to active tenant

**Benefits**:
- Cost-effective (single database)
- Operationally simple (one schema to manage)
- Easy backup and restore
- Suitable for most use cases

**Considerations**:
- Application must enforce tenant scoping (never trust client input)
- Indexing strategy must include tenant_id
- Careful query design to prevent cross-tenant data leaks

### Tenant Context Enforcement

All controllers, services, and repositories use `TenantContext`:

```php
// Controller resolves tenant
$tenantContext = $this->tenantResolver->fromRequest($this->request);
$schoolId = $tenantContext['school']['id'] ?? null;

// Service queries scoped by tenant
$students = $this->db->table('students')
    ->where('school_id', $schoolId)  // Always include tenant scope
    ->findAll();
```

### Future Isolation Options (Phase 3)

For high-security or compliance needs:
- **Schema-per-tenant**: Each tenant gets its own schema (e.g., `school_1`, `school_2`)
- **Database-per-tenant**: Each tenant gets a dedicated database
- Configurable per tenant based on security, compliance, or performance requirements

## Schema Layers

The database is organized into logical layers:

### 1. CI4 Core Tables (Prefix: ``)

Authentication and framework tables:
- `users` - User accounts (global, can access multiple schools)
- `roles` - Role definitions (global)
- `user_roles` - User-to-role assignments (global)
- `migrations` - Migration history (global)

### 2. Tenant Catalog (Prefix: ``)

Multi-tenant infrastructure:
- `tenant_catalog` - Organisations, schools, warehouses (global tenant registry)

### 3. Foundation Tables

Core services (audit, ledger, QR):
- `audit_events` - Immutable audit log (with `tenant_id`)
- `audit_seals` - Daily cryptographic seals (global)
- `ledger_entries` - Double-entry ledger (with `tenant_id`)
- `ledger_transactions` - Financial transactions (with `tenant_id`)
- `qr_tokens` - QR code tokens (with `tenant_id`)
- `qr_scans` - QR scan history (with `tenant_id`)

### 4. Module Tables

Domain-specific tables for each module:
- **Learning**: Classes, subjects, students, attendance, grades (all with `school_id`)
- **Finance**: Invoices, payments, fees, refunds (all with `school_id`)
- **HR**: Employees, payroll, leave, departments (all with `school_id`)
- **Inventory**: Assets, stock, requisitions, suppliers (all with `school_id`)
- **Library**: Books, borrowings, fines, members (all with `school_id`)

## Tenant Catalog

### tenant_catalog Table

**Purpose**: Central registry of all tenants (organisations, schools, warehouses)

**Schema**:
```sql
CREATE TABLE tenant_catalog (
    id VARCHAR(50) PRIMARY KEY,  -- Tenant identifier (e.g., 'school-1', 'org-1')
    tenant_type ENUM('organisation', 'school', 'warehouse') NOT NULL,
    name VARCHAR(200) NOT NULL,  -- Display name
    metadata JSON,  -- Flexible tenant-specific configuration
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tenant_type (tenant_type)
);
```

**Example Data**:
```sql
-- Organisation
INSERT INTO tenant_catalog VALUES (
    'org-1',
    'organisation',
    'County Education Network',
    '{"country": "Kenya", "timezone": "Africa/Nairobi", "currency": "KES"}',
    NOW(), NOW()
);

-- School (belongs to organisation)
INSERT INTO tenant_catalog VALUES (
    'school-1',
    'school',
    'Nairobi Primary School',
    '{"organisation_id": "org-1", "curriculum": "CBC", "motto": "Excellence", "address": "..."}',
    NOW(), NOW()
);

-- Warehouse (for inventory)
INSERT INTO tenant_catalog VALUES (
    'warehouse-1',
    'warehouse',
    'Central Warehouse',
    '{"organisation_id": "org-1", "location": "Nairobi"}',
    NOW(), NOW()
);
```

**Metadata Examples**:
- Organisation: `{"country": "Kenya", "timezone": "Africa/Nairobi", "currency": "KES"}`
- School: `{"organisation_id": "org-1", "curriculum": "CBC", "total_students": 500}`
- Warehouse: `{"organisation_id": "org-1", "location": "Nairobi", "capacity_sqm": 1000}`

## Tenant-Scoped Tables

Tables that contain tenant-specific data and include `tenant_id` (or equivalent) scoping.

### Example: Students Table

```sql
CREATE TABLE students (
    id INT PRIMARY KEY AUTO_INCREMENT,
    school_id VARCHAR(50) NOT NULL,  -- Tenant scoping (FK to tenant_catalog)
    admission_number VARCHAR(50) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    date_of_birth DATE,
    gender ENUM('M', 'F', 'Other'),
    email VARCHAR(255),
    phone VARCHAR(20),
    address TEXT,
    -- Standard audit columns
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT,
    updated_by INT,
    deleted_at TIMESTAMP NULL,
    -- Foreign keys
    FOREIGN KEY (school_id) REFERENCES tenant_catalog(id) ON DELETE RESTRICT,
    -- Indexes
    INDEX idx_school_id (school_id),
    INDEX idx_deleted_at (deleted_at),
    UNIQUE KEY unique_admission_per_school (school_id, admission_number)
);
```

**Key Points**:
- `school_id` is the tenant scoping column (references `tenant_catalog.id`)
- Unique constraints include `school_id` to prevent cross-tenant conflicts
- All queries must filter by `school_id`

### Example: Fee Structures Table

```sql
CREATE TABLE fee_structures (
    id INT PRIMARY KEY AUTO_INCREMENT,
    school_id VARCHAR(50) NOT NULL,  -- Tenant scoping
    name VARCHAR(100) NOT NULL,
    description TEXT,
    amount DECIMAL(10,2) NOT NULL,
    frequency ENUM('once', 'term', 'year') DEFAULT 'term',
    is_active BOOLEAN DEFAULT TRUE,
    -- Standard audit columns
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT,
    updated_by INT,
    deleted_at TIMESTAMP NULL,
    -- Foreign keys
    FOREIGN KEY (school_id) REFERENCES tenant_catalog(id) ON DELETE RESTRICT,
    -- Indexes
    INDEX idx_school_id (school_id),
    INDEX idx_is_active (is_active),
    INDEX idx_deleted_at (deleted_at)
);
```

### Tenant-Scoped Table Checklist

When creating a new tenant-scoped table:
- ✅ Include `school_id` (or `organisation_id`, `warehouse_id`) column
- ✅ Add foreign key to `tenant_catalog(id)`
- ✅ Add index on tenant scoping column
- ✅ Include tenant scoping column in unique constraints
- ✅ Add standard audit columns (`created_at`, `updated_at`, `created_by`, `updated_by`, `deleted_at`)
- ✅ Always filter queries by tenant scoping column

## Global Tables

Tables that are intentionally global (not scoped to tenants):

### users Table

Users can access multiple schools, so they are global:

```sql
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    active BOOLEAN DEFAULT TRUE,
    metadata JSON,  -- Store accessible school_ids: {"school_ids": ["school-1", "school-2"]}
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

**User-to-School Mapping**:
Option 1: Store in `metadata` JSON field:
```json
{"school_ids": ["school-1", "school-2"], "default_school_id": "school-1"}
```

Option 2: Separate mapping table (future):
```sql
CREATE TABLE user_school_assignments (
    user_id INT NOT NULL,
    school_id VARCHAR(50) NOT NULL,
    role_id INT,  -- Role at this school
    is_default BOOLEAN DEFAULT FALSE,
    PRIMARY KEY (user_id, school_id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (school_id) REFERENCES tenant_catalog(id),
    FOREIGN KEY (role_id) REFERENCES roles(id)
);
```

### Other Global Tables

- `roles` - Role definitions (shared across all tenants)
- `migrations` - Database migration history (system metadata)
- `audit_seals` - Daily audit log seals (cryptographic integrity, not tenant-specific)

## Naming Conventions

### Table Names
- **Plural**: Use plural nouns (e.g., `students`, `invoices`, `books`)
- **Lowercase**: All lowercase with underscores (e.g., `fee_structures`, `class_subjects`)
- **CI4 Prefix**: Framework tables prefixed with `` (e.g., `users`, `roles`)

### Column Names
- **Lowercase**: All lowercase with underscores (e.g., `first_name`, `date_of_birth`)
- **Foreign Keys**: Use `{table}_id` format (e.g., `student_id`, `school_id`)
- **Booleans**: Prefix with `is_` or `has_` (e.g., `is_active`, `has_paid`)
- **Timestamps**: Use `_at` suffix (e.g., `created_at`, `deleted_at`, `paid_at`)

### Index Names
- **Primary Key**: Implicitly named (no custom name)
- **Foreign Keys**: `fk_{table}_{column}` (e.g., `fk_students_school_id`)
- **Indexes**: `idx_{column(s)}` (e.g., `idx_school_id`, `idx_school_deleted`)
- **Unique Keys**: `unique_{column(s)}` (e.g., `unique_admission_per_school`)

## Migration Strategy

### Migration Files

Location: `app/Modules/{Module}/Database/Migrations/`

**Naming**: `YYYY-MM-DD-HHMMSS_DescriptiveName.php`

Example: `2024-10-06-000006_CreateTenantCatalog.php`

### Migration Template (Tenant-Scoped Table)

```php
<?php

namespace Modules\Learning\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateStudents extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'auto_increment' => true,
            ],
            'school_id' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
            ],
            'admission_number' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
            ],
            'first_name' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
            // ... other columns
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
        $this->forge->addUniqueKey(['school_id', 'admission_number']);
        $this->forge->addForeignKey('school_id', 'tenant_catalog', 'id', 'RESTRICT', 'RESTRICT');
        
        $this->forge->createTable('students');
    }

    public function down()
    {
        $this->forge->dropTable('students');
    }
}
```

## Tenant Isolation Rules

### Critical Security Requirements

**ALL tenant-scoped queries MUST follow these rules to prevent data leaks**:

1. **Use TenantAwareModel**: All models handling tenant data must extend `App\Models\TenantAwareModel`
2. **Always Set Tenant ID**: Call `setTenantId($tenantId)` before any query
3. **Verify Tenant Context**: Controllers must resolve and verify tenant context from request
4. **Never Trust Client Input**: Tenant ID must come from authenticated user context, not client request
5. **Test Tenant Isolation**: Every tenant-scoped feature must have tests verifying isolation

### TenantAwareModel Usage

**Basic Usage**:

```php
<?php

namespace Modules\Learning\Models;

use App\Models\TenantAwareModel;

class StudentModel extends TenantAwareModel
{
    protected $table = 'students';
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    
    // Specify tenant scoping column (default is 'school_id')
    protected $tenantColumn = 'school_id';
    
    // Enable tenant scoping enforcement (default is true)
    protected $enforceTenantScoping = true;
    
    protected $allowedFields = [
        'school_id',
        'admission_number',
        'first_name',
        'last_name',
        // ... other fields
    ];
    
    protected $useTimestamps = true;
    protected $useSoftDeletes = true;
}
```

**In Controllers**:

```php
<?php

namespace Modules\Learning\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use Modules\Learning\Models\StudentModel;

class StudentsController extends ResourceController
{
    protected $modelName = StudentModel::class;
    
    public function index()
    {
        // Get tenant ID from authenticated request
        $tenantId = $this->request->getAttribute('tenant_id');
        
        if (!$tenantId) {
            return $this->failForbidden('Tenant context not found');
        }
        
        // Set tenant ID - all subsequent queries will be scoped
        $students = $this->model
            ->setTenantId($tenantId)
            ->findAll();
        
        return $this->respond(['data' => $students]);
    }
    
    public function show($id = null)
    {
        $tenantId = $this->request->getAttribute('tenant_id');
        
        // Verify student belongs to tenant
        if (!$this->model->setTenantId($tenantId)->belongsToTenant($id)) {
            return $this->failNotFound('Student not found');
        }
        
        $student = $this->model->find($id);
        
        return $this->respond(['data' => $student]);
    }
    
    public function create()
    {
        $tenantId = $this->request->getAttribute('tenant_id');
        $data = $this->request->getJSON(true);
        
        // Tenant ID is automatically injected by TenantAwareModel
        $studentId = $this->model
            ->setTenantId($tenantId)
            ->insert($data);
        
        return $this->respondCreated(['data' => $this->model->find($studentId)]);
    }
}
```

### Testing Tenant Isolation

**Required Tests for Every Tenant-Scoped Feature**:

```php
<?php

namespace Tests\Modules\Learning;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

class StudentModelTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    
    /**
     * Test that queries without tenant ID throw exception
     */
    public function testThrowsExceptionWithoutTenantId()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Tenant ID must be set');
        
        $model = new \Modules\Learning\Models\StudentModel();
        $model->findAll(); // Should throw
    }
    
    /**
     * Test that findAll only returns current tenant's records
     */
    public function testFindAllOnlyReturnsTenantRecords()
    {
        // Create students for different tenants
        $this->db->table('students')->insert([
            'school_id' => 'school-1',
            'first_name' => 'Alice',
            'last_name' => 'Smith',
        ]);
        $this->db->table('students')->insert([
            'school_id' => 'school-2',
            'first_name' => 'Bob',
            'last_name' => 'Jones',
        ]);
        
        $model = new \Modules\Learning\Models\StudentModel();
        
        // Query as school-1
        $school1Students = $model->setTenantId('school-1')->findAll();
        
        $this->assertCount(1, $school1Students);
        $this->assertEquals('Alice', $school1Students[0]->first_name);
    }
    
    /**
     * Test that find() cannot access other tenant's records
     */
    public function testFindCannotAccessOtherTenantRecord()
    {
        // Create student for school-1
        $this->db->table('students')->insert([
            'id' => 100,
            'school_id' => 'school-1',
            'first_name' => 'Alice',
        ]);
        
        $model = new \Modules\Learning\Models\StudentModel();
        
        // Try to access as school-2
        $student = $model->setTenantId('school-2')->find(100);
        
        $this->assertNull($student); // Should not find it
    }
}
```

### Common Pitfalls

**❌ DON'T: Query without setting tenant ID**:
```php
// BAD: No tenant scoping
$students = $this->studentModel->findAll();
```

**✅ DO: Always set tenant ID first**:
```php
// GOOD: Tenant scoped
$students = $this->studentModel
    ->setTenantId($tenantId)
    ->findAll();
```

**❌ DON'T: Trust tenant ID from client request**:
```php
// BAD: Client could send any tenant_id
$tenantId = $this->request->getVar('tenant_id');
```

**✅ DO: Get tenant ID from authenticated user context**:
```php
// GOOD: Tenant ID from authenticated session
$tenantId = $this->request->getAttribute('tenant_id');
```

## References

- [System Overview](01-SYSTEM-OVERVIEW.md)
- [Architecture](ARCHITECTURE.md)
- [Multi-Tenant Feature Documentation](features/27-MULTI-TENANT.md)
- [Database Migrations Guide](development/DATABASE-MIGRATIONS.md)
- [TenantAwareModel](../app/Models/TenantAwareModel.php)

---

**Version**: 1.0.0
