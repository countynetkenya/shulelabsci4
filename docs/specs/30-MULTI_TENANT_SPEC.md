# 🏢 Multi-Tenant Productization Specification

**Version**: 1.0.0
**Status**: Phase 3 (Future)
**Last Updated**: 2025-11-27

---

## Part 1: Feature Definition (The "What" & "Why")
*Target Audience: Product Owners, Stakeholders, Developers*

### 1.1 Overview
The Multi-Tenant Productization module transforms ShuleLabs from a single-school system into a full SaaS platform. It provides tenant provisioning UI, management dashboard, custom branding, tenant-specific configuration, billing per tenant, usage tracking, migration tools, and advanced isolation options including schema-per-tenant for enterprise customers.

### 1.2 User Stories

- **As a Platform Admin**, I want to provision new school tenants, so that onboarding is streamlined.
- **As a School Admin**, I want to customize branding (logo, colors), so that the system reflects our identity.
- **As a Platform Admin**, I want to monitor usage per tenant, so that I can manage resources.
- **As a Platform Admin**, I want to bill tenants based on usage, so that we generate revenue.
- **As a School Admin**, I want to configure school-specific settings, so that the system fits our needs.
- **As a Platform Admin**, I want to migrate a tenant to dedicated infrastructure, so that we can offer premium tiers.

### 1.3 Tenant Tiers

| Tier | Isolation | Features | Use Case |
|:-----|:----------|:---------|:---------|
| **Starter** | Shared DB | Basic modules | Small schools |
| **Professional** | Shared DB, separate schemas | All modules | Medium schools |
| **Enterprise** | Dedicated DB | All + custom | Large schools |
| **Private** | Dedicated infrastructure | Full customization | School groups |

### 1.4 Acceptance Criteria

- [ ] Tenant provisioning via UI and API.
- [ ] Automatic database/schema creation.
- [ ] Custom domain configuration.
- [ ] Logo and theme customization.
- [ ] Feature toggles per tenant.
- [ ] Usage metering and limits.
- [ ] Billing integration.
- [ ] Tenant suspension and deletion.
- [ ] Data export for offboarding.
- [ ] Tier upgrade/downgrade paths.

---

## Part 2: Technical Specification (The "How")
*Target Audience: Developers, Architects*

### 2.1 Database Schema

#### `tenants`
(Enhanced schools table)
```sql
CREATE TABLE tenants (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    code VARCHAR(50) NOT NULL UNIQUE,
    subdomain VARCHAR(100) NOT NULL UNIQUE,
    custom_domain VARCHAR(255),
    tier ENUM('starter', 'professional', 'enterprise', 'private') DEFAULT 'starter',
    status ENUM('pending', 'active', 'suspended', 'cancelled', 'archived') DEFAULT 'pending',
    
    -- Isolation Settings
    isolation_type ENUM('shared', 'schema', 'database', 'server') DEFAULT 'shared',
    database_name VARCHAR(100),
    schema_name VARCHAR(100),
    server_host VARCHAR(255),
    
    -- Branding
    logo_path VARCHAR(500),
    favicon_path VARCHAR(500),
    primary_color VARCHAR(20) DEFAULT '#3B82F6',
    secondary_color VARCHAR(20) DEFAULT '#10B981',
    custom_css TEXT,
    
    -- Configuration
    timezone VARCHAR(50) DEFAULT 'Africa/Nairobi',
    locale VARCHAR(10) DEFAULT 'en',
    currency VARCHAR(3) DEFAULT 'KES',
    academic_year_start_month INT DEFAULT 1,
    features_enabled JSON,
    settings JSON,
    
    -- Billing
    billing_plan_id INT,
    billing_cycle ENUM('monthly', 'annual') DEFAULT 'annual',
    billing_start_date DATE,
    next_billing_date DATE,
    
    -- Limits
    max_students INT DEFAULT 500,
    max_staff INT DEFAULT 50,
    max_storage_gb INT DEFAULT 10,
    
    -- Metadata
    provisioned_at DATETIME,
    provisioned_by INT,
    suspended_at DATETIME,
    suspension_reason TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_subdomain (subdomain),
    INDEX idx_custom_domain (custom_domain),
    INDEX idx_status (status)
);
```

