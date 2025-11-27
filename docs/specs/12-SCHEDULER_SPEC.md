# ⏰ Cron & Job Scheduler Module Specification

**Version**: 1.0.0
**Status**: Draft
**Last Updated**: 2025-11-27

---

## Part 1: Feature Definition (The "What" & "Why")
*Target Audience: Product Owners, Stakeholders, Developers*

### 1.1 Overview
The Scheduler module is the "Heartbeat" of the ShuleLabs ecosystem. It manages scheduled tasks (cron jobs), background job queues, and automated workflows. It ensures that time-sensitive operations like report generation, notifications, data synchronization, and cleanup tasks run reliably and on schedule.

### 1.2 User Stories

- **As a System Admin**, I want to schedule recurring tasks (daily reports, weekly cleanups) via a UI, so that I don't need to edit cron files manually.
- **As a Bursar**, I want automated fee reminders sent 3 days before due dates, so that parents are notified without manual intervention.
- **As a Super Admin**, I want to monitor job execution and receive alerts on failures, so that I can address issues quickly.
- **As a Developer**, I want to queue long-running tasks (bulk imports, report generation), so that API responses remain fast.
- **As an IT Admin**, I want to retry failed jobs automatically with exponential backoff, so that transient errors are handled gracefully.

### 1.3 User Workflows

1. **Scheduled Task Configuration**:
   - Admin opens Scheduler dashboard.
   - Admin creates new scheduled job (e.g., "Daily Fee Reminder").
   - Admin selects job type, configures cron expression, and sets parameters.
   - System validates schedule and saves job.
   - Job runs at specified times.

2. **Job Monitoring**:
   - Admin views job execution history.
   - Admin sees status (success, failed, running).
   - On failure, admin views error details and stack trace.
   - Admin can manually retry failed jobs.

3. **Background Job Processing**:
   - Application queues a job (e.g., GenerateReportJob).
   - Worker picks up job from queue.
   - Worker executes job with retry logic.
   - Result logged and notification sent if configured.

4. **Alert on Failure**:
   - Job fails after max retries.
   - System sends alert to configured recipients.
   - Admin investigates and fixes issue.
   - Admin can manually re-run job.

### 1.4 Acceptance Criteria

- [ ] Jobs can be created, updated, paused, and deleted via UI and API.
- [ ] Cron expressions are validated and next run time calculated correctly.
- [ ] Jobs execute reliably at scheduled times.
- [ ] Failed jobs are retried with configurable delay and max attempts.
- [ ] Job execution logs capture start time, end time, status, and errors.
- [ ] Alerts are sent for failed jobs after max retries.
- [ ] Jobs can be manually triggered outside of schedule.
- [ ] Long-running jobs can be cancelled.
- [ ] All jobs are tenant-aware (school_id scoped where applicable).

---

## Part 2: Technical Specification (The "How")
*Target Audience: Developers, Architects*

### 2.1 Database Schema

#### `scheduled_jobs`
Defines recurring scheduled tasks.
```sql
CREATE TABLE scheduled_jobs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    school_id INT NULL,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    job_class VARCHAR(255) NOT NULL,
    job_method VARCHAR(100) DEFAULT 'handle',
    parameters JSON,
    cron_expression VARCHAR(100) NOT NULL,
    timezone VARCHAR(50) DEFAULT 'Africa/Nairobi',
    is_active BOOLEAN DEFAULT TRUE,
    run_as_user_id INT NULL,
    max_retries INT DEFAULT 3,
    retry_delay_seconds INT DEFAULT 60,
    timeout_seconds INT DEFAULT 3600,
    overlap_prevention BOOLEAN DEFAULT TRUE,
    last_run_at DATETIME,
    last_run_status ENUM('success', 'failed', 'running', 'timeout') NULL,
    next_run_at DATETIME NOT NULL,
    created_by INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (run_as_user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id),
    INDEX idx_next_run (next_run_at, is_active),
    INDEX idx_school (school_id)
);
```

