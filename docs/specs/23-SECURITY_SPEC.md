# 🔐 Security Framework Specification

**Version**: 1.0.0
**Status**: Implemented (Documentation)
**Last Updated**: 2025-11-27

---

## Part 1: Feature Definition (The "What" & "Why")
*Target Audience: Product Owners, Stakeholders, Developers*

### 1.1 Overview
The Security Framework is the "Guardian" of ShuleLabs. It provides comprehensive security controls including authentication (session and JWT), role-based access control (RBAC), permission management, CSRF/XSS protection, SQL injection prevention, password policies, two-factor authentication, rate limiting, IP whitelisting, and security headers. This framework ensures the platform is protected against common attack vectors.

### 1.2 Security Components

#### Authentication
- Session-based authentication for web users
- JWT authentication for mobile and API access
- Two-factor authentication (2FA) support
- Single sign-on (SSO) integration capability

#### Authorization
- Role-based access control (RBAC)
- Granular permission management
- Resource-level access control
- Context-aware authorization (tenant + role)

#### Protection
- CSRF token validation
- XSS sanitization
- SQL injection prevention via parameterized queries
- Security headers (CSP, HSTS, X-Frame-Options)

#### Policies
- Password complexity requirements
- Account lockout on failed attempts
- Session management and timeout
- IP-based access control

### 1.3 User Stories

- **As a User**, I want to log in with email and password, so that I can access my account.
- **As a Security Admin**, I want to enforce password policies, so that accounts are secure.
- **As a User**, I want to enable 2FA, so that my account has extra protection.
- **As an Admin**, I want to manage roles and permissions, so that users have appropriate access.
- **As a Security Officer**, I want to see failed login attempts, so that I can detect attacks.
- **As a Super Admin**, I want to whitelist IPs for admin access, so that only authorized locations can administer.

### 1.4 Acceptance Criteria

- [ ] Session and JWT authentication working correctly.
- [ ] RBAC with hierarchical roles implemented.
- [ ] Permissions assignable to roles and checkable in code.
- [ ] CSRF tokens validated on all state-changing requests.
- [ ] XSS sanitization applied to all user input.
- [ ] Security headers set on all responses.
- [ ] Password policies enforced on creation and reset.
- [ ] Account lockout after N failed attempts.
- [ ] 2FA with TOTP available for all users.
- [ ] Rate limiting prevents brute force attacks.
- [ ] IP whitelisting available for sensitive endpoints.

---

## Part 2: Technical Specification (The "How")
*Target Audience: Developers, Architects*

### 2.1 Database Schema

#### `roles`
User roles.
```sql
CREATE TABLE roles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    school_id INT,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL,
    description TEXT,
    is_system BOOLEAN DEFAULT FALSE,
    parent_role_id INT,
    level INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_role_id) REFERENCES roles(id) ON DELETE SET NULL,
    UNIQUE KEY uk_school_slug (school_id, slug),
    INDEX idx_level (level)
);
```

#### `permissions`
System permissions.
```sql
CREATE TABLE permissions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL,
    module VARCHAR(50) NOT NULL,
    description TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_slug (slug),
    INDEX idx_module (module)
);
```

#### `role_permissions`
Role-permission mapping.
```sql
CREATE TABLE role_permissions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    role_id INT NOT NULL,
    permission_id INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE,
    UNIQUE KEY uk_role_permission (role_id, permission_id)
);
```

#### `user_roles`
User-role assignments.
```sql
CREATE TABLE user_roles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    role_id INT NOT NULL,
    school_id INT NOT NULL,
    assigned_by INT,
    assigned_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    UNIQUE KEY uk_user_role_school (user_id, role_id, school_id)
);
```

#### `two_factor_auth`
2FA configuration per user.
```sql
CREATE TABLE two_factor_auth (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    method ENUM('totp', 'sms', 'email') DEFAULT 'totp',
    secret_encrypted VARCHAR(255),
    backup_codes JSON,
    is_enabled BOOLEAN DEFAULT FALSE,
    verified_at DATETIME,
    last_used_at DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY uk_user (user_id)
);
```

