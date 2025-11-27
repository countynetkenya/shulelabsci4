# Finance Module Design Document

**Version**: 1.0.0
**Status**: Draft
**Last Updated**: 2025-11-27

---

## Part 1: Feature Definition (The "What" & "Why")

### 1.1 Overview
The Finance module manages the financial health of the school. It handles Fee Structures (what students should pay), Invoicing (assigning fees to students), and Payments (recording incoming money). It must support Level 1/2/3 standards for financial integrity.

### 1.2 User Stories
- **As an Admin (Bursar)**, I want to create Fee Structures for different classes, so that I can automate billing.
- **As an Admin**, I want to generate invoices for students, so they know what they owe.
- **As an Admin**, I want to record payments against specific invoices, so that balances are updated.
- **As a Parent/Student**, I want to view my fee balance and transaction history via the mobile app.

### 1.3 User Workflows
1.  **Setting up Fees**:
    *   Bursar creates a "Term 1 2025 Tuition" fee item ($500).
    *   Bursar assigns this fee to "Grade 4".
2.  **Invoicing**:
    *   System (or Bursar) runs "Generate Invoices".
    *   All Grade 4 students get a $500 invoice.
3.  **Payment**:
    *   Student pays $300 via Mobile Money.
    *   System records transaction.
    *   Invoice balance updates to $200.

### 1.4 Acceptance Criteria
- [ ] Fee Structures can be defined per Class/Grade.
- [ ] Invoices are generated correctly linking Student to Fee.
- [ ] Payments reduce the Invoice balance.
- [ ] Double-entry bookkeeping principles (Transactions table) are observed.
- [ ] API allows fetching balance and history.
- [ ] Web UI allows searching students and recording payments.

---

## Part 2: Technical Specification (The "How")

### 2.1 Database Schema

#### `finance_fee_structures`
Defines the types of fees (Tuition, Transport, Lunch).
```sql
CREATE TABLE finance_fee_structures (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    academic_period_id INT, -- Link to Term/Year
    class_id INT, -- Nullable (if applies to all)
    created_at DATETIME,
    updated_at DATETIME
);
```

#### `finance_invoices`
The demand for payment sent to a student.
```sql
CREATE TABLE finance_invoices (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    fee_structure_id INT NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    balance DECIMAL(10, 2) NOT NULL,
    status VARCHAR(20) DEFAULT 'unpaid', -- unpaid, partial, paid
    due_date DATE,
    created_at DATETIME,
    updated_at DATETIME
);
```

#### `finance_payments`
The actual receipt of money.
```sql
CREATE TABLE finance_payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    invoice_id INT, -- Optional (can be account payment)
    amount DECIMAL(10, 2) NOT NULL,
    method VARCHAR(50), -- Cash, M-Pesa, Bank
    reference_number VARCHAR(100),
    recorded_by INT, -- User ID
    transaction_date DATE,
    created_at DATETIME
);
```

### 2.2 API Endpoints (Mobile-First)
- `GET /api/finance/balance/{student_id}` - Get current total balance.
- `GET /api/finance/invoices/{student_id}` - List all invoices.
- `GET /api/finance/payments/{student_id}` - List payment history.
- `POST /api/finance/payments` - Initiate a payment (Integration hook).

### 2.3 Web Interface (Views & Controllers)
- **Controller**: `App\Modules\Finance\Controllers\Web\FinanceWebController`
- **Views**:
    - `index.php`: Dashboard (Total collected, Outstanding).
    - `invoices/index.php`: List of all invoices (filterable).
    - `invoices/create.php`: Manual invoice creation.
    - `payments/create.php`: Receive payment form.
- **Routes**:
    - `GET /finance` -> `index`
    - `GET /finance/invoices` -> `listInvoices`
    - `GET /finance/receive-payment` -> `receivePaymentForm`
    - `POST /finance/pay` -> `processPayment`

### 2.4 Integration Points
- **Students Module**: Needs `student_id` validation.
- **Auth Module**: Needs `user_id` for `recorded_by`.

## Part 3: Test Data Strategy

### 3.1 Seeding Strategy
To ensure robust testing, we use `Modules\Finance\Database\Seeds\FinanceSeeder` to populate the database with realistic scenarios.

#### Fee Structures
- **Tuition**: 15,000 (Term 1 2025)
- **Transport**: 5,000
- **Lunch**: 3,000

#### Student Scenarios (Student ID 1)
1.  **Invoiced**: 15,000 (Tuition) + 5,000 (Transport) = 20,000 Total.
2.  **Paid**: 10,000 via M-Pesa (Ref: QWE123RTY).
3.  **Balance**: 10,000 Outstanding.

### 3.2 Testing Focus
- **API**: Verify that `GET /api/finance/invoices/1` returns the correct JSON structure and amounts.
- **Web**: Verify that the Dashboard correctly aggregates these totals (Total Invoiced: 20,000, Collected: 10,000).

