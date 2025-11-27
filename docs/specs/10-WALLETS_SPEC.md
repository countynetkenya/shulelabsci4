# 💳 Wallets Module Design Document

**Version**: 1.0.0
**Status**: Draft
**Last Updated**: 2025-11-27

---

## Part 1: Feature Definition (The "What" & "Why")
*Target Audience: Product Owners, Stakeholders, Developers*

### 1.1 Overview
The Wallets module provides digital wallet functionality for students, parents, and staff. It enables cashless transactions within the school ecosystem, integrates with Finance for fee payments, and connects with POS/Inventory for purchases.

### 1.2 User Stories
- **As a Parent**, I want to top up my child's wallet via M-Pesa so they can make purchases at school without carrying cash.
- **As a Student**, I want to use my wallet to buy lunch at the canteen by scanning my ID card.
- **As a Bursar**, I want to auto-allocate wallet funds to outstanding fees when parents top up.
- **As an Admin**, I want to set spending limits per day/week for student wallets.
- **As a Parent**, I want to receive notifications when my child makes purchases or when the balance is low.

### 1.3 User Workflows
1.  **Wallet Top-up**:
    *   Parent initiates top-up.
    *   M-Pesa STK Push triggered.
    *   Payment confirmed.
    *   Wallet credited.
    *   Optional auto-allocation to fees.

2.  **Purchase Flow**:
    *   Student scans QR/ID at POS.
    *   POS shows items in cart.
    *   Wallet debited for total amount.
    *   Receipt generated.
    *   Parent notified (if configured).

3.  **Fee Payment**:
    *   Parent views wallet balance.
    *   Allocates funds to outstanding fees.
    *   Invoice marked paid.
    *   Ledger updated.

4.  **Refund Flow**:
    *   Admin initiates refund.
    *   Wallet credited.
    *   Audit logged.

### 1.4 Acceptance Criteria
- [ ] Users can view their wallet balance and transaction history.
- [ ] Parents can top up wallets via M-Pesa STK Push.
- [ ] Students can make purchases using their wallet at POS terminals.
- [ ] Spending limits can be configured per wallet (daily/weekly/monthly).
- [ ] Wallet funds can be allocated to outstanding fee invoices.
- [ ] Low balance notifications are sent to configured recipients.
- [ ] All transactions are immutable and auditable.
- [ ] Balance reconciliation passes (balance_after = balance_before ± amount).

---

## Part 2: Technical Specification (The "How")
*Target Audience: Developers, Architects*

### 2.1 Database Schema

#### `wallets`
One wallet per entity (student, parent, or staff).
```sql
CREATE TABLE wallets (
    id INT PRIMARY KEY AUTO_INCREMENT,
    school_id INT NOT NULL,
    owner_type ENUM('student', 'parent', 'staff') NOT NULL,
    owner_id INT NOT NULL,
    balance DECIMAL(12,2) DEFAULT 0.00,
    currency VARCHAR(3) DEFAULT 'KES',
    daily_limit DECIMAL(10,2) DEFAULT NULL,
    weekly_limit DECIMAL(10,2) DEFAULT NULL,
    monthly_limit DECIMAL(10,2) DEFAULT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    pin_hash VARCHAR(255) NULL,
    last_activity_at DATETIME,
    created_at DATETIME,
    updated_at DATETIME,
    UNIQUE KEY uk_owner (school_id, owner_type, owner_id),
    INDEX idx_school_active (school_id, is_active)
);
```

#### `wallet_transactions`
Immutable transaction log.
```sql
CREATE TABLE wallet_transactions (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    wallet_id INT NOT NULL,
    transaction_type ENUM('credit', 'debit') NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    balance_before DECIMAL(12,2) NOT NULL,
    balance_after DECIMAL(12,2) NOT NULL,
    category ENUM('topup', 'purchase', 'fee_payment', 'transfer', 'refund', 'adjustment') NOT NULL,
    reference_type VARCHAR(50) NULL,
    reference_id INT NULL,
    description VARCHAR(255),
    metadata_json JSON,
    performed_by INT NULL,
    created_at DATETIME NOT NULL,
    FOREIGN KEY (wallet_id) REFERENCES wallets(id),
    INDEX idx_wallet_date (wallet_id, created_at),
    INDEX idx_reference (reference_type, reference_id)
);
```

#### `wallet_topups`
Top-up requests and confirmations.
```sql
CREATE TABLE wallet_topups (
    id INT PRIMARY KEY AUTO_INCREMENT,
    wallet_id INT NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    payment_method ENUM('mpesa', 'bank', 'cash', 'card') NOT NULL,
    payment_reference VARCHAR(100),
    mpesa_checkout_id VARCHAR(100),
    mpesa_receipt VARCHAR(50),
    status ENUM('pending', 'completed', 'failed', 'cancelled') DEFAULT 'pending',
    auto_allocate_to_fees BOOLEAN DEFAULT FALSE,
    allocated_amount DECIMAL(12,2) DEFAULT 0,
    initiated_by INT NOT NULL,
    completed_at DATETIME,
    created_at DATETIME,
    updated_at DATETIME,
    FOREIGN KEY (wallet_id) REFERENCES wallets(id),
    INDEX idx_status (status, created_at)
);
```

