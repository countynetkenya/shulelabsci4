# Finance Module Design Document

**Version**: 1.0.0
**Status**: Draft
**Last Updated**: 2025-12-03

---

## Part 1: Feature Definition (The "What" & "Why")

### 1.1 Overview
The Finance module manages the financial operations of the school, including fee structure definition, invoice generation for students, payment recording, and financial reporting. It ensures accurate tracking of school revenue and student balances.

### 1.2 User Stories
- As an **Admin**, I want to define fee structures so that I can standardize charges for different classes.
- As an **Admin**, I want to generate invoices for students so that they know what to pay.
- As an **Admin**, I want to record payments received so that student balances are updated.
- As a **Parent/Student**, I want to view my invoices and payment history so that I can track my financial status.

### 1.3 User Workflows
1.  **Fee Structure Setup**:
    *   Admin logs in.
    *   Navigates to Finance > Fee Structures.
    *   Creates a new fee item (e.g., "Term 1 Tuition").
2.  **Invoicing**:
    *   Admin selects a class or specific student.
    *   Applies a fee structure to generate invoices.
3.  **Recording Payment**:
    *   Admin navigates to Finance > Payments.
    *   Selects a student and enters payment details (Amount, Method, Reference).
    *   System updates the invoice status.

### 1.4 Acceptance Criteria
- [ ] Admin can CRUD Fee Structures.
- [ ] Admin can generate Invoices for students.
- [ ] Admin can record Payments against Invoices.
- [ ] System calculates outstanding balances correctly.
- [ ] Students/Parents can view their own financial records.

---

## Part 2: Technical Specification (The "How")

### 2.1 Database Schema

#### `finance_fee_structures`
```sql
CREATE TABLE finance_fee_structures (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    school_id INT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    term VARCHAR(50) NOT NULL,
    academic_year VARCHAR(9) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME NULL,
    CONSTRAINT fk_fee_school FOREIGN KEY (school_id) REFERENCES schools(id)
);
```

#### `finance_invoices`
```sql
CREATE TABLE finance_invoices (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    school_id INT UNSIGNED NOT NULL,
    student_id INT UNSIGNED NOT NULL,
    fee_structure_id INT UNSIGNED NULL,
    reference_number VARCHAR(50) NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    balance DECIMAL(10, 2) NOT NULL,
    status ENUM('unpaid', 'partial', 'paid', 'overdue') DEFAULT 'unpaid',
    due_date DATE NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME NULL,
    CONSTRAINT fk_inv_school FOREIGN KEY (school_id) REFERENCES schools(id),
    CONSTRAINT fk_inv_student FOREIGN KEY (student_id) REFERENCES users(id),
    CONSTRAINT fk_inv_fee FOREIGN KEY (fee_structure_id) REFERENCES finance_fee_structures(id)
);
```

#### `finance_payments`
```sql
CREATE TABLE finance_payments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    school_id INT UNSIGNED NOT NULL,
    invoice_id INT UNSIGNED NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    method ENUM('cash', 'bank_transfer', 'mobile_money', 'cheque') NOT NULL,
    reference_code VARCHAR(100) NULL,
    paid_at DATETIME NOT NULL,
    recorded_by INT UNSIGNED NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME NULL,
    CONSTRAINT fk_pay_school FOREIGN KEY (school_id) REFERENCES schools(id),
    CONSTRAINT fk_pay_invoice FOREIGN KEY (invoice_id) REFERENCES finance_invoices(id),
    CONSTRAINT fk_pay_recorder FOREIGN KEY (recorded_by) REFERENCES users(id)
);
```

### 2.2 API Endpoints (Mobile-First)
| Method | Endpoint | Description | Access |
|:-------|:---------|:------------|:-------|
| GET    | /api/finance/invoices | List my invoices | Student/Parent |
| GET    | /api/finance/payments | List my payments | Student/Parent |
| POST   | /api/finance/payments | Initiate payment (integration) | Student/Parent |

### 2.3 Web Interface (Views & Controllers)
- **Controller**: `App\Modules\Finance\Controllers\FinanceWebController`
- **Views**:
    - `finance/index.php`: Dashboard
    - `finance/invoices/index.php`: List invoices
    - `finance/payments/create.php`: Record payment form

### 2.4 Models & Validation
- **InvoiceModel**:
    - `amount`: required, decimal
    - `due_date`: required, valid_date
- **PaymentModel**:
    - `amount`: required, decimal, less_than_equal_to[invoice_balance]

---

## Part 3: Development Checklist
- [ ] Create Migration for tables.
- [ ] Create Models (FeeStructure, Invoice, Payment).
- [ ] Create FinanceWebController.
- [ ] Create Views.
- [ ] Implement TenantTestTrait in Feature Tests.
