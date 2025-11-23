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

**Test Status**: 79/90 tests passing (87.8%)  
**Code Coverage**: 87.8%

See [TESTING.md](TESTING.md) for complete testing guide.

## Documentation

- [Testing Guide](TESTING.md) - Test credentials, running tests, manual testing workflows
- [Build Validation Report](BUILD_VALIDATION_REPORT.md) - Complete system validation
- [Session Changelog](SESSION_CHANGELOG.md) - Recent changes and fixes
- [Architecture](docs/ARCHITECTURE.md) - System architecture
- [API Reference](docs/API-REFERENCE.md) - API documentation
- [Deployment](docs/DEPLOYMENT.md) - Production deployment guide

## Initial Setup (Advanced)

1. Install PHP 8.3 and the required extensions (`intl`, `mbstring`, `sqlite3`,
   `openssl`, `curl`).
2. If you are working with the standalone runtime, run Composer inside the new
   repository root:

   ```bash
   composer install
   ```

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