#### `login_attempts`
Failed login tracking.
```sql
CREATE TABLE login_attempts (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    identifier VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent VARCHAR(500),
    attempt_type ENUM('login', '2fa', 'password_reset') DEFAULT 'login',
    was_successful BOOLEAN DEFAULT FALSE,
    failure_reason VARCHAR(100),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_identifier (identifier, created_at),
    INDEX idx_ip (ip_address, created_at)
);
```

#### `password_policies`
School-specific password requirements.
```sql
CREATE TABLE password_policies (
    id INT PRIMARY KEY AUTO_INCREMENT,
    school_id INT NOT NULL,
    min_length INT DEFAULT 8,
    require_uppercase BOOLEAN DEFAULT TRUE,
    require_lowercase BOOLEAN DEFAULT TRUE,
    require_number BOOLEAN DEFAULT TRUE,
    require_special BOOLEAN DEFAULT TRUE,
    prevent_reuse INT DEFAULT 5,
    max_age_days INT DEFAULT 90,
    lockout_attempts INT DEFAULT 5,
    lockout_duration_minutes INT DEFAULT 30,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    UNIQUE KEY uk_school (school_id)
);
```

#### `ip_whitelist`
IP-based access control.
```sql
CREATE TABLE ip_whitelist (
    id INT PRIMARY KEY AUTO_INCREMENT,
    school_id INT,
    ip_address VARCHAR(45) NOT NULL,
    ip_range VARCHAR(50),
    description VARCHAR(255),
    applies_to ENUM('all', 'admin', 'api') DEFAULT 'admin',
    is_active BOOLEAN DEFAULT TRUE,
    created_by INT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    INDEX idx_ip (ip_address)
);
```

### 2.2 Default Roles

| Role | Level | Description | Inherits From |
|:-----|:------|:------------|:--------------|
| `superadmin` | 100 | Platform administrator | - |
| `admin` | 90 | School administrator | - |
| `principal` | 80 | School principal | admin |
| `teacher` | 50 | Teaching staff | - |
| `accountant` | 50 | Finance staff | - |
| `librarian` | 40 | Library staff | - |
| `parent` | 20 | Parent/Guardian | - |
| `student` | 10 | Student | - |

### 2.3 Permission Structure

```php
// Permissions are structured as: module.action
// Examples:
'students.view'
'students.create'
'students.edit'
'students.delete'
'finance.invoices.view'
'finance.invoices.create'
'finance.payments.record'
'reports.view'
'reports.export'
'settings.manage'
```

### 2.4 Security Headers

```php
// Applied via SecurityHeadersFilter
$headers = [
    'X-Frame-Options' => 'SAMEORIGIN',
    'X-Content-Type-Options' => 'nosniff',
    'X-XSS-Protection' => '1; mode=block',
    'Referrer-Policy' => 'strict-origin-when-cross-origin',
    'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains',
    'Content-Security-Policy' => "default-src 'self'; script-src 'self' 'unsafe-inline' cdn.example.com; style-src 'self' 'unsafe-inline';",
    'Permissions-Policy' => 'geolocation=(), microphone=(), camera=()',
];
```

### 2.5 Module Structure

```
app/Modules/Security/
├── Config/
│   ├── Security.php
│   └── Services.php
├── Controllers/
│   ├── Api/
│   │   ├── AuthController.php
│   │   ├── RoleController.php
│   │   ├── PermissionController.php
│   │   └── TwoFactorController.php
│   └── Web/
│       ├── LoginController.php
│       └── SecuritySettingsController.php
├── Models/
│   ├── RoleModel.php
│   ├── PermissionModel.php
│   ├── UserRoleModel.php
│   ├── TwoFactorAuthModel.php
│   ├── LoginAttemptModel.php
│   ├── PasswordPolicyModel.php
│   └── IpWhitelistModel.php
├── Services/
│   ├── AuthenticationService.php
│   ├── AuthorizationService.php
│   ├── RoleService.php
│   ├── PermissionService.php
│   ├── TwoFactorService.php
│   ├── PasswordPolicyService.php
│   ├── RateLimiterService.php
│   └── IpWhitelistService.php
├── Filters/
│   ├── AuthFilter.php
│   ├── PermissionFilter.php
│   ├── CsrfFilter.php
│   ├── XssFilter.php
│   ├── RateLimitFilter.php
│   ├── IpWhitelistFilter.php
│   └── SecurityHeadersFilter.php
├── Libraries/
│   ├── TotpGenerator.php
│   ├── PasswordValidator.php
│   └── JwtHandler.php
├── Database/
│   └── Migrations/
│       └── 2025-11-27-000001_CreateSecurityTables.php
└── Tests/
    ├── Unit/
    │   └── PasswordValidatorTest.php
    └── Feature/
        └── AuthenticationTest.php
```