#### `tenant_usage`
Usage tracking.
```sql
CREATE TABLE tenant_usage (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    tenant_id INT NOT NULL,
    period_date DATE NOT NULL,
    metric_type VARCHAR(100) NOT NULL,
    metric_value DECIMAL(15,4) NOT NULL,
    unit VARCHAR(50),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    UNIQUE KEY uk_tenant_date_metric (tenant_id, period_date, metric_type),
    INDEX idx_period (period_date)
);
```

#### `billing_plans`
Subscription plans.
```sql
CREATE TABLE billing_plans (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    tier ENUM('starter', 'professional', 'enterprise') NOT NULL,
    billing_cycle ENUM('monthly', 'annual') NOT NULL,
    base_price DECIMAL(10,2) NOT NULL,
    per_student_price DECIMAL(6,2) DEFAULT 0,
    features JSON,
    limits JSON,
    is_active BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
```

#### `billing_invoices`
Tenant invoices.
```sql
CREATE TABLE billing_invoices (
    id INT PRIMARY KEY AUTO_INCREMENT,
    tenant_id INT NOT NULL,
    invoice_number VARCHAR(50) NOT NULL,
    period_start DATE NOT NULL,
    period_end DATE NOT NULL,
    base_amount DECIMAL(12,2) NOT NULL,
    usage_amount DECIMAL(12,2) DEFAULT 0,
    discount_amount DECIMAL(12,2) DEFAULT 0,
    tax_amount DECIMAL(12,2) DEFAULT 0,
    total_amount DECIMAL(12,2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'KES',
    status ENUM('draft', 'sent', 'paid', 'overdue', 'cancelled') DEFAULT 'draft',
    due_date DATE NOT NULL,
    paid_at DATETIME,
    payment_reference VARCHAR(100),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    INDEX idx_tenant_status (tenant_id, status)
);
```

#### `tenant_migrations`
Migration tracking.
```sql
CREATE TABLE tenant_migrations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    tenant_id INT NOT NULL,
    migration_type ENUM('upgrade', 'downgrade', 'isolation_change', 'export', 'import') NOT NULL,
    source_tier VARCHAR(50),
    target_tier VARCHAR(50),
    status ENUM('pending', 'in_progress', 'completed', 'failed', 'rolled_back') DEFAULT 'pending',
    started_at DATETIME,
    completed_at DATETIME,
    error_message TEXT,
    log TEXT,
    initiated_by INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);
```

### 2.2 Tenant Isolation Strategies

#### Shared Database (Starter)
```
┌─────────────────────────────────────────┐
│            Shared Database               │
├─────────────────────────────────────────┤
│  users        (school_id = 1, 2, 3...)  │
│  students     (school_id = 1, 2, 3...)  │
│  invoices     (school_id = 1, 2, 3...)  │
│  ...                                     │
└─────────────────────────────────────────┘
```

#### Schema Per Tenant (Professional)
```
┌─────────────────────────────────────────┐
│            Shared Database               │
├─────────────────────────────────────────┤
│  schema_school_1                         │
│    ├── users                             │
│    ├── students                          │
│    └── invoices                          │
│  schema_school_2                         │
│    ├── users                             │
│    ├── students                          │
│    └── invoices                          │
└─────────────────────────────────────────┘
```

#### Database Per Tenant (Enterprise)
```
┌──────────────────┐  ┌──────────────────┐
│   school_1_db     │  │   school_2_db     │
├──────────────────┤  ├──────────────────┤
│  users            │  │  users            │
│  students         │  │  students         │
│  invoices         │  │  invoices         │
└──────────────────┘  └──────────────────┘
```

### 2.3 API Endpoints

