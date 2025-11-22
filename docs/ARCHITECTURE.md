# 🏗️ Architecture - ShuleLabs CI4

**Last Updated**: 2025-11-22  
**Version**: 1.0.0

## System Architecture Overview

ShuleLabs is built on a modular, API-first architecture using CodeIgniter 4 framework. The system follows SOLID principles and clean architecture patterns.

## 🎨 Architectural Principles

1. **Modular Design**: Self-contained, independent modules
2. **API-First**: All functionality exposed via REST APIs
3. **Security by Design**: Role-based access control throughout
4. **Scalability**: Horizontal and vertical scaling supported
5. **Maintainability**: Clear separation of concerns
6. **Testability**: Comprehensive test coverage

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
- Audit Service: Activity logging and compliance
- Ledger Service: Double-entry accounting
- Integration Registry: External system tracking
- Maker-Checker Service: Approval workflows
- QR Service: QR code generation and validation
- Tenant Catalog: Multi-tenant support (future)

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
   - `ci4_users`
   - `ci4_roles`
   - `ci4_user_roles`
   - `ci4_migrations`

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
