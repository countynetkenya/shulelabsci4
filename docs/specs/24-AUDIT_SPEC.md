# 📋 Audit & Compliance Module Specification

**Version**: 1.0.0
**Status**: Draft
**Last Updated**: 2025-11-27

---

## Part 1: Feature Definition (The "What" & "Why")
*Target Audience: Product Owners, Stakeholders, Developers*

### 1.1 Overview
The Audit & Compliance module is the "Watchdog" of ShuleLabs. It provides comprehensive activity logging, data change tracking with before/after state capture, cryptographic audit seals for tamper detection, configurable retention policies, compliance reporting (GDPR, data protection), and real-time alerts for suspicious activities. This module ensures accountability and supports regulatory compliance.

### 1.2 User Stories

- **As an Auditor**, I want to see all changes to financial records, so that I can verify accuracy.
- **As a DPO**, I want to generate GDPR compliance reports, so that we meet regulatory requirements.
- **As a Security Officer**, I want to receive alerts on suspicious activities, so that I can respond quickly.
- **As an Admin**, I want to search audit logs by user or entity, so that I can investigate issues.
- **As a Compliance Manager**, I want to verify audit seals, so that I can confirm data integrity.
- **As a System Admin**, I want to configure retention policies, so that old logs are archived properly.

### 1.3 User Workflows

1. **Audit Log Review**:
   - Auditor accesses audit console.
   - Auditor sets date range and filters.
   - System displays matching events.
   - Auditor drills down to see before/after.
   - Auditor exports report if needed.

2. **Seal Verification**:
   - Admin requests verification for date range.
   - System retrieves seals and events.
   - System recomputes hashes.
   - Comparison shows integrity status.
   - Alert raised if tampering detected.

3. **Data Subject Request (GDPR)**:
   - User submits data access/deletion request.
   - System collects all user data.
   - Report generated for access request.
   - Data anonymized for deletion request.
   - Audit trail maintained.

### 1.4 Acceptance Criteria

- [ ] All significant actions logged automatically.
- [ ] Before/after state captured for changes.
- [ ] Daily seals generated with hash chain.
- [ ] Seal verification detects tampering.
- [ ] Logs searchable and exportable.
- [ ] Retention policies configurable per school.
- [ ] GDPR data export available.
- [ ] GDPR data deletion/anonymization supported.
- [ ] Alerts sent for suspicious patterns.
- [ ] All data scoped by school_id.

---

## Part 2: Technical Specification (The "How")
*Target Audience: Developers, Architects*

### 2.1 Database Schema

#### `audit_events`
(Extended from Foundation module)
```sql
CREATE TABLE audit_events (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    school_id INT NOT NULL,
    user_id INT,
    session_id VARCHAR(128),
    action VARCHAR(100) NOT NULL,
    action_category ENUM('create', 'read', 'update', 'delete', 'login', 'logout', 'export', 'system') NOT NULL,
    entity_type VARCHAR(100) NOT NULL,
    entity_id INT,
    entity_name VARCHAR(255),
    before_state JSON,
    after_state JSON,
    changed_fields JSON,
    ip_address VARCHAR(45),
    user_agent VARCHAR(500),
    location_info JSON,
    trace_id VARCHAR(36),
    parent_event_id BIGINT,
    severity ENUM('info', 'warning', 'critical') DEFAULT 'info',
    is_sensitive BOOLEAN DEFAULT FALSE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (parent_event_id) REFERENCES audit_events(id) ON DELETE SET NULL,
    INDEX idx_entity (entity_type, entity_id),
    INDEX idx_school_date (school_id, created_at),
    INDEX idx_user_date (user_id, created_at),
    INDEX idx_trace (trace_id),
    INDEX idx_action (action, created_at)
);
```

