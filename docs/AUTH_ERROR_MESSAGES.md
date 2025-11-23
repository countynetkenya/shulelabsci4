# Authentication Error Messages - User Guide

**Version**: 2.0.0  
**Last Updated**: November 23, 2025

---

## Overview

The ShuleLabs authentication system provides **specific error messages** to help users identify and resolve login issues quickly. Each error message clearly indicates what went wrong.

---

## Error Message Types

### 1. 🚫 Username Not Found

**Message**: "Username not found. Please check your username and try again."

**Meaning**: The username/email you entered does not exist in the system.

**What to do**:
- Double-check your username for typos
- Verify you're using the correct email address
- Contact your school administrator if you're unsure of your username
- Check if you should use username vs email (system accepts both)

**Example**:
```
Username: wronguser@example.com
Password: Admin@123456
Result: ❌ Username not found
```

---

### 2. 🔑 Incorrect Password

**Message**: "Incorrect Password. The password you entered is incorrect. Please try again or contact support."

**Meaning**: Your username is correct, but the password is wrong.

**What to do**:
- Check if Caps Lock is on
- Verify you're using the correct password
- Remember passwords are case-sensitive
- Try resetting your password if you've forgotten it
- Contact support if you continue to have issues

**Example**:
```
Username: admin@shulelabs.local ✓ (correct)
Password: WrongPassword123
Result: ❌ Incorrect Password
```

---

### 3. ⛔ Account Deactivated

**Message**: "Account Deactivated. Your account has been deactivated. Please contact the administrator."

**Meaning**: Your account exists but has been disabled by an administrator.

**What to do**:
- Contact your school administrator
- Provide your username/email
- Ask why your account was deactivated
- Request account reactivation if appropriate

**Example**:
```
Username: inactive@shulelabs.local
Password: Correct password
Result: ❌ Account Deactivated
```

---

### 4. ⚠️ Account Configuration Error

**Message**: "Account Configuration Error. Your user account is not properly configured. Please contact the administrator."

**Meaning**: Your account exists but doesn't have a role assigned, or there's a configuration issue.

**What to do**:
- Contact your school administrator immediately
- Report the error message you received
- Administrator needs to assign a role to your account
- Wait for administrator to fix the configuration

**Example**:
```
Username: user@shulelabs.local
Password: Correct password
Result: ❌ No role assigned - configuration error
```

---

### 5. 📝 Validation Errors

**Messages**:
- "Username is required"
- "Password is required"
- "Username must not exceed 40 characters"
- "Password must not exceed 40 characters"

**Meaning**: Required form fields are missing or exceed maximum length.

**What to do**:
- Fill in all required fields (username and password)
- Ensure username is not longer than 40 characters
- Ensure password is not longer than 40 characters
- Check for extra spaces at the beginning or end

**Example**:
```
Username: [empty]
Password: Admin@123456
Result: ❌ Username is required
```

---

## Visual Guide

### Error Message Display

All error messages appear in a **red alert box** at the top of the login form:

```
┌─────────────────────────────────────────────┐
│ ×  🚫 Username not found.                   │
│    Please check your username and try again.│
└─────────────────────────────────────────────┘
```

The `×` button allows you to dismiss the message.

---

## Testing Credentials

Use these credentials to test successful login:

### Super Admin
```
Username: admin@shulelabs.local
Password: Admin@123456
Expected: ✅ Login successful → Redirect to dashboard
```

### Teacher
```
Username: teacher1@shulelabs.local
Password: Teacher@123
Expected: ✅ Login successful → Redirect to teacher portal
```

### Student
```
Username: student1@shulelabs.local
Password: Student@123
Expected: ✅ Login successful → Redirect to student portal
```

---

## For Developers: Testing Error Messages

### Test Wrong Username
```bash
curl -X POST http://localhost:8080/auth/signin \
  -d "username=wronguser@test.com" \
  -d "password=Admin@123456"
  
Expected: "Username not found"
```