#### `wallet_transfers`
Wallet-to-wallet transfers.
```sql
CREATE TABLE wallet_transfers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    from_wallet_id INT NOT NULL,
    to_wallet_id INT NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    reason VARCHAR(255),
    status ENUM('pending', 'completed', 'reversed') DEFAULT 'pending',
    initiated_by INT NOT NULL,
    approved_by INT,
    created_at DATETIME,
    completed_at DATETIME,
    FOREIGN KEY (from_wallet_id) REFERENCES wallets(id),
    FOREIGN KEY (to_wallet_id) REFERENCES wallets(id)
);
```

#### `wallet_limits`
Custom limits per wallet or category.
```sql
CREATE TABLE wallet_limits (
    id INT PRIMARY KEY AUTO_INCREMENT,
    wallet_id INT NOT NULL,
    limit_type ENUM('daily', 'weekly', 'monthly', 'per_transaction') NOT NULL,
    category VARCHAR(50) NULL,
    max_amount DECIMAL(10,2) NOT NULL,
    current_spent DECIMAL(10,2) DEFAULT 0,
    reset_at DATETIME,
    created_at DATETIME,
    FOREIGN KEY (wallet_id) REFERENCES wallets(id),
    UNIQUE KEY uk_wallet_limit (wallet_id, limit_type, category)
);
```

#### `wallet_notifications`
Low balance and transaction alerts.
```sql
CREATE TABLE wallet_notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    wallet_id INT NOT NULL,
    notification_type ENUM('low_balance', 'transaction', 'limit_reached', 'topup_success') NOT NULL,
    threshold_amount DECIMAL(10,2) NULL,
    is_enabled BOOLEAN DEFAULT TRUE,
    notify_via JSON,
    created_at DATETIME,
    FOREIGN KEY (wallet_id) REFERENCES wallets(id)
);
```

### 2.2 API Endpoints (Mobile-First)

| Method | Endpoint | Description | Access |
|:-------|:---------|:------------|:-------|
| GET | `/api/v1/wallets` | List wallets (admin) | Admin |
| GET | `/api/v1/wallets/my` | Get current user's wallet | Authenticated |
| GET | `/api/v1/wallets/{id}` | Get wallet details | Owner/Admin |
| GET | `/api/v1/wallets/{id}/transactions` | Get transaction history | Owner/Admin |
| GET | `/api/v1/wallets/{id}/balance` | Get current balance | Owner/Admin |
| POST | `/api/v1/wallets/{id}/topup` | Initiate top-up | Owner/Admin |
| POST | `/api/v1/wallets/{id}/topup/mpesa` | M-Pesa STK Push | Owner |
| POST | `/api/v1/wallets/topup/callback` | M-Pesa callback | System |
| POST | `/api/v1/wallets/{id}/debit` | Debit wallet (POS) | Staff |
| POST | `/api/v1/wallets/{id}/allocate-to-fees` | Allocate to fees | Owner/Admin |
| POST | `/api/v1/wallets/transfer` | Wallet-to-wallet transfer | Admin |
| PUT | `/api/v1/wallets/{id}/limits` | Set spending limits | Admin/Parent |
| PUT | `/api/v1/wallets/{id}/notifications` | Configure alerts | Owner |

### 2.3 Web Interface (Views & Controllers)
- **Controller**: `App\Modules\Wallets\Controllers\Web\WalletDashboardController`
- **Views**:
    - `index.php`: Wallet dashboard with balance and recent transactions
    - `transactions.php`: Full transaction history (filterable)
    - `topup.php`: Top-up form with M-Pesa integration
    - `limits.php`: Spending limit configuration
- **Routes**:
    - `GET /wallets` -> `index` (Dashboard)
    - `GET /wallets/transactions` -> `transactions`
    - `GET /wallets/topup` -> `topupForm`
    - `POST /wallets/topup` -> `processTopup`
    - `GET /wallets/limits` -> `limitsForm`
    - `POST /wallets/limits` -> `saveLimits`

