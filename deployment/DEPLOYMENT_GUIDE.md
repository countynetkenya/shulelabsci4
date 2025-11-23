# ShuleLabs CI4 - Complete Deployment Guide

**Version:** 2.0.0  
**Last Updated:** November 23, 2025  
**Status:** Production Ready

## Table of Contents
1. [Overview](#overview)
2. [Prerequisites](#prerequisites)
3. [Development Environment](#development-environment)
4. [Staging Environment](#staging-environment)
5. [Production Deployment](#production-deployment)
6. [Database Migration](#database-migration)
7. [Docker Deployment](#docker-deployment)
8. [Environment Configuration](#environment-configuration)
9. [SSL Configuration](#ssl-configuration)
10. [Monitoring & Health Checks](#monitoring--health-checks)
11. [Backup & Recovery](#backup--recovery)
12. [Rollback Procedure](#rollback-procedure)
13. [Troubleshooting](#troubleshooting)

---

## Overview

This guide covers complete deployment for ShuleLabs CI4 across all environments: development, staging, and production. It includes database migration from SQLite to MySQL, Docker deployment, and troubleshooting.

**Supported Deployment Methods:**
- Traditional Linux server (Ubuntu 22.04+)
- Docker containers
- Kubernetes (advanced)

**Deployment Architecture:**
- **Web Server:** Nginx 1.24+ with HTTP/2 and TLS 1.3
- **Application Server:** PHP 8.3-FPM
- **Database:** SQLite (dev) | MySQL 8.0+ (staging/prod)
- **Cache:** Redis 7.0+ (optional for dev, required for prod)
- **Queue:** Built-in CI4 scheduler
- **CDN:** CloudFlare (optional)

---

## Prerequisites

### System Requirements

| Environment | CPU | RAM | Storage | OS |
|------------|-----|-----|---------|-----|
| Development | 1 core | 2GB | 10GB | Any |
| Staging | 2 cores | 4GB | 20GB | Ubuntu 22.04+ |
| Production | 4 cores | 8GB | 50GB | Ubuntu 22.04+ |

### Software Requirements
```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install required packages
sudo apt install -y \
    nginx \
    mysql-server \
    php8.3-fpm \
    php8.3-cli \
    php8.3-mysql \
    php8.3-intl \
    php8.3-mbstring \
    php8.3-xml \
    php8.3-curl \
    php8.3-zip \
    php8.3-gd \
    php8.3-bcmath \
    php8.3-redis \
    redis-server \
    git \
    composer \
    certbot \
    python3-certbot-nginx
```

### Domain Configuration
- Point domain DNS to server IP
- Configure A record: `shulelabs.com → YOUR_SERVER_IP`
- Configure A record: `www.shulelabs.com → YOUR_SERVER_IP`
- Configure CNAME (optional): `api.shulelabs.com → shulelabs.com`

---

## Development Environment

### Quick Start (SQLite - Recommended for Development)

**No database setup needed! Uses SQLite by default.**

```bash
# 1. Clone repository
git clone https://github.com/countynetkenya/shulelabsci4.git
cd shulelabsci4

# 2. Install dependencies
composer install

# 3. Setup environment
cp env .env

# 4. Run migrations
php spark migrate

# 5. Seed test data (23 users, 8 roles)
php spark db:seed CompleteDatabaseSeeder

# 6. Start development server
php spark serve
```

**Access:** http://localhost:8080  
**Login:** admin@shulelabs.local / Admin@123456

### Development with MySQL (Optional)

```bash
# 1. Create database
mysql -u root -p -e "CREATE DATABASE shulelabs_dev;"

# 2. Create user
mysql -u root -p -e "CREATE USER 'shulelabs_dev'@'localhost' IDENTIFIED BY 'dev_password';"
mysql -u root -p -e "GRANT ALL PRIVILEGES ON shulelabs_dev.* TO 'shulelabs_dev'@'localhost';"

# 3. Update .env
nano .env
```

Update these lines in `.env`:
```env
database.default.hostname = localhost
database.default.database = shulelabs_dev
database.default.username = shulelabs_dev
database.default.password = dev_password
database.default.DBDriver = MySQLi
```

```bash
# 4. Run migrations
php spark migrate

# 5. Seed data
php spark db:seed CompleteDatabaseSeeder

# 6. Start server
php spark serve
```

### IDE Setup (VS Code)

**Recommended Extensions:**
- PHP Intelephense
- PHP Debug (Xdebug)
- EditorConfig
- GitLens

**.vscode/settings.json:**
```json
{
  "php.validate.executablePath": "/usr/bin/php8.3",
  "php.suggest.basic": false,
  "intelephense.environment.phpVersion": "8.3.0"
}
```

### Running Tests in Development

```bash
# Run all tests
php vendor/bin/phpunit -c phpunit.ci4.xml

# Run with coverage
php vendor/bin/phpunit --coverage-html coverage/

# Code quality checks
composer cs:check
composer phpstan
```

---

## Staging Environment

Staging should mirror production as closely as possible.

### 1. Setup Staging Server

```bash
# Create directory structure
sudo mkdir -p /var/www/staging/{releases,backups,shared}
sudo chown -R www-data:www-data /var/www/staging
```

### 2. Create Staging Database

```bash
mysql -u root -p <<EOF
CREATE DATABASE shulelabs_staging CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'shulelabs_staging'@'localhost' IDENTIFIED BY 'STRONG_PASSWORD';
GRANT ALL PRIVILEGES ON shulelabs_staging.* TO 'shulelabs_staging'@'localhost';
FLUSH PRIVILEGES;
EOF
```

### 3. Configure Nginx for Staging

Create `/etc/nginx/sites-available/staging.shulelabs.conf`:

```nginx
server {
    listen 80;
    server_name staging.shulelabs.com;
    
    root /var/www/staging/current/public;
    index index.php index.html;
    
    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    
    # Staging banner (optional)
    add_header X-Environment "Staging" always;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Enable site:
```bash
sudo ln -s /etc/nginx/sites-available/staging.shulelabs.conf /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### 4. Deploy to Staging

```bash
cd /var/www/staging
sudo -u www-data git clone https://github.com/countynetkenya/shulelabsci4.git current
cd current
sudo -u www-data composer install --no-dev --optimize-autoloader
sudo -u www-data cp .env.staging .env

# Edit environment variables
sudo -u www-data nano .env

# Run migrations
sudo -u www-data php spark migrate

# Create symlink
sudo rm -f /var/www/staging/current
sudo ln -s /var/www/staging/releases/$(date +%Y%m%d%H%M%S) /var/www/staging/current
```

### 5. Test Staging Deployment

```bash
curl -I http://staging.shulelabs.com/health
# Should return: HTTP/1.1 200 OK
```

---

## Production Deployment

### Server Setup

### 1. Create Application Directory Structure
```bash
sudo mkdir -p /var/www/shulelabs/{releases,backups}
sudo mkdir -p /var/backups/shulelabs
sudo mkdir -p /var/log/shulelabs
sudo chown -R www-data:www-data /var/www/shulelabs
sudo chown -R www-data:www-data /var/log/shulelabs
```

### 2. Configure PHP-FPM
Edit `/etc/php/8.3/fpm/pool.d/www.conf`:
```ini
[www]
user = www-data
group = www-data
listen = /var/run/php/php8.3-fpm.sock
listen.owner = www-data
listen.group = www-data
pm = dynamic
pm.max_children = 50
pm.start_servers = 10
pm.min_spare_servers = 5
pm.max_spare_servers = 20
pm.max_requests = 500
php_admin_value[upload_max_filesize] = 50M
php_admin_value[post_max_size] = 50M
php_admin_value[memory_limit] = 256M
php_admin_value[max_execution_time] = 300
```

Restart PHP-FPM:
```bash
sudo systemctl restart php8.3-fpm
```

### 3. Install Nginx Configuration
```bash
sudo cp deployment/nginx/shulelabs.conf /etc/nginx/sites-available/
sudo ln -s /etc/nginx/sites-available/shulelabs.conf /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

### 4. Configure Redis
Edit `/etc/redis/redis.conf`:
```conf
requirepass YOUR_REDIS_PASSWORD
maxmemory 256mb
maxmemory-policy allkeys-lru
```

Restart Redis:
```bash
sudo systemctl restart redis-server
```

---

## Database Migration

### SQLite to MySQL Migration

For production deployments, migrate from SQLite to MySQL for better performance and scalability.

### 1. Install MySQL
```bash
sudo mysql_secure_installation
# Answer prompts: Y, strong password, Y, Y, Y, Y
```

### 2. Create Production Database
```bash
mysql -u root -p <<EOF
CREATE DATABASE shulelabs_production CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'shulelabs_user'@'localhost' IDENTIFIED BY 'STRONG_SECURE_PASSWORD';
GRANT ALL PRIVILEGES ON shulelabs_production.* TO 'shulelabs_user'@'localhost';
FLUSH PRIVILEGES;
EOF
```

### 3. Run Migrations on MySQL

```bash
# Update .env to use MySQL
nano .env
```

Update these lines:
```env
database.default.hostname = localhost
database.default.database = shulelabs_production
database.default.username = shulelabs_user
database.default.password = STRONG_SECURE_PASSWORD
database.default.DBDriver = MySQLi
database.default.port = 3306
```

```bash
# Run migrations (creates all tables)
php spark migrate

# Seed initial data
php spark db:seed CompleteDatabaseSeeder
```

### 4. Migrate Existing SQLite Data (If Applicable)

```bash
# Export from SQLite
php spark migrate:export sqlite mysql

# Or use the migration script
php scripts/migrate-sqlite-to-mysql.php
```

### 5. Verify Migration
```bash
mysql -u shulelabs_user -p shulelabs_production -e "
SELECT 
    (SELECT COUNT(*) FROM users) as users,
    (SELECT COUNT(*) FROM roles) as roles,
    (SELECT COUNT(*) FROM students) as students;
"
```

---

## Docker Deployment

### Using Docker Compose (Recommended)

**Development:**
```bash
# Start all services
docker-compose -f docker-compose.dev.yml up -d

# View logs
docker-compose logs -f app

# Access application
# Web: http://localhost:8080
# MySQL: localhost:3307
# Redis: localhost:6380
```

**Production:**
```bash
# Build and start
docker-compose up -d

# Check status
docker-compose ps

# View logs
docker-compose logs -f

# Stop services
docker-compose down
```

### Docker Compose Files

**docker-compose.yml (Production):**
```yaml
version: '3.8'

services:
  app:
    build:
      context: .
      dockerfile: Dockerfile
    container_name: shulelabs_app
    restart: unless-stopped
    environment:
      - CI_ENVIRONMENT=production
    volumes:
      - ./:/var/www/html
      - ./writable:/var/www/html/writable
    ports:
      - "8080:80"
    depends_on:
      - db
      - redis
    networks:
      - shulelabs

  db:
    image: mysql:8.0
    container_name: shulelabs_db
    restart: unless-stopped
    environment:
      MYSQL_ROOT_PASSWORD: rootpassword
      MYSQL_DATABASE: shulelabs_production
      MYSQL_USER: shulelabs_user
      MYSQL_PASSWORD: dbpassword
    volumes:
      - mysql_data:/var/lib/mysql
    ports:
      - "3306:3306"
    networks:
      - shulelabs

  redis:
    image: redis:7-alpine
    container_name: shulelabs_redis
    restart: unless-stopped
    command: redis-server --requirepass redispassword
    ports:
      - "6379:6379"
    networks:
      - shulelabs

  nginx:
    image: nginx:alpine
    container_name: shulelabs_nginx
    restart: unless-stopped
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./deployment/nginx/nginx.conf:/etc/nginx/nginx.conf
      - ./deployment/nginx/shulelabs.conf:/etc/nginx/conf.d/default.conf
      - ./public:/var/www/html/public
      - ./ssl:/etc/nginx/ssl
    depends_on:
      - app
    networks:
      - shulelabs

volumes:
  mysql_data:
    driver: local

networks:
  shulelabs:
    driver: bridge
```

**docker-compose.dev.yml (Development):**
```yaml
version: '3.8'

services:
  app:
    build:
      context: .
      dockerfile: Dockerfile.dev
    container_name: shulelabs_dev
    restart: unless-stopped
    environment:
      - CI_ENVIRONMENT=development
      - XDEBUG_MODE=debug
    volumes:
      - ./:/var/www/html
    ports:
      - "8080:8080"
    depends_on:
      - db
      - redis
    networks:
      - shulelabs_dev

  db:
    image: mysql:8.0
    container_name: shulelabs_db_dev
    restart: unless-stopped
    environment:
      MYSQL_ROOT_PASSWORD: root
      MYSQL_DATABASE: shulelabs_dev
      MYSQL_USER: dev
      MYSQL_PASSWORD: dev
    ports:
      - "3307:3306"
    networks:
      - shulelabs_dev

  redis:
    image: redis:7-alpine
    container_name: shulelabs_redis_dev
    restart: unless-stopped
    ports:
      - "6380:6379"
    networks:
      - shulelabs_dev

networks:
  shulelabs_dev:
    driver: bridge
```

### Dockerfile (Production)

```dockerfile
FROM php:8.3-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    nginx \
    git \
    curl \
    libpng-dev \
    libzip-dev \
    icu-dev \
    oniguruma-dev \
    mysql-client

# Install PHP extensions
RUN docker-php-ext-install \
    pdo_mysql \
    mysqli \
    intl \
    mbstring \
    zip \
    gd \
    bcmath

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . .

# Install dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Set permissions
RUN chown -R www-data:www-data /var/www/html/writable \
    && chmod -R 775 /var/www/html/writable

# Expose port
EXPOSE 9000

CMD ["php-fpm"]
```

### Dockerfile.dev (Development)

```dockerfile
FROM php:8.3-cli-alpine

# Install Xdebug and development tools
RUN apk add --no-cache \
    $PHPIZE_DEPS \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

CMD ["php", "spark", "serve", "--host=0.0.0.0", "--port=8080"]
```

### Docker Commands Reference

```bash
# Build images
docker-compose build

# Start services
docker-compose up -d

# Stop services
docker-compose down

# View logs
docker-compose logs -f app

# Execute commands in container
docker-compose exec app php spark migrate
docker-compose exec app php spark db:seed CompleteDatabaseSeeder
docker-compose exec app composer install

# Access MySQL
docker-compose exec db mysql -u shulelabs_user -p shulelabs_production

# Access Redis
docker-compose exec redis redis-cli

# Rebuild specific service
docker-compose up -d --build app

# Scale application (load balancing)
docker-compose up -d --scale app=3
```

---

## Environment Configuration

### Environment Files

ShuleLabs uses different environment files for each environment:

| File | Purpose |
|------|---------|
| `env` | Template (committed to Git) |
| `.env` | Active environment (not committed) |
| `.env.development` | Development defaults |
| `.env.staging` | Staging configuration |
| `.env.production` | Production configuration |

### Creating Production .env

```bash
# Copy template
cp env .env

# Generate encryption key
php spark key:generate

# Edit configuration
nano .env
```

### Critical Environment Variables

#### Application Settings
```env
# Environment
CI_ENVIRONMENT = production

# Base URL
app.baseURL = 'https://shulelabs.com/'
app.indexPage = ''

# Encryption
encryption.key = hex2bin:YOUR_GENERATED_KEY_HERE

# Security
security.csrfProtection = 'session'
security.tokenRandomize = true
security.tokenName = 'csrf_token'
```

#### Database Configuration
```env
database.default.hostname = localhost
database.default.database = shulelabs_production
database.default.username = shulelabs_user
database.default.password = STRONG_DATABASE_PASSWORD
database.default.DBDriver = MySQLi
database.default.DBPrefix = ci4_
database.default.port = 3306
```

#### Cache Configuration (Redis)
```env
cache.handler = redis
cache.redis.host = 127.0.0.1
cache.redis.port = 6379
cache.redis.password = REDIS_PASSWORD
cache.redis.database = 0
cache.redis.timeout = 0
```

#### Session Configuration
```env
session.driver = CodeIgniter\Session\Handlers\DatabaseHandler
session.cookieName = shulelabs_session
session.expiration = 7200
session.savePath = ci4_sessions
session.matchIP = false
session.timeToUpdate = 300
session.regenerateDestroy = false
```

#### Email Configuration (SMTP)
```env
email.protocol = smtp
email.SMTPHost = smtp.gmail.com
email.SMTPPort = 587
email.SMTPUser = noreply@shulelabs.com
email.SMTPPass = YOUR_EMAIL_PASSWORD
email.SMTPCrypto = tls
email.fromEmail = noreply@shulelabs.com
email.fromName = ShuleLabs
```

#### SMS Configuration (Africa's Talking)
```env
sms.provider = africastalking
sms.username = YOUR_USERNAME
sms.apiKey = YOUR_API_KEY
sms.from = SHULELABS
```

#### Payment Gateway (M-Pesa)
```env
mpesa.consumerKey = YOUR_CONSUMER_KEY
mpesa.consumerSecret = YOUR_CONSUMER_SECRET
mpesa.shortcode = YOUR_SHORTCODE
mpesa.passkey = YOUR_PASSKEY
mpesa.environment = production
mpesa.callbackUrl = https://shulelabs.com/api/mpesa/callback
```

### Environment Validation

Validate environment configuration:

```bash
php spark env:check
```

Sample output:
```
Environment Configuration Check
================================
✓ CI_ENVIRONMENT set to: production
✓ app.baseURL configured
✓ encryption.key is set
✓ Database connection successful
✓ Redis connection successful
✓ Writable directory is writable
✓ Session handler configured
⚠ Email configuration not tested (set email.test=true to test)
✓ All critical settings configured

Status: READY FOR DEPLOYMENT
```

---

## Application Deployment

### 1. Configure Environment
```bash
# Copy production environment template
sudo cp .env.production /var/www/shulelabs/.env

# Edit with production values
sudo nano /var/www/shulelabs/.env
```

**Critical values to update:**
- `encryption.key` - Generate with: `php spark key:generate`
- `database.default.password` - MySQL password
- `cache.redis.password` - Redis password
- Email credentials (SMTP)
- Payment gateway credentials
- SMS gateway credentials

### 2. Run Deployment Script
```bash
sudo -u www-data bash deployment/scripts/deploy.sh production
```

The deployment script will:
1. ✅ Run pre-deployment checks
2. ✅ Create backup of current version
3. ✅ Clone latest code from GitHub
4. ✅ Install composer dependencies
5. ✅ Configure environment
6. ✅ Run database migrations
7. ✅ Clear cache
8. ✅ Set proper permissions
9. ✅ Create symlink to new release
10. ✅ Reload services
11. ✅ Run health check
12. ✅ Clean up old releases

**Expected output:**
```
╔════════════════════════════════════════════════════════════════╗
║         ShuleLabs CI4 - Production Deployment Script          ║
╚════════════════════════════════════════════════════════════════╝

[INFO] Starting deployment to production...
[SUCCESS] Pre-deployment checks passed
[SUCCESS] Backup created: backup_20251123120000
[SUCCESS] Repository cloned to /var/www/shulelabs/releases/20251123120000
...
═══════════════════════════════════════════════════════════════
  DEPLOYMENT SUCCESSFUL!
  Release: /var/www/shulelabs/releases/20251123120000
  Time: Sat Nov 23 12:05:30 UTC 2025
═══════════════════════════════════════════════════════════════
```

### 3. Install Systemd Service (Optional)
```bash
sudo cp deployment/systemd/shulelabs.service /etc/systemd/system/
sudo systemctl daemon-reload
sudo systemctl enable shulelabs
sudo systemctl start shulelabs
sudo systemctl status shulelabs
```

---

## Monitoring & Health Checks

### Built-in Health Check Endpoint

ShuleLabs includes a comprehensive health check endpoint:

```bash
curl https://shulelabs.com/health
```

**Response:**
```json
{
  "status": "healthy",
  "timestamp": "2025-11-23T12:00:00Z",
  "version": "1.0.0",
  "checks": {
    "database": {
      "status": "up",
      "response_time": "2ms"
    },
    "redis": {
      "status": "up",
      "response_time": "1ms"
    },
    "disk_space": {
      "status": "ok",
      "free": "45GB",
      "used": "5GB"
    },
    "memory": {
      "status": "ok",
      "free": "6GB",
      "used": "2GB"
    }
  }
}
```

### Application Monitoring

#### 1. Log Monitoring

```bash
# Real-time error monitoring
tail -f /var/www/shulelabs/current/writable/logs/log-$(date +%Y-%m-%d).log

# Search for errors
grep -i "error\|exception\|fatal" /var/www/shulelabs/current/writable/logs/log-*.log

# Monitor specific module
grep "Finance\|Invoice\|Payment" /var/www/shulelabs/current/writable/logs/log-*.log
```

#### 2. Nginx Logs

```bash
# Access logs
tail -f /var/log/nginx/shulelabs-access.log

# Error logs
tail -f /var/log/nginx/shulelabs-error.log

# Response time analysis
awk '{print $NF}' /var/log/nginx/shulelabs-access.log | sort -n | tail -20
```

#### 3. Database Monitoring

```bash
# Active connections
mysql -u shulelabs_user -p -e "SHOW PROCESSLIST;"

# Slow queries
mysql -u shulelabs_user -p -e "SHOW FULL PROCESSLIST WHERE Time > 5;"

# Database size
mysql -u shulelabs_user -p -e "
SELECT 
    table_schema AS 'Database',
    ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'Size (MB)'
FROM information_schema.TABLES 
WHERE table_schema = 'shulelabs_production'
GROUP BY table_schema;
"
```

### Automated Health Monitoring

Create `/usr/local/bin/shulelabs-monitor.sh`:

```bash
#!/bin/bash

LOG_FILE="/var/log/shulelabs/health-check.log"
ALERT_EMAIL="admin@shulelabs.com"

# Check application health
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" https://shulelabs.com/health)
if [[ $HTTP_CODE -ne 200 ]]; then
    echo "[$(date)] ALERT: Health check failed! HTTP $HTTP_CODE" >> $LOG_FILE
    echo "Health check failed! HTTP $HTTP_CODE" | mail -s "ShuleLabs Alert" $ALERT_EMAIL
fi

# Check disk space
DISK_USAGE=$(df -h /var/www | awk 'NR==2 {print $5}' | sed 's/%//')
if [[ $DISK_USAGE -gt 85 ]]; then
    echo "[$(date)] WARNING: Disk usage at $DISK_USAGE%" >> $LOG_FILE
    echo "Disk usage at $DISK_USAGE%" | mail -s "ShuleLabs Disk Alert" $ALERT_EMAIL
fi

# Check MySQL connection
mysql -u shulelabs_user -pPASSWORD shulelabs_production -e "SELECT 1;" > /dev/null 2>&1
if [[ $? -ne 0 ]]; then
    echo "[$(date)] CRITICAL: MySQL connection failed!" >> $LOG_FILE
    echo "MySQL connection failed!" | mail -s "ShuleLabs DB Alert" $ALERT_EMAIL
fi

# Check Redis
redis-cli -a REDIS_PASSWORD ping > /dev/null 2>&1
if [[ $? -ne 0 ]]; then
    echo "[$(date)] WARNING: Redis connection failed!" >> $LOG_FILE
fi
```

Make executable and schedule:
```bash
sudo chmod +x /usr/local/bin/shulelabs-monitor.sh
sudo crontab -e
# Add: */5 * * * * /usr/local/bin/shulelabs-monitor.sh
```

### Performance Monitoring

#### Application Performance Monitoring (APM)

Install New Relic (optional):
```bash
# Install New Relic PHP agent
curl -L https://download.newrelic.com/php_agent/release/newrelic-php5-10.x.x.x-linux.tar.gz | tar -C /tmp -zx
cd /tmp/newrelic-php5-*
sudo NR_INSTALL_SILENT=1 NR_INSTALL_KEY=YOUR_LICENSE_KEY ./newrelic-install install

# Restart PHP-FPM
sudo systemctl restart php8.3-fpm
```

#### Custom Metrics (Prometheus - Optional)

```bash
# Install Prometheus PHP client
composer require promphp/prometheus_client_php

# Create metrics endpoint
# See app/Modules/Foundation/Controllers/MetricsController.php
```

### Alert Configuration

#### Email Alerts

Configure email notifications in `.env`:
```env
alerts.email.enabled = true
alerts.email.recipients = admin@shulelabs.com,devops@shulelabs.com
alerts.email.level = error
```

#### Slack Webhooks (Optional)

```env
alerts.slack.enabled = true
alerts.slack.webhook = https://hooks.slack.com/services/YOUR/WEBHOOK/URL
alerts.slack.channel = #production-alerts
```

---

## Backup & Recovery

### Automated Database Backups

Create `/usr/local/bin/shulelabs-backup.sh`:

```bash
#!/bin/bash

BACKUP_DIR="/var/backups/shulelabs"
DATE=$(date +%Y%m%d_%H%M%S)
DB_NAME="shulelabs_production"
DB_USER="shulelabs_user"
DB_PASS="YOUR_PASSWORD"

# Create backup directory
mkdir -p $BACKUP_DIR

# Database backup
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME | gzip > $BACKUP_DIR/db_$DATE.sql.gz

# Application files backup (excluding vendor and writable)
tar -czf $BACKUP_DIR/app_$DATE.tar.gz \
    --exclude='vendor' \
    --exclude='writable/cache' \
    --exclude='writable/logs' \
    /var/www/shulelabs/current

# Keep only last 30 days
find $BACKUP_DIR -name "db_*.sql.gz" -mtime +30 -delete
find $BACKUP_DIR -name "app_*.tar.gz" -mtime +30 -delete

echo "[$(date)] Backup completed: db_$DATE.sql.gz, app_$DATE.tar.gz"
```

Schedule backups:
```bash
sudo chmod +x /usr/local/bin/shulelabs-backup.sh
sudo crontab -e
# Daily at 2 AM
# 0 2 * * * /usr/local/bin/shulelabs-backup.sh >> /var/log/shulelabs/backup.log 2>&1
```

### Manual Backup

```bash
# Database only
mysqldump -u shulelabs_user -p shulelabs_production | gzip > /tmp/manual_backup.sql.gz

# Full backup (database + files)
sudo /usr/local/bin/shulelabs-backup.sh
```

### Restore from Backup

```bash
# Restore database
gunzip < /var/backups/shulelabs/db_20251123_020000.sql.gz | \
    mysql -u shulelabs_user -p shulelabs_production

# Restore application files
tar -xzf /var/backups/shulelabs/app_20251123_020000.tar.gz -C /

# Restart services
sudo systemctl restart php8.3-fpm nginx
```

### Off-site Backup (S3)

```bash
# Install AWS CLI
sudo apt install awscli

# Configure AWS credentials
aws configure

# Sync to S3
aws s3 sync /var/backups/shulelabs s3://shulelabs-backups/production/ \
    --exclude "*" \
    --include "db_*.sql.gz" \
    --include "app_*.tar.gz"
```

Add to backup script:
```bash
# At end of /usr/local/bin/shulelabs-backup.sh
aws s3 cp $BACKUP_DIR/db_$DATE.sql.gz s3://shulelabs-backups/production/
aws s3 cp $BACKUP_DIR/app_$DATE.tar.gz s3://shulelabs-backups/production/
```

---

## SSL Configuration

### 1. Obtain SSL Certificate (Let's Encrypt)
```bash
sudo certbot --nginx -d shulelabs.com -d www.shulelabs.com
```

Answer prompts:
- Email: your-email@example.com
- Agree to terms: Y
- Share email: N
- Redirect HTTP to HTTPS: 2 (Yes)

### 2. Test SSL Configuration
```bash
curl -I https://shulelabs.com/health
# Should return: HTTP/2 200
```

### 3. Setup Auto-Renewal
```bash
sudo systemctl enable certbot.timer
sudo systemctl start certbot.timer
sudo certbot renew --dry-run
```

---

## Monitoring Setup

### 1. Application Logs
```bash
# Real-time error monitoring
tail -f /var/log/shulelabs/log-$(date +%Y-%m-%d).log

# Nginx access logs
tail -f /var/log/nginx/shulelabs-access.log

# Nginx error logs
tail -f /var/log/nginx/shulelabs-error.log
```

### 2. Health Monitoring Script
Create `/usr/local/bin/shulelabs-health-check.sh`:
```bash
#!/bin/bash
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" https://shulelabs.com/health)
if [[ $HTTP_CODE -ne 200 ]]; then
    echo "Health check failed! HTTP $HTTP_CODE" | mail -s "ShuleLabs Alert" admin@example.com
fi
```

Add to crontab:
```bash
sudo crontab -e
# Add: */5 * * * * /usr/local/bin/shulelabs-health-check.sh
```

### 3. Database Backups
Add to crontab:
```bash
# Daily backup at 2 AM
0 2 * * * mysqldump -u shulelabs_user -pPASSWORD shulelabs_production | gzip > /var/backups/shulelabs/db-$(date +\%Y\%m\%d).sql.gz
# Keep only last 30 days
0 3 * * * find /var/backups/shulelabs -name "db-*.sql.gz" -mtime +30 -delete
```

---

## Rollback Procedure

### Quick Rollback (< 2 minutes)
```bash
# Rollback to previous release
sudo -u www-data bash deployment/scripts/rollback.sh

# Rollback to specific version
sudo -u www-data bash deployment/scripts/rollback.sh --version=20251122100000
```

### Manual Rollback
```bash
# List available releases
ls -lt /var/www/shulelabs/releases/

# Change symlink
sudo rm /var/www/shulelabs/current
sudo ln -s /var/www/shulelabs/releases/PREVIOUS_VERSION /var/www/shulelabs/current

# Reload services
sudo systemctl reload php8.3-fpm nginx
```

### Database Rollback
```bash
# Restore from backup
gunzip < /var/backups/shulelabs/db-20251122.sql.gz | mysql -u shulelabs_user -p shulelabs_production
```

---

## Troubleshooting

### Issue: 502 Bad Gateway
**Cause:** PHP-FPM not running or socket permission issues

**Fix:**
```bash
sudo systemctl status php8.3-fpm
sudo systemctl restart php8.3-fpm
sudo chown www-data:www-data /var/run/php/php8.3-fpm.sock
```

### Issue: Database Connection Failed
**Cause:** Wrong credentials or MySQL not running

**Fix:**
```bash
sudo systemctl status mysql
mysql -u shulelabs_user -p shulelabs_production -e "SELECT 1;"
# Verify .env database credentials
```

### Issue: Writable Directory Errors
**Cause:** Incorrect permissions

**Fix:**
```bash
sudo chown -R www-data:www-data /var/www/shulelabs/current/writable
sudo chmod -R 775 /var/www/shulelabs/current/writable
```

### Issue: Session Not Persisting
**Cause:** Session table missing or Redis not configured

**Fix:**
```bash
# Check sessions table
mysql -u shulelabs_user -p shulelabs_production -e "SHOW TABLES LIKE 'ci4_sessions';"

# Or check Redis
redis-cli -a YOUR_PASSWORD ping
```

### Issue: High CPU Usage
**Cause:** PHP-FPM pool exhausted

**Fix:**
```bash
# Monitor PHP-FPM
sudo tail -f /var/log/php8.3-fpm.log

# Increase pool size in /etc/php/8.3/fpm/pool.d/www.conf
pm.max_children = 100
pm.start_servers = 20

sudo systemctl restart php8.3-fpm
```

---

## Performance Optimization

### Enable OPcache
Edit `/etc/php/8.3/fpm/php.ini`:
```ini
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=10000
opcache.revalidate_freq=60
opcache.fast_shutdown=1
```

### Configure MySQL Query Cache
Edit `/etc/mysql/mysql.conf.d/mysqld.cnf`:
```ini
[mysqld]
query_cache_type = 1
query_cache_size = 64M
query_cache_limit = 2M
innodb_buffer_pool_size = 1G
```

### Enable Nginx FastCGI Cache
Add to nginx config:
```nginx
fastcgi_cache_path /var/cache/nginx levels=1:2 keys_zone=SHULELABS:100m inactive=60m;
fastcgi_cache_key "$scheme$request_method$host$request_uri";
```

---

## Security Checklist

- [ ] SSL certificate installed and auto-renewing
- [ ] Firewall configured (allow only 80, 443, 22)
- [ ] SSH key-based authentication enabled
- [ ] Database credentials rotated from defaults
- [ ] Redis password set
- [ ] Application encryption key unique
- [ ] File permissions correct (755 for directories, 644 for files)
- [ ] Writable directory not web-accessible
- [ ] .env file not in web root
- [ ] Security headers configured in Nginx
- [ ] Rate limiting enabled
- [ ] Regular backups automated
- [ ] Monitoring and alerting active
- [ ] Fail2ban installed and configured
- [ ] Application logs being reviewed

---

## Production URL

After successful deployment, access your application at:
- **Main Site:** https://shulelabs.com
- **Health Check:** https://shulelabs.com/health
- **Admin Portal:** https://shulelabs.com/admin
- **API Endpoint:** https://shulelabs.com/api

---

## Support

For deployment issues:
- **Email:** devops@shulelabs.com
- **Documentation:** https://github.com/countynetkenya/shulelabsci4/tree/main/docs
- **Issues:** https://github.com/countynetkenya/shulelabsci4/issues

---

**Last Updated:** November 23, 2025  
**Maintained By:** ShuleLabs DevOps Team
