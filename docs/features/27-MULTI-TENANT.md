# 🎯 Multi-Tenant Support

**Last Updated**: 2025-11-22  
**Status**: Draft

## Overview

ShuleLabs supports multi-tenant architecture through the `ci4_tenant_catalog` table,
which stores organisations, schools, and warehouses. This enables a single installation
to serve multiple schools with proper data isolation and tenant context resolution.

## Table of Contents

- [Overview](#overview)
- [Requirements](#requirements)
- [Implementation](#implementation)
- [Usage](#usage)
- [Initial Setup](#initial-setup)
- [Testing](#testing)
- [References](#references)

## Requirements

- `ci4_tenant_catalog` table (created via Foundation module migration)
- TenantResolver service for resolving tenant context from requests
- Tenant identification via headers (X-Tenant-Context, X-School-ID, X-Organisation-ID) or query parameters

## Implementation

The multi-tenant system is implemented in the Foundation module:

- **Migration**: `app/Modules/Foundation/Database/Migrations/2024-10-06-000006_CreateTenantCatalog.php`
- **Service**: `app/Modules/Foundation/Services/TenantResolver.php`
- **Table**: `ci4_tenant_catalog` with columns: `id`, `tenant_type`, `name`, `metadata`, `created_at`, `updated_at`

### Tenant Types

- `organisation` - Parent organisation managing multiple schools
- `school` - Individual school/institution
- `warehouse` - Inventory/asset warehouse

### Tenant Metadata

The `metadata` JSON column stores tenant-specific information:
- For schools: `{"organisation_id": "org-1", "curriculum": "CBC"}`
- For organisations: `{"country": "Kenya"}`

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
   - **Organisation & School setup**: Creates first tenant entries in `ci4_tenant_catalog`
   - **Admin account**: Creates Super Admin user linked to the school

4. Set `app.installed = true` in `.env` after completion

### Manual Setup (Advanced)

Insert tenant records directly:

```php
$db->table('ci4_tenant_catalog')->insert([
    'id' => 'org-1',
    'tenant_type' => 'organisation',
    'name' => 'My Organisation',
    'metadata' => json_encode(['country' => 'Kenya']),
    'created_at' => date('Y-m-d H:i:s'),
]);

$db->table('ci4_tenant_catalog')->insert([
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

## Future Enhancements (Phase 3)

Full multi-tenant management features are planned for Phase 3:
- Dynamic tenant provisioning via admin UI
- Per-tenant database isolation options
- Tenant-specific billing and quotas
- Advanced tenant relationship management
- Tenant switching for multi-school users

The current implementation provides the foundation for these features by
establishing the tenant catalog structure and context resolution patterns.

## References

- [System Overview](../01-SYSTEM-OVERVIEW.md)
- [Architecture](../ARCHITECTURE.md)
- [Foundation Module](../modules/Foundation.md)

---

**Version**: 1.0.0
