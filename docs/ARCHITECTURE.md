# 🏗️ Architecture - ShuleLabs CI4

**Last Updated**: 2025-11-22  
**Version**: 1.0.0

## System Architecture Overview

ShuleLabs is built on a modular, API-first architecture using CodeIgniter 4 framework. The system follows SOLID principles and clean architecture patterns.

## 🎨 Architectural Principles

1. **Modular Design**: Self-contained, independent modules
2. **API-First**: All functionality exposed via REST APIs
3. **Security by Design**: Role-based access control throughout
4. **Tenant-Aware by Design**: Multi-tenancy from the start with `TenantContext` abstraction
5. **Observability as a Guardrail**: Baseline logging, metrics, and health checks for all features
6. **Scalability**: Horizontal and vertical scaling supported
7. **Maintainability**: Clear separation of concerns
8. **Testability**: Comprehensive test coverage

## 🏢 High-Level Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                    Presentation Layer                        │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐      │
│  │  Web UI      │  │  Mobile App  │  │  API Clients │      │
│  └──────┬───────┘  └──────┬───────┘  └──────┬───────┘      │
└─────────┼──────────────────┼──────────────────┼─────────────┘
          │                  │                  │
┌─────────┼──────────────────┼──────────────────┼─────────────┐
│         │        API Gateway / Router         │             │
│         └──────────────────┬───────────────────┘             │
│                            │                                 │
│  ┌─────────────────────────┴──────────────────────────────┐ │
│  │              Application Services Layer                 │ │
│  │  ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐  │ │
│  │  │Foundation│ │ Learning │ │ Finance  │ │    Hr    │  │ │
│  │  └──────────┘ └──────────┘ └──────────┘ └──────────┘  │ │
│  │  ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐  │ │
│  │  │Inventory │ │ Library  │ │ Threads  │ │  Mobile  │  │ │
│  │  └──────────┘ └──────────┘ └──────────┘ └──────────┘  │ │
│  └─────────────────────────┬──────────────────────────────┘ │
│                            │                                 │
│  ┌─────────────────────────┴──────────────────────────────┐ │
│  │                 Domain Layer                            │ │
│  │  Entities, Value Objects, Domain Services               │ │
│  └─────────────────────────┬──────────────────────────────┘ │
│                            │                                 │
│  ┌─────────────────────────┴──────────────────────────────┐ │
│  │              Infrastructure Layer                       │ │
│  │  Database, Cache, Queue, External Services              │ │
│  └─────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────┘
```

## 📦 Module Architecture

Each module follows a consistent structure:

```
app/Modules/{ModuleName}/
├── Config/           Module configuration
├── Controllers/      API and web controllers
├── Models/          Database models
├── Entities/        Domain entities
├── Services/        Business logic services
├── Filters/         Request/response filters
├── Views/           UI templates (if applicable)
├── Database/        
│   ├── Migrations/  Schema migrations
│   └── Seeds/       Test data
├── Tests/           Unit and integration tests
└── Language/        Internationalization
```

### Module Responsibilities

#### Foundation Module
**Purpose**: Core system services

**Components**:
- Audit Service: Activity logging and compliance (with tenant context)
- Ledger Service: Double-entry accounting
- Integration Registry: External system tracking
- Maker-Checker Service: Approval workflows
- QR Service: QR code generation and validation
- **Tenant Resolver**: Multi-tenant context resolution from requests
- **TenantContext Abstraction**: Used throughout controllers, services, and repositories

**Dependencies**: None (foundation layer)

#### Learning Module
**Purpose**: Academic management

**Components**:
- Class and section management
- Subject allocation
- Timetable management
- Attendance tracking
- Grade management
- Assignment system
- Examination management

**Dependencies**: Foundation

#### Finance Module
**Purpose**: Billing and accounting

**Components**:
- Invoice Service: Bill generation
- Payment Service: Payment processing
- Fee Structure: Fee configuration
- Receipt Service: Payment receipts
- Ledger Integration: Financial recording

**Dependencies**: Foundation (Ledger)

#### Hr Module
**Purpose**: Human resources and payroll

**Components**:
- Employee management
- Attendance tracking
- Leave management
- Payroll processing
- Performance reviews

**Dependencies**: Foundation

#### Inventory Module
**Purpose**: Asset and inventory management

**Components**:
- Asset management
- Stock control
- Requisitions
- Purchase orders
- Supplier management

**Dependencies**: Foundation

#### Library Module
**Purpose**: Library management

**Components**:
- Book catalog
- Borrowing system
- Fine calculation
- Member management

**Dependencies**: Foundation

#### Threads Module
**Purpose**: Communication and messaging

**Components**:
- Internal messaging
- Announcements
- Notifications
- Event system

**Dependencies**: Foundation

#### Mobile Module
**Purpose**: Mobile app backend

**Components**:
- Mobile APIs
- Push notifications
- Mobile authentication
- Offline sync

**Dependencies**: All modules (aggregator)

#### Gamification Module
**Purpose**: Engagement and rewards

**Components**:
- Points system
- Badges
- Leaderboards
- Achievements

**Dependencies**: Foundation, Learning

## 🔄 Data Flow Architecture

### Request Flow

```
HTTP Request
    ↓
