# ✅ Approval Workflows (Maker-Checker) Module Specification

**Version**: 1.0.0
**Status**: Draft
**Last Updated**: 2025-11-27

---

## Part 1: Feature Definition (The "What" & "Why")
*Target Audience: Product Owners, Stakeholders, Developers*

### 1.1 Overview
The Approval Workflows module implements the "Maker-Checker" pattern for sensitive operations. It ensures that critical actions (large payments, data changes, user permissions) require approval from authorized personnel before execution. This module provides workflow definition, multi-level approvals, routing rules, notifications, escalation, and delegation support.

### 1.2 User Stories

- **As a Principal**, I want high-value payments to require my approval, so that I maintain financial control.
- **As a Bursar**, I want to submit payment requests that go through approval, so that the process is documented.
- **As an Admin**, I want to define approval workflows with multiple levels, so that proper oversight exists.
- **As an Approver**, I want to see pending items and approve/reject with notes, so that I can fulfill my role.
- **As a User**, I want to track the status of my requests, so that I know where they are in the process.
- **As a Manager**, I want to delegate my approval authority during leave, so that work continues.

### 1.3 User Workflows

1. **Submit for Approval**:
   - User performs action requiring approval (e.g., large expense).
   - System creates approval request.
   - System determines approval path.
   - First approver notified.
   - Request appears in approver's queue.

2. **Approval Process**:
   - Approver reviews request details.
   - Approver approves or rejects with notes.
   - If approved and more levels exist, next approver notified.
   - If rejected, requester notified with reason.
   - If final approval, action is executed.

3. **Escalation**:
   - Approver doesn't respond within SLA.
   - System escalates to next level.
   - Original approver notified of escalation.
   - Higher-level approver can approve.

4. **Delegation**:
   - Manager going on leave.
   - Manager delegates authority to another user.
   - Delegated items route to delegate.
   - Audit trail shows delegation.

### 1.4 Acceptance Criteria

- [ ] Approval workflows configurable per school.
- [ ] Multi-level approval chains supported.
- [ ] Routing based on amount, type, or custom rules.
- [ ] Approvers notified via preferred channels.
- [ ] Rejection includes mandatory reason.
- [ ] Timeout triggers escalation.
- [ ] Delegation of authority supported.
- [ ] Complete audit trail maintained.
- [ ] Pending requests visible in dashboard.
- [ ] All data scoped by school_id.

---

## Part 2: Technical Specification (The "How")
*Target Audience: Developers, Architects*

### 2.1 Database Schema

#### `approval_workflows`
Workflow definitions.
```sql
CREATE TABLE approval_workflows (
    id INT PRIMARY KEY AUTO_INCREMENT,
    school_id INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    code VARCHAR(50) NOT NULL,
    entity_type VARCHAR(100) NOT NULL,
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_by INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id),
    UNIQUE KEY uk_school_code (school_id, code),
    INDEX idx_entity (entity_type)
);
```

#### `approval_stages`
Stages within a workflow.
```sql
CREATE TABLE approval_stages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    workflow_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    stage_order INT NOT NULL,
    approver_type ENUM('user', 'role', 'department_head', 'custom') NOT NULL,
    approver_id INT,
    approver_role_id INT,
    approval_mode ENUM('any', 'all', 'majority') DEFAULT 'any',
    sla_hours INT DEFAULT 24,
    auto_approve BOOLEAN DEFAULT FALSE,
    auto_approve_condition JSON,
    skip_condition JSON,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (workflow_id) REFERENCES approval_workflows(id) ON DELETE CASCADE,
    FOREIGN KEY (approver_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (approver_role_id) REFERENCES roles(id) ON DELETE SET NULL,
    UNIQUE KEY uk_workflow_order (workflow_id, stage_order)
);
```

