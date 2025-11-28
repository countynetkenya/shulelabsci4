# 🎯 Multi-Tenant Support

**Last Updated**: 2025-11-22  
**Status**: Foundation Complete (Phase 1/2), Productization Planned (Phase 3)

## Overview

ShuleLabs is architected with **multi-tenancy by design** from Phase 1 onwards. The `tenant_catalog` table stores organisations, schools, and warehouses, enabling a single installation to serve multiple schools with proper data isolation and tenant context resolution.

**Key Point**: Multi-tenancy is not a Phase 3 feature being introduced for the first time. Instead, **Phase 1 and Phase 2 establish tenant-aware code paths**, while **Phase 3 focuses on tenant orchestration and productization** (management UI, billing, custom branding, etc.).

## Table of Contents

- [Overview](#overview)
- [Architecture Strategy](#architecture-strategy)
- [Phase 1/2: Tenant-Aware Foundation](#phase-12-tenant-aware-foundation)
- [Phase 3: Tenant Orchestration & Productization](#phase-3-tenant-orchestration--productization)
- [Implementation Details](#implementation-details)
- [Usage](#usage)
- [Initial Setup](#initial-setup)
- [Testing](#testing)
- [References](#references)

## Architecture Strategy

### Multi-Tenancy Across Phases

1. **Phase 1 (Complete)**: Tenant-aware foundation
   - `tenant_catalog` table for storing organisations, schools, warehouses
   - `TenantResolver` service for resolving tenant context from requests
   - Tenant-scoped audit logging (all events include `tenant_id`)
   - Database schema designed with row-level `tenant_id` on applicable tables

2. **Phase 2 (In Progress)**: Tenant-aware features
   - All new APIs (Admissions, Billing, Portals, Communications, Inventory, Library) built with tenant context from the start
   - Controllers, services, and repositories use `TenantContext` abstraction
   - Data queries automatically scoped to active tenant
   - Mobile and portal backends designed for multi-school deployment

3. **Phase 3 (Planned)**: Tenant orchestration and productization
   - Tenant management UI (create, configure, deactivate schools)
   - Billing per tenant (subscription tracking, usage reports)
   - Custom branding (logos, colors, domain mapping per school)
   - Tenant analytics (usage dashboards, performance metrics)
   - Advanced isolation options (schema-per-tenant, database-per-tenant)
   - Migration tools (onboarding, data import/export)

### Data Partitioning Approach

**Row-Level Isolation** (Current Strategy):
- Shared database and schema across all tenants
- Tables include `tenant_id` column (or equivalent like `school_id`, `organisation_id`)
- Application-level query scoping enforces tenant isolation
- Suitable for most use cases; cost-effective and operationally simple

**Future Isolation Options** (Phase 3):
- **Schema-per-tenant**: Each school gets its own schema within a shared database
- **Database-per-tenant**: High-security tenants can opt for dedicated databases
- Configurable per tenant based on security, compliance, or performance needs

## Phase 1/2: Tenant-Aware Foundation

### Requirements

- `tenant_catalog` table (created via Foundation module migration)
- TenantResolver service for resolving tenant context from requests
- Tenant identification via headers (X-Tenant-Context, X-School-ID, X-Organisation-ID) or query parameters

### Implementation

The multi-tenant foundation is implemented in the Foundation module:

- **Migration**: `app/Modules/Foundation/Database/Migrations/2024-10-06-000006_CreateTenantCatalog.php`
- **Service**: `app/Modules/Foundation/Services/TenantResolver.php`
- **Table**: `tenant_catalog` with columns: `id`, `tenant_type`, `name`, `metadata`, `created_at`, `updated_at`

### Tenant Types

- `organisation` - Parent organisation managing multiple schools
- `school` - Individual school/institution
- `warehouse` - Inventory/asset warehouse

### Tenant Metadata

The `metadata` JSON column stores tenant-specific information:
- For schools: `{"organisation_id": "org-1", "curriculum": "CBC"}`
- For organisations: `{"country": "Kenya"}`

### Tenant-Aware Code Patterns

**Controllers**:
```php
// Resolve tenant context from request
$tenantContext = $this->tenantResolver->fromRequest($this->request);
$schoolId = $tenantContext['school']['id'] ?? null;

// Pass tenant context to services
$students = $this->studentService->listStudents($schoolId);
```

**Services**:
```php
public function listStudents(string $schoolId): array
{
    // All queries scoped to tenant
    return $this->studentModel
        ->where('school_id', $schoolId)
        ->findAll();
}
```

**Audit Logging**:
```php
$this->auditService->recordEvent(
    'student.created',
    'create',
    ['tenant_id' => $schoolId, 'actor_id' => $userId],
    null,
    $studentData
);
```

## Phase 3: Tenant Orchestration & Productization

### Planned Features (Q3 2025)

**Tenant Management UI**:
- Create and configure new schools via admin interface
- View all schools in a tenant directory
- Activate/deactivate schools
- Assign quota limits (storage, users, modules)
- Manage school administrators

**Billing Per Tenant**:
- Track usage per school (active users, storage, API calls)
- Generate invoices based on subscription plans
- Payment collection and receipt generation
- Billing analytics and reports

**Custom Branding**:
- Upload school logo, colors, and theme
- Custom domain mapping (school1.shulelabs.com)
- White-label options for premium tenants
- Email template customization per school

**Tenant Analytics**:
- Usage dashboards (logins, feature adoption, storage)
- Performance metrics per school
- Comparative analytics across tenants
- Predictive insights (churn risk, expansion opportunities)

**Migration Tools**:
- Tenant onboarding wizard (guided setup)
- Data import from CSV/Excel (bulk student upload, etc.)
- Data export and backup per tenant
- Tenant cloning for new academic years

**Advanced Isolation**:
- Option to provision schema-per-tenant
- Option to provision database-per-tenant for high-security needs
- Tenant-specific encryption keys
- Compliance reporting (GDPR, data residency)

## Implementation Details

## Usage

### Resolving Tenant Context

Use the `TenantResolver` service to determine the active tenant from request headers:

```php
use Modules\Foundation\Services\TenantResolver;

$resolver = new TenantResolver();
$context = $resolver->fromRequest($request);

// Returns: ['tenant_id' => 'school-1', 'school' => [...], 'organisation' => [...]]
```

### Mobile/API Integration

Mobile apps and API clients should include tenant headers:

```http
X-School-ID: school-1
X-Organisation-ID: org-1
```

Or use the combined JSON header:

```http
X-Tenant-Context: {"school_id": "school-1", "organisation_id": "org-1"}
```

### Tenant Context in Controllers

All controllers should resolve and use tenant context:

```php
class StudentController extends BaseController
{
    public function list()
    {
        $tenantContext = $this->tenantResolver->fromRequest($this->request);
        $schoolId = $tenantContext['school']['id'] ?? null;
        
        if (!$schoolId) {
            return $this->failUnauthorized('Tenant context required');
        }
        
        $students = $this->studentService->listStudents($schoolId);
        return $this->respond($students);
    }
}
```

## Initial Setup

### Web-Based Installer (Recommended)

ShuleLabs provides a web-based installer to bootstrap the first organisation, school, and admin user:

1. Run migrations:
   ```bash
   php bin/migrate/latest
   ```

2. Visit `/install` in your browser

3. Follow the three-step wizard:
   - **Environment check**: Verifies database and migrations
   - **Organisation & School setup**: Creates first tenant entries in `tenant_catalog`
   - **Admin account**: Creates Super Admin user linked to the school

4. Set `app.installed = true` in `.env` after completion

### Manual Setup (Advanced)

Insert tenant records directly:

```php
$db->table('tenant_catalog')->insert([
    'id' => 'org-1',
    'tenant_type' => 'organisation',
    'name' => 'My Organisation',
    'metadata' => json_encode(['country' => 'Kenya']),
    'created_at' => date('Y-m-d H:i:s'),
]);

$db->table('tenant_catalog')->insert([
    'id' => 'school-1',
    'tenant_type' => 'school',
    'name' => 'My School',
    'metadata' => json_encode(['organisation_id' => 'org-1', 'curriculum' => 'CBC']),
    'created_at' => date('Y-m-d H:i:s'),
]);
```

## Testing

See `tests/Foundation/TenantResolverTest.php` for examples of:
- Resolving tenants from headers
- Handling missing tenants
- Working with tenant metadata

### Test Coverage

**Phase 1/2 Testing Focus**:
- Tenant context resolution from various request formats
- Query scoping with tenant_id
- Audit trail includes tenant context
- API endpoints properly enforce tenant isolation

**Phase 3 Testing Focus**:
- Tenant provisioning workflows
- Billing calculations per tenant
- Custom branding application
- Tenant switching for multi-school admins
- Schema-per-tenant isolation (if implemented)

## Data Model Examples

### Tables with Tenant Scoping

**Student Table**:
```sql
CREATE TABLE students (
    id INT PRIMARY KEY AUTO_INCREMENT,
    school_id VARCHAR(50) NOT NULL,  -- Tenant scoping
    admission_number VARCHAR(50) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    -- ... other columns
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES tenant_catalog(id),
    UNIQUE KEY unique_admission_per_school (school_id, admission_number)
);
```

**Fee Structure Table**:
```sql
CREATE TABLE fee_structures (
    id INT PRIMARY KEY AUTO_INCREMENT,
    school_id VARCHAR(50) NOT NULL,  -- Tenant scoping
    name VARCHAR(100) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    -- ... other columns
    FOREIGN KEY (school_id) REFERENCES tenant_catalog(id)
);
```

### Global Tables (No Tenant Scoping)

Some tables are intentionally global and not scoped to tenants:
- `users` (users can belong to multiple schools)
- `roles` (role definitions are shared)
- `migrations` (system metadata)
- `audit_seals` (cryptographic seals are global)

User-to-school mapping is handled via:
- User metadata (JSON field storing accessible school_ids)
- Separate user_school_assignments table (for explicit many-to-many relationships)

## Future Enhancements (Phase 3)

Full multi-tenant management features are planned for Phase 3 (Q3 2025):
- Dynamic tenant provisioning via admin UI
- Per-tenant database isolation options
- Tenant-specific billing and quotas
- Advanced tenant relationship management (school groups, franchises)
- Tenant switching for multi-school users
- Tenant analytics dashboard
- Compliance and data residency controls

The Phase 1/2 implementation provides the architectural foundation for these features by establishing the tenant catalog structure, context resolution patterns, and tenant-aware data modeling practices.

## References

- [System Overview](../01-SYSTEM-OVERVIEW.md)
- [Architecture](../ARCHITECTURE.md)
- [Master Implementation Plan](../02-MASTER-IMPLEMENTATION-PLAN.md)
- [Foundation Module](../modules/Foundation/README.md)
- [Database Schema](../DATABASE.md)

---

**Version**: 1.0.0