### 2.6 API Endpoints

| Method | Endpoint | Description | Access |
|:-------|:---------|:------------|:-------|
| **Authentication** |
| POST | `/api/v1/auth/login` | Login | Public |
| POST | `/api/v1/auth/logout` | Logout | Auth |
| POST | `/api/v1/auth/refresh` | Refresh token | Auth |
| POST | `/api/v1/auth/password/forgot` | Request reset | Public |
| POST | `/api/v1/auth/password/reset` | Reset password | Public |
| **2FA** |
| POST | `/api/v1/auth/2fa/setup` | Setup 2FA | Auth |
| POST | `/api/v1/auth/2fa/verify` | Verify code | Auth |
| POST | `/api/v1/auth/2fa/disable` | Disable 2FA | Auth |
| **Roles** |
| GET | `/api/v1/security/roles` | List roles | Admin |
| POST | `/api/v1/security/roles` | Create role | Admin |
| PUT | `/api/v1/security/roles/{id}` | Update role | Admin |
| GET | `/api/v1/security/roles/{id}/permissions` | Get permissions | Admin |
| POST | `/api/v1/security/roles/{id}/permissions` | Assign permissions | Admin |
| **Users** |
| POST | `/api/v1/security/users/{id}/roles` | Assign role | Admin |
| DELETE | `/api/v1/security/users/{id}/roles/{roleId}` | Remove role | Admin |

---

## Part 3: Architectural Safeguards

### 3.1 Password Hashing
- Use bcrypt with cost factor 12.
- Never store plaintext passwords.
- Salt included in hash automatically.

### 3.2 Token Security
- JWTs signed with RS256.
- Short expiry (15 min) for access tokens.
- Refresh tokens rotated on use.
- Tokens stored securely on client.

### 3.3 Session Security
- HTTP-only cookies.
- Secure flag in production.
- Session regeneration on login.
- Timeout after inactivity.

### 3.4 Input Validation
- Validate all input server-side.
- Use parameterized queries.
- Sanitize output for display.
- Validate content types.

### 3.5 Rate Limiting
- 5 login attempts per minute per IP.
- 100 API requests per minute per user.
- Graduated lockout on failures.

---

## Part 4: Test Data Strategy

### 4.1 Seeding Strategy
- Default roles and permissions.
- Sample users with various roles.
- Password policy configurations.

### 4.2 Testing Scenarios
| Scenario | Expected Outcome |
|:---------|:-----------------|
| Login with valid credentials | Session created |
| Login with wrong password | Failure logged |
| Exceed login attempts | Account locked |
| Access without permission | 403 Forbidden |
| CSRF token mismatch | Request rejected |

---

## Part 5: Development Checklist

- [x] **Auth**: Session login/logout.
- [x] **Auth**: JWT for API.
- [x] **RBAC**: Roles and permissions.
- [x] **CSRF**: Token validation.
- [x] **XSS**: Input sanitization.
- [ ] **2FA**: TOTP implementation.
- [ ] **2FA**: Backup codes.
- [x] **Rate Limiting**: Basic implementation.
- [ ] **IP Whitelist**: Full implementation.
- [ ] **Password Policy**: Age enforcement.
- [x] **Headers**: Security headers filter.