#### `workflow_routing_rules`
Routing conditions.
```sql
CREATE TABLE workflow_routing_rules (
    id INT PRIMARY KEY AUTO_INCREMENT,
    workflow_id INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    condition_type ENUM('amount', 'field', 'expression') NOT NULL,
    condition_config JSON NOT NULL,
    target_workflow_id INT,
    skip_stages JSON,
    priority INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (workflow_id) REFERENCES approval_workflows(id) ON DELETE CASCADE,
    FOREIGN KEY (target_workflow_id) REFERENCES approval_workflows(id) ON DELETE SET NULL,
    INDEX idx_priority (workflow_id, priority DESC)
);
```

#### `approval_requests`
Pending and processed requests.
```sql
CREATE TABLE approval_requests (
    id INT PRIMARY KEY AUTO_INCREMENT,
    school_id INT NOT NULL,
    workflow_id INT NOT NULL,
    current_stage_id INT,
    entity_type VARCHAR(100) NOT NULL,
    entity_id INT NOT NULL,
    entity_data JSON,
    requester_id INT NOT NULL,
    status ENUM('pending', 'approved', 'rejected', 'cancelled', 'escalated') DEFAULT 'pending',
    priority ENUM('low', 'normal', 'high', 'urgent') DEFAULT 'normal',
    due_at DATETIME,
    completed_at DATETIME,
    final_approver_id INT,
    final_notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (workflow_id) REFERENCES approval_workflows(id) ON DELETE RESTRICT,
    FOREIGN KEY (current_stage_id) REFERENCES approval_stages(id) ON DELETE SET NULL,
    FOREIGN KEY (requester_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (final_approver_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_status (school_id, status),
    INDEX idx_requester (requester_id),
    INDEX idx_entity (entity_type, entity_id)
);
```

#### `approval_actions`
Individual approval/rejection actions.
```sql
CREATE TABLE approval_actions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    request_id INT NOT NULL,
    stage_id INT NOT NULL,
    approver_id INT NOT NULL,
    action ENUM('approve', 'reject', 'return', 'delegate', 'escalate') NOT NULL,
    notes TEXT,
    delegated_to INT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (request_id) REFERENCES approval_requests(id) ON DELETE CASCADE,
    FOREIGN KEY (stage_id) REFERENCES approval_stages(id) ON DELETE CASCADE,
    FOREIGN KEY (approver_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (delegated_to) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_request (request_id),
    INDEX idx_approver (approver_id)
);
```

#### `approval_delegations`
Delegation of authority.
```sql
CREATE TABLE approval_delegations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    school_id INT NOT NULL,
    delegator_id INT NOT NULL,
    delegate_id INT NOT NULL,
    workflow_id INT,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    reason TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (delegator_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (delegate_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (workflow_id) REFERENCES approval_workflows(id) ON DELETE CASCADE,
    INDEX idx_active (is_active, start_date, end_date)
);
```

### 2.2 API Endpoints

| Method | Endpoint | Description | Access |
|:-------|:---------|:------------|:-------|
| **Workflows** |
| GET | `/api/v1/approvals/workflows` | List workflows | Admin |
| POST | `/api/v1/approvals/workflows` | Create workflow | Admin |
| GET | `/api/v1/approvals/workflows/{id}` | Get details | Admin |
| PUT | `/api/v1/approvals/workflows/{id}` | Update workflow | Admin |
| POST | `/api/v1/approvals/workflows/{id}/stages` | Add stage | Admin |
| **Requests** |
| GET | `/api/v1/approvals/requests` | My requests | User |
| GET | `/api/v1/approvals/requests/pending` | My pending actions | Approver |
| POST | `/api/v1/approvals/requests` | Submit request | User |
| GET | `/api/v1/approvals/requests/{id}` | Request details | User/Approver |
| POST | `/api/v1/approvals/requests/{id}/approve` | Approve | Approver |
| POST | `/api/v1/approvals/requests/{id}/reject` | Reject | Approver |
| POST | `/api/v1/approvals/requests/{id}/return` | Return for revision | Approver |
| POST | `/api/v1/approvals/requests/{id}/cancel` | Cancel request | Requester |
| **Delegation** |
| GET | `/api/v1/approvals/delegations` | My delegations | User |
| POST | `/api/v1/approvals/delegations` | Create delegation | User |
| DELETE | `/api/v1/approvals/delegations/{id}` | Cancel delegation | User |

