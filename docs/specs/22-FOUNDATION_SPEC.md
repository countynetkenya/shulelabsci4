# 🏗️ Foundation Module Specification

**Version**: 1.0.0
**Status**: Implemented (Documentation)
**Last Updated**: 2025-11-27

---

## Part 1: Feature Definition (The "What" & "Why")
*Target Audience: Product Owners, Stakeholders, Developers*

### 1.1 Overview
The Foundation module is the "Bedrock" of ShuleLabs. It provides core infrastructure services that all other modules depend on, including tenant resolution, audit service, double-entry ledger service, QR code generation, maker-checker approval workflows, and the integration registry. These services ensure consistency, security, and maintainability across the entire platform.

### 1.2 Core Services

#### Tenant Context Service
Manages multi-tenant isolation, resolving the current school context from subdomain, session, or API token, and providing it to all downstream services.

#### Audit Service
Captures all significant actions with cryptographic sealing for tamper detection, enabling complete audit trails for compliance.

#### Ledger Service
Implements double-entry accounting principles, ensuring financial integrity with balanced transactions and immutable journal entries.

#### QR Code Service
Generates and validates QR codes for various use cases (student IDs, inventory items, receipts, documents).

#### Approval Workflow Service
Implements maker-checker patterns for sensitive operations requiring multi-level authorization.

#### Integration Registry
Central registry for all external integrations, managing adapters, configurations, and health status.

### 1.3 User Stories

- **As a Developer**, I want a consistent way to get the current school context, so that all my queries are properly scoped.
- **As an Auditor**, I want to verify that audit logs haven't been tampered with, so that I can trust the trail.
- **As a Finance Officer**, I want all financial transactions to follow double-entry principles, so that books are always balanced.
- **As a Store Manager**, I want to scan QR codes on inventory items, so that tracking is efficient.
- **As a Principal**, I want certain operations to require my approval, so that I maintain oversight.

### 1.4 Acceptance Criteria

- [ ] Tenant context available in all requests.
- [ ] Audit entries capture before/after state.
- [ ] Audit seals detect tampering.
- [ ] Ledger enforces balanced transactions.
- [ ] QR codes generated and scannable.
- [ ] Approval workflows configurable.
- [ ] Integration registry tracks all providers.
- [ ] All services accessible via dependency injection.

---

## Part 2: Technical Specification (The "How")
*Target Audience: Developers, Architects*

### 2.1 Database Schema

#### `tenant_contexts`
Active tenant sessions.
```sql
CREATE TABLE tenant_contexts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    session_id VARCHAR(128) NOT NULL,
    school_id INT NOT NULL,
    user_id INT,
    resolved_via ENUM('subdomain', 'session', 'token', 'header') NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    INDEX idx_session (session_id),
    INDEX idx_expires (expires_at)
);
```

#### `audit_events`
Comprehensive audit trail.
```sql
CREATE TABLE audit_events (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    school_id INT NOT NULL,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(100) NOT NULL,
    entity_id INT,
    before_state JSON,
    after_state JSON,
    changed_fields JSON,
    ip_address VARCHAR(45),
    user_agent VARCHAR(500),
    trace_id VARCHAR(36),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_entity (entity_type, entity_id),
    INDEX idx_school_date (school_id, created_at),
    INDEX idx_trace (trace_id)
);
```

#### `audit_seals`
Cryptographic seals for audit batches.
```sql
CREATE TABLE audit_seals (
    id INT PRIMARY KEY AUTO_INCREMENT,
    school_id INT NOT NULL,
    seal_date DATE NOT NULL,
    first_event_id BIGINT NOT NULL,
    last_event_id BIGINT NOT NULL,
    event_count INT NOT NULL,
    hash_algorithm VARCHAR(20) DEFAULT 'SHA-256',
    data_hash VARCHAR(64) NOT NULL,
    previous_seal_hash VARCHAR(64),
    sealed_by INT,
    sealed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    UNIQUE KEY uk_school_date (school_id, seal_date)
);
```

#### `ledger_accounts`
Chart of accounts.
```sql
CREATE TABLE ledger_accounts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    school_id INT NOT NULL,
    account_code VARCHAR(20) NOT NULL,
    name VARCHAR(150) NOT NULL,
    account_type ENUM('asset', 'liability', 'equity', 'revenue', 'expense') NOT NULL,
    parent_id INT,
    is_header BOOLEAN DEFAULT FALSE,
    normal_balance ENUM('debit', 'credit') NOT NULL,
    current_balance DECIMAL(15,2) DEFAULT 0.00,
    is_active BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES ledger_accounts(id) ON DELETE SET NULL,
    UNIQUE KEY uk_school_code (school_id, account_code)
);
```