#### `audit_seals`
(Extended from Foundation module)
```sql
CREATE TABLE audit_seals (
    id INT PRIMARY KEY AUTO_INCREMENT,
    school_id INT NOT NULL,
    seal_type ENUM('daily', 'weekly', 'monthly', 'custom') DEFAULT 'daily',
    seal_date DATE NOT NULL,
    period_start DATETIME NOT NULL,
    period_end DATETIME NOT NULL,
    first_event_id BIGINT NOT NULL,
    last_event_id BIGINT NOT NULL,
    event_count INT NOT NULL,
    hash_algorithm VARCHAR(20) DEFAULT 'SHA-256',
    data_hash VARCHAR(64) NOT NULL,
    previous_seal_id INT,
    previous_seal_hash VARCHAR(64),
    chain_position INT NOT NULL,
    verification_status ENUM('unverified', 'valid', 'invalid') DEFAULT 'unverified',
    last_verified_at DATETIME,
    sealed_by INT,
    sealed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (previous_seal_id) REFERENCES audit_seals(id) ON DELETE SET NULL,
    UNIQUE KEY uk_school_date_type (school_id, seal_date, seal_type),
    INDEX idx_chain (school_id, chain_position)
);
```

#### `retention_policies`
Data retention configuration.
```sql
CREATE TABLE retention_policies (
    id INT PRIMARY KEY AUTO_INCREMENT,
    school_id INT NOT NULL,
    data_category VARCHAR(100) NOT NULL,
    retention_days INT NOT NULL,
    archive_after_days INT,
    delete_after_archive_days INT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    UNIQUE KEY uk_school_category (school_id, data_category)
);
```

#### `compliance_requests`
GDPR and data subject requests.
```sql
CREATE TABLE compliance_requests (
    id INT PRIMARY KEY AUTO_INCREMENT,
    school_id INT NOT NULL,
    request_type ENUM('access', 'rectification', 'erasure', 'portability', 'objection') NOT NULL,
    requester_type ENUM('user', 'parent', 'external') NOT NULL,
    requester_id INT,
    requester_email VARCHAR(255) NOT NULL,
    subject_user_id INT NOT NULL,
    status ENUM('received', 'in_progress', 'completed', 'rejected') DEFAULT 'received',
    request_details TEXT,
    response_details TEXT,
    response_file_path VARCHAR(500),
    due_date DATE NOT NULL,
    completed_at DATETIME,
    handled_by INT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (requester_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (subject_user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (handled_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_status (status, due_date)
);
```

#### `audit_alerts`
Alert configurations.
```sql
CREATE TABLE audit_alerts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    school_id INT,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    trigger_type ENUM('pattern', 'threshold', 'anomaly') NOT NULL,
    trigger_config JSON NOT NULL,
    severity ENUM('info', 'warning', 'critical') DEFAULT 'warning',
    notification_channels JSON,
    notification_recipients JSON,
    is_active BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE
);
```

#### `audit_alert_events`
Triggered alerts.
```sql
CREATE TABLE audit_alert_events (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    alert_id INT NOT NULL,
    audit_event_id BIGINT,
    trigger_details JSON,
    acknowledged BOOLEAN DEFAULT FALSE,
    acknowledged_by INT,
    acknowledged_at DATETIME,
    resolution_notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (alert_id) REFERENCES audit_alerts(id) ON DELETE CASCADE,
    FOREIGN KEY (audit_event_id) REFERENCES audit_events(id) ON DELETE SET NULL,
    INDEX idx_alert_date (alert_id, created_at),
    INDEX idx_unacknowledged (acknowledged, created_at)
);
```

### 2.2 API Endpoints

| Method | Endpoint | Description | Access |
|:-------|:---------|:------------|:-------|
| **Audit Logs** |
| GET | `/api/v1/audit/events` | Search events | Auditor |
| GET | `/api/v1/audit/events/{id}` | Get event details | Auditor |
| GET | `/api/v1/audit/events/entity/{type}/{id}` | Events for entity | Auditor |
| GET | `/api/v1/audit/events/user/{id}` | Events by user | Auditor |
| GET | `/api/v1/audit/events/export` | Export to CSV | Auditor |
| **Seals** |
| GET | `/api/v1/audit/seals` | List seals | Auditor |
| POST | `/api/v1/audit/seals/generate` | Generate seal | Admin |
| POST | `/api/v1/audit/seals/{id}/verify` | Verify seal | Auditor |
| GET | `/api/v1/audit/seals/chain/verify` | Verify chain | Auditor |
| **Compliance** |
| GET | `/api/v1/compliance/requests` | List requests | DPO |
| POST | `/api/v1/compliance/requests` | Create request | User |
| GET | `/api/v1/compliance/requests/{id}` | Get details | DPO |
| POST | `/api/v1/compliance/requests/{id}/process` | Process request | DPO |
| GET | `/api/v1/compliance/reports/gdpr` | GDPR report | DPO |
| **Retention** |
| GET | `/api/v1/audit/retention` | Get policies | Admin |
| PUT | `/api/v1/audit/retention` | Update policies | Admin |
| **Alerts** |
| GET | `/api/v1/audit/alerts` | List alerts | Security |
| POST | `/api/v1/audit/alerts` | Create alert | Security |
| GET | `/api/v1/audit/alerts/events` | Alert events | Security |
| POST | `/api/v1/audit/alerts/events/{id}/acknowledge` | Acknowledge | Security |

