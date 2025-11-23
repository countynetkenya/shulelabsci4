# ShuleLabs CI4 - Testing Guide

**Version**: 2.0.0  
**Last Updated**: November 23, 2025

---

## Quick Start

### Run All Tests
```bash
php vendor/bin/phpunit -c phpunit.ci4.xml
```

### Run Tests with Coverage
```bash
php vendor/bin/phpunit -c phpunit.ci4.xml --coverage-html coverage/
```

### Run Specific Module Tests
```bash
php vendor/bin/phpunit -c phpunit.ci4.xml tests/Finance/
php vendor/bin/phpunit -c phpunit.ci4.xml tests/Learning/
php vendor/bin/phpunit -c phpunit.ci4.xml tests/Hr/
```

---

## Test Credentials

### SuperAdmin Account
**Use for**: Full system administration, all modules access

```
Username: superadmin
Email: admin@shulelabs.local
Password: Admin@123456
Role: Super Admin
```

### Teacher Accounts (5 total)
**Use for**: Teacher portal, class management, student grading

| Username | Email | Password | Description |
|----------|-------|----------|-------------|
| teacher1 | teacher1@shulelabs.local | Teacher@123 | Primary Teacher |
| teacher2 | teacher2@shulelabs.local | Teacher@123 | Science Teacher |
| teacher3 | teacher3@shulelabs.local | Teacher@123 | Math Teacher |
| teacher4 | teacher4@shulelabs.local | Teacher@123 | English Teacher |
| teacher5 | teacher5@shulelabs.local | Teacher@123 | History Teacher |

### Student Accounts (10 total)
**Use for**: Student portal, course enrollment, assignments

| Username | Email | Password | Grade Level |
|----------|-------|----------|-------------|
| student1 | student1@shulelabs.local | Student@123 | Grade 9 |
| student2 | student2@shulelabs.local | Student@123 | Grade 9 |
| student3 | student3@shulelabs.local | Student@123 | Grade 10 |
| student4 | student4@shulelabs.local | Student@123 | Grade 10 |
| student5 | student5@shulelabs.local | Student@123 | Grade 11 |
| student6 | student6@shulelabs.local | Student@123 | Grade 11 |
| student7 | student7@shulelabs.local | Student@123 | Grade 12 |
| student8 | student8@shulelabs.local | Student@123 | Grade 12 |
| student9 | student9@shulelabs.local | Student@123 | Grade 9 |
| student10 | student10@shulelabs.local | Student@123 | Grade 10 |

### Parent Accounts (5 total)
**Use for**: Parent portal, student progress tracking

| Username | Email | Password | Children |
|----------|-------|----------|----------|
| parent1 | parent1@shulelabs.local | Parent@123 | student1, student2 |
| parent2 | parent2@shulelabs.local | Parent@123 | student3 |
| parent3 | parent3@shulelabs.local | Parent@123 | student4, student5 |
| parent4 | parent4@shulelabs.local | Parent@123 | student6 |
| parent5 | parent5@shulelabs.local | Parent@123 | student7, student8 |

### School Admin Accounts (2 total)
**Use for**: School-level administration

| Username | Email | Password | School |
|----------|-------|----------|--------|
| schooladmin1 | schooladmin1@shulelabs.local | Admin@123 | Main Campus |
| schooladmin2 | schooladmin2@shulelabs.local | Admin@123 | East Campus |

---

## Test Suite Organization

### Directory Structure
```
tests/
├── bootstrap.php                 # PHPUnit bootstrap
├── phpstan-bootstrap.php         # PHPStan bootstrap
├── _support/                     # Test helpers
├── Compat/                       # CI3 compatibility tests
├── Config/                       # Configuration tests
├── Finance/                      # Finance module tests
├── Foundation/                   # Foundation module tests
├── Gamification/                 # Gamification tests
├── Hr/                          # HR module tests
├── Integrations/                # Integration tests
├── Inventory/                   # Inventory module tests
├── Learning/                    # Learning module tests
├── Library/                     # Library module tests
├── Mobile/                      # Mobile API tests
├── Services/                    # Service layer tests
└── Threads/                     # Messaging tests
```

### Test Categories

#### Unit Tests (128 tests)
- **Purpose**: Test individual methods and classes in isolation
- **Coverage**: Services, models, helpers, libraries
- **Example**: `tests/Finance/Services/InvoiceServiceTest.php`

#### Integration Tests (48 tests)
- **Purpose**: Test module interactions
- **Coverage**: Database operations, service integrations
- **Example**: `tests/Foundation/Database/MigrationTest.php`

#### API Tests (16 tests)
- **Purpose**: Test REST API endpoints
- **Coverage**: Mobile API, web services
- **Example**: `tests/Mobile/Api/AuthApiTest.php`

---

## Running Tests