#### `ledger_journals`
Journal entry headers.
```sql
CREATE TABLE ledger_journals (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    school_id INT NOT NULL,
    journal_number VARCHAR(50) NOT NULL,
    journal_date DATE NOT NULL,
    description VARCHAR(500) NOT NULL,
    reference_type VARCHAR(100),
    reference_id INT,
    total_amount DECIMAL(15,2) NOT NULL,
    status ENUM('draft', 'posted', 'reversed') DEFAULT 'draft',
    posted_by INT,
    posted_at DATETIME,
    created_by INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (posted_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id),
    UNIQUE KEY uk_school_number (school_id, journal_number),
    INDEX idx_date (journal_date),
    INDEX idx_reference (reference_type, reference_id)
);
```

#### `ledger_entries`
Journal entry lines (debits and credits).
```sql
CREATE TABLE ledger_entries (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    journal_id BIGINT NOT NULL,
    account_id INT NOT NULL,
    debit_amount DECIMAL(15,2) DEFAULT 0.00,
    credit_amount DECIMAL(15,2) DEFAULT 0.00,
    description VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (journal_id) REFERENCES ledger_journals(id) ON DELETE CASCADE,
    FOREIGN KEY (account_id) REFERENCES ledger_accounts(id) ON DELETE RESTRICT,
    INDEX idx_journal (journal_id),
    INDEX idx_account (account_id)
);
```

#### `qr_codes`
Generated QR codes.
```sql
CREATE TABLE qr_codes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    school_id INT NOT NULL,
    code VARCHAR(100) NOT NULL,
    entity_type VARCHAR(100) NOT NULL,
    entity_id INT NOT NULL,
    qr_type ENUM('id', 'url', 'data') DEFAULT 'id',
    payload JSON,
    image_path VARCHAR(500),
    is_active BOOLEAN DEFAULT TRUE,
    scanned_count INT DEFAULT 0,
    last_scanned_at DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    UNIQUE KEY uk_code (code),
    INDEX idx_entity (entity_type, entity_id)
);
```

### 2.2 Core Services Implementation

#### TenantContextService
```php
<?php
namespace App\Modules\Foundation\Services;

class TenantContextService
{
    private ?int $currentSchoolId = null;
    
    public function resolve(): int
    {
        // 1. Check session
        if ($schoolId = session('school_id')) {
            return $this->setContext($schoolId, 'session');
        }
        
        // 2. Check subdomain
        if ($schoolId = $this->resolveFromSubdomain()) {
            return $this->setContext($schoolId, 'subdomain');
        }
        
        // 3. Check API token
        if ($schoolId = $this->resolveFromToken()) {
            return $this->setContext($schoolId, 'token');
        }
        
        throw new TenantNotFoundException();
    }
    
    public function getCurrentSchoolId(): int
    {
        return $this->currentSchoolId ?? throw new NoTenantContextException();
    }
    
    public function scopeQuery(BaseBuilder $builder, string $table = null): BaseBuilder
    {
        $column = $table ? "{$table}.school_id" : 'school_id';
        return $builder->where($column, $this->getCurrentSchoolId());
    }
}
```

#### AuditService
```php
<?php
namespace App\Modules\Foundation\Services;

class AuditService
{
    public function log(
        string $action,
        string $entityType,
        int $entityId,
        array $before = null,
        array $after = null
    ): void {
        $changedFields = $this->computeChangedFields($before, $after);
        
        $this->auditModel->insert([
            'school_id' => $this->tenant->getCurrentSchoolId(),
            'user_id' => auth()->id(),
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'before_state' => json_encode($before),
            'after_state' => json_encode($after),
            'changed_fields' => json_encode($changedFields),
            'ip_address' => $this->request->getIPAddress(),
            'user_agent' => $this->request->getUserAgent()->getAgentString(),
            'trace_id' => $this->getTraceId(),
        ]);
    }
    
    public function seal(int $schoolId, string $date): AuditSeal
    {
        $events = $this->getUnsealedEvents($schoolId, $date);
        $hash = $this->computeHash($events);
        
        return $this->createSeal($schoolId, $date, $events, $hash);
    }
    
    public function verify(int $sealId): bool
    {
        $seal = $this->sealModel->find($sealId);
        $events = $this->getEventsInRange($seal->first_event_id, $seal->last_event_id);
        $computedHash = $this->computeHash($events);
        
        return hash_equals($seal->data_hash, $computedHash);
    }
}
```