### Test Wrong Password
```bash
curl -X POST http://localhost:8080/auth/signin \
  -d "username=admin@shulelabs.local" \
  -d "password=WrongPassword"
  
Expected: "Incorrect Password"
```

### Test Empty Fields
```bash
curl -X POST http://localhost:8080/auth/signin \
  -d "username=" \
  -d "password="
  
Expected: "Username is required"
```

---

## Error Message Logs

All authentication errors are logged for security monitoring:

### Log Levels

| Error Type | Log Level | Example |
|------------|-----------|---------|
| Username not found | INFO | `Auth::processSignin() - User not found in any table: wronguser` |
| Incorrect password | INFO | `Auth::processSignin() - Password mismatch for user: admin` |
| Inactive account | WARNING | `Auth::processSignin() - Inactive account signin attempt: user123` |
| No role assigned | ERROR | `Auth::processSignin() - No role found for user: newuser` |

### View Logs
```bash
# View auth logs
tail -f writable/logs/log-*.log | grep "Auth::"

# View today's auth errors
grep "Auth::processSignin()" writable/logs/log-$(date +%Y-%m-%d).log
```

---

## Security Features

### Rate Limiting (Future Enhancement)
- Track failed login attempts
- Lock account after 5 failed attempts
- Require CAPTCHA after 3 failed attempts
- Send email notification on suspicious activity

### Password Security
- Passwords are hashed using SHA1 (CI3 compatibility)
- Plain text passwords never stored
- Password comparison happens on server side
- Session cookies use secure flags

---

## Troubleshooting Common Issues

### "I can't remember my username"
1. Check your email for welcome messages
2. Contact your school administrator
3. Provide your full name and ID number
4. Administrator can look up your username

### "I can't remember my password"
1. Click "Forgot Password" link (if available)
2. Contact school administrator for password reset
3. Provide your username or email
4. Administrator can reset your password

### "Nothing happens when I click Sign In"
1. Check browser console for JavaScript errors (F12)
2. Verify JavaScript is enabled
3. Try a different browser
4. Clear browser cache and cookies
5. Check internet connection

### "I keep getting 'Incorrect Password' but I'm sure it's right"
1. Check if Caps Lock is on
2. Copy/paste password from secure location
3. Verify no extra spaces before/after password
4. Try typing password in notepad first to verify
5. Request password reset from administrator

---

## For Administrators

### Fixing "Account Configuration Error"

This error occurs when a user account exists but has no role assigned.

**Steps to fix**:
1. Login as administrator
2. Go to User Management → Users
3. Find the user by email/username
4. Click "Edit" or "Assign Role"
5. Select appropriate role (Teacher, Student, Admin, etc.)
6. Save changes
7. User can now login successfully

### Activating Deactivated Account

**Steps to activate**:
1. Login as administrator
2. Go to User Management → Users
3. Filter by "Inactive Users"
4. Find the user
5. Click "Activate Account"
6. Confirm activation
7. User receives email notification

---

## Error Message Customization

### Changing Error Messages

Edit `/app/Controllers/Auth.php`:

```php
// Username not found
$this->data['form_validation'] = 'Your custom message here';

// Incorrect password
$this->data['form_validation'] = 'Your custom message here';

// Account deactivated
$this->data['form_validation'] = 'Your custom message here';
```

### Adding Support Contact Info

```php
$this->data['form_validation'] = 'Error message. Contact support: support@yourschool.com or +254-XXX-XXXX';
```

---

## Related Documentation

- [Testing Guide](../TESTING.md) - Full testing documentation
- [User Management](USER_MANAGEMENT.md) - Managing user accounts
- [Security Guide](../SECURITY.md) - Security best practices
- [API Reference](../API-REFERENCE.md) - Authentication API

---

**Last Updated**: November 23, 2025  
**Maintained By**: ShuleLabs Development Team  
**Contact**: support@shulelabs.com
