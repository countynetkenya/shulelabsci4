# CI4 Standalone - ShuleLabs School Management System

This is the standalone CodeIgniter 4 version of ShuleLabs School Management System. **CI4 now operates independently with its own user authentication schema** while maintaining the ability to coexist with CI3 during migration.

## Features

- **CI4-Native Authentication System**: Independent user authentication with normalized `ci4_users` table
- **Role-Based Access Control**: Flexible role management via `ci4_roles` and `ci4_user_roles` tables
- **Multi-User Type Support**: Super admin, admin, teacher, student, parent, accountant, librarian, receptionist
- **Automatic CI3 Migration**: Seamlessly backfills user data from existing CI3 tables on first run
- **School Selection**: Support for users with access to multiple schools
- **Dashboard**: User-specific dashboard with statistics and quick links
- **Admin Panel**: System administration interface for super admins
- **Session Sharing**: Database sessions compatible with CI3 `school_sessions` table (optional)
- **Password Compatibility**: Uses SHA-512 hashing compatible with CI3 for seamless migration

## Requirements

- PHP 8.3 or higher
- MySQL 8.0 / MariaDB 10.6 or higher
- Composer
- Apache/Nginx web server
- Required PHP extensions:
  - pdo_mysql
  - mysqli
  - intl
  - gd
  - bcmath
  - zip

## Installation

### 1. Clone Repository

```bash
git clone <repository-url> shulelabs-ci4
cd shulelabs-ci4
```

### 2. Install Dependencies

The CI4 standalone installation includes its own composer.json with the CodeIgniter 4 framework:

```bash
cd ci4
composer install
```

This will install:
- CodeIgniter 4 framework (^4.5)
- PHPUnit for testing
- All required dependencies

The framework will be installed in `ci4/vendor/codeigniter4/framework/`.

**Note:** If you prefer to use the root-level vendor directory (shared with CI3), you can skip this step. The Paths.php is configured to check both locations.

### 3. Configure Environment

Copy the example environment file:

```bash
cp .env.example .env
```

Edit `.env` and configure:

**Database Settings:**
```env
DB_HOST=localhost
DB_DATABASE=shulelabs
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password
DB_PORT=3306
```

**Security Settings (IMPORTANT):**
```env
# Generate a secure random key (32+ characters)
encryption.key=YOUR_SECURE_RANDOM_KEY_HERE
ENCRYPTION_KEY=YOUR_SECURE_RANDOM_KEY_HERE
```

**Note:** The `ENCRYPTION_KEY` MUST match the encryption key from your CI3 installation if you're sharing the database. This is critical for password verification.

**Session Settings:**
```env
SESSION_DRIVER=database
SESSION_COOKIE_NAME=school
SESSION_EXPIRATION=7200
SESSION_SAVE_PATH=school_sessions
```

### 4. Database Setup

CI4 now uses its own normalized user schema (`ci4_users`, `ci4_roles`, `ci4_user_roles`) that is completely independent from CI3.

**For Migration from CI3:**

1. Ensure your existing CI3 database has the legacy user tables (student, teacher, parents, user, systemadmin)
2. Ensure the `school_sessions` table exists for session sharing (optional)
3. Run CI4 migrations to create CI4-specific tables and backfill from CI3:

```bash
php spark migrate --all
```

This will:
- Create the `ci4_users` table for all user identities
- Create the `ci4_roles` and `ci4_user_roles` tables for role management
- Seed 8 default roles (super_admin, admin, teacher, student, parent, accountant, librarian, receptionist)
- **Automatically backfill** all users from CI3 tables into `ci4_users`
- Preserve original passwords (CI3-compatible hashes) for seamless login

4. (Optional) Seed a default CI4 superadmin if you don't have any CI3 users:

```bash
php spark db:seed Ci4DefaultSuperadminSeeder
```

**For Fresh Installation (No CI3 data):**

1. Run migrations to create all tables:

```bash
php spark migrate --all
```

2. Seed a default superadmin:

```bash
php spark db:seed Ci4DefaultSuperadminSeeder
```

Default credentials will be:
- Username: `admin_ci4`
- Password: `ChangeMe123!`
- **IMPORTANT:** Change this password immediately after first login!

