# 📊 Monitoring & Observability Module Specification

**Version**: 1.0.0
**Status**: Draft
**Last Updated**: 2025-11-27

---

## Part 1: Feature Definition (The "What" & "Why")
*Target Audience: Product Owners, Stakeholders, Developers*

### 1.1 Overview
The Monitoring & Observability module is the "Eyes and Ears" of ShuleLabs. It provides health check endpoints, structured logging, metrics collection, distributed tracing, error tracking, performance dashboards, and SLA monitoring. This module ensures the platform is observable, debuggable, and maintains high availability.

### 1.2 Components

#### Health Checks
Endpoints that report the health status of various system components (database, cache, external services).

#### Structured Logging
Consistent log format with standard fields (timestamp, level, message, context, trace_id) for easy parsing and analysis.

#### Metrics
Numerical measurements (request count, latency, error rate) collected and exposed for monitoring systems.

#### Distributed Tracing
End-to-end request tracing across services using trace IDs and spans.

#### Error Tracking
Capture, aggregate, and alert on application errors with context.

### 1.3 User Stories

- **As an Ops Engineer**, I want health check endpoints, so that load balancers can route traffic correctly.
- **As a Developer**, I want structured logs with trace IDs, so that I can debug issues across services.
- **As a DevOps**, I want metrics exported to Prometheus, so that I can create dashboards.
- **As an Admin**, I want to see error rates and latency, so that I can monitor system health.
- **As a Support Agent**, I want to search logs by trace ID, so that I can investigate user issues.
- **As a CTO**, I want SLA reports, so that I can report on system reliability.

### 1.4 Acceptance Criteria

- [ ] Health check endpoint returns component status.
- [ ] Logs follow consistent structured format.
- [ ] Every request has a unique trace_id.
- [ ] Metrics available in Prometheus format.
- [ ] Error tracking captures stack traces and context.
- [ ] Performance dashboard shows key metrics.
- [ ] SLA tracking with uptime calculations.
- [ ] Alerts configured for critical thresholds.
- [ ] Log search by trace_id and other fields.

---

## Part 2: Technical Specification (The "How")
*Target Audience: Developers, Architects*

### 2.1 Health Check Endpoint

#### `/health` Response
```json
{
  "status": "healthy",
  "timestamp": "2025-11-27T12:00:00Z",
  "version": "1.5.0",
  "checks": {
    "database": {
      "status": "healthy",
      "latency_ms": 5
    },
    "cache": {
      "status": "healthy",
      "latency_ms": 1
    },
    "storage": {
      "status": "healthy",
      "free_gb": 50
    },
    "external_mpesa": {
      "status": "healthy",
      "latency_ms": 150
    }
  }
}
```

#### `/health/ready` - Readiness probe
Returns 200 if ready to receive traffic.

#### `/health/live` - Liveness probe
Returns 200 if process is alive.

### 2.2 Structured Logging Format

```json
{
  "timestamp": "2025-11-27T12:00:00.123Z",
  "level": "info",
  "message": "Payment processed successfully",
  "logger": "Finance.PaymentService",
  "trace_id": "abc123-def456",
  "span_id": "span789",
  "school_id": 1,
  "user_id": 42,
  "request_id": "req-12345",
  "method": "POST",
  "path": "/api/v1/payments",
  "duration_ms": 150,
  "context": {
    "payment_id": 1001,
    "amount": 5000,
    "method": "mpesa"
  }
}
```

### 2.3 Database Schema

#### `application_metrics`
Aggregated metrics.
```sql
CREATE TABLE application_metrics (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    metric_name VARCHAR(150) NOT NULL,
    metric_type ENUM('counter', 'gauge', 'histogram') NOT NULL,
    labels JSON,
    value DECIMAL(20,4) NOT NULL,
    school_id INT,
    recorded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_name_time (metric_name, recorded_at),
    INDEX idx_school (school_id, recorded_at)
);
```

