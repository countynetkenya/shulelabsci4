# 🚀 Getting Started - ShuleLabs CI4

**Time Required**: 5 minutes  
**Difficulty**: Beginner

## Quick Start

Get ShuleLabs running on your local machine in 5 minutes.

## Prerequisites

- PHP 8.3 or higher
- MySQL 8.0 / MariaDB 10.6+
- Composer
- Git

## Installation Steps

### 1. Clone the Repository

```bash
git clone https://github.com/countynetkenya/shulelabsci4.git
cd shulelabsci4
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Configure Environment

```bash
# Copy environment template
cp .env.example .env

# Edit .env with your settings
nano .env
```

**Required settings in .env**:
```env
# Database Configuration
DB_HOST = localhost
DB_DATABASE = shulelabs
DB_USERNAME = your_username
DB_PASSWORD = your_password
DB_DRIVER = MySQLi
DB_PORT = 3306

# Application
CI_ENVIRONMENT = development
app.baseURL = 'http://localhost:8080/'

# Security (change this!)
encryption.key = 'your-32-character-encryption-key-here'
```

### 4. Create Database

```bash
# Login to MySQL
mysql -u root -p

# Create database
CREATE DATABASE shulelabs CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
exit;
```

### 5. Run Migrations

```bash
# Run all migrations
php bin/migrate/latest

# Or use spark directly
php spark migrate --all
```

### 6. Start Development Server

```bash
php spark serve
```

Visit: http://localhost:8080

## Default Credentials

After seeding (optional):
```
Username: admin@shulelabs.local
Password: admin123
```

**⚠️ Change these credentials in production!**

## Verify Installation

### Check System Health

```bash
# Run tests
./vendor/bin/phpunit -c phpunit.ci4.xml

# Check migrations
php bin/migrate/status
```

### Access the Application

1. Open http://localhost:8080
2. You should see the login page
3. Login with default credentials
4. Explore the dashboard

## Next Steps

- [📖 Read System Overview](../01-SYSTEM-OVERVIEW.md)
- [💻 Set Up Development Environment](LOCAL-SETUP.md)
- [🏗️ Understand Architecture](../ARCHITECTURE.md)
- [🔐 Configure Security](../SECURITY.md)

## Troubleshooting

### Database Connection Error

**Problem**: "Unable to connect to database"

**Solution**:
1. Verify MySQL is running
2. Check DB credentials in `.env`
3. Ensure database exists
4. Test connection: `mysql -u username -p database_name`

### Migration Errors

**Problem**: Migration fails

**Solution**:
```bash
# Check migration status
php bin/migrate/status

# Rollback and retry
php bin/migrate/rollback
php bin/migrate/latest
```

### Permission Errors

**Problem**: "Permission denied" when writing files

**Solution**:
```bash
# Fix writable directory permissions
chmod -R 777 writable/
```

### Port Already in Use

**Problem**: Port 8080 is occupied

**Solution**:
```bash
# Use a different port
php spark serve --port=8081
```

## Common Tasks

### Create a New User

```bash
php spark make:user
# Follow prompts
```

### Reset Admin Password

```bash
php spark user:reset-password admin@shulelabs.local
```

### Clear Cache

```bash
php spark cache:clear
```

## Development Workflow

1. **Make Changes**: Edit code in `app/` directory
2. **Run Tests**: `./vendor/bin/phpunit`
3. **Check Code Quality**: `composer phpstan`
4. **Create Migration**: `php spark make:migration CreateTableName`
5. **Run Migration**: `php bin/migrate/latest`
6. **Commit**: `git add . && git commit -m "Description"`

## Additional Resources

- [Local Setup Guide](LOCAL-SETUP.md) - Detailed development setup
- [Docker Setup](DOCKER-SETUP.md) - Run with Docker
- [Testing Guide](TESTING.md) - How to write and run tests
- [Deployment Guide](DEPLOYMENT.md) - Deploy to production

## Need Help?

- [📚 Full Documentation](../00-START-HERE.md)
- [🐛 Report Issues](https://github.com/countynetkenya/shulelabsci4/issues)
- [💬 Ask Questions](https://github.com/countynetkenya/shulelabsci4/discussions)

---

**Last Updated**: 2025-11-22  
**Version**: 1.0.0
