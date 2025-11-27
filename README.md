# ShuleLabs CodeIgniter 4

A comprehensive school management system built with CodeIgniter 4, designed for modern educational institutions.

## Quick Start

### Development Setup (SQLite - Fastest)
```bash
# Install dependencies
composer install

# Copy environment file
cp env .env

# Environment is pre-configured for SQLite
# No additional database setup needed!

# Run migrations
php spark migrate

# Seed test data (23 users, 8 roles)
php spark db:seed CompleteDatabaseSeeder

# Start development server
php spark serve
```

**Access**: http://localhost:8080  
**Login**: admin@shulelabs.local / Admin@123456

**See all test credentials**: [TESTING.md](TESTING.md)

### Production Setup (MySQL)
See [docs/DEPLOYMENT.md](docs/DEPLOYMENT.md) for complete production setup instructions.

## Features

### Core Modules
- **Foundation** - Multi-tenant architecture, system configuration
- **HR** - User management, roles, permissions (8 role types)
- **Finance** - Invoices, payments, M-Pesa integration
- **Learning** - Courses, assignments, grading, attendance
- **Library** - Book cataloging, borrowing, inventory
- **Inventory** - Stock management, asset tracking
- **Mobile** - Mobile-first REST API
- **Threads** - Real-time messaging and notifications

### Key Features
- Role-based access control (SuperAdmin, Admin, Teacher, Student, Parent, Accountant, Librarian, Receptionist)
- Student information management
- Academic management (courses, assignments, grading)
- Finance management with M-Pesa integration
- Library and inventory management
- Mobile-first API design
- Gamification (badges, achievements, leaderboards)

## CI3 Compatibility Layer (Retained)
- Purpose: Maintain operational compatibility with legacy CI3-era data and logs while running on CI4.
- Scope: Selected helpers/services and the `loginlog` table exist to support legacy flows and migrations.
- Configuration: No action required by default. You can remove the layer later by dropping legacy tables and deleting compatibility files when your data is fully migrated.
- Impact: Does not affect the new unprefixed `users`, `roles`, `user_roles` schema or CI4 routing; it simply preserves backward compatibility where needed.

## Running Tests

```bash
# Run all tests
php vendor/bin/phpunit -c phpunit.ci4.xml --testdox

# Run specific module
php vendor/bin/phpunit tests/Finance/ --testdox

# Code coverage
php vendor/bin/phpunit --coverage-html coverage/

# Code style
php vendor/bin/php-cs-fixer fix --dry-run

# Static analysis
php vendor/bin/phpstan analyse
```

**Test Status**: 87.8% tests passing (181/206)  
**Code Coverage**: 87.8%  
**Code Quality**: Grade A (PHPStan Level 8)

See [TESTING.md](TESTING.md) for complete testing guide.

## Documentation

### Quick References
- **[API Documentation](docs/API_DOCUMENTATION.md)** - Complete REST API reference
- **[Developer Guide](docs/DEVELOPER_GUIDE.md)** - Development setup & coding standards
- **[Testing Guide](TESTING.md)** - Test credentials, running tests, workflows

### Architecture & Design
- **[Detailed Architecture](docs/ARCHITECTURE_DETAILED.md)** - System architecture with diagrams
- **[Database Design](docs/DATABASE.md)** - Schema, multi-tenancy, migrations
- **[Security](docs/SECURITY.md)** - Authentication, authorization, RBAC

### Deployment & Operations
- **[Deployment Guide](deployment/DEPLOYMENT_GUIDE.md)** - Complete deployment instructions
- **[Docker Deployment](DOCKER.md)** - Docker & Kubernetes setup
- **[Observability](docs/OBSERVABILITY.md)** - Logging, metrics, monitoring

### Reports & Status
- **[Build Validation](BUILD_VALIDATION_REPORT.md)** - Complete system validation
- **[Quality Report](COMPLETE_QUALITY_REPORT.md)** - Code quality metrics
- **[Session Changelog](SESSION_CHANGELOG.md)** - Recent changes and fixes

## Features

### Core Modules

#### 🏢 Foundation
- Multi-tenant architecture with row-level isolation
- Audit logging with tenant context
- QR code generation and validation
- Maker-checker approval workflows
- Integration registry for external services

#### 👥 HR (Human Resources)
- Employee management (Create, Read, Update, Delete)
- Department structure and organization
- Payroll processing with automatic calculations
- Staff attendance tracking
- Leave management with approval workflow
- 8 role types with granular permissions

#### 💰 Finance
- Invoice management with auto-numbering
- Payment processing (Cash, M-Pesa, Bank Transfer)
- M-Pesa STK Push integration
- Fee structure configuration
- Financial reporting and analytics
- Student fee tracking and reminders

#### 📚 Learning
- Student information management
- Class and section organization
- Subject allocation and teacher assignment
- Daily attendance tracking with reporting
- Grading system with GPA calculation
- Timetable generation
- Academic reporting (progress reports, transcripts)

#### 📖 Library
- Book cataloging with ISBN support
- Borrowing and return tracking
- Fine calculation for overdue books
- Inventory management
- Search and filter functionality

#### 📦 Inventory
- Stock management with quantity tracking
- Requisition system with approval workflow
- Inter-department transfers
- Asset tracking and location management
- Low stock alerts

#### 📱 Mobile
- Mobile-first REST API
- JWT authentication for mobile apps
- Offline data sync with snapshots
- Push notifications (FCM ready)
- Optimized payload sizes

#### 💬 Threads
- Internal messaging system
- User-to-user conversations
- System notifications
- Announcement broadcasts
- Real-time updates (WebSocket ready)