**Note:** The `ENCRYPTION_KEY` in `.env` must be set before running migrations, as it's used for password hashing.
php spark migrate --all
```

If setting up fresh (not recommended - use CI3 schema):

```bash
# Import the full ShuleLabs schema
mysql -u your_user -p shulelabs < database/schema.sql
```

### 5. Set Permissions

```bash
# Make writable directories
chmod -R 775 writable/
chmod -R 775 public/uploads/

# Ensure web server owns the files
chown -R www-data:www-data writable/
chown -R www-data:www-data public/uploads/
```

### 6. Web Server Configuration

#### Apache

Create a virtual host:

```apache
<VirtualHost *:80>
    ServerName shulelabs.local
    DocumentRoot /path/to/shulelabs-ci4/public
    
    <Directory /path/to/shulelabs-ci4/public>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/shulelabs_error.log
    CustomLog ${APACHE_LOG_DIR}/shulelabs_access.log combined
</VirtualHost>
```

Enable rewrite module:
```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

#### Nginx

```nginx
server {
    listen 80;
    server_name shulelabs.local;
    root /path/to/shulelabs-ci4/public;
    
    index index.php index.html;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    location ~ /\.ht {
        deny all;
    }
}
```

### 7. Access the Application

Open your browser and navigate to:
```
http://shulelabs.local/auth/signin
```

## Default Credentials

**For Fresh CI4 Installation (using Ci4DefaultSuperadminSeeder):**

- **CI4 Super Admin**: username: `admin_ci4`, password: `ChangeMe123!`

**For Migrated CI3 Database:**

Your existing CI3 credentials will work seamlessly after migration:

- **Admin**: username: `admin`, password: `123456` (if using standard ShuleLabs CI3 database)
- **Teacher**: username: `teacher1`, password: `123456`
- **Student**: username: `student1`, password: `123456`
- **Parent**: username: `parent1`, password: `123456`

**IMPORTANT:** 
- All CI3 passwords are preserved during migration and will work with CI4
- Change default passwords immediately after first login!
- The CI4 authentication now uses the `ci4_users` table instead of the legacy CI3 tables

## Directory Structure

```
ci4/
├── app/
│   ├── Config/          # Configuration files
│   ├── Controllers/     # Application controllers
│   │   ├── Auth.php     # Authentication controller
│   │   ├── School.php   # School selection
│   │   ├── Dashboard.php
│   │   └── Admin.php
│   ├── Models/          # Database models
│   │   ├── UserModel.php
│   │   ├── SiteModel.php
│   │   ├── LoginLogModel.php
│   │   └── SystemAdminModel.php
│   ├── Views/           # View templates
│   │   ├── auth/
│   │   ├── school/
│   │   ├── dashboard/
│   │   ├── admin/
│   │   ├── layouts/
│   │   └── components/
│   ├── Filters/         # Request filters
│   ├── Helpers/         # Helper functions
│   ├── Libraries/       # Custom libraries
│   └── Modules/         # HMVC modules
├── public/              # Web-accessible files
│   ├── index.php        # Front controller
│   ├── assets/          # CSS, JS, images
│   └── uploads/         # User uploads
├── writable/            # Cache, logs, sessions
└── tests/               # Unit tests
```

## User Types & Roles

The system supports the following user roles in the `ci4_roles` table:

| Role | CI3 usertypeID | Description |
|------|---------------|-------------|
| **Super Admin** | 0 | Full system access across all schools |
| **Admin** | 1 | School administration |
| **Teacher** | 2 | Teaching staff |
| **Student** | 3 | Student account |
| **Parent** | 4 | Parent/Guardian account |
| **Accountant** | 5 | Accounting staff |
| **Librarian** | 6 | Library staff |
| **Receptionist** | 7 | Reception staff |

Users can have multiple roles assigned via the `ci4_user_roles` pivot table.

## Authentication Flow

**CI4 now authenticates exclusively against the `ci4_users` table:**

1. User visits `/auth/signin`
2. Username is looked up in `ci4_users` table
3. Password is hashed using SHA-512 with encryption key (CI3 compatible via `HashCompat`)
4. Hashed password is compared against `password_hash` field in `ci4_users`
5. On success, user roles are loaded from `ci4_user_roles` and `ci4_roles`
6. Session is created in `school_sessions` table
7. User with multiple schools sees school selection page
8. After school selection (or if single school), redirect to dashboard
9. Super admins are redirected to admin panel

**Key Change:** CI4 no longer queries CI3 tables (student, teacher, parents, user, systemadmin) for authentication. All authentication is handled by the normalized `ci4_users` table.

