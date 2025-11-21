# How to Access CI4 Through the Browser

## Current Setup (In This Repository)

Since you're currently in the main ShuleLabs repository where CI3 and CI4 coexist, there are two ways to access CI4:

### Option 1: Using the /v2 Route (Current Setup)

While both CI3 and CI4 are in the same repository, CI4 is accessible through the `/v2` prefix:

**Using Docker (Recommended for current setup):**

```bash
# 1. Start the Docker containers
docker compose up -d

# 2. Access CI4 in your browser at:
http://localhost:8080/v2/auth/signin
```

**Full URL Structure:**
- Sign In: `http://localhost:8080/v2/auth/signin`
- Dashboard: `http://localhost:8080/v2/dashboard`
- Admin Panel: `http://localhost:8080/v2/admin`
- School Selection: `http://localhost:8080/v2/school/select`

**How it works:**
- The file `public/v2.php` acts as a front controller for CI4
- It redirects `/v2/*` requests to the CI4 application in the `ci4/` folder
- CI3 handles all other routes (without `/v2`)

### Option 2: Using CI4 Development Server

You can run CI4's built-in development server directly:

```bash
# 1. Navigate to the ci4 directory
cd ci4

# 2. Install dependencies (if not already done)
composer install

# 3. Copy environment file
cp .env.example .env

# 4. Edit .env and set database credentials
nano .env  # or use your preferred editor

# 5. Start the development server
php spark serve

# 6. Access in browser at:
http://localhost:8080/auth/signin
```

**Note:** When using `php spark serve`, there's NO `/v2` prefix because CI4 runs standalone.

## After Extracting to Standalone Repository

Once you extract CI4 to its own repository (following the extraction guide), access changes:

### Production Setup with Apache

**1. Configure Apache Virtual Host:**

```apache
<VirtualHost *:80>
    ServerName shulelabs-ci4.local
    DocumentRoot /path/to/shulelabs-ci4/public
    
    <Directory /path/to/shulelabs-ci4/public>
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/shulelabs_ci4_error.log
    CustomLog ${APACHE_LOG_DIR}/shulelabs_ci4_access.log combined
</VirtualHost>
```

**2. Add to /etc/hosts (for local development):**
```
127.0.0.1   shulelabs-ci4.local
```

**3. Restart Apache:**
```bash
sudo systemctl restart apache2
```

**4. Access in browser:**
```
http://shulelabs-ci4.local/auth/signin
```

### Production Setup with Nginx

**1. Configure Nginx:**

```nginx
server {
    listen 80;
    server_name shulelabs-ci4.local;
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

**2. Add to /etc/hosts:**
```
127.0.0.1   shulelabs-ci4.local
```

**3. Restart Nginx:**
```bash
sudo systemctl restart nginx
```

**4. Access in browser:**
```
http://shulelabs-ci4.local/auth/signin
```

### Standalone Development Server

**Easiest way for development:**

```bash
cd /path/to/shulelabs-ci4
php spark serve --host=0.0.0.0 --port=8080

# Access at:
http://localhost:8080/auth/signin
```

## Quick Start Guide

### For Current Repository (with /v2)

```bash
# 1. Clone and setup
git clone https://github.com/countynetkenya/shulelabs.git
cd shulelabs

# 2. Start Docker
docker compose up -d

# 3. Open browser
# Go to: http://localhost:8080/v2/auth/signin
```

### For Standalone CI4 (after extraction)

```bash
# 1. Extract ci4 folder to new repo
# (See CI4_EXTRACTION_GUIDE.md)

# 2. Install dependencies
cd ci4
composer install

# 3. Configure environment
cp .env.example .env
# Edit .env with your database credentials

# 4. Start development server
php spark serve