### Key Features

✅ **Role-Based Access Control (RBAC)**  
8 role types: SuperAdmin, Admin, Teacher, Student, Parent, Accountant, Librarian, Receptionist

✅ **Multi-Tenant Support**  
Row-level isolation with `school_id` scoping, supports multiple schools

✅ **Comprehensive API**  
RESTful API with 95+ endpoints, JSON responses, JWT authentication

✅ **Mobile-First Design**  
Optimized for mobile consumption, offline support, snapshot API

✅ **Security by Design**  
Authentication, authorization, CSRF protection, SQL injection prevention

✅ **Payment Integration**  
M-Pesa STK Push, callback handling, payment reconciliation

✅ **Audit Logging**  
Complete audit trail with tenant context for compliance

✅ **Gamification Ready**  
Badges, achievements, leaderboards (Coming soon)

✅ **Observability**  
Health checks, structured logging, metrics ready

✅ **High Test Coverage**  
87.8% code coverage with 181 automated tests

## Technology Stack

- **Framework**: CodeIgniter 4.5+
- **PHP**: 8.3+
- **Database**: MySQL 8.0+ (Production), SQLite (Development)
- **Cache**: Redis 7.0+ (Optional)
- **Queue**: Built-in CI4 scheduler
- **Authentication**: Session-based + JWT for mobile
- **API Style**: RESTful with JSON
- **Testing**: PHPUnit 10.5+
- **Code Quality**: PHPStan Level 8, PHP-CS-Fixer (PSR-12)

## System Requirements

### Minimum Requirements
- PHP 8.3 or higher
- Composer 2.x
- Web Server (Apache/Nginx)
- 2GB RAM
- 10GB Storage

### PHP Extensions
- `intl` - Internationalization
- `mbstring` - Multibyte string handling
- `mysqli` or `pdo_mysql` - Database connectivity
- `curl` - HTTP requests
- `zip` - File compression
- `gd` - Image processing
- `bcmath` - Arbitrary precision mathematics
- `sqlite3` - SQLite support (development)
- `redis` - Redis cache (optional)

## Quick Links

- **GitHub**: https://github.com/countynetkenya/shulelabsci4
- **Issues**: https://github.com/countynetkenya/shulelabsci4/issues
- **Discussions**: https://github.com/countynetkenya/shulelabsci4/discussions
- **Changelog**: [SESSION_CHANGELOG.md](SESSION_CHANGELOG.md)

## Support

For support and questions:
- **Email**: support@shulelabs.com
- **Documentation**: [docs/](docs/)
- **GitHub Issues**: For bug reports and feature requests

## License

Proprietary - ShuleLabs

## Contributors

Developed and maintained by the ShuleLabs Development Team.

---

**Version**: 1.0.0  
**Last Updated**: November 23, 2025  
**Status**: Production Ready ✅

3. Copy the default environment file and adjust credentials:

   ```bash
   php scripts/setup-directories.php
   # or manually:
   cp .env.example .env
   ```

   The setup script also ensures the writable folders exist with the correct
   permissions.

## Database migrations & seeders

All migration entry points live under `bin/migrate`:

```bash
php bin/migrate/status    # Show pending migrations
php bin/migrate/latest    # Apply all pending migrations (passes --all)
php bin/migrate/version 2024-10-06-000006  # Jump to a specific version
php bin/migrate/rollback  # Roll back the last batch (uses --all)
```

These wrappers call the `spark` CLI internally so the same commands work whether
`ci4/` is embedded or standalone. Feel free to use `php spark …` directly if you
prefer.

**Note:** Module migrations (like Foundation) are discovered via symbolic links in
`app/Modules/Database/Migrations/`. See [docs/MIGRATION_SYSTEM.md](docs/MIGRATION_SYSTEM.md)
for technical details about the migration system configuration.

## Initial Web-Based Setup

After configuring your database and running migrations, ShuleLabs provides a
web-based installer to bootstrap your first organisation, school, and admin user.

1. Configure your database credentials in `.env`:

   ```bash
   cp .env.example .env
   # Edit .env with your database settings
   ```

2. Run all migrations to create the required database tables:

   ```bash
   php bin/migrate/latest
   ```

3. Visit `/install` in your browser (e.g., `http://localhost:8080/install`)

4. Follow the three-step wizard:
   - **Step 1**: Environment check - verifies database connection and migrations
   - **Step 2**: Organisation & School setup - create your first organisation and school
   - **Step 3**: Admin account - create your administrator user

5. After completing the installer, you will be redirected to the login page.
   Sign in with your new admin credentials.

6. **Important**: Once installation is complete, set `app.installed = true` in
   your `.env` file to prevent access to the installer.

## Running the test suite

Use the portable test runner to execute the PHPUnit suite:

```bash
bash scripts/run-tests.sh
```

The script will reuse the monorepo's vendor directory when present, otherwise it
runs `composer install` locally before invoking PHPUnit.

## OpenAPI specification

All OpenAPI annotations live in `docs/openapi`. Run the standard Composer script
from the repository root (embedded mode) or from the standalone checkout:

```bash
composer openapi:build
```

The command writes `docs/openapi.yaml` in place.

## Next steps when creating a new repository

- Add CI/CD automation (GitHub Actions, GitLab CI, etc.) that runs
  `composer ci` or the equivalent composer scripts you rely on.
- Publish container images or Docker Compose manifests mirroring your target
  deployment.
- Configure application secrets (.env values) for each environment and make sure
  they are stored securely (Vault, Doppler, etc.).