#### LedgerService
```php
<?php
namespace App\Modules\Foundation\Services;

class LedgerService
{
    public function createJournal(
        string $description,
        array $entries,
        string $referenceType = null,
        int $referenceId = null
    ): LedgerJournal {
        // Validate entries balance
        $this->validateBalance($entries);
        
        $this->db->transStart();
        
        $journal = $this->journalModel->insert([
            'school_id' => $this->tenant->getCurrentSchoolId(),
            'journal_number' => $this->generateJournalNumber(),
            'journal_date' => date('Y-m-d'),
            'description' => $description,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'total_amount' => $this->calculateTotal($entries),
            'status' => 'draft',
            'created_by' => auth()->id(),
        ]);
        
        foreach ($entries as $entry) {
            $this->entryModel->insert([
                'journal_id' => $journal->id,
                'account_id' => $entry['account_id'],
                'debit_amount' => $entry['debit'] ?? 0,
                'credit_amount' => $entry['credit'] ?? 0,
                'description' => $entry['description'] ?? null,
            ]);
        }
        
        $this->db->transComplete();
        
        return $journal;
    }
    
    public function postJournal(int $journalId): void
    {
        $journal = $this->journalModel->find($journalId);
        
        if ($journal->status !== 'draft') {
            throw new JournalAlreadyPostedException();
        }
        
        $this->db->transStart();
        
        // Update account balances
        foreach ($journal->entries as $entry) {
            $this->updateAccountBalance($entry);
        }
        
        $journal->status = 'posted';
        $journal->posted_by = auth()->id();
        $journal->posted_at = date('Y-m-d H:i:s');
        $journal->save();
        
        $this->db->transComplete();
    }
    
    private function validateBalance(array $entries): void
    {
        $totalDebit = array_sum(array_column($entries, 'debit'));
        $totalCredit = array_sum(array_column($entries, 'credit'));
        
        if (abs($totalDebit - $totalCredit) > 0.001) {
            throw new UnbalancedJournalException(
                "Debits ({$totalDebit}) must equal Credits ({$totalCredit})"
            );
        }
    }
}
```

### 2.3 Module Structure

```
app/Modules/Foundation/
├── Config/
│   ├── Services.php
│   └── Foundation.php
├── Contracts/
│   ├── TenantAwareInterface.php
│   ├── AuditableInterface.php
│   └── ApprovalRequiredInterface.php
├── Traits/
│   ├── TenantScopeTrait.php
│   ├── AuditableTrait.php
│   └── SoftDeletesTrait.php
├── Services/
│   ├── TenantContextService.php
│   ├── AuditService.php
│   ├── AuditSealService.php
│   ├── LedgerService.php
│   ├── QRCodeService.php
│   ├── ApprovalWorkflowService.php
│   └── IntegrationRegistryService.php
├── Models/
│   ├── TenantContextModel.php
│   ├── AuditEventModel.php
│   ├── AuditSealModel.php
│   ├── LedgerAccountModel.php
│   ├── LedgerJournalModel.php
│   ├── LedgerEntryModel.php
│   └── QRCodeModel.php
├── Filters/
│   ├── TenantFilter.php
│   └── AuditFilter.php
├── Libraries/
│   ├── QRGenerator.php
│   └── HashChain.php
├── Database/
│   └── Migrations/
│       ├── 2025-11-27-000001_CreateAuditTables.php
│       └── 2025-11-27-000002_CreateLedgerTables.php
└── Tests/
    ├── Unit/
    │   ├── LedgerServiceTest.php
    │   └── AuditSealTest.php
    └── Feature/
        └── TenantIsolationTest.php
```

### 2.4 Integration Points

- **All Modules**: Tenant scoping, audit logging.
- **Finance Module**: Ledger service for transactions.
- **Inventory Module**: QR codes for items.
- **Approval Workflows**: Maker-checker patterns.
- **Integrations Module**: Registry service.

---

## Part 3: Architectural Safeguards

### 3.1 Tenant Isolation
- Every database query must include school_id.
- TenantScopeTrait auto-applies filter.
- Cross-tenant access throws exception.

### 3.2 Audit Integrity
- Audit entries are append-only.
- Daily seals create tamper-evident chain.
- Verification available on demand.

### 3.3 Ledger Integrity
- Transactions must balance (debit = credit).
- Posted journals are immutable.
- Reversals create new entries, don't modify.

### 3.4 QR Code Security
- Codes are unique and unguessable.
- Expiry enforced on time-limited codes.
- Validation checks active status.

---

## Part 4: Development Checklist

- [x] **Tenant**: Context resolution.
- [x] **Tenant**: Scope trait.
- [x] **Audit**: Event logging.
- [ ] **Audit**: Seal generation.
- [ ] **Audit**: Verification.
- [x] **Ledger**: Account management.
- [x] **Ledger**: Journal creation.
- [ ] **Ledger**: Posting with balance updates.
- [x] **QR**: Generation.
- [ ] **QR**: Validation.
- [ ] **Approval**: Workflow service.
