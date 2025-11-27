# Docker Deployment Guide

## Quick Start

### Development Environment
```bash
# Start development environment with Xdebug and Mailhog
docker-compose -f docker-compose.dev.yml up -d

# Access services
# - App: http://localhost:8080
# - Adminer (DB): http://localhost:8081
# - Mailhog: http://localhost:8025
```

### Production Environment
```bash
# Build and start production environment
docker-compose up -d

# Run migrations
docker-compose exec app php spark migrate --all

# Access
# - App: http://localhost:8080
```

## Architecture

### Production Stack (`docker-compose.yml`)
- **App Container**: PHP 8.3-FPM + Nginx + Supervisor
  - CodeIgniter 4 application
  - PHP extensions: bcmath, gd, intl, zip, PDO MySQL
  - Process manager: Supervisor (php-fpm + nginx)
  
- **MySQL 8.0**: Primary database
  - Port: 3306
  - Persistent volume: `db-data`
  
- **Redis 7**: Cache and sessions
  - Port: 6379
  - Persistent volume: `redis-data`
  
- **Adminer**: Database management UI
  - Port: 8081

### Development Stack (`docker-compose.dev.yml`)
Extends production with:
- **Xdebug**: Remote debugging on port 9003
- **Mailhog**: Email testing
  - SMTP: localhost:1025
  - Web UI: http://localhost:8025
- **Live code mounting**: Changes reflect immediately
- **Development PHP.ini**: Display errors, verbose logging

## File Structure

```
/
├── Dockerfile              # Production image
├── Dockerfile.dev          # Development image
├── docker-compose.yml      # Production stack
├── docker-compose.dev.yml  # Development stack
├── .dockerignore          # Build optimization
└── deployment/
    ├── nginx/
    │   ├── nginx.conf     # Main Nginx config
    │   └── default.conf   # CodeIgniter server block
    ├── supervisor/
    │   └── supervisord.conf  # Process manager
    └── scripts/
        ├── deploy.sh      # Deployment automation
        └── rollback.sh    # Rollback script
```

## Configuration

### Environment Variables

Create `.env` file in project root:

```env
# Application
CI_ENVIRONMENT=production
APP_PORT=8080

# Database
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=shulelabs
DB_USERNAME=shulelabs
DB_PASSWORD=your_secure_password_here

# Redis
REDIS_HOST=redis
REDIS_PORT=6379

# Security
ENCRYPTION_KEY=your_encryption_key_here
JWT_SECRET=your_jwt_secret_here

# Base URL
APP_BASE_URL=https://your-domain.com
```

### Database Initialization

