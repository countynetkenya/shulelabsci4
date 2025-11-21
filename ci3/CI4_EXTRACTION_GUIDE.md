# Extracting CI4 to Its Own Repository

## Overview

Yes, you can now extract the `ci4` folder to its own repository! The CI4 application has been prepared to run completely standalone without the `/v2` URL prefix.

## What Changed

### Removed `/v2` Requirement
- **Before**: CI4 ran at `http://localhost:8080/v2/` (when alongside CI3)
- **After**: CI4 runs at `http://localhost:8080/` (standalone)
- The `/v2` prefix was only needed for the dual-runtime setup where CI3 and CI4 ran side-by-side
- For standalone deployment, CI4 uses standard root URLs

### Configuration Updates
- `ci4/app/Config/App.php`: Updated `$baseURL` to remove `/v2` prefix
- `ci4/.env.example`: Already configured without `/v2`
- All routes work directly from root (e.g., `/auth/signin`, `/dashboard`, `/admin`)

## Extraction Steps

### Option 1: Complete Extraction (Recommended)

Extract the entire `ci4` folder to a new repository:

```bash
# 1. Create new repository on GitHub
# Example: shulelabs-ci4

# 2. Clone the new empty repository
git clone https://github.com/yourusername/shulelabs-ci4.git
cd shulelabs-ci4

# 3. Copy CI4 contents from the original repository
# From the original shulelabs repo:
cp -r /path/to/shulelabs/ci4/* .
cp /path/to/shulelabs/ci4/.env.example .
cp /path/to/shulelabs/ci4/.gitignore .

# 4. Initialize as root-level application
# Move contents up if needed (optional)
# The ci4 structure is already standalone-ready

# 5. Commit and push
git add .
git commit -m "Initial CI4 standalone application"
git push origin main
```

### Option 2: Keep ci4 Structure

If you prefer to keep the `ci4` folder structure in the new repo:

```bash
git clone https://github.com/yourusername/shulelabs-ci4.git
cd shulelabs-ci4

# Copy the entire ci4 folder
cp -r /path/to/shulelabs/ci4 .

# Copy documentation
cp /path/to/shulelabs/CI4_*.md .

git add .
git commit -m "Initial CI4 standalone application"
git push origin main
```

## Post-Extraction Setup

After extracting to the new repository:

### 1. Install Dependencies

```bash
composer install
```

This will install:
- CodeIgniter 4 framework (^4.5)
- PHPUnit for testing
- All required dependencies

### 2. Configure Environment

```bash
cp .env.example .env
```

Edit `.env` with your settings:

```env
# Base URL (NO /v2 prefix for standalone)
app.baseURL = 'https://yourdomain.com/'

# Database
DB_HOST=localhost
DB_DATABASE=shulelabs
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Security (MUST match CI3 if sharing database)
encryption.key=YOUR_SECURE_KEY
ENCRYPTION_KEY=YOUR_SECURE_KEY

# Session
SESSION_DRIVER=database
SESSION_COOKIE_NAME=school
SESSION_SAVE_PATH=school_sessions
```

### 3. Configure Web Server

**Apache (.htaccess already included)**

Point your DocumentRoot to the `public` directory:

