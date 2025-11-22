# 🔐 Security

**Last Updated**: 2025-11-22  
**Status**: Active  
**Version**: 1.0.0

## Overview

This document defines security standards, authentication and authorization patterns, audit logging, and best practices for ShuleLabs CI4.

## Table of Contents

- [Authentication](#authentication)
- [Authorization (RBAC)](#authorization-rbac)
- [Multi-Tenant Security](#multi-tenant-security)
- [Audit Logging](#audit-logging)
- [Input Validation](#input-validation)
- [CSRF Protection](#csrf-protection)
- [SQL Injection Prevention](#sql-injection-prevention)
- [XSS Prevention](#xss-prevention)
- [Password Security](#password-security)
- [API Security](#api-security)
- [Security Headers](#security-headers)
- [References](#references)

## Authentication

### CI4 Authentication Filter

**Purpose**: Verify user identity before allowing access to protected resources

**Implementation**: `app/Filters/AuthFilter.php`

```php
<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();
        
        // Check if user is authenticated
        if (!$session->has('user_id')) {
            // For API requests, return 401 JSON response
            if ($this->isApiRequest($request)) {
                return service('response')->setJSON([
                    'status' => 'error',
                    'error' => [
                        'code' => 'UNAUTHORIZED',
                        'message' => 'Authentication required',
                    ],
                ])->setStatusCode(401);
            }
            
            // For web requests, redirect to login
            return redirect()->to('/login');
        }
        
        // Load user data into request
        $userId = $session->get('user_id');
        $user = model('UserModel')->find($userId);
        
        if (!$user || !$user->is_active) {
            $session->destroy();
            return redirect()->to('/login?error=inactive');
        }
        
        // Set user in request attribute
        $request->setAttribute('user', $user);
        $request->setAttribute('user_id', $user->id);
        
        // Resolve tenant context
        $tenantId = $this->resolveTenantId($request, $user);
        $request->setAttribute('tenant_id', $tenantId);
        
        return $request;
    }
    
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return $response;
    }
    
    private function isApiRequest(RequestInterface $request): bool
    {
        $uri = $request->getUri()->getPath();
        return strpos($uri, '/api/') === 0;
    }
    
    private function resolveTenantId(RequestInterface $request, $user): ?string
    {
        // Priority 1: X-Tenant-ID header (for API requests)
        $tenantId = $request->getHeaderLine('X-Tenant-ID');
        
        // Priority 2: Session tenant_id
        if (!$tenantId) {
            $tenantId = session('tenant_id');
        }
        
        // Priority 3: User's default school_id
        if (!$tenantId && isset($user->schoolID)) {
            $tenantId = 'school-' . $user->schoolID;
        }
        
        // Verify user has access to this tenant
        if ($tenantId && !$this->userHasAccessToTenant($user, $tenantId)) {
            throw new \RuntimeException('Access denied to tenant: ' . $tenantId);
        }
        
        return $tenantId;
    }
    
    private function userHasAccessToTenant($user, string $tenantId): bool
    {
        // Check if user metadata includes this tenant
        $metadata = json_decode($user->metadata ?? '{}', true);
        $allowedTenants = $metadata['school_ids'] ?? [];
        
        return in_array($tenantId, $allowedTenants, true);
    }
}
```

**Registration** (`app/Config/Filters.php`):

```php
public array $aliases = [
    'auth' => \App\Filters\AuthFilter::class,
    'authorize' => \App\Filters\AuthorizationFilter::class,
];

public array $globals = [
    'before' => [
        // 'auth', // Apply globally if needed
    ],
];
```

## Authorization (RBAC)

### Role-Based Access Control

**Database Schema**:

```sql
-- Roles table
CREATE TABLE ci4_roles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    role_name VARCHAR(100) NOT NULL,
    role_slug VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    permissions JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- User-Role assignments
CREATE TABLE ci4_user_roles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    role_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES ci4_users(id) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES ci4_roles(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_role (user_id, role_id)
);
```

**Permissions** (stored in `permissions` JSON field):

```json
{
  "students": ["create", "read", "update", "delete"],
  "teachers": ["read"],
  "finance": ["read"],
  "reports": ["read", "export"]
}
```

### Authorization Filter

**Implementation**: `app/Filters/AuthorizationFilter.php`

```php
<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthorizationFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $user = $request->getAttribute('user');
        
        if (!$user) {
            return service('response')->setJSON([
                'status' => 'error',
                'error' => ['code' => 'UNAUTHORIZED', 'message' => 'User not authenticated'],
            ])->setStatusCode(401);
        }
        
        // Get required permission from route arguments
        // Example: ['filter' => 'authorize:students.create']
        $requiredPermission = $arguments[0] ?? null;
        
        if (!$requiredPermission) {
            // No specific permission required
            return $request;
        }
        
        // Check if user has permission
        if (!$this->userHasPermission($user, $requiredPermission)) {
            return service('response')->setJSON([
                'status' => 'error',
                'error' => ['code' => 'FORBIDDEN', 'message' => 'Insufficient permissions'],
            ])->setStatusCode(403);
        }
        
        return $request;
    }
    
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return $response;
    }
    
    private function userHasPermission($user, string $permission): bool
    {
        // Get user's roles
        $db = \Config\Database::connect();
        $roles = $db->table('ci4_user_roles ur')
            ->select('r.permissions')
            ->join('ci4_roles r', 'r.id = ur.role_id')
            ->where('ur.user_id', $user->id)
            ->get()
            ->getResult();
        
        // Parse permission (e.g., "students.create" => resource: students, action: create)
        [$resource, $action] = explode('.', $permission);
        
        // Check each role's permissions
        foreach ($roles as $role) {
            $permissions = json_decode($role->permissions, true);
            
            if (isset($permissions[$resource]) && in_array($action, $permissions[$resource], true)) {
                return true;
            }
            
            // Check for wildcard permission
            if (isset($permissions['*']) || isset($permissions[$resource]) && in_array('*', $permissions[$resource], true)) {
                return true;
            }
        }
        
        return false;
    }
}
```

**Usage in Routes**:

```php
// app/Config/Routes.php
$routes->group('api/v1/learning', ['filter' => 'auth'], function($routes) {
    // Only users with 'students.create' permission can access
    $routes->post('students', 'StudentsController::create', ['filter' => 'authorize:students.create']);
    
    // Only users with 'students.read' permission
    $routes->get('students', 'StudentsController::index', ['filter' => 'authorize:students.read']);
});
```

## Multi-Tenant Security

### Tenant Isolation Rules

**Critical**: ALL data queries MUST be scoped by tenant_id to prevent cross-tenant data leaks.

**Enforcement Mechanisms**:

1. **TenantAwareModel**: All models extend this base class
2. **Tenant Context Filter**: Sets tenant_id in request
3. **Query Scope Validation**: Automated tests verify tenant scoping

See [DATABASE.md](DATABASE.md) for detailed tenant isolation implementation.

## Audit Logging

### Audit Service

**Purpose**: Immutable log of all sensitive actions

**Table**: `audit_events`

```sql
CREATE TABLE audit_events (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    tenant_id VARCHAR(50) NOT NULL,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    resource_type VARCHAR(50),
    resource_id VARCHAR(50),
    ip_address VARCHAR(45),
    user_agent TEXT,
    context JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at),
    FOREIGN KEY (tenant_id) REFERENCES ci4_tenant_catalog(id) ON DELETE RESTRICT
);
```

**Service**: `app/Modules/Foundation/Services/AuditService.php`

```php
<?php

namespace Modules\Foundation\Services;

class AuditService
{
    protected $db;
    
    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }
    
    /**
     * Log an audit event
     */
    public function log(string $action, array $context, string $tenantId): void
    {
        $request = service('request');
        
        $data = [
            'tenant_id' => $tenantId,
            'user_id' => $request->getAttribute('user_id'),
            'action' => $action,
            'resource_type' => $context['resource_type'] ?? null,
            'resource_id' => $context['resource_id'] ?? null,
            'ip_address' => $request->getIPAddress(),
            'user_agent' => $request->getUserAgent()->getAgentString(),
            'context' => json_encode($context),
            'created_at' => date('Y-m-d H:i:s'),
        ];
        
        $this->db->table('audit_events')->insert($data);
    }
}
```

### Actions to Audit

**Always audit**:
- User login/logout
- User creation/deletion/update
- Role assignment changes
- Permission changes
- Financial transactions (invoices, payments, refunds)
- Student data changes (enrollment, withdrawal)
- Grade modifications
- Sensitive report access

**Example Usage**:

```php
// After creating a student
$this->auditService->log('student.created', [
    'resource_type' => 'student',
    'resource_id' => $studentId,
    'data' => $data,
], $tenantId);
```

## Input Validation

### Validation Rules

**Always validate**:
- All user inputs (forms, API requests)
- File uploads (type, size, content)
- URL parameters
- Query strings

**CodeIgniter 4 Validation**:

```php
protected $validationRules = [
    'email' => 'required|valid_email|max_length[255]',
    'password' => 'required|min_length[8]|max_length[255]',
    'first_name' => 'required|alpha_space|max_length[100]',
    'date_of_birth' => 'required|valid_date',
    'phone' => 'permit_empty|regex_match[/^\+?[0-9]{10,15}$/]',
];
```

## CSRF Protection

**Enable CSRF protection** in `app/Config/Security.php`:

```php
public string $csrfProtection = 'session'; // or 'cookie'
public int $csrfExpire = 7200; // 2 hours
public bool $csrfRegenerate = true;
```

**In Forms**:

```php
<form method="POST" action="/admin/students">
    <?= csrf_field() ?>
    <!-- form fields -->
</form>
```

**In AJAX**:

```javascript
fetch('/api/endpoint', {
    method: 'POST',
    headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        'Content-Type': 'application/json',
    },
    body: JSON.stringify(data),
});
```

## SQL Injection Prevention

**Use Query Builder** (never raw SQL with user input):

```php
// GOOD: Using Query Builder
$students = $this->db->table('students')
    ->where('school_id', $schoolId)
    ->where('first_name', $firstName)
    ->get()
    ->getResult();

// BAD: Raw SQL with concatenation
$students = $this->db->query("SELECT * FROM students WHERE first_name = '$firstName'");
```

**If raw SQL is necessary**, use parameter binding:

```php
$sql = "SELECT * FROM students WHERE first_name = ? AND school_id = ?";
$students = $this->db->query($sql, [$firstName, $schoolId])->getResult();
```

## XSS Prevention

**Always escape output**:

```php
// In views
<p><?= esc($user->name) ?></p>
<p><?= esc($student->email, 'html') ?></p>
```

**For JavaScript context**:

```php
<script>
    const userName = <?= json_encode($user->name) ?>;
</script>
```

## Password Security

### Password Hashing

**Use PHP's password_hash**:

```php
// Hash password
$hash = password_hash($password, PASSWORD_DEFAULT);

// Verify password
if (password_verify($password, $hash)) {
    // Password is correct
}
```

### Password Policy

**Requirements**:
- Minimum 8 characters
- At least one uppercase letter
- At least one lowercase letter
- At least one number
- At least one special character

**Validation Rule**:

```php
'password' => 'required|min_length[8]|regex_match[/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])/]',
```

## API Security

### Rate Limiting

**Implementation**: `app/Filters/RateLimitFilter.php`

```php
public function before(RequestInterface $request, $arguments = null)
{
    $key = $request->getIPAddress() . ':' . $request->getUri()->getPath();
    $limit = 100; // requests
    $window = 60; // seconds
    
    $cache = \Config\Services::cache();
    $count = $cache->get($key) ?? 0;
    
    if ($count >= $limit) {
        return service('response')->setJSON([
            'status' => 'error',
            'error' => ['code' => 'RATE_LIMIT_EXCEEDED', 'message' => 'Too many requests'],
        ])->setStatusCode(429);
    }
    
    $cache->save($key, $count + 1, $window);
    
    return $request;
}
```

## Security Headers

**Set security headers** in `app/Config/App.php` or via filter:

```php
$response->setHeader('X-Content-Type-Options', 'nosniff');
$response->setHeader('X-Frame-Options', 'SAMEORIGIN');
$response->setHeader('X-XSS-Protection', '1; mode=block');
$response->setHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
$response->setHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
```

## References

- [System Overview](01-SYSTEM-OVERVIEW.md)
- [Architecture](ARCHITECTURE.md)
- [Database Schema](DATABASE.md)
- [Observability](OBSERVABILITY.md)
- [OWASP Top 10](https://owasp.org/www-project-top-ten/)

---

**Version**: 1.0.0  
**Maintained By**: ShuleLabs Platform Team