**Option 1**: Use Adminer (http://localhost:8081)
- System: MySQL
- Server: mysql
- Username: shulelabs
- Password: (from .env)
- Database: shulelabs

**Option 2**: Command line
```bash
docker-compose exec mysql mysql -u root -p -e "
  CREATE DATABASE IF NOT EXISTS shulelabs;
  GRANT ALL ON shulelabs.* TO 'shulelabs'@'%';
  FLUSH PRIVILEGES;"
```

## Deployment Scripts

### Auto-deployment
```bash
# Deploy to production
./deployment/scripts/deploy.sh production

# Deploy to development
./deployment/scripts/deploy.sh development
```

The script will:
1. Stop existing containers
2. Build fresh images
3. Start services
4. Run migrations (production only)
5. Display logs

### Manual Deployment

```bash
# Build production image
docker-compose build --no-cache

# Start services
docker-compose up -d

# Check health
docker-compose ps
docker-compose logs -f app

# Run migrations
docker-compose exec app php spark migrate --all

# Seed data (if needed)
docker-compose exec app php spark db:seed InitialDataSeeder
```

## Common Tasks

### View Logs
```bash
# All services
docker-compose logs -f

# Specific service
docker-compose logs -f app
docker-compose logs -f mysql
```

### Access Container Shell
```bash
# App container
docker-compose exec app sh

# MySQL
docker-compose exec mysql mysql -u shulelabs -p shulelabs
```

### Run Tests Inside Container
```bash
docker-compose exec app ./vendor/bin/phpunit

# Specific test
docker-compose exec app ./vendor/bin/phpunit tests/Foundation/
```

### Database Backup
```bash
# Export
docker-compose exec mysql mysqldump -u shulelabs -p shulelabs > backup.sql

# Import
docker-compose exec -T mysql mysql -u shulelabs -p shulelabs < backup.sql
```

### Clear Cache
```bash
docker-compose exec app php spark cache:clear
docker-compose exec redis redis-cli FLUSHALL
```

## Performance Tuning

### PHP-FPM
Edit `Dockerfile` and adjust:
```dockerfile
RUN echo "pm.max_children = 50" >> /usr/local/etc/php-fpm.d/www.conf
RUN echo "pm.start_servers = 10" >> /usr/local/etc/php-fpm.d/www.conf
RUN echo "pm.min_spare_servers = 5" >> /usr/local/etc/php-fpm.d/www.conf
RUN echo "pm.max_spare_servers = 15" >> /usr/local/etc/php-fpm.d/www.conf
```

### Nginx
Edit `deployment/nginx/nginx.conf`:
```nginx
worker_processes auto;
worker_connections 2048;
```

### MySQL
Add to `docker-compose.yml`:
```yaml
mysql:
  command: --max-connections=500 --innodb-buffer-pool-size=1G
```

## Troubleshooting

### Container won't start
```bash
# Check logs
docker-compose logs app

# Verify image build
docker-compose build app

# Check permissions
docker-compose exec app ls -la /var/www/html/writable
```

### Database connection failed
```bash
# Verify MySQL is running
docker-compose ps mysql

# Check network
docker-compose exec app ping mysql

# Test connection
docker-compose exec app php spark db:table schools
```

### Permission issues
```bash
# Fix writable permissions
docker-compose exec app chown -R www-data:www-data /var/www/html/writable
docker-compose exec app chmod -R 775 /var/www/html/writable
```

### Nginx 502 Bad Gateway
```bash
# Check PHP-FPM status
docker-compose exec app supervisorctl status php-fpm

# Restart PHP-FPM
docker-compose exec app supervisorctl restart php-fpm

# Check PHP-FPM logs
docker-compose exec app tail -f /var/log/php-fpm.log
```

## Codespaces Deployment

### GitHub Codespaces Setup

1. **Start services in Codespaces**:
```bash
docker-compose -f docker-compose.dev.yml up -d
```

2. **Port forwarding** (automatic in Codespaces):
   - 8080 → Application
   - 8081 → Adminer
   - 8025 → Mailhog
   - 3306 → MySQL

3. **Access from browser**:
   - Codespaces will provide forwarded URLs
   - Example: `https://username-repo-8080.preview.app.github.dev`

### Codespaces .env Configuration
```env
CI_ENVIRONMENT=development
APP_PORT=8080
DB_HOST=mysql
APP_BASE_URL=https://your-codespace-url.preview.app.github.dev
```

## Security Best Practices

### Production Checklist
- [ ] Change default passwords in `.env`
- [ ] Use strong `ENCRYPTION_KEY` (32 chars)
- [ ] Use strong `JWT_SECRET`
- [ ] Disable Adminer in production
- [ ] Enable HTTPS with SSL certificates
- [ ] Set `CI_ENVIRONMENT=production`
- [ ] Configure firewall rules
- [ ] Regular database backups
- [ ] Monitor logs for security events

### SSL/TLS Setup (Production)
1. Install certbot in container
2. Obtain Let's Encrypt certificate
3. Update `deployment/nginx/default.conf`:
```nginx
server {
    listen 443 ssl http2;
    ssl_certificate /etc/letsencrypt/live/domain/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/domain/privkey.pem;
    # ... rest of config
}
```

## Monitoring & Health Checks

### Health Check Endpoint
```bash
# Check app health
curl http://localhost:8080/health

# Expected response
{"status":"ok","database":"connected","cache":"available"}
```

### Container Health
```bash
# All services status
docker-compose ps

# Resource usage
docker stats

# Inspect container
docker-compose inspect app
```

## Scaling

### Horizontal Scaling
```yaml
# docker-compose.yml
services:
  app:
    deploy:
      replicas: 3
    # ... rest of config
```

### Load Balancer
Use Nginx reverse proxy or cloud load balancer (AWS ALB, etc.)

## CI/CD Integration

### GitHub Actions Example
```yaml
name: Deploy to Production

on:
  push:
    branches: [main]

jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      
      - name: Build Docker image
        run: docker-compose build
      
      - name: Run tests
        run: docker-compose run app ./vendor/bin/phpunit
      
      - name: Deploy
        run: ./deployment/scripts/deploy.sh production
```

## Support & Resources

- **CodeIgniter 4 Docs**: https://codeigniter.com/user_guide/
- **Docker Docs**: https://docs.docker.com/
- **Nginx Docs**: http://nginx.org/en/docs/
- **Supervisor Docs**: http://supervisord.org/

## License
MIT License - see LICENSE file for details