## Database Sessions

Sessions are stored in the `school_sessions` table with this structure:

```sql
CREATE TABLE `school_sessions` (
  `id` varchar(128) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `timestamp` int(10) unsigned NOT NULL DEFAULT 0,
  `data` blob NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ci_sessions_timestamp` (`timestamp`)
);
```

This allows optional session sharing between CI3 and CI4 during migration, though CI4 now operates independently for authentication.

## CI4 User Schema

CI4 uses three primary tables for user management:

**ci4_users** - Main user identity store:
- `id` - Primary key
- `username` - Unique username
- `email` - User email
- `password_hash` - Password hash (CI3-compatible SHA-512 initially)
- `full_name` - User's full name
- `photo` - Profile photo filename
- `schoolID` - Comma-separated school IDs for multi-school access
- `ci3_user_id` - Original CI3 user ID (for migration tracking)
- `ci3_user_table` - Original CI3 table name (student, teacher, etc.)
- `is_active` - Active status flag
- `created_at`, `updated_at` - Timestamps

**ci4_roles** - Role definitions:
- `id` - Primary key
- `role_name` - Display name
- `role_slug` - URL-friendly identifier
- `ci3_usertype_id` - Maps to CI3 usertypeID for compatibility
- `description` - Role description

**ci4_user_roles** - User-to-role assignments:
- `id` - Primary key
- `user_id` - References ci4_users.id
- `role_id` - References ci4_roles.id

## Security Considerations

1. **Encryption Key**: Must match CI3 installation for password compatibility during migration
2. **Independent Authentication**: CI4 uses its own `ci4_users` table, not CI3 user tables
3. **Password Migration**: Existing CI3 passwords are preserved as SHA-512 hashes and work seamlessly
4. **Future Upgrade Path**: Passwords can be upgraded to bcrypt/Argon2 on next login
5. **CSRF Protection**: Enabled globally (except auth routes)
6. **Input Validation**: All forms use validation rules
7. **XSS Protection**: Output is escaped in views
8. **Session Security**: Database-backed sessions with IP tracking
9. **Login Logging**: All login attempts are logged with IP and browser

## Troubleshooting

### "Encryption key not set" Error

Set the `ENCRYPTION_KEY` in `.env`:
```env
ENCRYPTION_KEY=your_32_character_or_longer_key_here
```

### Database Connection Failed

1. Verify credentials in `.env`
2. Ensure MySQL is running: `sudo systemctl status mysql`
3. Test connection: `mysql -u username -p database_name`
4. Check firewall rules if using remote database

### Sessions Not Working

1. Ensure `school_sessions` table exists
2. Verify session configuration in `.env`
3. Check writable permissions: `chmod -R 775 writable/`
4. Clear session files: `php spark cache:clear`

### Cannot Login

**For CI4 users (after migration):**

1. Verify the `ci4_users` table has been populated:
   ```sql
   SELECT * FROM ci4_users WHERE username='admin';
   ```

2. Check that the user is active:
   ```sql
   UPDATE ci4_users SET is_active=1 WHERE username='admin';
   ```

3. Verify the encryption key in `.env` matches your CI3 installation (for migrated users)

4. Test password hash manually:
   ```php
   $hash = hash('sha512', 'password' . getenv('ENCRYPTION_KEY'));
   echo $hash;
   ```
   Compare with the `password_hash` field in `ci4_users`

5. Check user has an assigned role:
   ```sql
   SELECT u.username, r.role_name 
   FROM ci4_users u
   LEFT JOIN ci4_user_roles ur ON u.id = ur.user_id
   LEFT JOIN ci4_roles r ON ur.role_id = r.id
   WHERE u.username='admin';
   ```

6. If no users exist, seed the default superadmin:
   ```bash
   php spark db:seed Ci4DefaultSuperadminSeeder
   ```

**For fresh installations:**

Use the default CI4 superadmin credentials:
- Username: `admin_ci4`
- Password: `ChangeMe123!`

### Migrations Failed

1. Check database credentials in `.env`
2. Ensure database user has CREATE/ALTER privileges
3. Check if tables already exist (migrations are idempotent):
   ```sql
   SHOW TABLES LIKE 'ci4_%';
   ```
4. View migration status:
   ```bash
   php spark migrate:status
   ```
5. Rollback if needed:
   ```bash
   php spark migrate:rollback
   ```

### 404 Errors

1. Enable mod_rewrite (Apache): `sudo a2enmod rewrite`
2. Check .htaccess in public directory
3. Verify DocumentRoot points to `public/` directory
4. Test: `php spark routes` to see registered routes

## Development

### Running Tests

```bash
composer test
```

### Code Quality Checks

```bash
# Run linter
composer lint