# 5. Open browser
# Go to: http://localhost:8080/auth/signin
# (No /v2 prefix!)
```

## Common Access Scenarios

### Scenario 1: Local Development (Current Repo)
```
URL: http://localhost:8080/v2/auth/signin
Method: Docker or public/v2.php
```

### Scenario 2: CI4 Development Server (Current Repo)
```bash
cd ci4
php spark serve
# URL: http://localhost:8080/auth/signin
# (No /v2 when running standalone)
```

### Scenario 3: Production (Standalone CI4)
```
URL: https://yourdomain.com/auth/signin
Method: Apache/Nginx pointing to public/ directory
```

### Scenario 4: Staging/Testing (Standalone)
```bash
php spark serve --host=staging.local --port=8000
# URL: http://staging.local:8000/auth/signin
```

## URLs Reference

### In Current Repository (with /v2)
- **Sign In**: http://localhost:8080/v2/auth/signin
- **Sign Out**: http://localhost:8080/v2/auth/signout
- **Dashboard**: http://localhost:8080/v2/dashboard
- **Admin**: http://localhost:8080/v2/admin
- **School Select**: http://localhost:8080/v2/school/select

### Standalone CI4 (after extraction)
- **Sign In**: http://localhost:8080/auth/signin
- **Sign Out**: http://localhost:8080/auth/signout
- **Dashboard**: http://localhost:8080/dashboard
- **Admin**: http://localhost:8080/admin
- **School Select**: http://localhost:8080/school/select

## Troubleshooting

### "404 Not Found" Error

**Problem:** Can't access any CI4 pages

**Solutions:**
1. **Check you're using the right URL**
   - Current repo: Use `/v2` prefix
   - Standalone: No `/v2` prefix

2. **Verify web server is running**
   ```bash
   # For Docker:
   docker compose ps
   
   # For Apache:
   sudo systemctl status apache2
   
   # For development server:
   # Make sure you ran: php spark serve
   ```

3. **Check DocumentRoot points to public/ directory**
   - Apache/Nginx must point to the `public/` folder, not the root

### "Connection Refused" Error

**Problem:** Browser can't connect

**Solutions:**
1. **Check the port is correct**
   - Docker: port 8080
   - Dev server: usually 8080 (check `php spark serve` output)

2. **Verify service is running**
   ```bash
   # Check what's listening
   sudo netstat -tlnp | grep 8080
   ```

3. **Try different port**
   ```bash
   php spark serve --port=9000
   ```

### "Blank Page" or "500 Error"

**Problem:** Page loads but shows error

**Solutions:**
1. **Check .env file exists**
   ```bash
   cd ci4
   ls -la .env
   ```

2. **Verify database connection**
   - Check DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD in .env

3. **Check file permissions**
   ```bash
   chmod -R 755 ci4/writable
   ```

4. **View error logs**
   ```bash
   tail -f ci4/writable/logs/*.log
   ```

### "CSRF Token Mismatch" Error

**Problem:** Form submissions fail

**Solutions:**
1. **Clear browser cookies**
   - Delete cookies for localhost:8080

2. **Check session configuration**
   - Verify SESSION_DRIVER=database in .env
   - Ensure school_sessions table exists

3. **Restart development server**
   ```bash
   # Stop (Ctrl+C) and restart
   php spark serve
   ```

## Default Credentials

If using the demo database, default credentials are:

- **Admin**: 
  - Username: `admin`
  - Password: `123456`

- **Teacher**: 
  - Username: `teacher1`
  - Password: `123456`

- **Student**: 
  - Username: `student1`
  - Password: `123456`

**⚠️ IMPORTANT:** Change these passwords immediately after first login!

## Production Deployment URLs

When deployed to production, configure your `.env`:

```env
app.baseURL = 'https://school.yourdomain.com/'
```

Then access at:
```
https://school.yourdomain.com/auth/signin
```

## Summary

**Right Now (in this repository):**
- Access CI4 at: `http://localhost:8080/v2/auth/signin`
- Use Docker or `php spark serve` from ci4 directory

**After Extraction (standalone):**
- Access CI4 at: `http://localhost:8080/auth/signin` (no /v2)
- Configure web server or use development server

The `/v2` prefix is **only needed in the current dual-setup** where CI3 and CI4 coexist. For standalone CI4, use direct URLs without `/v2`.