```apache
<VirtualHost *:80>
    ServerName shulelabs.yourdomain.com
    DocumentRoot /path/to/shulelabs-ci4/public
    
    <Directory /path/to/shulelabs-ci4/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

**Nginx**

```nginx
server {
    listen 80;
    server_name shulelabs.yourdomain.com;
    root /path/to/shulelabs-ci4/public;
    
    index index.php;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### 4. Run Migrations (if needed)

```bash
php spark migrate --all
```

### 5. Test the Application

```bash
# Development server
php spark serve

# Access at http://localhost:8080
# No /v2 prefix needed!
```

## URL Structure in Standalone Mode

### Authentication
- **Sign In**: `http://yourdomain.com/auth/signin` (not `/v2/auth/signin`)
- **Sign Out**: `http://yourdomain.com/auth/signout`

### Main Pages
- **Dashboard**: `http://yourdomain.com/dashboard`
- **Admin Panel**: `http://yourdomain.com/admin`
- **School Selection**: `http://yourdomain.com/school/select`

### Module Routes
Module routes remain the same but without `/v2`:
- Foundation: `http://yourdomain.com/foundation/*`
- Finance: `http://yourdomain.com/finance/*`
- HR: `http://yourdomain.com/hr/*`
- etc.

## Database Considerations

### Shared Database (During Migration)
If you want CI4 standalone to share the database with CI3:

1. Keep the same database credentials in `.env`
2. Ensure `ENCRYPTION_KEY` matches CI3 exactly
3. Both systems can access the same `school_sessions` table
4. Users can authenticate in either system

### Separate Database (Full Independence)
For complete independence:

1. Export the database schema from CI3
2. Import to a new database
3. Update `.env` with new database credentials
4. Generate a new `ENCRYPTION_KEY`
5. Update all user passwords (since hash will be different)

## Differences from Dual-Runtime Setup

### What's Changed
- ✅ No `/v2` prefix in URLs
- ✅ Standalone `composer.json` with CI4 framework
- ✅ Independent `vendor` directory
- ✅ Self-contained application

### What's the Same
- ✅ Database schema compatibility
- ✅ Session table structure (`school_sessions`)
- ✅ Password hashing algorithm (when using same encryption key)
- ✅ All features and functionality
- ✅ Authentication system
- ✅ Multi-school support

## Deployment Checklist

- [ ] Extract `ci4` folder to new repository
- [ ] Run `composer install` in the new repo
- [ ] Copy and configure `.env` file
- [ ] Update `app.baseURL` in `.env` (no `/v2`)
- [ ] Configure web server to point to `public/` directory
- [ ] Set file permissions (755 for directories, 644 for files)
- [ ] Run database migrations if needed
- [ ] Test authentication flow
- [ ] Test all main routes (dashboard, admin, etc.)
- [ ] Verify CSRF protection works
- [ ] Test with production database
- [ ] Configure SSL certificate
- [ ] Set up automated backups
- [ ] Configure monitoring

## Troubleshooting

### "404 Not Found" on Routes
- Ensure web server points to `public/` directory
- Check `.htaccess` exists in `public/`
- Enable `mod_rewrite` for Apache
- Verify `$baseURL` doesn't have `/v2` in `.env` or `App.php`

### "Can't Find Framework"
- Run `composer install` to install CodeIgniter 4 framework
- Check `vendor/codeigniter4/framework/` exists
- Verify `Paths.php` can detect the framework location

### Session Issues
- Ensure `school_sessions` table exists
- Verify `SESSION_DRIVER=database` in `.env`
- Check database credentials are correct
- Confirm session table structure matches CI3

### Authentication Fails
- Verify `ENCRYPTION_KEY` matches CI3 (if sharing database)
- Check database contains user records
- Ensure users are active (`active = 1`)
- Test with known working credentials

## Migration Path

### Phase 1: Dual Runtime (Current in main repo)
- CI3 and CI4 run side-by-side
- CI4 accessible at `/v2` routes
- Share database and sessions

### Phase 2: Standalone CI4 (After Extraction)
- CI4 runs independently in its own repo
- No `/v2` prefix needed
- Can still share database with CI3 if desired
- Independent deployments

### Phase 3: Full Migration (Future)
- Decommission CI3
- CI4 is the only system
- Complete feature parity achieved

## Support

For issues during extraction:
- Review `README_STANDALONE.md` for setup details
- Check `CI4_VALIDATION_CHECKLIST.md` for testing
- Consult `CI4_STANDALONE_IMPLEMENTATION.md` for architecture

## Summary

**Can you extract CI4 now?** ✅ **YES!**

**Does it need /v2?** ❌ **NO!**

The CI4 application is fully standalone and ready for extraction. It runs without the `/v2` prefix and operates completely independently. You can copy the `ci4` folder to its own repository and it will work as a complete, self-contained application.
