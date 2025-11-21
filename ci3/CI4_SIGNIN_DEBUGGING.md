# CI4 Signin Debugging Guide

## Overview

The CI4 authentication system has been enhanced with comprehensive debugging capabilities to help diagnose signin issues. This guide explains the debugging features and how to use them.

## Enhanced Error Messages

### User-Facing Error Messages

The signin form now provides specific error messages to help users and administrators diagnose issues:

1. **Unknown username**: Displayed when the username doesn't exist in the database
   - Message: `"Unknown username"`
   - Indicates the user needs to verify the username spelling or that the account hasn't been created

2. **Incorrect password**: Displayed when the username exists but the password is wrong
   - Message: `"Incorrect password"`
   - Indicates the user needs to reset their password or verify they're typing it correctly

3. **Inactive account**: Displayed when the account exists but is deactivated
   - Message: `"Your account has been deactivated. Please contact administrator."`
   - Indicates the account needs to be reactivated by an administrator

4. **Missing role**: Displayed when the user has no assigned role
   - Message: `"User account is not properly configured. Please contact administrator."`
   - Indicates a database integrity issue that needs administrative attention

5. **Validation errors**: Displayed for missing or invalid form fields
   - Messages: `"Username is required"`, `"Password is required"`, etc.
   - Indicates the form wasn't filled out correctly

### Error Message Security

- Error messages use `esc()` function to prevent XSS attacks
- Passwords are never logged or displayed in error messages
- Username is logged for debugging but not displayed in generic error messages

## Comprehensive Logging

### Log Levels

The CI4 Logger is configured to write all log levels in non-production environments:
- **Debug (8)**: Detailed flow information
- **Info (7)**: Important events like signin attempts
- **Warning (5)**: Suspicious activity like inactive account attempts
- **Error (4)**: Critical issues like missing roles

### Log Locations

Logs are written to:
```
ci4/writable/logs/log-YYYY-MM-DD.log
```

Example log filename: `log-2025-11-19.log`

### Log Events

#### Page Access
```
DEBUG - Auth::signin() - Signin page accessed
DEBUG - Auth::signin() - POST request detected, processing signin
DEBUG - Auth::signin() - Displaying signin form
```

#### Validation
```
INFO - Auth::processSignin() - Validation failed: ["username","password"]
```

#### Authentication Attempts
```
INFO - Auth::processSignin() - Signin attempt for username: john.doe@example.com
DEBUG - Auth::processSignin() - User found: ID=123, active=1
INFO - Auth::processSignin() - User not found: invalid.user@example.com
INFO - Auth::processSignin() - Password mismatch for user: john.doe@example.com
WARNING - Auth::processSignin() - Inactive account signin attempt: deactivated.user@example.com
INFO - Auth::processSignin() - Authentication successful for user: john.doe@example.com
```

#### Role & Session
```
DEBUG - Auth::processSignin() - User role: Admin (usertypeID=1)
ERROR - Auth::processSignin() - No role found for user: misconfigured@example.com
DEBUG - Auth::processSignin() - Creating login log for user: john.doe@example.com
DEBUG - Auth::processSignin() - Setting user session for user: john.doe@example.com
DEBUG - Auth::setUserSession() - Setting session data: {"loginuserID":123,"usertypeID":1,"usertype":"Admin","schools":"1,2"}
DEBUG - Auth::setUserSession() - Session data set successfully
```

#### Redirects
```
DEBUG - Auth::processSignin() - Redirecting user after signin: john.doe@example.com
DEBUG - Auth::redirectAfterSignin() - Determining redirect for user: usertypeID=1, loginuserID=123, schools=1,2
INFO - Auth::redirectAfterSignin() - Super admin detected, redirecting to /admin
INFO - Auth::redirectAfterSignin() - Multiple schools detected, redirecting to /school/select
INFO - Auth::redirectAfterSignin() - Single school assigned: 1, setting session and redirecting to /dashboard
DEBUG - Auth::setSchoolSession() - Setting school session for schoolID: 1
DEBUG - Auth::setSchoolSession() - School session set successfully: schoolID=1
```

## Debugging Common Issues

### Issue: Form Reloads with No Error Message

**Symptoms:**
- User submits signin form
- Page reloads to signin page
- No error message visible

**Debugging Steps:**

1. Check the logs in `ci4/writable/logs/log-YYYY-MM-DD.log`
2. Look for validation errors or authentication failures
3. Common causes:
   - CSRF token mismatch (check if `csrf_field()` is in the form)
   - Session not persisting (check session configuration)
   - Redirect loop (check if user is already logged in)

**Log Patterns to Look For:**
```
INFO - Auth::processSignin() - Validation failed: [...]
INFO - Auth::processSignin() - User not found: [username]
INFO - Auth::processSignin() - Password mismatch for user: [username]
```

### Issue: User Not Redirected After Signin

**Symptoms:**
- User signs in successfully
- Stays on signin page or redirects incorrectly

**Debugging Steps:**

1. Check redirect logs:
   ```
   DEBUG - Auth::redirectAfterSignin() - Determining redirect for user: ...
   INFO - Auth::redirectAfterSignin() - [redirect decision]
   ```

2. Verify session data is set:
   ```
   DEBUG - Auth::setUserSession() - Session data set successfully
   ```

3. Check if super admin detection is working:
   ```
   INFO - Auth::redirectAfterSignin() - Super admin detected, redirecting to /admin
   ```

4. Verify school session is set (for non-admin users):
   ```
   DEBUG - Auth::setSchoolSession() - School session set successfully: schoolID=1
   ```

### Issue: Password Always Fails

**Symptoms:**
- Correct password appears to fail
- User exists in database

**Debugging Steps:**