#### `job_runs`
Execution history for scheduled jobs.
```sql
CREATE TABLE job_runs (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    scheduled_job_id INT NULL,
    job_class VARCHAR(255) NOT NULL,
    job_method VARCHAR(100),
    parameters JSON,
    school_id INT NULL,
    run_type ENUM('scheduled', 'manual', 'queued', 'retry') NOT NULL,
    status ENUM('pending', 'running', 'success', 'failed', 'cancelled', 'timeout') DEFAULT 'pending',
    attempt_number INT DEFAULT 1,
    started_at DATETIME,
    completed_at DATETIME,
    duration_ms INT,
    result_summary TEXT,
    error_message TEXT,
    error_trace TEXT,
    output_log TEXT,
    triggered_by INT NULL,
    worker_id VARCHAR(100),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (scheduled_job_id) REFERENCES scheduled_jobs(id) ON DELETE SET NULL,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (triggered_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_job_status (scheduled_job_id, status),
    INDEX idx_school_date (school_id, created_at),
    INDEX idx_status_created (status, created_at)
);
```

#### `job_logs`
Detailed execution logs for debugging.
```sql
CREATE TABLE job_logs (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    job_run_id BIGINT NOT NULL,
    level ENUM('debug', 'info', 'warning', 'error') DEFAULT 'info',
    message TEXT NOT NULL,
    context JSON,
    logged_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (job_run_id) REFERENCES job_runs(id) ON DELETE CASCADE,
    INDEX idx_run_level (job_run_id, level)
);
```

#### `job_queue`
Queue for background jobs.
```sql
CREATE TABLE job_queue (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    queue_name VARCHAR(100) DEFAULT 'default',
    job_class VARCHAR(255) NOT NULL,
    job_method VARCHAR(100) DEFAULT 'handle',
    payload JSON NOT NULL,
    school_id INT NULL,
    priority INT DEFAULT 0,
    attempts INT DEFAULT 0,
    max_attempts INT DEFAULT 3,
    available_at DATETIME NOT NULL,
    reserved_at DATETIME NULL,
    reserved_by VARCHAR(100),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    INDEX idx_queue_available (queue_name, available_at, reserved_at),
    INDEX idx_priority (priority DESC, available_at)
);
```

#### `job_failed`
Failed jobs for inspection and retry.
```sql
CREATE TABLE job_failed (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    queue_name VARCHAR(100),
    job_class VARCHAR(255) NOT NULL,
    payload JSON NOT NULL,
    school_id INT NULL,
    exception TEXT NOT NULL,
    failed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    INDEX idx_school_failed (school_id, failed_at)
);
```

### 2.2 Built-in Job Types

| Job Class | Description | Default Schedule |
|:----------|:------------|:-----------------|
| `ProcessScheduledReportsJob` | Generate and email scheduled reports | */5 * * * * |
| `SendFeeRemindersJob` | Send payment reminders | 0 8 * * * |
| `CleanupExpiredSessionsJob` | Remove expired sessions | 0 1 * * * |
| `SyncMpesaTransactionsJob` | Reconcile M-Pesa payments | */15 * * * * |
| `PurgeOldAuditLogsJob` | Archive old audit entries | 0 2 * * 0 |
| `RefreshAggregateSummariesJob` | Update daily fee summaries | 0 0 * * * |
| `SendLowBalanceAlertsJob` | Wallet low balance notifications | 0 9 * * * |
| `ExpireWaitlistOffersJob` | Expire unanswered admission offers | 0 10 * * * |
| `CleanupGpsLogsJob` | Purge old transport GPS data | 0 3 * * 0 |
| `GenerateDailyBackupJob` | Create database backups | 0 4 * * * |

### 2.3 API Endpoints