#### `request_traces`
Distributed traces.
```sql
CREATE TABLE request_traces (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    trace_id VARCHAR(36) NOT NULL,
    span_id VARCHAR(36) NOT NULL,
    parent_span_id VARCHAR(36),
    operation_name VARCHAR(150) NOT NULL,
    service_name VARCHAR(100) NOT NULL,
    school_id INT,
    user_id INT,
    start_time DATETIME(3) NOT NULL,
    end_time DATETIME(3),
    duration_ms INT,
    status_code INT,
    error BOOLEAN DEFAULT FALSE,
    tags JSON,
    logs JSON,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_trace (trace_id),
    INDEX idx_time (start_time),
    INDEX idx_error (error, start_time)
);
```

#### `error_events`
Error tracking.
```sql
CREATE TABLE error_events (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    error_hash VARCHAR(64) NOT NULL,
    error_type VARCHAR(255) NOT NULL,
    error_message TEXT NOT NULL,
    stack_trace TEXT,
    school_id INT,
    user_id INT,
    trace_id VARCHAR(36),
    request_path VARCHAR(255),
    request_method VARCHAR(10),
    request_body JSON,
    server_context JSON,
    occurrence_count INT DEFAULT 1,
    first_seen DATETIME DEFAULT CURRENT_TIMESTAMP,
    last_seen DATETIME DEFAULT CURRENT_TIMESTAMP,
    status ENUM('new', 'investigating', 'resolved', 'ignored') DEFAULT 'new',
    assigned_to INT,
    resolution_notes TEXT,
    INDEX idx_hash (error_hash),
    INDEX idx_status (status, last_seen),
    INDEX idx_school (school_id, last_seen)
);
```

#### `sla_metrics`
SLA tracking.
```sql
CREATE TABLE sla_metrics (
    id INT PRIMARY KEY AUTO_INCREMENT,
    school_id INT,
    period_date DATE NOT NULL,
    period_type ENUM('hourly', 'daily', 'weekly', 'monthly') NOT NULL,
    total_requests BIGINT DEFAULT 0,
    successful_requests BIGINT DEFAULT 0,
    failed_requests BIGINT DEFAULT 0,
    total_latency_ms BIGINT DEFAULT 0,
    p50_latency_ms INT,
    p95_latency_ms INT,
    p99_latency_ms INT,
    uptime_seconds INT,
    downtime_seconds INT DEFAULT 0,
    availability_percent DECIMAL(5,2),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_school_period (school_id, period_date, period_type),
    INDEX idx_date (period_date)
);
```

### 2.4 Metrics Endpoint

#### `/metrics` (Prometheus format)
```
# HELP http_requests_total Total HTTP requests
# TYPE http_requests_total counter
http_requests_total{method="GET",path="/api/v1/students",status="200"} 1523

# HELP http_request_duration_seconds HTTP request latency
# TYPE http_request_duration_seconds histogram
http_request_duration_seconds_bucket{le="0.1"} 1000
http_request_duration_seconds_bucket{le="0.5"} 1400
http_request_duration_seconds_bucket{le="1.0"} 1500

# HELP active_users Current active users
# TYPE active_users gauge
active_users{school_id="1"} 45
```

### 2.5 API Endpoints

| Method | Endpoint | Description | Access |
|:-------|:---------|:------------|:-------|
| **Health** |
| GET | `/health` | Full health check | Public |
| GET | `/health/ready` | Readiness probe | Public |
| GET | `/health/live` | Liveness probe | Public |
| **Metrics** |
| GET | `/metrics` | Prometheus metrics | Internal |
| GET | `/api/v1/monitoring/metrics` | API metrics | Admin |
| **Traces** |
| GET | `/api/v1/monitoring/traces` | Search traces | Admin |
| GET | `/api/v1/monitoring/traces/{id}` | Get trace | Admin |
| **Errors** |
| GET | `/api/v1/monitoring/errors` | List errors | Admin |
| GET | `/api/v1/monitoring/errors/{id}` | Error details | Admin |
| PUT | `/api/v1/monitoring/errors/{id}` | Update status | Admin |
| **SLA** |
| GET | `/api/v1/monitoring/sla` | SLA report | Admin |
| GET | `/api/v1/monitoring/dashboard` | Dashboard data | Admin |