### 2.4 Module Structure
```
app/Modules/Wallets/
├── Config/
│   ├── Routes.php
│   └── Services.php
├── Controllers/
│   ├── Api/
│   │   ├── WalletController.php
│   │   ├── TopupController.php
│   │   ├── TransactionController.php
│   │   └── TransferController.php
│   └── Web/
│       └── WalletDashboardController.php
├── Models/
│   ├── WalletModel.php
│   ├── WalletTransactionModel.php
│   ├── WalletTopupModel.php
│   ├── WalletTransferModel.php
│   └── WalletLimitModel.php
├── Services/
│   ├── WalletService.php
│   ├── TopupService.php
│   ├── TransactionService.php
│   ├── LimitEnforcementService.php
│   └── FeeAllocationService.php
├── Events/
│   ├── WalletCredited.php
│   ├── WalletDebited.php
│   └── LowBalanceAlert.php
├── Database/
│   └── Migrations/
│       └── 2025-11-27-000001_CreateWalletTables.php
└── Tests/
    ├── Unit/
    │   └── WalletServiceTest.php
    └── Feature/
        └── WalletApiTest.php
```

### 2.5 Models & Validation

#### WalletModel
- **owner_type**: required, in_list[student,parent,staff]
- **owner_id**: required, integer
- **balance**: decimal, min[0]
- **currency**: max_length[3]
- **daily_limit**: decimal, permit_empty
- **weekly_limit**: decimal, permit_empty
- **monthly_limit**: decimal, permit_empty

#### WalletTransactionModel
- **wallet_id**: required, integer
- **transaction_type**: required, in_list[credit,debit]
- **amount**: required, decimal, greater_than[0]
- **balance_before**: required, decimal
- **balance_after**: required, decimal
- **category**: required, in_list[topup,purchase,fee_payment,transfer,refund,adjustment]

### 2.6 Integration Points
- **Finance**: Fee allocation via `FeeAllocationService`, invoice payment
- **POS/Inventory**: Purchase debits via `WalletService::debit()`
- **Threads**: Transaction notifications via events
- **M-Pesa**: STK Push integration via existing `MpesaService`
- **Reports**: Wallet tab in Student/Parent views

---

## Part 3: Architectural Safeguards
*Target Audience: Developers, Architects*

### 3.1 Atomic Balance Updates
Use database transactions with row locking to prevent race conditions.
```php
$db->transStart();
$db->query('SELECT balance FROM wallets WHERE id = ? FOR UPDATE', [$walletId]);
// Update balance
$db->transComplete();
```

### 3.2 Immutable Transactions
Never update or delete `wallet_transactions`. Only append new records. This ensures a complete audit trail.

### 3.3 Balance Reconciliation
Every transaction must satisfy: `balance_after = balance_before + amount` (for credit) or `balance_after = balance_before - amount` (for debit).

### 3.4 Limit Enforcement
Always check spending limits BEFORE processing a transaction:
```php
public function canDebit(int $walletId, float $amount): bool
{
    $wallet = $this->walletModel->find($walletId);
    
    // Check wallet balance
    if ($wallet->balance < $amount) {
        return false;
    }
    
    // Check daily limit
    if ($wallet->daily_limit && $this->getTodaySpent($walletId) + $amount > $wallet->daily_limit) {
        return false;
    }
    
    return true;
}
```

### 3.5 Idempotency
Use `payment_reference` to prevent duplicate top-ups. Before processing any M-Pesa callback, check if a transaction with that reference already exists.

---

## Part 4: Embedded Reports for Entity Views
*Target Audience: Developers, Product Owners*

### 4.1 Student View - Finance Tab (Wallet Section)
- Current wallet balance
- Spending summary (today/week/month)
- Recent transactions (last 10)
- Spending by category chart

### 4.2 Parent View - Finance Tab
- All children's wallet balances
- Top-up button (M-Pesa integration)
- Transaction history across children
- Auto-allocation settings toggle

---

## Part 5: Test Data Strategy
*Target Audience: QA, Developers*

### 5.1 Seeding Strategy
Use `Modules\Wallets\Database\Seeds\WalletSeeder` to populate the database with test scenarios.

#### Wallets
- **Student A**: Balance 1,500 KES, daily limit 500 KES
- **Student B**: Balance 0 KES (zero balance scenario)
- **Parent A**: Balance 5,000 KES

#### Transactions
1. **Top-up**: Parent A tops up Student A with 2,000 KES via M-Pesa.
2. **Purchase**: Student A buys lunch for 150 KES.
3. **Fee Allocation**: Parent A allocates 1,000 KES to Term 1 fees.

### 5.2 Testing Focus
- **API**: Verify that `GET /api/v1/wallets/my` returns correct balance.
- **API**: Verify that `POST /api/v1/wallets/{id}/debit` enforces limits.
- **Web**: Verify top-up form triggers M-Pesa STK Push.
- **Integration**: Verify POS debit updates wallet balance correctly.

---

## Part 6: Development Checklist
- [ ] **Design**: Review and approve this document.
- [ ] **Tests**: Write failing feature tests (TDD).
- [ ] **Scaffold**: Generate files (Controllers, Models, Migrations).
- [ ] **Database**: Run migrations and verify schema.
- [ ] **Code**: Implement logic to pass tests.
- [ ] **Integration**: Connect with Finance, POS, and M-Pesa modules.
- [ ] **Review**: Code review and merge.