1. Check password hash in database matches expected hash
2. Verify encryption key matches CI3 installation:
   ```sql
   SELECT password_hash FROM ci4_users WHERE username = 'testuser';
   ```

3. Test hash generation:
   ```php
   <?php
   $password = 'testpassword';
   $encryptionKey = '8bc8ae426d4354c8df0488e2d7f1a9de'; // From CI3
   $hash = hash('sha512', $password . $encryptionKey);
   echo "Hash: $hash\n";
   ```

4. Check HashCompat logs (if any are added in the future)

### Issue: User Has No Role

**Symptoms:**
- User exists but signin fails with "not properly configured" message

**Debugging Steps:**

1. Check log for missing role error:
   ```
   ERROR - Auth::processSignin() - No role found for user: [username]
   ```

2. Verify user has role assigned in database:
   ```sql
   SELECT u.username, r.role_name 
   FROM ci4_users u
   LEFT JOIN ci4_user_roles ur ON u.id = ur.user_id
   LEFT JOIN ci4_roles r ON ur.role_id = r.id
   WHERE u.username = 'testuser';
   ```

3. If no role assigned, assign one:
   ```sql
   INSERT INTO ci4_user_roles (user_id, role_id, created_at)
   SELECT u.id, r.id, NOW()
   FROM ci4_users u, ci4_roles r
   WHERE u.username = 'testuser' AND r.role_slug = 'admin';
   ```

## Testing Signin Flow

### Manual Testing Checklist

Use this checklist to verify the signin flow is working correctly:

- [ ] Visit `/auth/signin` - page loads without errors
- [ ] Submit empty form - validation errors appear
- [ ] Submit invalid username - "Unknown username" appears
- [ ] Submit valid username + wrong password - "Incorrect password" appears
- [ ] Submit inactive user credentials - "account deactivated" appears
- [ ] Submit valid credentials - redirects to correct page
- [ ] Check logs - all events logged correctly
- [ ] Verify session data - session contains all expected fields

### Test User Scenarios

#### Super Admin Test
```
Username: afyamart@gmail.com
Expected: Redirect to /admin after signin
Session: schoolID=0, usertypeID=0 or 1
```

#### Multi-School Admin Test
```
Username: [admin with multiple schools]
Expected: Redirect to /school/select
Session: schools=[comma-separated IDs]
```

#### Single-School User Test
```
Username: drewgash@yahoo.com
Expected: Redirect to /dashboard
Session: schoolID=[single ID], defaultschoolyearID, lang
```

## Configuration

### Logger Configuration

The logger is configured in `ci4/app/Config/Logger.php`:

```php
public $threshold = (ENVIRONMENT === 'production') ? 4 : 9;
```

- **Development/Testing**: Threshold 9 (all logs)
- **Production**: Threshold 4 (error and above)

To enable debug logs in production temporarily:
```php
public $threshold = 9; // Log everything
```

**Important**: Revert to threshold 4 in production after debugging to avoid log file bloat.

### Environment Variables

Key environment variables for debugging:

```env
# Set to development to enable all logs
CI_ENVIRONMENT=development

# Optional: Set encryption key (falls back to CI3 key if not set)
ENCRYPTION_KEY=8bc8ae426d4354c8df0488e2d7f1a9de

# Database connection (needed for authentication)
DB_HOST=localhost
DB_DATABASE=shulelabs
DB_USERNAME=shulelabs
DB_PASSWORD=shulelabs
```

## Security Considerations

### What's Safe to Log

✅ **Safe to log:**
- Usernames
- User IDs
- IP addresses
- Browser information
- Timestamps
- Validation error field names
- Redirect decisions
- Session field names (not values)

❌ **Never log:**
- Passwords (plain or hashed)
- CSRF tokens
- Session IDs
- API keys
- Encryption keys

### Log Rotation

Logs can grow large. Implement log rotation:

```bash
# Example logrotate configuration
/path/to/ci4/writable/logs/*.log {
    daily
    rotate 7
    compress
    delaycompress
    notifempty
    missingok
}
```

## Troubleshooting Tools

### View Recent Logs

```bash
# Last 50 lines of today's log
tail -n 50 ci4/writable/logs/log-$(date +%Y-%m-%d).log

# Follow logs in real-time
tail -f ci4/writable/logs/log-$(date +%Y-%m-%d).log

# Search for specific user
grep "john.doe@example.com" ci4/writable/logs/log-*.log

# Search for errors
grep "ERROR\|WARNING" ci4/writable/logs/log-$(date +%Y-%m-%d).log
```

### Database Queries

```sql
-- Check user exists
SELECT * FROM ci4_users WHERE username = 'testuser';

-- Check user roles
SELECT u.username, u.is_active, r.role_name, r.ci3_usertype_id
FROM ci4_users u
LEFT JOIN ci4_user_roles ur ON u.id = ur.user_id
LEFT JOIN ci4_roles r ON ur.role_id = r.id
WHERE u.username = 'testuser';

-- Check recent login logs
SELECT * FROM loginlog 
WHERE userID = 123 
ORDER BY login DESC 
LIMIT 10;
```

## Summary

The enhanced CI4 signin system provides:

1. **Detailed error messages** for better user experience
2. **Comprehensive logging** for debugging
3. **Security-conscious** logging (no password exposure)
4. **Environment-aware** logging levels
5. **CI3 compatibility** maintained

For issues not covered in this guide, check:
- `CI4_USER_SCHEMA_IMPLEMENTATION.md` - User schema details
- `CI4_VALIDATION_CHECKLIST.md` - Testing checklist
- `CI4_STANDALONE_IMPLEMENTATION.md` - Overall CI4 implementation

---

**Last Updated**: November 2025
**Version**: 1.0