Router (Routes.php)
    ↓
Middleware/Filters
    ↓
Controller
    ↓
Service Layer
    ↓
Repository/Model
    ↓
Database
    ↓
Response (JSON/HTML)
```

### Authentication Flow

```
1. Login Request → AuthController
2. Validate Credentials → AuthService
3. Generate JWT Token → JWT Library
4. Store Session → Database
5. Return Token + User Data
6. Client Stores Token
7. Subsequent Requests Include Token
8. Verify Token → JWT Filter
9. Load User Context
10. Process Request
```

## 🗄️ Database Architecture

### Database Schema Layers

1. **CI4 Core Tables**: User authentication and roles
   - `users`
   - `roles`
   - `user_roles`
   - `migrations`

2. **Foundation Tables**: Audit and ledger
   - `audit_events`
   - `audit_seals`
   - `ledger_entries`
   - `ledger_transactions`
   - `qr_tokens`
   - `qr_scans`

3. **Module Tables**: Domain-specific tables
   - Learning: classes, subjects, attendance, grades
   - Finance: invoices, payments, fees
   - Hr: employees, payroll, leave
   - Inventory: assets, stock, requisitions
   - Library: books, borrowings, fines

### Database Design Principles

- **Normalization**: 3NF minimum
- **Referential Integrity**: Foreign keys enforced
- **Soft Deletes**: `deleted_at` column pattern
- **Audit Columns**: `created_at`, `updated_at`, `created_by`, `updated_by`
- **UUID Support**: Optional UUID primary keys for distributed systems

See: [Database Documentation](DATABASE.md)

## 🔐 Security Architecture

### Multi-Layer Security

```
┌──────────────────────────────────────┐
│    1. Network Layer                  │
│    - HTTPS/TLS                       │
│    - Firewall Rules                  │
│    - DDoS Protection                 │
└────────────┬─────────────────────────┘
             ↓
┌──────────────────────────────────────┐
│    2. Application Layer              │
│    - CSRF Protection                 │
│    - XSS Prevention                  │
│    - SQL Injection Prevention        │
│    - Rate Limiting                   │
└────────────┬─────────────────────────┘
             ↓
┌──────────────────────────────────────┐
│    3. Authentication Layer           │
│    - JWT Tokens                      │
│    - Session Management              │
│    - 2FA (Optional)                  │
│    - Password Policies               │
└────────────┬─────────────────────────┘
             ↓
