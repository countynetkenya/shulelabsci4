# 🎯 System Monitoring & Observability

**Last Updated**: 2025-11-22  
**Status**: Baseline Complete (Phase 1), Advanced Features In Progress (Phase 2C)

## Overview

ShuleLabs treats **observability as a platform guardrail**, not just a feature. All modules and features are built with baseline observability from the start, ensuring visibility, debuggability, and operational excellence.

**Key Principle**: No new feature is considered "done" unless it integrates with baseline observability—structured logging, basic metrics, and health dashboards.

## Table of Contents

- [Overview](#overview)
- [Observability Levels](#observability-levels)
- [Baseline Observability (Phase 1/2)](#baseline-observability-phase-12)
- [Advanced Observability (Phase 2C/3)](#advanced-observability-phase-2c3)
- [Standard Log Fields](#standard-log-fields)
- [Metrics Standards](#metrics-standards)
- [Implementation Guidelines](#implementation-guidelines)
- [Testing](#testing)
- [References](#references)

## Observability Levels

### Baseline Observability ✅ (Phase 1, Mandatory for All Features)

**Purpose**: Essential visibility for all features, always enabled

**Components**:
- **Structured Logging**: All events logged with standard fields (timestamp, level, service, tenant_id, user_id, trace_id, action, result)
- **Health Checks**: Liveness and readiness endpoints for each module
- **Error Tracking**: Exceptions logged with full context and stack traces
- **Basic Metrics**: Request count, error rate, response time per endpoint
- **Log Aggregation**: Centralized log collection (file-based, ELK stack, or cloud logging)
- **Uptime Monitoring**: Service availability checks
- **Resource Monitoring**: CPU, memory, disk usage tracking
- **Audit Trail Integration**: All significant actions flow through audit log with tenant context

**Status**: ✅ Complete in Phase 1

### Advanced Observability 🟡 (Phase 2C/3, Optional Enhancements)

**Purpose**: Deep insights, predictive analytics, advanced troubleshooting

**Components**:
- **APM Integration**: New Relic, DataDog, or Elastic APM for distributed tracing
- **Custom Dashboards**: Grafana or similar for complex visualizations
- **Predictive Alerts**: Anomaly detection based on historical trends
- **Distributed Tracing**: Request flow across microservices (future architecture)
- **Performance Profiling**: Detailed query analysis, code-level bottleneck detection
- **SLA Tracking**: Automated SLA compliance reports
- **Advanced Analytics**: Custom metrics, business KPIs, trend analysis

**Status**: 🟡 In Progress (Phase 2C)

## Baseline Observability (Phase 1/2)

### Requirements

All modules and features must implement:

1. **Structured Logging**
   - Use standard log format with required fields
   - Log all significant actions (create, update, delete, approve, etc.)
   - Include tenant context in every log entry
   - Use appropriate log levels (DEBUG, INFO, WARNING, ERROR, CRITICAL)

2. **Health Checks**
   - Implement `/health` endpoint returning JSON status
   - Check database connectivity
   - Check external dependencies (cache, queue, etc.)
   - Return appropriate HTTP status codes (200 OK, 503 Service Unavailable)

3. **Error Handling**
   - Catch and log all exceptions with full context
   - Emit error metrics (increment error counters)
   - Return user-friendly error messages (hide sensitive details)
   - Log stack traces for debugging

4. **Basic Metrics**
   - Request count per endpoint
   - Error count per endpoint
   - Response time (avg, p50, p95, p99)
   - Database query count and time

## Standard Log Fields

All log entries MUST include these fields:

```json
{
  "timestamp": "2025-11-22T10:52:31.028Z",
  "level": "INFO",
  "service": "learning.students",
  "tenant_id": "school-1",
  "user_id": 123,
  "trace_id": "abc123xyz",
  "action": "student.created",
  "result": "success",
  "duration_ms": 45,
  "message": "Student created successfully",
  "metadata": {
    "student_id": 456,
    "admission_number": "2025-001"
  }
}
```

### Field Definitions

- **timestamp**: ISO 8601 UTC timestamp
- **level**: Log level (DEBUG, INFO, WARNING, ERROR, CRITICAL)
- **service**: Module and component (e.g., `finance.billing`, `learning.attendance`)
- **tenant_id**: Active tenant/school identifier (for tenant-scoped actions)
- **user_id**: Authenticated user ID (for user-initiated actions)
- **trace_id**: Request correlation ID for distributed tracing
- **action**: Action key (e.g., `student.created`, `fee.paid`, `grade.updated`)
- **result**: Action outcome (`success`, `failure`, `partial`)
- **duration_ms**: Action duration in milliseconds
- **message**: Human-readable description
- **metadata**: Additional context (flexible JSON object)

### Log Levels

- **DEBUG**: Detailed information for debugging (disabled in production)
- **INFO**: General informational messages (normal operations)
- **WARNING**: Warning messages (potential issues, degraded performance)
- **ERROR**: Error messages (failures that don't crash the system)
- **CRITICAL**: Critical errors (system failures, data corruption)

## Metrics Standards

### Required Metrics Per Module

Every module must expose these metrics:

1. **Request Metrics**
   - `http_requests_total{method, endpoint, status}` - Total requests
   - `http_request_duration_seconds{method, endpoint}` - Request latency histogram

2. **Error Metrics**
   - `http_errors_total{method, endpoint, error_type}` - Total errors
   - `exceptions_total{service, exception_class}` - Exception count

3. **Database Metrics**
   - `db_queries_total{service, query_type}` - Total queries
   - `db_query_duration_seconds{service, query_type}` - Query latency

4. **Business Metrics** (Module-Specific)
   - Learning: `students_created_total`, `attendance_marked_total`
   - Finance: `invoices_generated_total`, `payments_processed_total`
   - Library: `books_borrowed_total`, `fines_collected_total`

### Metrics Endpoint

All modules should expose metrics at `/metrics` (Prometheus format or JSON):

```
# TYPE http_requests_total counter
http_requests_total{method="POST",endpoint="/api/v1/students",status="200"} 1523
http_requests_total{method="GET",endpoint="/api/v1/students",status="200"} 8764

# TYPE http_request_duration_seconds histogram
http_request_duration_seconds_bucket{method="POST",endpoint="/api/v1/students",le="0.1"} 1200
http_request_duration_seconds_bucket{method="POST",endpoint="/api/v1/students",le="0.5"} 1520
http_request_duration_seconds_sum{method="POST",endpoint="/api/v1/students"} 68.3
http_request_duration_seconds_count{method="POST",endpoint="/api/v1/students"} 1523
```

## Implementation Guidelines

### Logging in Controllers

For consistency and maintainability, consider creating a structured logging helper:

**Helper Function** (optional, recommended for production):
```php
// app/Helpers/logging_helper.php

if (!function_exists('log_structured')) {
    /**
     * Log a structured event with standard fields
     */
    function log_structured(
        string $level,
        string $service,
        string $action,
        string $result,
        ?string $tenantId,
        ?int $userId,
        ?string $traceId,
        float $durationMs,
        string $message,
        array $metadata = []
    ): void {
        $logData = [
            'timestamp' => date('c'),
            'level' => strtoupper($level),
            'service' => $service,
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'trace_id' => $traceId,
            'action' => $action,
            'result' => $result,
            'duration_ms' => round($durationMs, 2),
            'message' => $message,
            'metadata' => $metadata,
        ];
        
        log_message($level, json_encode($logData));
    }
}
```

**Usage in Controllers** (with helper):
```php
class StudentController extends BaseController
{
    public function create()
    {
        $traceId = $this->request->getHeaderLine('X-Trace-ID') ?: uniqid('trace_', true);
        $tenantContext = $this->tenantResolver->fromRequest($this->request);
        $schoolId = $tenantContext['school']['id'] ?? null;
        $userId = auth()->id();
        
        $startTime = microtime(true);
        
        try {
            $data = $this->request->getJSON(true);
            $student = $this->studentService->createStudent($schoolId, $data);
            
            log_structured(
                'info',
                'learning.students',
                'student.created',
                'success',
                $schoolId,
                $userId,
                $traceId,
                (microtime(true) - $startTime) * 1000,
                'Student created successfully',
                ['student_id' => $student['id']]
            );
            
            return $this->respondCreated($student);
            
        } catch (\Exception $e) {
            log_structured(
                'error',
                'learning.students',
                'student.created',
                'failure',
                $schoolId,
                $userId,
                $traceId,
                (microtime(true) - $startTime) * 1000,
                'Failed to create student',
                ['error' => $e->getMessage()]
            );
            
            return $this->failServerError('Failed to create student');
        }
    }
}
```

**Inline Logging** (without helper, for reference):
```php
class StudentController extends BaseController
{
    public function create()
    {
        $traceId = $this->request->getHeaderLine('X-Trace-ID') ?: uniqid('trace_', true);
        $tenantContext = $this->tenantResolver->fromRequest($this->request);
        $schoolId = $tenantContext['school']['id'] ?? null;
        $userId = auth()->id();
        
        $startTime = microtime(true);
        
        try {
            $data = $this->request->getJSON(true);
            $student = $this->studentService->createStudent($schoolId, $data);
            
            $duration = (microtime(true) - $startTime) * 1000;
            
            log_message('info', json_encode([
                'timestamp' => date('c'),
                'level' => 'INFO',
                'service' => 'learning.students',
                'tenant_id' => $schoolId,
                'user_id' => $userId,
                'trace_id' => $traceId,
                'action' => 'student.created',
                'result' => 'success',
                'duration_ms' => $duration,
                'message' => 'Student created successfully',
                'metadata' => ['student_id' => $student['id']],
            ]));
            
            return $this->respondCreated($student);
            
        } catch (\Exception $e) {
            $duration = (microtime(true) - $startTime) * 1000;
            
            log_message('error', json_encode([
                'timestamp' => date('c'),
                'level' => 'ERROR',
                'service' => 'learning.students',
                'tenant_id' => $schoolId,
                'user_id' => $userId,
                'trace_id' => $traceId,
                'action' => 'student.created',
                'result' => 'failure',
                'duration_ms' => $duration,
                'message' => 'Failed to create student',
                'metadata' => ['error' => $e->getMessage()],
            ]));
            
            return $this->failServerError('Failed to create student');
        }
    }
}
```

### Health Check Endpoint

```php
class HealthController extends BaseController
{
    public function index()
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'disk' => $this->checkDisk(),
        ];
        
        $healthy = !in_array(false, $checks, true);
        $status = $healthy ? 200 : 503;
        
        return $this->response->setJSON([
            'status' => $healthy ? 'healthy' : 'unhealthy',
            'timestamp' => date('c'),
            'checks' => $checks,
        ])->setStatusCode($status);
    }
    
    private function checkDatabase(): bool
    {
        try {
            $db = \Config\Database::connect();
            $db->query('SELECT 1');
            return true;
        } catch (\Exception $e) {
            log_message('error', 'Database health check failed: ' . $e->getMessage());
            return false;
        }
    }
    
    private function checkCache(): bool
    {
        // Check Redis/cache connectivity
        return true;
    }
    
    private function checkDisk(): bool
    {
        $freeSpace = disk_free_space('/');
        $totalSpace = disk_total_space('/');
        $usagePercent = (1 - ($freeSpace / $totalSpace)) * 100;
        
        return $usagePercent < 90; // Alert if >90% full
    }
}
```

### Audit Trail Integration

All significant actions should flow through the audit trail:

```php
$this->auditService->recordEvent(
    'student.created',
    'create',
    [
        'tenant_id' => $schoolId,
        'actor_id' => $userId,
    ],
    null, // before state
    $studentData, // after state
    [
        'ip' => $this->request->getIPAddress(),
        'user_agent' => $this->request->getUserAgent(),
        'trace_id' => $traceId,
    ]
);
```

## Advanced Observability (Phase 2C/3)

### Planned Features

1. **APM Integration**
   - New Relic or DataDog integration
   - Distributed request tracing
   - Code-level performance profiling
   - Database query analysis

2. **Custom Dashboards**
   - Grafana dashboards for each module
   - Real-time metrics visualization
   - Alerting rules and notifications
   - Business KPI tracking

3. **Predictive Monitoring**
   - Anomaly detection (unusual traffic, error spikes)
   - Capacity planning (predict when to scale)
   - SLA forecasting (will we meet this month's SLA?)
   - Trend analysis (performance degradation over time)

4. **Advanced Alerting**
   - Multi-channel alerts (email, SMS, Slack, PagerDuty)
   - Alert escalation policies
   - Alert grouping and deduplication
   - Runbook automation (auto-restart failed services)

## Testing

### Observability Testing

All features must include tests for observability:

```php
class StudentControllerTest extends TestCase
{
    public function testCreateStudentLogsSuccess()
    {
        // Arrange
        $data = ['name' => 'John Doe', 'admission_number' => '2025-001'];
        
        // Act
        $response = $this->withHeaders(['X-School-ID' => 'school-1'])
            ->post('/api/v1/students', $data);
        
        // Assert response
        $this->assertEquals(201, $response->getStatusCode());
        
        // Assert logging (check log file or mock logger)
        $logs = $this->getLogMessages();
        $this->assertStringContainsString('student.created', $logs);
        $this->assertStringContainsString('success', $logs);
        $this->assertStringContainsString('school-1', $logs);
    }
    
    public function testHealthCheckReturnsStatus()
    {
        $response = $this->get('/health');
        
        $this->assertEquals(200, $response->getStatusCode());
        $data = $response->getJSON();
        $this->assertEquals('healthy', $data['status']);
        $this->assertArrayHasKey('checks', $data);
        $this->assertArrayHasKey('database', $data['checks']);
    }
}
```

## Definition of Done

A feature is considered "done" only when:

- ✅ Structured logging implemented with all standard fields
- ✅ Health check endpoint added (if new module)
- ✅ Error handling with logging and metrics
- ✅ Basic metrics exposed (request count, error rate, latency)
- ✅ Audit trail integration for significant actions
- ✅ Tests include observability validation
- ✅ Feature visible on shared monitoring dashboard

## References

- [Master Implementation Plan](../02-MASTER-IMPLEMENTATION-PLAN.md)
- [Architecture](../ARCHITECTURE.md)
- [Audit Log Feature](22-AUDIT-LOG.md)
- [Operations Guide](../operations/MONITORING.md)

---

**Version**: 1.0.0