### 2.3 Module Structure

```
app/Modules/ApprovalWorkflows/
├── Config/
│   ├── Routes.php
│   └── Workflows.php
├── Controllers/
│   ├── Api/
│   │   ├── WorkflowController.php
│   │   ├── RequestController.php
│   │   └── DelegationController.php
│   └── Web/
│       └── ApprovalDashboardController.php
├── Models/
│   ├── ApprovalWorkflowModel.php
│   ├── ApprovalStageModel.php
│   ├── WorkflowRoutingRuleModel.php
│   ├── ApprovalRequestModel.php
│   ├── ApprovalActionModel.php
│   └── ApprovalDelegationModel.php
├── Services/
│   ├── WorkflowService.php
│   ├── RequestService.php
│   ├── RoutingService.php
│   ├── ApproverResolverService.php
│   ├── EscalationService.php
│   ├── DelegationService.php
│   └── NotificationService.php
├── Traits/
│   └── RequiresApprovalTrait.php
├── Jobs/
│   ├── CheckEscalationsJob.php
│   └── SendReminderJob.php
├── Database/
│   └── Migrations/
│       └── 2025-11-27-000001_CreateApprovalTables.php
├── Views/
│   ├── dashboard/
│   ├── workflows/
│   └── requests/
└── Tests/
    ├── Unit/
    │   └── RoutingServiceTest.php
    └── Feature/
        └── ApprovalApiTest.php
```

### 2.4 Sample Workflow Configurations

#### High-Value Payment Approval
```json
{
  "code": "payment_approval",
  "entity_type": "finance_payment",
  "routing_rules": [
    {
      "condition": "amount >= 100000",
      "stages": ["finance_manager", "principal", "board_treasurer"]
    },
    {
      "condition": "amount >= 50000",
      "stages": ["finance_manager", "principal"]
    },
    {
      "condition": "amount >= 10000",
      "stages": ["finance_manager"]
    }
  ]
}
```

### 2.5 Integration Points

- **Finance Module**: Payment approvals.
- **HR Module**: Leave approvals.
- **Inventory Module**: Transfer approvals.
- **Threads Module**: Approval notifications.
- **Scheduler Module**: Escalation checks.
- **Foundation Module**: Audit logging.

---

## Part 3: Architectural Safeguards

### 3.1 Workflow Integrity
- Workflow modifications don't affect in-progress requests.
- Version workflows for tracking.
- Deleted workflows remain for historical requests.

### 3.2 Approval Authority
- Verify approver has authority at time of action.
- Check delegation validity.
- Prevent self-approval.

### 3.3 Audit Trail
- Every action logged with timestamp and user.
- Notes mandatory for rejections.
- Complete history viewable.

### 3.4 SLA Enforcement
- Track time at each stage.
- Alert before SLA breach.
- Auto-escalate on breach.

---

## Part 4: Test Data Strategy

### 4.1 Seeding Strategy
- 5 sample workflows.
- 20 pending requests.
- 50 completed requests.
- Active delegations.

### 4.2 Testing Scenarios
| Scenario | Expected Outcome |
|:---------|:-----------------|
| Submit request | First approver notified |
| Approve at level 1 | Advances to level 2 |
| Reject | Requester notified |
| SLA breach | Escalated |
| Delegation active | Delegate can approve |

---

## Part 5: Development Checklist

- [ ] **Workflows**: CRUD implementation.
- [ ] **Stages**: Multi-level support.
- [ ] **Routing**: Rule-based routing.
- [ ] **Requests**: Submit and track.
- [ ] **Actions**: Approve/reject flow.
- [ ] **Escalation**: SLA monitoring.
- [ ] **Delegation**: Authority transfer.
- [ ] **Notifications**: Integration.
- [ ] **Dashboard**: Pending items view.