┌──────────────────────────────────────┐
│    4. Authorization Layer            │
│    - Role-Based Access Control       │
│    - Permission Checking             │
│    - Resource-Level Security         │
└────────────┬─────────────────────────┘
             ↓
┌──────────────────────────────────────┐
│    5. Data Layer                     │
│    - Encrypted Sensitive Data        │
│    - Audit Logging                   │
│    - Backup Encryption               │
└──────────────────────────────────────┘
```

See: [Security Documentation](SECURITY.md)

## 🏢 Multi-Tenancy Architecture

### Tenant-Aware Design

ShuleLabs is built with **multi-tenancy by design** from Phase 1:

```
┌──────────────────────────────────────────────────────────────┐
│                    Request Flow                              │
└──────────────────────────────────────────────────────────────┘
                             ↓
┌──────────────────────────────────────────────────────────────┐
│  1. TenantResolver extracts tenant context                  │
│     - X-Tenant-Context header (JSON)                         │
│     - X-School-ID / X-Organisation-ID headers                │
│     - Query parameters (school_id, organisation_id)          │
└────────────────────┬─────────────────────────────────────────┘
                     ↓
┌──────────────────────────────────────────────────────────────┐
│  2. TenantContext Object Created                            │
│     {                                                        │
│       tenant_id: "school-1",                                 │
│       school: {...},                                         │
│       organisation: {...}                                    │
│     }                                                        │
└────────────────────┬─────────────────────────────────────────┘
                     ↓
┌──────────────────────────────────────────────────────────────┐
│  3. Controller uses TenantContext                           │
│     - Validates tenant access                                │
│     - Passes to service layer                                │
└────────────────────┬─────────────────────────────────────────┘
                     ↓
┌──────────────────────────────────────────────────────────────┐
│  4. Service/Repository queries scoped by tenant_id          │
│     - WHERE tenant_id = 'school-1'                           │
│     - Prevents cross-tenant data leaks                       │
└────────────────────┬─────────────────────────────────────────┘
                     ↓
┌──────────────────────────────────────────────────────────────┐
│  5. Audit logging includes tenant_id                        │
│     - All events tracked per tenant                          │
│     - Compliance and isolation guaranteed                    │
└──────────────────────────────────────────────────────────────┘
```

### TenantContext Abstraction

**Service**: `Modules\Foundation\Services\TenantResolver`

**Usage Pattern**:
```php
class StudentController extends BaseController
{
    public function __construct()
    {
        $this->tenantResolver = new TenantResolver();
    }
    
    public function index()
    {
        // Resolve tenant from request
        $tenantContext = $this->tenantResolver->fromRequest($this->request);
        $schoolId = $tenantContext['school']['id'] ?? null;
        
        if (!$schoolId) {
            return $this->failUnauthorized('Tenant context required');
        }
        
        // Pass to service layer
        $students = $this->studentService->listStudents($schoolId);
        
        return $this->respond($students);
    }
}
```

### Tenant-Aware Data Modeling

**Row-Level Isolation** (Current Approach):
- Shared database and schema
- Tables include `tenant_id`, `school_id`, or `organisation_id` column
- Application enforces query scoping

**Example Tables**:
```sql
-- Student table with school scoping
CREATE TABLE students (
    id INT PRIMARY KEY AUTO_INCREMENT,
    school_id VARCHAR(50) NOT NULL,  -- Tenant scoping
    admission_number VARCHAR(50) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    -- ... other columns
    FOREIGN KEY (school_id) REFERENCES tenant_catalog(id),
    UNIQUE KEY unique_admission_per_school (school_id, admission_number)
);