# Run static analysis
composer phpstan

# Run all checks
composer ci
```

### Adding New Routes

Edit `app/Config/Routes.php`:

```php
$routes->get('myroute', 'MyController::index', ['filter' => 'auth']);
```

### Creating Controllers

```bash
php spark make:controller MyController
```

### Creating Models

```bash
php spark make:model MyModel
```

## Deployment

### Production Checklist

1. Set environment to production in `.env`:
   ```env
   CI_ENVIRONMENT = production
   ```

2. Generate secure encryption key (32+ chars)

3. Update base URL:
   ```env
   app.baseURL = 'https://yourdomain.com/'
   ```

4. Disable debug mode:
   ```env
   CI_DEBUG = false
   ```

5. Configure proper database credentials

6. Set up SSL certificate

7. Configure proper file permissions:
   ```bash
   chmod -R 755 .
   chmod -R 775 writable/
   chmod -R 775 public/uploads/
   ```

8. Enable production-level caching

9. Set up automated backups

10. Configure monitoring and logging

## Database Audit & Upgrade

### 📚 Complete Migration Guide

For a comprehensive step-by-step guide to migrating from CI3 to CI4, including strategies, rollback procedures, and troubleshooting, see:

**[CI3 to CI4 Migration Guide](docs/CI3_TO_CI4_MIGRATION_GUIDE.md)**

### Checking Database Compatibility

The CI4 runtime requires certain tables and columns that may not exist in a legacy CI3 database. Use the database audit tools to check compatibility and apply necessary upgrades.

### Using Spark Commands (Recommended)

**Audit database for compatibility issues:**

```bash
# Basic audit
php spark db:audit

# Show results in JSON format
php spark db:audit --format=json

# Include experimental features (OKR, etc.)
php spark db:audit --include-experimental

# With table prefix (e.g., ci4_)
php spark db:audit --prefix=ci4_

# Include data validation
php spark db:audit --validate-data
```

**Upgrade database schema:**

```bash
# Dry run (show what would be changed)
php spark db:upgrade --dry-run

# Apply changes (requires confirmation)
php spark db:upgrade --apply

# Generate migration files instead of direct SQL
php spark db:upgrade --migrations

# Include experimental tables
php spark db:upgrade --apply --include-experimental

# With table prefix for CI4 tables
php spark db:upgrade --apply --prefix=ci4_
```

**Validate and backfill data:**

```bash
# Check for data issues
php spark db:backfill --dry-run

# Fix data issues (requires confirmation)
php spark db:backfill --apply

# With table prefix
php spark db:backfill --apply --prefix=ci4_
```

**Rollback schema changes:**

```bash
# List applied migrations
php spark db:rollback --list

# Rollback last migration
php spark db:rollback

# Rollback multiple steps
php spark db:rollback --steps=3

# Rollback to specific version
php spark db:rollback --to-version=upgrade_2024-11-19_12-00-00
```

**Note**: All `db:upgrade --apply` operations automatically create backups and track schema versions, enabling safe rollback capabilities.

### Using Standalone Script (No Spark Required)

If you can't use Spark commands, use the standalone script:

```bash
# Audit only
php ci4/scripts/ci3-db-upgrade.php \
  --dsn="mysql:host=localhost;dbname=shulelabs" \
  --user=root \
  --pass=secret

# Apply changes
php ci4/scripts/ci3-db-upgrade.php \
  --dsn="mysql:host=localhost;dbname=shulelabs" \
  --user=root \
  --pass=secret \
  --apply

# Include experimental features
php ci4/scripts/ci3-db-upgrade.php \
  --dsn="mysql:host=localhost;dbname=shulelabs" \
  --user=root \
  --pass=secret \
  --apply \
  --include-experimental
