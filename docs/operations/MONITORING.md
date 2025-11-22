# 📊 Monitoring Operations Guide

**Last Updated**: 2025-11-22  
**Status**: Baseline Infrastructure Complete

## Overview

This guide covers operational aspects of monitoring and observability in ShuleLabs. It focuses on setting up, maintaining, and troubleshooting the monitoring infrastructure.

## Table of Contents

- [Overview](#overview)
- [Monitoring Stack](#monitoring-stack)
- [Baseline Observability Infrastructure](#baseline-observability-infrastructure)
- [Log Aggregation](#log-aggregation)
- [Health Checks](#health-checks)
- [Metrics Collection](#metrics-collection)
- [Alerting](#alerting)
- [Dashboards](#dashboards)
- [Troubleshooting](#troubleshooting)
- [References](#references)

## Monitoring Stack

### Current Stack (Phase 1/2 - Baseline)

- **Logging**: File-based logs with structured JSON format
- **Log Aggregation**: File tailing or ELK stack (optional)
- **Health Checks**: Built-in `/health` endpoints
- **Metrics**: Custom metrics exposed via `/metrics` endpoints
- **Uptime Monitoring**: External service (UptimeRobot, Pingdom) or internal cron checks
- **Error Tracking**: Logged to files and audit trail

### Future Stack (Phase 2C/3 - Advanced)

- **APM**: New Relic, DataDog, or Elastic APM
- **Dashboards**: Grafana with Prometheus
- **Alerting**: PagerDuty, OpsGenie, or Slack integration
- **Distributed Tracing**: Jaeger or Zipkin (for microservices architecture)

## Baseline Observability Infrastructure

### Log Configuration

**Log Location**:
```
writable/logs/
├── log-2025-11-22.log    # Daily log files
├── log-2025-11-23.log
└── errors/
    └── error-2025-11-22.log  # Error-specific logs
```

**Log Rotation**:
- Daily rotation enabled by default (CI4 configuration)
- Retention: 30 days (configurable in `app/Config/Logger.php`)
- Archives: Compress logs older than 7 days

**Log Format**:
All logs use structured JSON format with standard fields:
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
  "metadata": {}
}
```

### Standard Log Fields Reference

See [Feature Documentation](../features/25-MONITORING.md#standard-log-fields) for complete field definitions.

## Log Aggregation

### File-Based (Default)

**Setup**:
1. Logs written to `writable/logs/`
2. Parse logs with tools like `jq` for JSON filtering
3. Monitor with `tail -f` for real-time viewing

**Example Commands**:
```bash
# Watch all logs in real-time
tail -f writable/logs/log-$(date +%Y-%m-%d).log

# Filter by service
cat writable/logs/log-*.log | jq 'select(.service == "learning.students")'

# Filter errors for a specific tenant
cat writable/logs/log-*.log | jq 'select(.tenant_id == "school-1" and .level == "ERROR")'

# Count errors by service
cat writable/logs/log-*.log | jq -r 'select(.level == "ERROR") | .service' | sort | uniq -c
```

### ELK Stack (Optional)

**Setup**:
1. Install Elasticsearch, Logstash, Kibana
2. Configure Filebeat to ship logs to Logstash
3. Parse JSON logs in Logstash
4. Index in Elasticsearch
5. Visualize in Kibana

**Logstash Configuration**:
```ruby
input {
  file {
    path => "/path/to/shulelabs/writable/logs/log-*.log"
    codec => "json"
    type => "shulelabs"
  }
}

filter {
  if [type] == "shulelabs" {
    json {
      source => "message"
    }
    date {
      match => ["timestamp", "ISO8601"]
      target => "@timestamp"
    }
  }
}

output {
  elasticsearch {
    hosts => ["localhost:9200"]
    index => "shulelabs-%{+YYYY.MM.dd}"
  }
}
```

## Health Checks

### Endpoint

**URL**: `GET /health`

**Response (Healthy)**:
```json
{
  "status": "healthy",
  "timestamp": "2025-11-22T10:52:31.028Z",
  "checks": {
    "database": true,
    "cache": true,
    "disk": true
  }
}
```

**Response (Unhealthy)**:
```json
{
  "status": "unhealthy",
  "timestamp": "2025-11-22T10:52:31.028Z",
  "checks": {
    "database": true,
    "cache": false,
    "disk": true
  }
}
```

### Monitoring Health Checks

**Cron Job** (every 5 minutes):
```bash
#!/bin/bash
# /etc/cron.d/shulelabs-health

*/5 * * * * /usr/bin/curl -s https://shulelabs.example.com/health | jq -e '.status == "healthy"' || /usr/local/bin/alert-admin.sh
```

**Alert Script Example** (`/usr/local/bin/alert-admin.sh`):
```bash
#!/bin/bash
# Simple alert script - customize for your environment

MESSAGE="$1"
TIMESTAMP=$(date '+%Y-%m-%d %H:%M:%S')
LOG_FILE="/var/log/shulelabs-alerts.log"

# Log the alert
echo "[$TIMESTAMP] $MESSAGE" >> "$LOG_FILE"

# Send email alert (requires mailutils or sendmail)
if command -v mail &> /dev/null; then
    echo "$MESSAGE at $TIMESTAMP" | mail -s "ShuleLabs Alert" admin@example.com
fi

# Send Slack notification (optional, requires webhook URL)
if [ -n "$SLACK_WEBHOOK_URL" ]; then
    curl -X POST -H 'Content-type: application/json' \
        --data "{\"text\":\"ShuleLabs Alert: $MESSAGE\"}" \
        "$SLACK_WEBHOOK_URL"
fi

# Send SMS via Africa's Talking (optional)
# if [ -n "$AFRICASTALKING_API_KEY" ]; then
#     curl -X POST "https://api.africastalking.com/version1/messaging" \
#         -H "apiKey: $AFRICASTALKING_API_KEY" \
#         -d "username=sandbox" \
#         -d "to=+254712345678" \
#         -d "message=ShuleLabs Alert: $MESSAGE"
# fi
```

Make the script executable:
```bash
chmod +x /usr/local/bin/alert-admin.sh
```

**External Monitoring**:
- UptimeRobot: Monitor `/health` endpoint every 5 minutes
- Pingdom: Monitor with alerting to email/SMS
- AWS Route 53: Health checks for failover routing

## Metrics Collection

### Metrics Endpoint

**URL**: `GET /metrics`

**Response** (Prometheus format):
```
# TYPE http_requests_total counter
http_requests_total{method="POST",endpoint="/api/v1/students",status="200",tenant="school-1"} 1523

# TYPE http_request_duration_seconds histogram
http_request_duration_seconds_bucket{method="POST",endpoint="/api/v1/students",le="0.1"} 1200
http_request_duration_seconds_bucket{method="POST",endpoint="/api/v1/students",le="0.5"} 1520
http_request_duration_seconds_sum{method="POST",endpoint="/api/v1/students"} 68.3
http_request_duration_seconds_count{method="POST",endpoint="/api/v1/students"} 1523

# TYPE db_queries_total counter
db_queries_total{service="learning",query_type="select"} 8432
db_queries_total{service="learning",query_type="insert"} 1523

# TYPE db_query_duration_seconds histogram
db_query_duration_seconds_sum{service="learning",query_type="select"} 21.4
db_query_duration_seconds_count{service="learning",query_type="select"} 8432
```

### Prometheus Setup (Optional)

**prometheus.yml**:
```yaml
global:
  scrape_interval: 15s

scrape_configs:
  - job_name: 'shulelabs'
    static_configs:
      - targets: ['localhost:8080']
    metrics_path: '/metrics'
```

## Alerting

### Error Rate Alerts

**Threshold**: >5% error rate over 5 minutes

**Alert Command** (cron):
```bash
#!/bin/bash
# Check error rate every 5 minutes

TOTAL=$(cat /var/log/shulelabs/log-$(date +%Y-%m-%d).log | jq -r 'select(.level == "INFO" or .level == "ERROR")' | wc -l)
ERRORS=$(cat /var/log/shulelabs/log-$(date +%Y-%m-%d).log | jq -r 'select(.level == "ERROR")' | wc -l)

if [ $TOTAL -gt 0 ]; then
  ERROR_RATE=$(echo "scale=2; ($ERRORS / $TOTAL) * 100" | bc)
  if (( $(echo "$ERROR_RATE > 5" | bc -l) )); then
    /usr/local/bin/send-alert.sh "High error rate: $ERROR_RATE%"
  fi
fi
```

### Disk Space Alerts

**Threshold**: >90% disk usage

**Alert Command**:
```bash
#!/bin/bash
USAGE=$(df / | tail -1 | awk '{print $5}' | sed 's/%//')
if [ $USAGE -gt 90 ]; then
  /usr/local/bin/send-alert.sh "Disk usage critical: $USAGE%"
fi
```

### Database Connection Alerts

**Threshold**: Health check fails 3 times in a row

**Alert Logic**:
```bash
#!/bin/bash
FAILURES=0
for i in {1..3}; do
  curl -s https://shulelabs.example.com/health | jq -e '.checks.database == true' || ((FAILURES++))
  sleep 10
done

if [ $FAILURES -eq 3 ]; then
  /usr/local/bin/send-alert.sh "Database connection failed 3 times"
fi
```

## Dashboards

### Simple Dashboard (HTML)

Create a simple dashboard showing key metrics:

```html
<!DOCTYPE html>
<html>
<head>
  <title>ShuleLabs Monitoring</title>
  <meta http-equiv="refresh" content="30">
</head>
<body>
  <h1>ShuleLabs Health Dashboard</h1>
  <iframe src="/health" width="100%" height="200"></iframe>
  <iframe src="/metrics" width="100%" height="600"></iframe>
</body>
</html>
```

### Grafana Dashboard (Advanced)

**Sample Dashboard JSON**:
```json
{
  "dashboard": {
    "title": "ShuleLabs Overview",
    "panels": [
      {
        "title": "Request Rate",
        "targets": [
          {"expr": "rate(http_requests_total[5m])"}
        ]
      },
      {
        "title": "Error Rate",
        "targets": [
          {"expr": "rate(http_errors_total[5m])"}
        ]
      },
      {
        "title": "Response Time (p95)",
        "targets": [
          {"expr": "histogram_quantile(0.95, http_request_duration_seconds_bucket)"}
        ]
      },
      {
        "title": "Database Query Time",
        "targets": [
          {"expr": "rate(db_query_duration_seconds_sum[5m]) / rate(db_query_duration_seconds_count[5m])"}
        ]
      }
    ]
  }
}
```

## Troubleshooting

### High Error Rate

**Symptoms**: Error rate >5%, alerts firing

**Steps**:
1. Check recent logs for error patterns:
   ```bash
   cat writable/logs/log-*.log | jq 'select(.level == "ERROR")' | tail -20
   ```

2. Group errors by service:
   ```bash
   cat writable/logs/log-*.log | jq -r 'select(.level == "ERROR") | .service' | sort | uniq -c
   ```

3. Check for tenant-specific issues:
   ```bash
   cat writable/logs/log-*.log | jq 'select(.level == "ERROR" and .tenant_id == "school-1")'
   ```

4. Review stack traces and fix underlying issues

### Slow Response Times

**Symptoms**: p95 latency >500ms, users complaining

**Steps**:
1. Identify slow endpoints:
   ```bash
   cat writable/logs/log-*.log | jq 'select(.duration_ms > 500)' | jq -r '.action' | sort | uniq -c
   ```

2. Check database query times:
   ```bash
   cat writable/logs/log-*.log | jq 'select(.metadata.db_query_time > 100)'
   ```

3. Enable query logging (temporarily):
   ```php
   // In app/Config/Database.php
   public array $default = [
       // ...
       'DBDebug' => true,  // Enable query logging
   ];
   ```

4. Optimize slow queries (add indexes, rewrite queries)

### Database Connection Issues

**Symptoms**: Health check failing, database errors

**Steps**:
1. Check database server status:
   ```bash
   systemctl status mysql
   ```

2. Test connection manually:
   ```bash
   mysql -u username -p -h localhost -e "SELECT 1"
   ```

3. Check connection pool exhaustion:
   ```bash
   mysql -u root -p -e "SHOW PROCESSLIST"
   ```

4. Review database logs:
   ```bash
   tail -f /var/log/mysql/error.log
   ```

### Disk Space Issues

**Symptoms**: Disk >90% full, logs not rotating

**Steps**:
1. Find large files:
   ```bash
   du -h writable/logs | sort -h | tail -10
   ```

2. Compress old logs:
   ```bash
   find writable/logs -name "log-*.log" -mtime +7 -exec gzip {} \;
   ```

3. Delete old archives:
   ```bash
   find writable/logs -name "*.gz" -mtime +30 -delete
   ```

4. Increase disk space or adjust retention policies:
   - Edit `app/Config/Logger.php` to reduce retention:
     ```php
     public array $handlers = [
         'file' => [
             'class' => FileHandler::class,
             'permissions' => 0644,
             'path' => WRITEPATH . 'logs/',
             'fileExtension' => 'log',
             'dateFormat' => 'Y-m-d',
             'threshold' => 'info',
             'maxFiles' => 7,  // Keep only 7 days of logs (default: 30)
         ],
     ];
     ```
   - Set up automated cleanup cron:
     ```bash
     # /etc/cron.daily/shulelabs-log-cleanup
     #!/bin/bash
     # Compress logs older than 7 days
     find /path/to/shulelabs/writable/logs -name "log-*.log" -mtime +7 -exec gzip {} \;
     # Delete compressed logs older than 30 days
     find /path/to/shulelabs/writable/logs -name "*.gz" -mtime +30 -delete
     ```

## Best Practices

1. **Always use structured logging** with standard fields
2. **Include tenant context** in every log entry for multi-tenant visibility
3. **Monitor health checks** continuously (every 5 minutes minimum)
4. **Set up alerts** for critical issues (error rate, disk space, database)
5. **Review logs daily** for unusual patterns
6. **Rotate logs** to prevent disk exhaustion
7. **Test alerting** regularly to ensure notifications work
8. **Document incidents** and their resolutions

## References

- [Monitoring Feature Documentation](../features/25-MONITORING.md)
- [Architecture](../ARCHITECTURE.md)
- [Master Implementation Plan](../02-MASTER-IMPLEMENTATION-PLAN.md)
- [Troubleshooting Guide](TROUBLESHOOTING.md)

---

**Version**: 1.0.0