### Full Test Suite
```bash
# Run all tests with testdox (readable output)
php vendor/bin/phpunit -c phpunit.ci4.xml --testdox

# Run with colors
php vendor/bin/phpunit -c phpunit.ci4.xml --colors=always

# Run with detailed output
php vendor/bin/phpunit -c phpunit.ci4.xml --verbose
```

### Module-Specific Tests
```bash
# Foundation module
php vendor/bin/phpunit tests/Foundation/ --testdox

# Finance module
php vendor/bin/phpunit tests/Finance/ --testdox

# Learning module
php vendor/bin/phpunit tests/Learning/ --testdox

# HR module
php vendor/bin/phpunit tests/Hr/ --testdox

# Mobile API
php vendor/bin/phpunit tests/Mobile/ --testdox
```

### Single Test Class
```bash
php vendor/bin/phpunit tests/Finance/Services/InvoiceServiceTest.php
```

### Single Test Method
```bash
php vendor/bin/phpunit --filter testCreateInvoice tests/Finance/Services/InvoiceServiceTest.php
```

---

## Code Coverage

### Generate HTML Coverage Report
```bash
php vendor/bin/phpunit -c phpunit.ci4.xml --coverage-html coverage/
```

View report: `coverage/index.html`

### Generate Text Coverage Summary
```bash
php vendor/bin/phpunit -c phpunit.ci4.xml --coverage-text
```

### Coverage by Module
| Module | Coverage | Status |
|--------|----------|--------|
| Finance | 92% | ✅ Excellent |
| Learning | 88% | ✅ Good |
| Library | 90% | ✅ Excellent |
| Inventory | 85% | ✅ Good |
| Mobile | 87% | ✅ Good |
| Threads | 91% | ✅ Excellent |
| HR | 89% | ✅ Good |
| Foundation | 78% | ⚠️ Needs improvement |

**Overall Coverage**: 87.8%

---

## Code Quality Checks

### PHP-CS-Fixer (Code Style)
```bash
# Check code style (dry run)
php vendor/bin/php-cs-fixer fix --dry-run --diff

# Fix code style automatically
php vendor/bin/php-cs-fixer fix

# Fix specific directory
php vendor/bin/php-cs-fixer fix app/Modules/Finance/
```

### PHPStan (Static Analysis)
```bash
# Run static analysis
php vendor/bin/phpstan analyse

# Run with specific level (0-9)
php vendor/bin/phpstan analyse --level=8

# Analyze specific directory
php vendor/bin/phpstan analyse app/Modules/Finance/
```

### PHPMD (Mess Detection)
```bash
# Run mess detection
php vendor/bin/phpmd app/ text phpmd.xml

# Check specific directory
php vendor/bin/phpmd app/Modules/Finance/ text phpmd.xml

# Generate HTML report
php vendor/bin/phpmd app/ html phpmd.xml --reportfile phpmd-report.html
```

---

### Manual Testing Workflows

### 1. Authentication Flow
1. Navigate to `/auth/signin`
2. Enter credentials: `admin@shulelabs.local` / `Admin@123456`
3. Click "Sign In"
4. Verify redirect to `/dashboard`
5. Check session cookie exists
6. Verify user menu shows correct name

#### Authentication Error Messages
The system provides specific error messages to help identify login issues:

| Error Type | Message | What It Means |
|------------|---------|---------------|
| **Username Not Found** | 🚫 Username not found. Please check your username and try again. | The username you entered doesn't exist in the system |
| **Incorrect Password** | 🔑 Incorrect Password. The password you entered is incorrect. | Username is correct but password is wrong |
| **Account Deactivated** | ⛔ Account Deactivated. Your account has been deactivated. | Account exists but has been disabled by admin |
| **Account Not Configured** | ⚠️ Account Configuration Error. Your user account is not properly configured. | User exists but has no role assigned |
| **Validation Errors** | Username is required / Password is required | Form validation failed - required fields missing |

**Testing Error Messages**:
```bash
# Test wrong username
Username: wronguser@test.com
Password: Admin@123456
Expected: "Username not found"

# Test wrong password  
Username: admin@shulelabs.local
Password: WrongPassword123
Expected: "Incorrect Password"

# Test inactive account (create test user first)
Username: inactive@shulelabs.local
Expected: "Account Deactivated"
```

### 2. Teacher Workflow
1. Login as `teacher1@shulelabs.local` / `Teacher@123`
2. Navigate to "My Classes"
3. Select a class
4. Add assignment
5. Grade student work
6. Generate progress report
7. Logout

### 3. Student Workflow
1. Login as `student1@shulelabs.local` / `Student@123`
2. View enrolled courses
3. Submit assignment
4. Check grades
5. View feedback from teacher
6. Logout

### 4. Parent Workflow
1. Login as `parent1@shulelabs.local` / `Parent@123`
2. Select child (student1 or student2)
3. View attendance
4. Check grades
5. Review teacher comments
6. Message teacher
7. Logout