| Method | Endpoint | Description | Access |
|:-------|:---------|:------------|:-------|
| GET | `/api/v1/scheduler/jobs` | List scheduled jobs | Admin |
| POST | `/api/v1/scheduler/jobs` | Create scheduled job | SuperAdmin |
| GET | `/api/v1/scheduler/jobs/{id}` | Get job details | Admin |
| PUT | `/api/v1/scheduler/jobs/{id}` | Update job | SuperAdmin |
| DELETE | `/api/v1/scheduler/jobs/{id}` | Delete job | SuperAdmin |
| POST | `/api/v1/scheduler/jobs/{id}/toggle` | Enable/disable job | Admin |
| POST | `/api/v1/scheduler/jobs/{id}/run` | Trigger manual run | Admin |
| GET | `/api/v1/scheduler/jobs/{id}/runs` | Get job run history | Admin |
| GET | `/api/v1/scheduler/runs` | List all job runs | Admin |
| GET | `/api/v1/scheduler/runs/{id}` | Get run details with logs | Admin |
| POST | `/api/v1/scheduler/runs/{id}/retry` | Retry failed run | Admin |
| POST | `/api/v1/scheduler/runs/{id}/cancel` | Cancel running job | Admin |
| GET | `/api/v1/scheduler/failed` | List failed jobs | Admin |
| POST | `/api/v1/scheduler/failed/{id}/retry` | Retry failed job | Admin |
| DELETE | `/api/v1/scheduler/failed/{id}` | Delete failed job | Admin |

### 2.4 Module Structure

```
app/Modules/Scheduler/
├── Config/
│   ├── Routes.php
│   └── Services.php
├── Commands/
│   ├── ProcessScheduledJobs.php
│   ├── ProcessJobQueue.php
│   └── RetryFailedJobs.php
├── Controllers/
│   ├── Api/
│   │   ├── ScheduledJobController.php
│   │   ├── JobRunController.php
│   │   └── FailedJobController.php
│   └── Web/
│       └── SchedulerDashboardController.php
├── Models/
│   ├── ScheduledJobModel.php
│   ├── JobRunModel.php
│   ├── JobLogModel.php
│   ├── JobQueueModel.php
│   └── JobFailedModel.php
├── Services/
│   ├── SchedulerService.php
│   ├── CronExpressionParser.php
│   ├── JobDispatcher.php
│   ├── JobRunner.php
│   ├── RetryHandler.php
│   ├── AlertService.php
│   └── JobLogger.php
├── Jobs/
│   ├── BaseJob.php
│   ├── ProcessScheduledReportsJob.php
│   ├── SendFeeRemindersJob.php
│   ├── CleanupExpiredSessionsJob.php
│   ├── SyncMpesaTransactionsJob.php
│   ├── PurgeOldAuditLogsJob.php
│   ├── RefreshAggregateSummariesJob.php
│   └── ...
├── Database/
│   └── Migrations/
│       └── 2025-11-27-000001_CreateSchedulerTables.php
├── Views/
│   ├── dashboard/
│   │   └── index.php
│   ├── jobs/
│   │   ├── index.php
│   │   └── form.php
│   └── runs/
│       ├── index.php
│       └── show.php
└── Tests/
    ├── Unit/
    │   ├── CronExpressionParserTest.php
    │   └── RetryHandlerTest.php
    └── Feature/
        └── SchedulerApiTest.php
```

### 2.5 Cron Expression Support

The scheduler supports standard 5-field cron expressions:

| Field | Values | Special Characters |
|:------|:-------|:------------------|
| Minute | 0-59 | * , - / |
| Hour | 0-23 | * , - / |
| Day of Month | 1-31 | * , - / ? |
| Month | 1-12 or JAN-DEC | * , - / |
| Day of Week | 0-6 or SUN-SAT | * , - / ? |

**Examples**:
- `0 9 * * *` - Daily at 9 AM
- `0 8 * * 1-5` - Weekdays at 8 AM
- `*/15 * * * *` - Every 15 minutes
- `0 0 1 * *` - First of every month at midnight

### 2.6 Integration Points

- **All Modules**: Any module can dispatch jobs via `JobDispatcher::dispatch()`.
- **Reports Module**: Uses `ProcessScheduledReportsJob` for automated report delivery.
- **Finance Module**: Uses `SendFeeRemindersJob` for payment notifications.
- **Integrations Module**: Uses sync jobs for M-Pesa, SMS reconciliation.
- **Threads Module**: Receives alerts for failed jobs.
- **Audit Module**: All job executions are audit-logged.

---

## Part 3: Architectural Safeguards
*Target Audience: Architects, Security Engineers*