| Method | Endpoint | Description | Access |
|:-------|:---------|:------------|:-------|
| **Tenants** |
| GET | `/api/v1/platform/tenants` | List tenants | Platform |
| POST | `/api/v1/platform/tenants` | Provision tenant | Platform |
| GET | `/api/v1/platform/tenants/{id}` | Tenant details | Platform |
| PUT | `/api/v1/platform/tenants/{id}` | Update tenant | Platform |
| POST | `/api/v1/platform/tenants/{id}/suspend` | Suspend tenant | Platform |
| POST | `/api/v1/platform/tenants/{id}/activate` | Activate tenant | Platform |
| **Branding** |
| GET | `/api/v1/tenant/branding` | Get branding | Admin |
| PUT | `/api/v1/tenant/branding` | Update branding | Admin |
| POST | `/api/v1/tenant/branding/logo` | Upload logo | Admin |
| **Configuration** |
| GET | `/api/v1/tenant/config` | Get config | Admin |
| PUT | `/api/v1/tenant/config` | Update config | Admin |
| GET | `/api/v1/tenant/features` | List features | Admin |
| PUT | `/api/v1/tenant/features` | Toggle features | Admin |
| **Usage & Billing** |
| GET | `/api/v1/platform/tenants/{id}/usage` | Get usage | Platform |
| GET | `/api/v1/platform/tenants/{id}/invoices` | List invoices | Platform |
| GET | `/api/v1/tenant/billing` | Tenant billing | Admin |
| **Migration** |
| POST | `/api/v1/platform/tenants/{id}/migrate` | Start migration | Platform |
| GET | `/api/v1/platform/migrations/{id}` | Migration status | Platform |
| POST | `/api/v1/platform/tenants/{id}/export` | Export data | Platform |

### 2.4 Module Structure

```
app/Modules/MultiTenant/
├── Config/
│   ├── Routes.php
│   └── Tenancy.php
├── Controllers/
│   ├── Api/
│   │   ├── TenantController.php
│   │   ├── BrandingController.php
│   │   ├── ConfigController.php
│   │   ├── BillingController.php
│   │   └── MigrationController.php
│   └── Web/
│       ├── PlatformDashboardController.php
│       └── TenantSettingsController.php
├── Models/
│   ├── TenantModel.php
│   ├── TenantUsageModel.php
│   ├── BillingPlanModel.php
│   ├── BillingInvoiceModel.php
│   └── TenantMigrationModel.php
├── Services/
│   ├── TenantProvisioningService.php
│   ├── SchemaManagerService.php
│   ├── DatabaseProvisioningService.php
│   ├── BrandingService.php
│   ├── FeatureToggleService.php
│   ├── UsageMeteringService.php
│   ├── BillingService.php
│   ├── MigrationService.php
│   └── DataExportService.php
├── Jobs/
│   ├── ProvisionTenantJob.php
│   ├── MigrateTenantJob.php
│   ├── CalculateUsageJob.php
│   └── GenerateInvoicesJob.php
├── Middleware/
│   ├── TenantResolver.php
│   ├── FeatureGate.php
│   └── UsageLimiter.php
├── Database/
│   └── Migrations/
│       └── 2025-11-27-000001_CreateTenancyTables.php
└── Views/
    ├── platform/
    │   ├── dashboard.php
    │   └── tenants/
    └── tenant/
        ├── branding.php
        └── settings.php
```

---

## Part 3: Provisioning Workflow

### 3.1 New Tenant Flow
```
1. Platform admin creates tenant (or self-service)
2. System validates and creates tenant record
3. Based on tier:
   - Starter: Just set school_id scope
   - Professional: Create schema, run migrations
   - Enterprise: Provision database, run migrations
4. Create admin user with credentials
5. Apply default configuration
6. Send welcome email
7. Tenant is active
```

### 3.2 Migration Flow (Upgrade)
```
1. Admin initiates upgrade
2. System schedules migration window
3. Lock tenant for writes
4. Export data from current location
5. Create new isolation level
6. Import data to new location
7. Update tenant configuration
8. Validate data integrity
9. Switch tenant to new location
10. Unlock tenant
11. Clean up old location
```

---

## Part 4: Development Checklist

- [ ] **Provisioning**: Tenant creation.
- [ ] **Provisioning**: Schema creation.
- [ ] **Provisioning**: Database creation.
- [ ] **Branding**: Logo and colors.
- [ ] **Branding**: Custom CSS.
- [ ] **Features**: Toggle system.
- [ ] **Limits**: Enforcement.
- [ ] **Usage**: Metering.
- [ ] **Billing**: Invoice generation.
- [ ] **Migration**: Tier changes.
- [ ] **Export**: Data portability.
