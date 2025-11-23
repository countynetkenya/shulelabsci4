# ShuleLabs CI4 - Production Deployment Guide

**Version:** 1.0.0  
**Last Updated:** November 23, 2025  
**Status:** Production Ready

## Table of Contents
1. [Overview](#overview)
2. [Prerequisites](#prerequisites)
3. [Server Setup](#server-setup)
4. [Database Migration](#database-migration)
5. [Application Deployment](#application-deployment)
6. [SSL Configuration](#ssl-configuration)
7. [Monitoring Setup](#monitoring-setup)
8. [Rollback Procedure](#rollback-procedure)
9. [Troubleshooting](#troubleshooting)

---

## Overview

This guide covers the complete production deployment process for ShuleLabs CI4, including infrastructure setup, database migration from SQLite to MySQL, and zero-downtime deployment strategies.

**Deployment Architecture:**
- **Web Server:** Nginx 1.24+ with HTTP/2 and TLS 1.3
- **Application Server:** PHP 8.3-FPM
- **Database:** MySQL 8.0+ (migrated from SQLite)
- **Cache:** Redis 7.0+
- **Queue:** Built-in CI4 scheduler
- **CDN:** CloudFlare (optional)

---

## Prerequisites

### System Requirements
- Ubuntu 22.04 LTS or higher
- Minimum 2 CPU cores
- Minimum 4GB RAM
- Minimum 20GB SSD storage
- Root or sudo access

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

## Server Setup

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

### 1. Install MySQL
```bash
sudo mysql_secure_installation
# Answer prompts: Y, strong password, Y, Y, Y, Y
```

### 2. Create Database and User
```bash
sudo mysql -u root -p < deployment/database/migrate-to-mysql.sql
```

**Important:** Update the password in the SQL file before running!

### 3. Export SQLite Data
```bash
# Install sqlite3
sudo apt install sqlite3

# Export users
sqlite3 writable/database.db ".mode insert ci4_users" ".output /tmp/users.sql" "SELECT * FROM ci4_users;"

# Export other tables (repeat for all tables)
sqlite3 writable/database.db ".mode insert ci4_roles" ".output /tmp/roles.sql" "SELECT * FROM ci4_roles;"
# ... (see full migration script in deployment/scripts/)
```

### 4. Import to MySQL
```bash
mysql -u shulelabs_user -p shulelabs_production < /tmp/users.sql
mysql -u shulelabs_user -p shulelabs_production < /tmp/roles.sql
# ... (import all exported tables)
```

### 5. Verify Migration
```bash
mysql -u shulelabs_user -p -e "USE shulelabs_production; SELECT COUNT(*) FROM ci4_users;"
# Should show 21 users
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
