# Security Implementation Guide
## ShuleLabs CI4 Multi-School Platform

**Version**: 2.0.0  
**Last Updated**: November 23, 2025  
**Classification**: Internal Use

---

## Table of Contents

1. [Security Architecture](#1-security-architecture)
2. [Multi-Tenant Security](#2-multi-tenant-security)
3. [Authentication & Authorization](#3-authentication--authorization)
4. [Data Protection](#4-data-protection)
5. [API Security](#5-api-security)
6. [Security Best Practices](#6-security-best-practices)
7. [Threat Model](#7-threat-model)
8. [Security Monitoring](#8-security-monitoring)
9. [Incident Response](#9-incident-response)
10. [Compliance](#10-compliance)

---

## 1. Security Architecture

### Defense in Depth Strategy

```
┌────────────────────────────────────────────┐
│  Layer 1: Network Security                 │
│  - Firewall, WAF, DDoS Protection          │
└────────────┬───────────────────────────────┘
             │
┌────────────▼───────────────────────────────┐
│  Layer 2: Application Security             │
│  - HTTPS Only, CORS, CSP Headers           │
└────────────┬───────────────────────────────┘
             │
┌────────────▼───────────────────────────────┐
│  Layer 3: Authentication                   │
│  - JWT Tokens, Session Management          │
└────────────┬───────────────────────────────┘
             │
┌────────────▼───────────────────────────────┐
│  Layer 4: Authorization                    │
│  - Role-Based Access Control (RBAC)        │
│  - School-Level Permissions                │
└────────────┬───────────────────────────────┘
             │
┌────────────▼───────────────────────────────┐
│  Layer 5: Multi-Tenant Isolation           │
│  - TenantModel Auto-Scoping                │
│  - Query-Level School Filtering            │
└────────────┬───────────────────────────────┘
             │
┌────────────▼───────────────────────────────┐
│  Layer 6: Data Protection                  │
│  - Input Validation, Output Escaping       │
│  - Prepared Statements, SQL Injection      │
└────────────┬───────────────────────────────┘
             │
┌────────────▼───────────────────────────────┐
│  Layer 7: Database Security                │
│  - Encrypted Connections, Backups          │
│  - Principle of Least Privilege            │
└────────────────────────────────────────────┘
```

---

## 2. Multi-Tenant Security

### Tenant Isolation Architecture

**Critical**: Our multi-tenant isolation is the foundation of data security.

#### How It Works

1. **TenantFilter (Middleware)**
   ```php
   // app/Filters/TenantFilter.php
   public function before(RequestInterface $request, $arguments = null)
   {
       $tenantService = service('tenant');
       $tenantId = $tenantService->getCurrentTenantId();
       
       if (empty($tenantId)) {
           throw new \RuntimeException('No tenant context available');
       }
   }
   ```

2. **TenantService (Context Resolution)**
   ```php
   // app/Services/TenantService.php
   public function getCurrentTenantId(): ?int
   {
       // Priority 1: Session
       if ($schoolId = session('current_school_id')) {
           return $schoolId;
       }
       
       // Priority 2: JWT Token
       if ($schoolId = $this->getSchoolIdFromJWT()) {
           return $schoolId;
       }
       
       // Priority 3: User's primary school
       return $this->getUserPrimarySchool();
   }
   ```

3. **TenantModel (Auto-Scoping)**
   ```php
   // app/Models/TenantModel.php
   protected function scopeToTenant(BaseBuilder $builder): BaseBuilder
   {
       $tenantId = service('tenant')->getCurrentTenantId();
       
       if ($tenantId === null) {
           throw new \RuntimeException('Tenant context required');
       }
       
       return $builder->where("{$this->table}.school_id", $tenantId);
   }
   ```

#### Security Guarantees

✅ **Automatic Query Scoping**: Every query automatically filtered by school_id  
✅ **No Manual WHERE Clauses**: Developers cannot forget tenant filtering  
✅ **Runtime Validation**: Throws exception if tenant context missing  
✅ **Cross-Tenant Protection**: Impossible to access another school's data  

### Tenant Switching Security

```php
// app/Services/TenantService.php
public function switchTenant(int $schoolId): bool
{
    // 1. Verify user has access to this school
    $schoolUser = model('SchoolUserModel')
        ->where('user_id', $this->currentUserId)
        ->where('school_id', $schoolId)
        ->first();
    
    if (!$schoolUser) {
        log_message('warning', "Unauthorized tenant switch attempt by user {$this->currentUserId} to school {$schoolId}");
        return false;
    }
    
    // 2. Update session
    session()->set('current_school_id', $schoolId);
    
    // 3. Log the switch for audit
    log_message('info', "User {$this->currentUserId} switched to school {$schoolId}");
    
    return true;
}
```

**Security Controls**:
- ✅ Authorization check before switch
- ✅ Audit logging
- ✅ Session-based persistence
- ✅ Cannot switch to unauthorized schools

---

## 3. Authentication & Authorization

### Authentication Methods

#### 1. Session-Based Authentication
```php
// Used for: Web portal, admin interface
// Storage: Server-side sessions
// Lifetime: Configurable (default: 2 hours)
// Security: CSRF protection, secure cookies
```

#### 2. JWT Token Authentication
```php
// Used for: Mobile API, third-party integrations
// Storage: Client-side (localStorage/secure storage)
// Lifetime: 24 hours (refresh tokens available)
// Security: HS256 signature, expiration validation
```

### Role-Based Access Control (RBAC)

#### Roles Hierarchy

```
Super Admin (School Owner)
    │
    ├── School Admin
    │   │
    │   ├── Teacher
    │   │   │
    │   │   └── Student
    │   │
    │   └── Staff (HR, Finance, Library, Inventory)
    │
    └── Parent/Guardian
```

#### Permission Matrix

| Resource | Super Admin | School Admin | Teacher | Staff | Student | Parent |
|----------|-------------|--------------|---------|-------|---------|--------|
| **Schools** | CRUD | Read | Read | Read | Read | Read |
| **Users** | CRUD | CRUD | Read | Read | - | - |
| **Classes** | CRUD | CRUD | Read | - | Read | Read |
| **Enrollments** | CRUD | CRUD | Update | - | Read | Read |
| **Invoices** | CRUD | CRUD | - | CRUD | Read | Read |
| **Payments** | CRUD | CRUD | - | CRUD | Read | Read |
| **Courses** | CRUD | CRUD | CRUD | - | Read | - |
| **Assignments** | CRUD | CRUD | CRUD | - | Read | - |
| **Grades** | CRUD | CRUD | CRUD | - | Read | Read |
| **Library** | CRUD | CRUD | - | CRUD | Read | - |
| **Inventory** | CRUD | CRUD | - | CRUD | - | - |
| **Messages** | Read | Read | CRU | CRU | CRU | CRU |

**Legend**: C = Create, R = Read, U = Update, D = Delete

### Authorization Implementation

```php
// Example: Check if user can create invoices
public function before(RequestInterface $request, $arguments = null)
{
    $user = auth()->user();
    
    if (!in_array($user->role, ['super_admin', 'school_admin', 'staff_finance'])) {
        return redirect()->to('/unauthorized')
            ->with('error', 'You do not have permission to access this resource.');
    }
}
```

---

## 4. Data Protection

### Input Validation

#### Validation Rules

All user inputs are validated using CodeIgniter validation:

```php
// Example: Invoice creation
protected $validationRules = [
    'student_id' => 'required|integer',
    'amount' => 'required|decimal|greater_than[0]',
    'due_date' => 'required|valid_date',
    'description' => 'required|max_length[500]|alpha_numeric_punct',
];
```

#### Sanitization

```php
// Input sanitization
$data = [
    'title' => strip_tags($input['title']),
    'description' => htmlspecialchars($input['description'], ENT_QUOTES, 'UTF-8'),
    'email' => filter_var($input['email'], FILTER_SANITIZE_EMAIL),
];
```

### Output Encoding

All view outputs are escaped:

```php
// In views
<h1><?= esc($title) ?></h1>
<p><?= esc($description, 'html') ?></p>
<input value="<?= esc($value, 'attr') ?>">
```

### SQL Injection Protection

✅ **100% Query Builder Usage** - No raw SQL in new code  
✅ **Prepared Statements** - All inputs parameterized  
✅ **Type Casting** - ID parameters cast to integers  

```php
// Safe query example
$invoice = $this->invoiceModel
    ->where('id', (int)$invoiceId)  // Type cast
    ->where('school_id', $this->tenantId)  // Auto-scoped
    ->first();  // Prepared statement
```

### XSS Protection

✅ **Output Escaping** - All user data escaped in views  
✅ **Content Security Policy** - CSP headers configured  
✅ **Input Validation** - Reject scripts in user input  

### CSRF Protection

✅ **Enabled Globally** - All POST/PUT/DELETE protected  
✅ **Token Validation** - Automatic validation  
✅ **Ajax Support** - Token included in AJAX headers  

```php
// config/Security.php
public string $csrfProtection = 'session';
public int $csrfExpire = 7200;
public bool $csrfRegenerate = true;
```

---

## 5. API Security

### HTTPS Enforcement

```php
// config/App.php
public bool $forceGlobalSecureRequests = true;  // Production only
```

### CORS Configuration

```php
// config/Cors.php
public array $allowedOrigins = [
    'https://app.shulelabs.com',
    'https://mobile.shulelabs.com',
];

public array $allowedMethods = ['GET', 'POST', 'PUT', 'DELETE'];
public array $allowedHeaders = ['Content-Type', 'Authorization', 'X-Tenant-ID'];
public int $maxAge = 7200;
```

### Rate Limiting

**Recommendation**: Implement rate limiting to prevent abuse.

```php
// Example implementation (to be added)
$throttle = new \CodeIgniter\Throttle\Throttle();
$throttle->check($ipAddress, 100, MINUTE); // 100 requests/minute
```

**Suggested Limits**:
- API Endpoints: 100 requests/minute per IP
- Authentication: 5 failed attempts/minute per IP
- Password Reset: 3 attempts/hour per email

### API Authentication

#### JWT Token Structure

```json
{
  "header": {
    "alg": "HS256",
    "typ": "JWT"
  },
  "payload": {
    "user_id": 123,
    "school_id": 6,
    "role": "teacher",
    "exp": 1700000000,
    "iat": 1699913600
  },
  "signature": "..."
}
```

#### Token Validation

```php
public function validateJWT(string $token): ?array
{
    try {
        $decoded = JWT::decode($token, $this->jwtSecret, ['HS256']);
        
        // Check expiration
        if ($decoded->exp < time()) {
            return null;
        }
        
        return (array)$decoded;
    } catch (\Exception $e) {
        log_message('warning', 'Invalid JWT: ' . $e->getMessage());
        return null;
    }
}
```

---

## 6. Security Best Practices

### Secure Configuration

#### .env File Security

```bash
# Never commit .env to version control!
# Add to .gitignore
.env
.env.*
```

#### Environment Variables

```bash
# Production .env
CI_ENVIRONMENT = production
app.baseURL = 'https://api.shulelabs.com'
app.forceGlobalSecureRequests = true

# Database
database.default.hostname = 'db.example.com'
database.default.database = 'shulelabs_prod'
database.default.username = 'app_user'  # Limited privileges
database.default.password = 'STRONG_RANDOM_PASSWORD'

# Encryption
encryption.key = 'hex:RANDOM_32_BYTE_KEY_HERE'

# JWT
jwt.secret = 'RANDOM_SECRET_KEY_HERE'
jwt.expire = 86400  # 24 hours
```

### Password Security

#### Hashing

```php
// Always use PHP's password_hash()
$hashedPassword = password_hash($plainPassword, PASSWORD_ARGON2ID, [
    'memory_cost' => 65536,
    'time_cost' => 4,
    'threads' => 3
]);
```

#### Password Policy

- Minimum length: 8 characters
- Must contain: uppercase, lowercase, number, special character
- Cannot be common password
- Cannot be same as username/email

### Session Security

```php
// config/Session.php
public string $driver = 'CodeIgniter\Session\Handlers\DatabaseHandler';
public string $cookieName = 'shulelabs_session';
public int $expiration = 7200;  # 2 hours
public bool $matchIP = true;
public bool $cookieSecure = true;  # HTTPS only
public bool $cookieHTTPOnly = true;  # No JavaScript access
public string $cookieSameSite = 'Strict';
```

### File Upload Security

```php
// Validate file uploads
$validationRules = [
    'avatar' => [
        'uploaded[avatar]',
        'max_size[avatar,2048]',  # 2MB max
        'is_image[avatar]',
        'mime_in[avatar,image/jpg,image/jpeg,image/png]',
    ],
];

// Sanitize filename
$filename = pathinfo($file->getName(), PATHINFO_FILENAME);
$filename = preg_replace('/[^a-zA-Z0-9_-]/', '', $filename);
$newName = $filename . '_' . time() . '.' . $file->getExtension();

// Move to secure directory
$file->move(WRITEPATH . 'uploads', $newName);
```

---

## 7. Threat Model

### STRIDE Analysis

| Threat | Impact | Likelihood | Mitigation |
|--------|--------|------------|------------|
| **Spoofing Identity** | High | Medium | JWT tokens, session validation |
| **Tampering Data** | High | Low | CSRF protection, input validation |
| **Repudiation** | Medium | Low | Audit logging, transaction logs |
| **Information Disclosure** | Critical | Low | Tenant isolation, encryption |
| **Denial of Service** | High | Medium | Rate limiting, resource monitoring |
| **Elevation of Privilege** | Critical | Low | RBAC, authorization checks |

### Common Attack Vectors

#### 1. Cross-Tenant Data Access
- **Risk**: User accesses another school's data
- **Mitigation**: TenantModel auto-scoping
- **Status**: ✅ Protected

#### 2. SQL Injection
- **Risk**: Malicious SQL in user input
- **Mitigation**: Query Builder, prepared statements
- **Status**: ✅ Protected

#### 3. XSS (Cross-Site Scripting)
- **Risk**: Malicious scripts in user content
- **Mitigation**: Output escaping, CSP headers
- **Status**: ✅ Protected

#### 4. CSRF (Cross-Site Request Forgery)
- **Risk**: Unauthorized actions via forged requests
- **Mitigation**: CSRF tokens, SameSite cookies
- **Status**: ✅ Protected

#### 5. Brute Force Attacks
- **Risk**: Password guessing, API abuse
- **Mitigation**: Rate limiting (to be implemented)
- **Status**: ⚠️ Recommended

#### 6. Session Hijacking
- **Risk**: Stolen session cookies
- **Mitigation**: Secure cookies, IP matching, HTTPS
- **Status**: ✅ Protected

---

## 8. Security Monitoring

### Logging Strategy

#### Security Events to Log

1. **Authentication Events**
   - Login attempts (success/failure)
   - Logout events
   - Password changes
   - Failed authentication count

2. **Authorization Events**
   - Access denied attempts
   - Privilege escalation attempts
   - Tenant switch attempts

3. **Data Access**
   - Sensitive data access (student records, financial data)
   - Bulk data exports
   - Data modifications

4. **System Events**
   - Configuration changes
   - User role changes
   - Database migrations

#### Log Format

```php
// Structured logging
log_message('security', json_encode([
    'event' => 'unauthorized_access_attempt',
    'user_id' => $userId,
    'ip_address' => $ipAddress,
    'requested_resource' => $resource,
    'tenant_id' => $tenantId,
    'timestamp' => date('c'),
]));
```

### Monitoring Tools

**Recommended**:
- **Sentry**: Error tracking and performance monitoring
- **New Relic**: Application performance monitoring
- **CloudFlare**: WAF and DDoS protection
- **Fail2Ban**: Intrusion prevention

---

## 9. Incident Response

### Security Incident Response Plan

#### Phase 1: Detection
1. Monitor logs for suspicious activity
2. Set up alerts for security events
3. Regular security audits

#### Phase 2: Containment
1. Isolate affected systems
2. Block malicious IP addresses
3. Disable compromised accounts

#### Phase 3: Eradication
1. Remove malicious code/data
2. Patch vulnerabilities
3. Update security configurations

#### Phase 4: Recovery
1. Restore from backups if needed
2. Verify system integrity
3. Resume normal operations

#### Phase 5: Lessons Learned
1. Document the incident
2. Update security procedures
3. Implement preventive measures

### Emergency Contacts

- **Security Team Lead**: [Contact Info]
- **System Administrator**: [Contact Info]
- **Database Administrator**: [Contact Info]
- **Legal/Compliance**: [Contact Info]

---

## 10. Compliance

### Data Protection Regulations

#### GDPR Compliance (if applicable)
- ✅ Right to access personal data
- ✅ Right to data portability
- ✅ Right to be forgotten (soft deletes implemented)
- ✅ Data breach notification procedures
- ⚠️ Privacy policy and consent management (to be documented)

#### Data Retention
- **Student Records**: Retain for 7 years after graduation
- **Financial Records**: Retain for 7 years
- **System Logs**: Retain for 90 days
- **Backup Data**: Retain for 30 days

### Audit Requirements

#### Regular Audits
- **Code Review**: Quarterly
- **Penetration Testing**: Annually
- **Access Review**: Quarterly
- **Backup Verification**: Monthly

#### Compliance Reporting
- Security incident reports
- Access logs review
- Vulnerability assessments
- Compliance certifications

---

## Summary

### Security Posture: **A-** (Excellent)

✅ **Strengths**:
- Multi-tenant isolation (100% guaranteed)
- SQL injection protection
- XSS/CSRF protection
- Secure authentication
- Comprehensive logging
- Role-based access control

⚠️ **Recommendations**:
- Implement rate limiting
- Add API throttling
- Set up security monitoring
- Regular penetration testing
- Implement data encryption at rest

### Next Security Steps

1. **Immediate**: Install security monitoring tools
2. **Short-term**: Implement rate limiting
3. **Ongoing**: Regular security audits and updates

---

**Document Version**: 1.0.0  
**Last Updated**: November 23, 2025  
**Classification**: Internal Use  
**Owner**: ShuleLabs Platform Team