### 3.1 Overlap Prevention
- Check if job is already running before starting.
- Use database locking (`FOR UPDATE`) on scheduled_jobs row.
- Skip execution if previous run still in progress.

```php
public function runJob(int $jobId): bool
{
    $this->db->transStart();
    $job = $this->db->query('SELECT * FROM scheduled_jobs WHERE id = ? FOR UPDATE', [$jobId])->getRow();
    
    if ($job->last_run_status === 'running') {
        $this->db->transRollback();
        return false; // Already running
    }
    
    // Update to running
    $this->db->table('scheduled_jobs')->where('id', $jobId)->update(['last_run_status' => 'running']);
    $this->db->transComplete();
    
    // Execute job...
}
```

### 3.2 Retry with Exponential Backoff
- First retry after 60 seconds.
- Second retry after 120 seconds.
- Third retry after 240 seconds.
- Configurable per job.

```php
public function calculateNextRetryDelay(int $attempt, int $baseDelay): int
{
    return $baseDelay * pow(2, $attempt - 1);
}
```

### 3.3 Timeout Handling
- Set PHP `max_execution_time` per job.
- Use SIGTERM for graceful shutdown.
- Mark as "timeout" if exceeded.

### 3.4 Queue Worker Safety
- Workers self-restart after processing N jobs (prevent memory leaks).
- Workers handle SIGTERM for graceful shutdown.
- Workers log heartbeat for monitoring.

### 3.5 Tenant Context
- Jobs running for specific schools receive `school_id` context.
- TenantScope automatically applied during job execution.
- Global jobs (no school_id) run without tenant filter.

---

## Part 4: Embedded Reports for Entity Views
*Target Audience: Frontend Developers, Product Owners*

### 4.1 Scheduler Dashboard Widgets
| Widget | Description |
|:-------|:------------|
| Active Jobs | Count of enabled scheduled jobs |
| Today's Runs | Success/Failed/Pending counts |
| Failed Jobs | Jobs requiring attention |
| Next Runs | Upcoming 10 job executions |
| Average Duration | Performance metrics |

### 4.2 Job Detail View
| Field | Description |
|:------|:------------|
| Job Name | Name and description |
| Schedule | Cron expression with human-readable format |
| Last Run | Status, duration, timestamp |
| Next Run | Calculated next execution time |
| Run History | Chart of success/fail over time |
| Recent Logs | Last 100 log entries |

---

## Part 5: Test Data Strategy
*Target Audience: QA, Developers*

### 5.1 Seeding Strategy
Use `Modules\Scheduler\Database\Seeds\SchedulerSeeder` to populate:

#### Scheduled Jobs
- 10 built-in jobs with realistic schedules.
- Mix of active and inactive jobs.

#### Job Runs
- 100 historical job runs across past month.
- Mix of success (80%), failed (15%), timeout (5%).

#### Failed Jobs
- 5 failed jobs in queue for retry testing.

### 5.2 Testing Scenarios
| Scenario | Expected Outcome |
|:---------|:-----------------|
| Create job with invalid cron | Validation error returned |
| Manually trigger job | Job runs immediately |
| Job fails | Retried up to max_retries |
| Job times out | Marked as timeout |
| Overlap prevention | Second run skipped |
| Retry failed job | New run created |

---

## Part 6: Development Checklist

- [ ] **Design**: Review and approve this specification.
- [ ] **Scaffold**: Generate Controllers, Models, Commands.
- [ ] **Database**: Run migrations and verify schema.
- [ ] **Core**: Implement CronExpressionParser.
- [ ] **Core**: Implement JobRunner with retry logic.
- [ ] **Core**: Implement JobDispatcher for queue.
- [ ] **Commands**: Create ProcessScheduledJobs CLI command.
- [ ] **Commands**: Create ProcessJobQueue worker command.
- [ ] **API**: Implement CRUD endpoints for scheduled jobs.
- [ ] **Web**: Build scheduler dashboard.
- [ ] **Alerts**: Integrate with Threads for failure alerts.
- [ ] **Testing**: Write unit and feature tests.
- [ ] **Review**: Code review and merge.