### 2.3 Module Structure

```
app/Modules/Audit/
├── Config/
│   ├── Routes.php
│   └── Audit.php
├── Controllers/
│   ├── Api/
│   │   ├── AuditEventController.php
│   │   ├── SealController.php
│   │   ├── ComplianceController.php
│   │   ├── RetentionController.php
│   │   └── AlertController.php
│   └── Web/
│       └── AuditDashboardController.php
├── Models/
│   ├── AuditEventModel.php
│   ├── AuditSealModel.php
│   ├── RetentionPolicyModel.php
│   ├── ComplianceRequestModel.php
│   ├── AuditAlertModel.php
│   └── AuditAlertEventModel.php
├── Services/
│   ├── AuditLogService.php
│   ├── SealGeneratorService.php
│   ├── SealVerificationService.php
│   ├── ComplianceService.php
│   ├── DataExportService.php
│   ├── DataAnonymizationService.php
│   ├── RetentionService.php
│   ├── AlertDetectionService.php
│   └── AnomalyDetectorService.php
├── Jobs/
│   ├── GenerateDailySealsJob.php
│   ├── ProcessRetentionJob.php
│   └── AlertMonitoringJob.php
├── Database/
│   └── Migrations/
│       └── 2025-11-27-000001_CreateAuditTables.php
├── Views/
│   ├── dashboard/
│   ├── events/
│   ├── seals/
│   └── compliance/
└── Tests/
    ├── Unit/
    │   └── SealVerificationTest.php
    └── Feature/
        └── AuditApiTest.php
```

### 2.4 Integration Points

- **Foundation Module**: Core audit service.
- **All Modules**: Automatic event logging.
- **Scheduler Module**: Daily seal generation job.
- **Threads Module**: Alert notifications.
- **Security Module**: Login/logout events.

---

## Part 3: Architectural Safeguards

### 3.1 Immutability
- Audit events are append-only.
- No update or delete operations.
- Archive old data, don't delete.

### 3.2 Hash Chain
- Each seal includes previous seal hash.
- Breaking chain detectable.
- Daily verification recommended.

### 3.3 Sensitive Data
- Mask PII in logs.
- Encrypt sensitive fields.
- Access to audit logs restricted.

### 3.4 GDPR Compliance
- Data export within 30 days.
- Anonymization preserves audit trail.
- Consent tracking maintained.

---

## Part 4: Test Data Strategy

### 4.1 Seeding Strategy
- 1000 sample audit events.
- 30 days of seals.
- Sample compliance requests.
- Alert configurations.

### 4.2 Testing Scenarios
| Scenario | Expected Outcome |
|:---------|:-----------------|
| Verify valid seal | Status: valid |
| Verify tampered data | Status: invalid |
| Export user data | Complete data package |
| Anonymize user | References cleared |
| Alert threshold | Notification sent |

---

## Part 5: Development Checklist

- [x] **Events**: Logging service.
- [ ] **Events**: Full before/after capture.
- [ ] **Seals**: Daily generation.
- [ ] **Seals**: Verification.
- [ ] **Seals**: Chain validation.
- [ ] **Compliance**: GDPR export.
- [ ] **Compliance**: Anonymization.
- [ ] **Retention**: Policy enforcement.
- [ ] **Alerts**: Pattern detection.
- [ ] **Alerts**: Notifications.