```

### What Gets Checked

The audit tool checks for:

- **Required CI4 Tables:**
  - `audit_events` - Immutable audit trail
  - `idempotency_keys` - API request deduplication
  - `school_sessions` - Database-backed sessions
  - `menu_overrides` - UI menu customization

- **Experimental Tables (with --include-experimental):**
  - `okr_objectives` - Objectives and Key Results
  - `okr_key_results` - Key result metrics

- **Column Presence:**
  - Validates all required columns exist in each table
  - Checks for proper data types

- **Indexes:**
  - Ensures performance-critical indexes are present
  - Validates unique constraints

### Safety Notes

**⚠️ IMPORTANT: Database backups are now automatic!**

**Automatic Backup Features (NEW):**

- `db:upgrade --apply` automatically creates backups before changes
- Backups stored in `writable/backups/` with metadata
- Each backup linked to schema version for easy rollback
- Use `--no-backup` flag to skip (not recommended)

**Manual Backup (if needed):**

```bash
# Create backup
mysqldump -u root -p shulelabs > backup_$(date +%Y%m%d_%H%M%S).sql

# Restore if needed
mysql -u root -p shulelabs < backup_20241118_120000.sql
```

**Safety Features:**

- **NEW**: Automatic backups before all schema changes
- **NEW**: Schema version tracking for audit trail
- **NEW**: One-command rollback capability (`db:rollback`)
- All destructive operations require `--apply` flag
- Dry-run mode is the default
- Interactive confirmation before applying changes
- Transaction-wrapped operations (rollback on error)
- No DROP or ALTER operations that shrink columns

**What the Tool Does NOT Do:**

- Drop tables or columns
- Modify existing data
- Alter column types (except adding new columns)
- Change foreign key relationships

## Migration from CI3

**CI4 User Authentication is Now Independent:**

The CI4 runtime now uses its own normalized user schema and no longer depends on CI3 user tables for authentication. Here's the migration process:

### Migration Steps

1. **Ensure both CI3 and CI4 share the same database** (during transition period)

2. **Run CI4 migrations to create CI4 user tables:**
   ```bash
   php spark migrate --all
   ```
   This will:
   - Create `ci4_users`, `ci4_roles`, and `ci4_user_roles` tables
   - Seed default roles
   - **Automatically backfill all users from CI3 tables** (student, teacher, parents, user, systemadmin)
   - Preserve original passwords (CI3-compatible hashes)

3. **Verify the migration:**
   ```bash
   # Check that users were migrated
   php spark db:query "SELECT COUNT(*) as total FROM ci4_users"
   
   # Check roles were seeded
   php spark db:query "SELECT * FROM ci4_roles"
   ```

4. **Test authentication:**
   - Existing CI3 usernames and passwords will work with CI4
   - CI4 now authenticates against `ci4_users` instead of CI3 tables
   - Session sharing via `school_sessions` still works (optional)

5. **Both systems can run in parallel:**
   - CI3 continues using its original tables (student, teacher, etc.)
   - CI4 uses its own `ci4_users` table
   - Users are synchronized at migration time, not at runtime
   - Changes in CI3 users won't automatically reflect in CI4 (and vice versa)

6. **Gradually migrate features from CI3 to CI4**

7. **Once migration is complete, you can:**
   - Decommission CI3
   - Optionally drop old CI3 user tables (student, teacher, parents, user, systemadmin)
   - CI4 will continue working independently

### Key Differences from Previous Approach

**Before:** CI4 authenticated against CI3 tables (multi-table lookup)
**Now:** CI4 authenticates against its own `ci4_users` table exclusively

**Before:** User tables were shared between CI3 and CI4
**Now:** CI4 has its own user schema with role-based access control

**Before:** Changes in CI3 automatically visible to CI4
**Now:** CI4 operates independently; one-time migration at setup

### Backward Compatibility

- **Password compatibility:** CI4 uses the same SHA-512 + ENCRYPTION_KEY hashing as CI3
- **Session compatibility:** Both can share `school_sessions` table (optional)
- **Migration tracking:** `ci4_users` stores original CI3 user ID and table name for reference

### Fresh Installation (No CI3)

If you're installing CI4 fresh without CI3:

```bash
# Run migrations
php spark migrate --all

# Seed default superadmin
php spark db:seed Ci4DefaultSuperadminSeeder
```

Default credentials: `admin_ci4` / `ChangeMe123!`

## Support

For issues and questions:

- GitHub Issues: <repository-url>/issues
- Documentation: <repository-url>/wiki
- Email: support@shulelabs.com

## License

Proprietary - All rights reserved

## Credits

Developed by the ShuleLabs Team
Based on CodeIgniter 4 Framework