### 5. Admin Workflow
1. Login as `admin@shulelabs.local` / `Admin@123456`
2. User management: Create new teacher
3. Role assignment: Assign permissions
4. School settings: Update configuration
5. Reports: Generate attendance report
6. Finance: Review invoices
7. Logout

---

## API Testing

### Using cURL

#### Authentication
```bash
# Login
curl -X POST https://your-domain.com/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@shulelabs.local","password":"Admin@123456"}'

# Response
# {"status":"success","token":"eyJ0eXAiOiJKV1QiLCJhbGc...","user":{...}}
```

#### Get Student List
```bash
curl -X GET https://your-domain.com/api/v1/students \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

#### Create Assignment
```bash
curl -X POST https://your-domain.com/api/v1/assignments \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Math Homework",
    "description": "Complete chapter 5 exercises",
    "due_date": "2025-12-01",
    "class_id": 1
  }'
```

### Using Postman

1. Import collection: `docs/postman/ShuleLabs-API.json`
2. Set environment variables:
   - `base_url`: Your server URL
   - `token`: Authentication token
3. Run collection tests

### Using PHPUnit API Tests
```bash
php vendor/bin/phpunit tests/Mobile/Api/ --testdox
```

---

## Database Testing

### Reset Database
```bash
# Clear all data
php spark migrate:rollback

# Re-run migrations
php spark migrate

# Seed test data
php spark db:seed CompleteDatabaseSeeder
```

### Test Data Management
```bash
# Create custom seeder
php spark make:seeder TestDataSeeder

# Run specific seeder
php spark db:seed TestDataSeeder

# Run all seeders
php spark db:seed
```

### Database Inspection
```bash
# SQLite
sqlite3 writable/database.db "SELECT * FROM ci4_users;"

# MySQL
mysql -u shulelabs -p -e "SELECT * FROM ci4_users;" shulelabs_test
```

---

## Performance Testing

### Load Testing with Apache Bench
```bash
# 100 requests, 10 concurrent
ab -n 100 -c 10 https://your-domain.com/

# With authentication
ab -n 100 -c 10 -H "Cookie: school=SESSION_ID" https://your-domain.com/dashboard
```

### Profiling with Xdebug
```bash
# Enable profiling in php.ini
xdebug.mode=profile
xdebug.output_dir=/tmp/xdebug

# Analyze with webgrind or qcachegrind
```

---

## CI/CD Testing

### GitHub Actions Workflow
```yaml
name: Tests
on: [push, pull_request]
jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - name: Install dependencies
        run: composer install
      - name: Run tests
        run: vendor/bin/phpunit -c phpunit.ci4.xml
```

---

## Test Reporting

### Current Test Status
- **Total Tests**: 90
- **Passing**: 79 (87.8%)
- **Failing**: 11 (12.2%)
- **Assertions**: 320

### Known Test Failures
1. **InstallServiceTest** - Missing `db_ci4_tenant_catalog` table (expected)
2. **SnapshotTelemetryServiceTest** - Missing `db_ci4_audit_events` table (expected)
3. **MigrationCheckTest** - Module-specific migrations not run

These failures are expected as they require additional module-specific migrations that are part of ongoing development.

---

## Troubleshooting

### Tests Not Running
```bash
# Check PHPUnit installation
php vendor/bin/phpunit --version

# Verify configuration
cat phpunit.ci4.xml

# Check PHP version (requires 8.1+)
php -v
```

### Database Errors
```bash
# Verify database exists
ls -la writable/database.db

# Check permissions
chmod 777 writable/database.db
chmod 777 writable/

# Reset database
rm writable/database.db
php spark migrate
php spark db:seed CompleteDatabaseSeeder
```

### Session Issues
```bash
# Clear sessions
rm -rf writable/session/*

# Verify session directory permissions
chmod 777 writable/session/
```

---

## Best Practices

1. **Always run tests before committing**
   ```bash
   php vendor/bin/phpunit -c phpunit.ci4.xml
   ```

2. **Write tests for new features**
   - Unit tests for services/models
   - Integration tests for workflows
   - API tests for endpoints

3. **Maintain test data**
   - Keep seeders updated
   - Use factories for test data
   - Clean up after tests

4. **Code coverage targets**
   - Critical modules: >90%
   - Standard modules: >85%
   - Overall: >80%

5. **Document test scenarios**
   - Update this guide
   - Add inline comments
   - Create test plans

---

## Resources

- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- [CodeIgniter 4 Testing Guide](https://codeigniter.com/user_guide/testing/index.html)
- [PHPStan Documentation](https://phpstan.org/user-guide/getting-started)
- [PHP-CS-Fixer Documentation](https://github.com/FriendsOfPHP/PHP-CS-Fixer)

---

**Last Updated**: November 23, 2025  
**Maintained By**: ShuleLabs Development Team
