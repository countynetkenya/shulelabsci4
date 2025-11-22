# 📊 Observability Framework

**Last Updated**: 2025-11-22  
**Status**: Active  
**Version**: 1.0.0

## Overview

This document defines the observability framework for ShuleLabs CI4, covering logging, monitoring, tracing, and alerting standards to ensure system reliability and debuggability.

## Table of Contents

- [The Three Pillars](#the-three-pillars)
  - [1. Logging](#1-logging)
  - [2. Metrics](#2-metrics)
  - [3. Tracing](#3-tracing)
- [Health Checks](#health-checks)
- [Alerting](#alerting)
- [Dashboards](#dashboards)
- [Implementation Guide](#implementation-guide)
- [Best Practices](#best-practices)
- [References](#references)

## The Three Pillars

Observability is built on three fundamental pillars:

### 1. Logging

**Purpose**: Capture detailed event information for debugging and auditing

#### Log Format

All logs must use structured JSON format:

```json
{
  "timestamp": "2025-11-22T11:23:29.123Z",
  "level": "INFO",
  "message": "Student created successfully",
  "context": {
    "tenant_id": "school-1",
    "user_id": 123,
    "request_id": "req_abc123",
    "trace_id": "trace_xyz789",
    "student_id": 456,
    "ip_address": "192.168.1.100"
  },
  "service": "learning",
  "environment": "production"
}
```

#### Required Fields

Every log entry **must** include:
- `timestamp` (ISO 8601 format with milliseconds)
- `level` (DEBUG, INFO, WARNING, ERROR, CRITICAL)
- `message` (human-readable description)
- `context` (structured data object)
- `service` (module/service name)
- `environment` (production, staging, development)

#### Context Fields

For tenant-scoped operations, include:
- `tenant_id` - The active tenant (school_id, organisation_id)
- `user_id` - The authenticated user
- `request_id` - Unique request identifier
- `trace_id` - Distributed trace identifier
- `ip_address` - Client IP address

#### Log Levels

Use appropriate log levels:

| Level | Usage | Example |
|-------|-------|---------|
| **DEBUG** | Detailed diagnostic information (dev only) | "Database query: SELECT * FROM students WHERE id=123" |
| **INFO** | General informational messages | "Student created successfully" |
| **WARNING** | Unexpected but handled situations | "Validation failed: Email already exists" |
| **ERROR** | Error conditions that don't stop execution | "Failed to send email notification" |
| **CRITICAL** | Severe errors requiring immediate attention | "Database connection failed" |

#### Logging Examples

**API Request**:
```json
{
  "timestamp": "2025-11-22T11:23:29.123Z",
  "level": "INFO",
  "message": "API request received",
  "context": {
    "method": "POST",
    "endpoint": "/api/v1/learning/students",
    "tenant_id": "school-1",
    "user_id": 123,
    "request_id": "req_abc123",
    "ip_address": "192.168.1.100"
  },
  "service": "learning",
  "environment": "production"
}
```

**API Response**:
```json
{
  "timestamp": "2025-11-22T11:23:29.456Z",
  "level": "INFO",
  "message": "API request completed",
  "context": {
    "method": "POST",
    "endpoint": "/api/v1/learning/students",
    "status_code": 201,
    "duration_ms": 333,
    "request_id": "req_abc123"
  },
  "service": "learning",
  "environment": "production"
}
```

**Error**:
```json
{
  "timestamp": "2025-11-22T11:23:29.789Z",
  "level": "ERROR",
  "message": "Failed to create student",
  "context": {
    "error": "Database constraint violation",
    "error_code": "23000",
    "request_id": "req_abc123",
    "tenant_id": "school-1",
    "user_id": 123,
    "stack_trace": "..."
  },
  "service": "learning",
  "environment": "production"
}
```

#### Log Implementation

**Service**: `app/Services/StructuredLogger.php`

```php
<?php

namespace App\Services;

use CodeIgniter\Log\Logger;

class StructuredLogger
{
    protected Logger $logger;
    protected array $context = [];
    
    public function __construct()
    {
        $this->logger = service('logger');
    }
    
    public function setContext(array $context): void
    {
        $this->context = array_merge($this->context, $context);
    }
    
    public function info(string $message, array $context = []): void
    {
        $this->log('info', $message, $context);
    }
    
    public function error(string $message, array $context = []): void
    {
        $this->log('error', $message, $context);
    }
    
    protected function log(string $level, string $message, array $context = []): void
    {
        $logData = [
            'timestamp' => date('c'),
            'level' => strtoupper($level),
            'message' => $message,
            'context' => array_merge($this->context, $context),
            'service' => 'shulelabs',
            'environment' => ENVIRONMENT,
        ];
        
        $this->logger->log($level, json_encode($logData));
    }
}
```

**Usage**:
```php
$logger = service('StructuredLogger');
$logger->setContext([
    'tenant_id' => $tenantId,
    'user_id' => $userId,
    'request_id' => uniqid('req_'),
]);

$logger->info('Creating student', ['data' => $data]);
```

### 2. Metrics

**Purpose**: Track quantitative measurements over time

#### Metric Types

1. **Counter**: Cumulative value that only increases (e.g., total requests)
2. **Gauge**: Point-in-time value that can go up or down (e.g., active users)
3. **Histogram**: Distribution of values (e.g., request duration)
4. **Summary**: Similar to histogram with configurable quantiles

#### Key Metrics

**System Metrics**:
- `system.cpu.usage` - CPU utilization (%)
- `system.memory.usage` - Memory usage (MB)
- `system.disk.usage` - Disk usage (%)
- `system.network.in` - Network ingress (bytes/sec)
- `system.network.out` - Network egress (bytes/sec)

**Application Metrics**:
- `api.request.count` - Total API requests (tagged by endpoint, method, status)
- `api.request.duration` - Request duration (ms) (p50, p95, p99)
- `api.error.rate` - Error rate (%)
- `db.query.duration` - Database query time (ms)
- `db.connection.count` - Active DB connections
- `cache.hit.rate` - Cache hit rate (%)

**Business Metrics**:
- `students.total` - Total students (per tenant)
- `students.enrolled.daily` - Students enrolled today
- `fees.collected.amount` - Fees collected (per tenant, per period)
- `attendance.rate` - Attendance percentage
- `invoices.pending.count` - Unpaid invoices

#### Metrics Implementation

**Service**: `app/Services/MetricsService.php`

```php
<?php

namespace App\Services;

class MetricsService
{
    protected array $metrics = [];
    
    public function increment(string $metric, int $value = 1, array $tags = []): void
    {
        $this->metrics[] = [
            'type' => 'counter',
            'name' => $metric,
            'value' => $value,
            'tags' => $tags,
            'timestamp' => time(),
        ];
    }
    
    public function gauge(string $metric, float $value, array $tags = []): void
    {
        $this->metrics[] = [
            'type' => 'gauge',
            'name' => $metric,
            'value' => $value,
            'tags' => $tags,
            'timestamp' => time(),
        ];
    }
    
    public function timing(string $metric, float $milliseconds, array $tags = []): void
    {
        $this->metrics[] = [
            'type' => 'timing',
            'name' => $metric,
            'value' => $milliseconds,
            'tags' => $tags,
            'timestamp' => time(),
        ];
    }
    
    public function flush(): void
    {
        // Send to metrics backend (StatsD, Prometheus, CloudWatch)
        // Implementation depends on chosen backend
        
        $this->metrics = [];
    }
}
```

**Usage**:
```php
$metrics = service('MetricsService');

// Record API request
$startTime = microtime(true);
// ... process request ...
$duration = (microtime(true) - $startTime) * 1000;

$metrics->timing('api.request.duration', $duration, [
    'endpoint' => '/api/v1/learning/students',
    'method' => 'POST',
]);

$metrics->increment('api.request.count', 1, [
    'endpoint' => '/api/v1/learning/students',
    'status' => 201,
]);

// Flush at end of request
$metrics->flush();
```

### 3. Tracing

**Purpose**: Track request flow across services for distributed debugging

#### Trace ID Propagation

**Generate Trace ID**:
- On first entry point (API gateway, web server)
- Format: `trace_{unique_id}` (e.g., `trace_abc123xyz`)
- Propagate to all downstream services

**Implementation**:

**Filter**: `app/Filters/TraceIdFilter.php`

```php
<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class TraceIdFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Extract or generate trace ID
        $traceId = $request->getHeaderLine('X-Trace-ID') ?: uniqid('trace_');
        
        // Store in request attribute
        $request->setAttribute('trace_id', $traceId);
        
        return $request;
    }
    
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Add trace ID to response headers
        $traceId = $request->getAttribute('trace_id');
        $response->setHeader('X-Trace-ID', $traceId);
        
        return $response;
    }
}
```

**Include in Logs**:
```php
$logger->setContext([
    'trace_id' => $request->getAttribute('trace_id'),
    // ... other context
]);
```

**Propagate to External Calls**:
```php
$client = \Config\Services::curlrequest();
$response = $client->request('POST', 'https://api.external.com/endpoint', [
    'headers' => [
        'X-Trace-ID' => $request->getAttribute('trace_id'),
    ],
]);
```

## Health Checks

**Purpose**: Monitor service availability and dependencies

### Endpoints

**Basic Health Check**: `GET /health`
- Returns overall system health
- Checks: database, cache, filesystem
- Status: 200 (healthy) or 503 (unhealthy)

**Readiness Check**: `GET /health/ready`
- Indicates if service can accept traffic
- Used by load balancers and orchestrators
- Status: 200 (ready) or 503 (not ready)

**Liveness Check**: `GET /health/live`
- Indicates if service is alive
- Used for restart decisions
- Status: 200 (alive) or 503 (dead)

### Response Format

**Healthy**:
```json
{
  "status": "healthy",
  "timestamp": "2025-11-22T11:23:29Z",
  "checks": {
    "database": {"status": "ok"},
    "cache": {"status": "ok"},
    "filesystem": {"status": "ok"}
  }
}
```

**Unhealthy**:
```json
{
  "status": "unhealthy",
  "timestamp": "2025-11-22T11:23:29Z",
  "checks": {
    "database": {"status": "error", "message": "Connection timeout"},
    "cache": {"status": "ok"},
    "filesystem": {"status": "ok"}
  }
}
```

### Implementation

**Controller**: `app/Controllers/HealthController.php`

```php
<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;

class HealthController extends BaseController
{
    public function index(): ResponseInterface
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'filesystem' => $this->checkFilesystem(),
        ];
        
        $status = 'healthy';
        $statusCode = 200;
        
        foreach ($checks as $check) {
            if ($check['status'] !== 'ok') {
                $status = 'unhealthy';
                $statusCode = 503;
                break;
            }
        }
        
        return $this->response->setJSON([
            'status' => $status,
            'timestamp' => date('c'),
            'checks' => $checks,
        ])->setStatusCode($statusCode);
    }
    
    private function checkDatabase(): array
    {
        try {
            $db = \Config\Database::connect();
            $db->query('SELECT 1');
            return ['status' => 'ok'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
    
    private function checkCache(): array
    {
        try {
            $cache = \Config\Services::cache();
            $cache->save('health_check', true, 10);
            $value = $cache->get('health_check');
            
            if ($value === true) {
                return ['status' => 'ok'];
            }
            
            return ['status' => 'error', 'message' => 'Cache read/write failed'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
    
    private function checkFilesystem(): array
    {
        try {
            $testFile = WRITEPATH . 'health_check.tmp';
            file_put_contents($testFile, 'test');
            $content = file_get_contents($testFile);
            unlink($testFile);
            
            if ($content === 'test') {
                return ['status' => 'ok'];
            }
            
            return ['status' => 'error', 'message' => 'Filesystem read/write failed'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
}
```

## Alerting

**Purpose**: Proactive notification of issues

### Alert Rules

**Critical Alerts** (immediate notification):
- Service down (health check fails)
- Database unavailable
- Disk >95% full
- Error rate >10%

**Warning Alerts** (notify within 15 minutes):
- High error rate (>5%)
- Slow response time (p95 >1s)
- Disk >80% full
- High memory usage (>80%)

### Alert Configuration

Define alerts in `app/Config/Alerts.php`:

```php
public array $rules = [
    [
        'name' => 'high_error_rate',
        'condition' => 'error_rate > 0.05',
        'duration' => '5m',
        'severity' => 'warning',
        'channels' => ['email', 'slack'],
    ],
    [
        'name' => 'service_down',
        'condition' => 'health_status == "unhealthy"',
        'duration' => '1m',
        'severity' => 'critical',
        'channels' => ['email', 'sms', 'pagerduty'],
    ],
];
```

### Notification Channels

- **Email**: For all alerts
- **Slack**: For team visibility
- **SMS**: For critical alerts only
- **PagerDuty**: For on-call rotation

## Dashboards

**Purpose**: Real-time visualization of system state

### Dashboard Types

1. **System Dashboard**: CPU, memory, disk, network
2. **API Performance Dashboard**: Request count, latency, errors
3. **Business Metrics Dashboard**: Students, fees, attendance
4. **Tenant Usage Dashboard**: Per-tenant API usage, storage

### Tools

- **Grafana**: Open-source dashboarding (recommended)
- **Kibana**: For Elasticsearch/ELK stack
- **CloudWatch Dashboards**: AWS-native
- **Datadog**: All-in-one monitoring

## Implementation Guide

### Step 1: Enable Structured Logging

1. Create `StructuredLogger` service
2. Update all controllers to use structured logging
3. Include tenant_id, user_id, request_id in all logs

### Step 2: Add Metrics Collection

1. Create `MetricsService`
2. Add metrics middleware/filter to track API requests
3. Configure metrics backend (StatsD, Prometheus, etc.)
4. Instrument critical paths (DB queries, external API calls)

### Step 3: Implement Trace ID Propagation

1. Create `TraceIdFilter`
2. Register filter globally
3. Include trace_id in all logs
4. Propagate to external service calls

### Step 4: Create Health Checks

1. Implement `HealthController` with `/health`, `/health/ready`, `/health/live`
2. Add database, cache, filesystem checks
3. Configure health check monitoring

### Step 5: Set Up Alerting

1. Define alert rules
2. Configure notification channels
3. Test alert delivery

### Step 6: Build Dashboards

1. Create Grafana dashboards (or equivalent)
2. Add system, API, and business metric charts
3. Share dashboards with team

## Best Practices

1. **Log at the right level**: Use INFO for normal operations, ERROR for failures
2. **Include context**: Always include tenant_id, user_id, request_id
3. **Don't log sensitive data**: No passwords, tokens, PII in logs
4. **Use structured logging**: JSON format for machine parsing
5. **Instrument critical paths**: Log start/end of important operations
6. **Monitor what matters**: Focus on actionable metrics
7. **Set meaningful alerts**: Avoid alert fatigue with too many notifications
8. **Document runbooks**: What to do when alerts fire
9. **Regular review**: Review logs and metrics weekly
10. **Test observability**: Simulate failures and verify alerts

## References

- [Monitoring & Observability Agent](agents/monitoring-observability-agent.md)
- [Architecture](ARCHITECTURE.md)
- [Security](SECURITY.md)
- [Database Schema](DATABASE.md)

---

**Version**: 1.0.0  
**Maintained By**: ShuleLabs Platform Team