-- Fee structure scoped by school
CREATE TABLE fee_structures (
    id INT PRIMARY KEY AUTO_INCREMENT,
    school_id VARCHAR(50) NOT NULL,  -- Tenant scoping
    name VARCHAR(100) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    -- ... other columns
    FOREIGN KEY (school_id) REFERENCES tenant_catalog(id)
);
```

**Global Tables** (No Tenant Scoping):
- `users` (users can access multiple schools)
- `roles` (role definitions are shared)
- `migrations` (system metadata)
- `audit_seals` (cryptographic integrity)

### Tenant Catalog Table

**Table**: `tenant_catalog`

**Schema**:
```sql
CREATE TABLE tenant_catalog (
    id VARCHAR(50) PRIMARY KEY,
    tenant_type ENUM('organisation', 'school', 'warehouse') NOT NULL,
    name VARCHAR(200) NOT NULL,
    metadata JSON,  -- Flexible tenant-specific data
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

**Example Data**:
```sql
-- Organisation
INSERT INTO tenant_catalog VALUES (
    'org-1', 
    'organisation', 
    'County Education Network',
    '{"country": "Kenya", "timezone": "Africa/Nairobi"}',
    NOW(), NOW()
);

-- School
INSERT INTO tenant_catalog VALUES (
    'school-1',
    'school',
    'Nairobi Primary School',
    '{"organisation_id": "org-1", "curriculum": "CBC", "motto": "Excellence in Learning"}',
    NOW(), NOW()
);
```

### Phase 3 Tenant Features

See [Multi-Tenant Feature Documentation](features/27-MULTI-TENANT.md) for Phase 3 productization plans:
- Tenant provisioning UI
- Billing per tenant
- Custom branding
- Tenant analytics
- Schema-per-tenant option

## 📊 Observability Architecture

### Baseline Observability (Platform Guardrail)

All modules must integrate with baseline observability:

```
┌──────────────────────────────────────────────────────────────┐
│                 Application Layer                            │
│  - Controllers log requests with tenant/user context         │
│  - Services log business operations                          │
│  - Exceptions logged with full context                       │
└────────────────────┬─────────────────────────────────────────┘
                     ↓
┌──────────────────────────────────────────────────────────────┐
│              Structured Logging Layer                        │
│  Standard Fields: timestamp, level, service, tenant_id,      │
│                   user_id, trace_id, action, result          │
│  Format: JSON                                                │
└────────────────────┬─────────────────────────────────────────┘
                     ↓
┌──────────────────────────────────────────────────────────────┐
│                Log Aggregation                               │
│  - File-based: writable/logs/log-YYYY-MM-DD.log              │
│  - ELK Stack (optional): Elasticsearch + Kibana              │
│  - Cloud Logging (future): CloudWatch, Stackdriver           │
└────────────────────┬─────────────────────────────────────────┘
                     ↓
┌──────────────────────────────────────────────────────────────┐
│              Metrics & Health Checks                         │
│  - /health endpoint: database, cache, disk checks            │
│  - /metrics endpoint: request count, latency, errors         │
│  - Audit trail integration: tenant-scoped events             │
└──────────────────────────────────────────────────────────────┘
```

### Standard Log Format

**All logs use structured JSON**:
```json
{
  "timestamp": "2025-11-22T10:52:31.028Z",
  "level": "INFO",
  "service": "learning.students",
  "tenant_id": "school-1",
  "user_id": 123,
  "trace_id": "abc123xyz",
  "action": "student.created",
  "result": "success",
  "duration_ms": 45,
  "message": "Student created successfully",
  "metadata": {
    "student_id": 456,
    "admission_number": "2025-001"
  }
}
```

### Observability Integration Points

**Controllers**:
- Log all requests with tenant context
- Emit metrics (request count, latency)
- Include trace_id for request tracking

**Services**:
- Log business operations (create, update, delete)
- Log errors with full context
- Include tenant_id for multi-tenant visibility

**Repositories/Models**:
- Log slow queries (>100ms)
- Track query count and time

**Audit Trail**:
- All significant actions logged with tenant context
- Immutable audit log with cryptographic sealing

### Health Check Endpoints

**Endpoint**: `GET /health`

**Response**:
```json
{
  "status": "healthy",
  "timestamp": "2025-11-22T10:52:31.028Z",
  "checks": {
    "database": true,
    "cache": true,
    "disk": true
  }
}
```

**Status Codes**:
- `200 OK`: All checks passed
- `503 Service Unavailable`: One or more checks failed

### Metrics Endpoints

**Endpoint**: `GET /metrics`

**Format**: Prometheus-compatible or JSON

**Example Metrics**:
- `http_requests_total{method, endpoint, status, tenant}` - Request count
- `http_request_duration_seconds{method, endpoint}` - Request latency
- `db_queries_total{service, query_type}` - Database query count
- `errors_total{service, error_type}` - Error count

### Definition of Done (Observability)

A feature is not considered "done" unless:
- ✅ Structured logging with standard fields implemented
- ✅ Health checks added (if new module/service)
- ✅ Error handling logs exceptions with context
- ✅ Basic metrics exposed (request count, latency, errors)
- ✅ Audit trail integration for significant actions
- ✅ Feature visible on shared monitoring dashboard

See [Monitoring Feature Documentation](features/25-MONITORING.md) for complete details.

See: [Security Documentation](SECURITY.md)

## 🚀 Deployment Architecture

### Production Environment

```
┌──────────────────────────────────────────────┐
│           Load Balancer (Nginx)              │
└──────────────┬───────────────────────────────┘
               │
       ┌───────┴───────┐
       ↓               ↓
┌─────────────┐ ┌─────────────┐
│  App Server │ │  App Server │ (Horizontal Scaling)
│  (PHP-FPM)  │ │  (PHP-FPM)  │
└──────┬──────┘ └──────┬──────┘
       │               │
       └───────┬───────┘
               ↓
    ┌──────────────────┐
    │   MySQL Primary  │
    │   (Write)        │
    └────────┬─────────┘
             │
       ┌─────┴─────┐
       ↓           ↓
┌────────────┐ ┌────────────┐
│MySQL Replica│ │MySQL Replica│ (Read Replicas)
│  (Read)     │ │  (Read)     │
└────────────┘ └────────────┘
       
       Redis Cache
       ┌────────────┐
       │   Redis    │
       │  (Cache)   │
       └────────────┘
       
       Background Jobs
       ┌────────────┐
       │   Queue    │
       │  Worker    │
       └────────────┘
```

### Docker Architecture

```yaml
services:
  app:
    - PHP 8.3-FPM
    - CodeIgniter 4
    
  web:
    - Nginx
    
  database:
    - MySQL 8.0
    
  cache:
    - Redis 7
    
  queue:
    - Redis Queue
```

See: [Deployment Guide](guides/DEPLOYMENT.md)

## 📡 API Architecture

### RESTful API Design

**Endpoint Pattern**:
```
/api/v1/{module}/{resource}/{id}
```

**Examples**:
```
GET    /api/v1/learning/students           # List students
POST   /api/v1/learning/students           # Create student
GET    /api/v1/learning/students/123       # Get student
PUT    /api/v1/learning/students/123       # Update student
DELETE /api/v1/learning/students/123       # Delete student
```

### API Response Format

**Success Response**:
```json
{
  "status": "success",
  "data": {
    "id": 123,
    "name": "John Doe"
  },
  "message": "Student retrieved successfully",
  "meta": {
    "timestamp": "2025-11-22T07:40:00Z",
    "version": "1.0"
  }
}
```

**Error Response**:
```json
{
  "status": "error",
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "Invalid input data",
    "details": {
      "email": ["Email is required"]
    }
  },
  "meta": {
    "timestamp": "2025-11-22T07:40:00Z"
  }
}
```

See: [API Reference](API-REFERENCE.md)

## 🔧 Technology Stack

### Backend
- **Framework**: CodeIgniter 4.6.3
- **Language**: PHP 8.3+
- **Database**: MySQL 8.0 / MariaDB 10.6+
- **Cache**: Redis 7.0+
- **Queue**: Redis Queue / Beanstalkd
- **Search**: MySQL Full-Text / Elasticsearch (future)

### Frontend
- **HTML5**: Semantic markup
- **CSS3**: Modern styling
- **JavaScript**: ES6+ features
- **AJAX**: Fetch API
- **Templates**: CI4 View system

### DevOps
- **CI/CD**: GitHub Actions
- **Containers**: Docker
- **Orchestration**: Docker Compose / Kubernetes (future)
- **Monitoring**: Custom health checks
- **Logging**: File-based / ELK stack (future)

### Development Tools
- **Testing**: PHPUnit 10
- **Code Quality**: PHPStan, PHP CS Fixer
- **Security**: CodeQL, Snyk
- **Documentation**: Markdown, OpenAPI

## 📊 Performance Architecture

### Caching Strategy

1. **Application Cache**: Redis
   - Session data
   - User permissions
   - Configuration
   - Query results

2. **Database Query Cache**: MySQL
   - Frequently accessed data
   - Lookup tables

3. **HTTP Cache**: Browser/CDN
   - Static assets
   - Public pages

### Performance Targets

- **API Response Time**: <200ms (p95)
- **Page Load Time**: <2s (p95)
- **Database Query Time**: <50ms (p95)
- **Uptime**: >99.9%
- **Concurrent Users**: 1000+

## 🧪 Testing Architecture

### Test Pyramid

```
     /\
    /  \     E2E Tests (10%)
   /────\    
  /      \   Integration Tests (30%)
 /────────\  
/          \ Unit Tests (60%)
────────────
```

### Test Types

1. **Unit Tests**: Individual class/method testing
2. **Integration Tests**: Module interaction testing
3. **API Tests**: Endpoint testing
4. **Database Tests**: Repository testing
5. **E2E Tests**: Full user flow testing (future)

See: [Testing Guide](development/TESTING.md)

## 🔄 Integration Architecture

### External Services

```
ShuleLabs CI4
    ↓
┌───────────────────────────┐
│  Integration Layer        │
├───────────────────────────┤
│  - Google Drive API       │
│  - SMS Gateways           │
│  - Payment Gateways       │
│  - Email Services         │
│  - Video Conferencing     │
└───────────────────────────┘
```

### Integration Patterns

1. **REST APIs**: Standard HTTP/JSON communication
2. **Webhooks**: Event-driven notifications
3. **OAuth 2.0**: Secure authorization
4. **Queue-based**: Asynchronous processing
5. **Retry Logic**: Fault tolerance

## 📝 Code Organization

### Namespace Structure

```
App\
├── Modules\
│   ├── Foundation\
│   │   ├── Controllers\
│   │   ├── Models\
│   │   ├── Entities\
│   │   └── Services\
│   ├── Learning\
│   └── ...
├── Services\
├── Filters\
├── Libraries\
└── Helpers\
```

### Coding Standards

- **PSR-12**: Code style
- **PSR-4**: Autoloading
- **Type Hints**: Strict typing
- **Documentation**: PHPDoc blocks
- **Error Handling**: Exception-based

See: [Code Standards](development/CODE-STANDARDS.md)

## 🚀 Scalability Considerations

### Horizontal Scaling
- Stateless application servers
- Load balancer distribution
- Session stored in Redis
- Database read replicas

### Vertical Scaling
- Optimized database queries
- Efficient caching
- Resource pooling
- Code optimization

### Future Enhancements
- Microservices architecture
- Event sourcing
- CQRS pattern
- Distributed caching

## 📚 Additional Resources

- [Database Schema](DATABASE.md)
- [API Reference](API-REFERENCE.md)
- [Security](SECURITY.md)
- [Module Documentation](modules/)
- [Development Guide](development/)

---

**Last Updated**: 2025-11-22  
**Version**: 1.0.0