### 2.6 Module Structure

```
app/Modules/Monitoring/
├── Config/
│   ├── Routes.php
│   └── Monitoring.php
├── Controllers/
│   ├── HealthController.php
│   ├── MetricsController.php
│   ├── Api/
│   │   ├── TraceController.php
│   │   ├── ErrorController.php
│   │   └── SlaController.php
│   └── Web/
│       └── MonitoringDashboardController.php
├── Models/
│   ├── ApplicationMetricModel.php
│   ├── RequestTraceModel.php
│   ├── ErrorEventModel.php
│   └── SlaMetricModel.php
├── Services/
│   ├── HealthCheckService.php
│   ├── MetricsCollectorService.php
│   ├── TracingService.php
│   ├── ErrorTrackingService.php
│   ├── SlaCalculatorService.php
│   └── AlertingService.php
├── Middleware/
│   ├── TracingMiddleware.php
│   ├── MetricsMiddleware.php
│   └── ErrorCaptureMiddleware.php
├── Collectors/
│   ├── DatabaseHealthCollector.php
│   ├── CacheHealthCollector.php
│   ├── StorageHealthCollector.php
│   └── ExternalServiceHealthCollector.php
├── Jobs/
│   ├── AggregateSlaMetricsJob.php
│   └── PurgeOldTracesJob.php
├── Database/
│   └── Migrations/
│       └── 2025-11-27-000001_CreateMonitoringTables.php
├── Views/
│   └── dashboard/
│       └── index.php
└── Tests/
    ├── Unit/
    │   └── HealthCheckServiceTest.php
    └── Feature/
        └── HealthEndpointTest.php
```

### 2.7 Integration Points

- **All Modules**: Request tracing, error capture.
- **Scheduler Module**: Metrics aggregation jobs.
- **Threads Module**: Alert notifications.
- **Foundation Module**: Logging service.

---

## Part 3: Architectural Safeguards

### 3.1 Performance Impact
- Metrics collection async where possible.
- Sampling for high-volume traces.
- Aggregate, don't store every data point.

### 3.2 Data Retention
- Traces: 7 days
- Detailed metrics: 30 days
- Aggregated metrics: 1 year
- Error events: Until resolved + 90 days

### 3.3 Security
- Health endpoints don't expose sensitive data.
- Metrics endpoint internal only.
- Error details sanitized (no secrets).

### 3.4 High Availability
- Health checks lightweight.
- Graceful degradation if monitoring fails.
- Monitoring shouldn't cause outages.

---

## Part 4: Standard Metrics

| Metric | Type | Labels | Description |
|:-------|:-----|:-------|:------------|
| `http_requests_total` | Counter | method, path, status | Total requests |
| `http_request_duration_seconds` | Histogram | method, path | Request latency |
| `active_sessions` | Gauge | school_id | Active user sessions |
| `database_queries_total` | Counter | table, operation | DB queries |
| `database_query_duration_seconds` | Histogram | table | Query latency |
| `cache_hits_total` | Counter | cache | Cache hits |
| `cache_misses_total` | Counter | cache | Cache misses |
| `queue_jobs_total` | Counter | queue, status | Job counts |
| `queue_job_duration_seconds` | Histogram | queue | Job duration |
| `external_api_calls_total` | Counter | service, status | External calls |
| `external_api_duration_seconds` | Histogram | service | Call latency |

---

## Part 5: Development Checklist

- [ ] **Health**: Endpoint implementation.
- [ ] **Health**: Component collectors.
- [ ] **Logging**: Structured format.
- [ ] **Tracing**: Middleware.
- [ ] **Tracing**: Storage and search.
- [ ] **Metrics**: Collection.
- [ ] **Metrics**: Prometheus export.
- [ ] **Errors**: Capture and aggregate.
- [ ] **SLA**: Calculation.
- [ ] **Dashboard**: UI.
- [ ] **Alerts**: Integration.
