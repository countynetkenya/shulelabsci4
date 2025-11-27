# 🛒 Point of Sale (POS) & Billing Specification

**Version**: 1.0.0
**Status**: Draft
**Last Updated**: 2025-11-27

---

## Part 1: Feature Definition (The "What" & "Why")

### 1.1 Overview
The POS module is the "Front Office" of the school. It unifies **Retail Sales** (Canteen, Uniform Shop), **Fee Billing** (Tuition), and **Service Charges** (Library Fines) into a single **Universal Transaction Terminal**. It supports "Walk-in" customers, Students, and Staff, with seamless integration to Inventory and Finance.

### 1.2 User Stories
- **As a Canteen Staff**, I want to sell snacks to students using their ID card (Wallet), so they don't need cash.
- **As a Bursar**, I want to sell a "Grade 1 Fee Package" (Tuition + Uniforms) in one transaction.
- **As a Parent**, I want to receive a digital receipt via Threads immediately after my child buys a uniform.
- **As a Store Manager**, I want to see my daily sales total (Z-Report) and auto-post it to the Finance ledger.

### 1.3 User Workflows
1.  **Retail Sale (Uniform Shop)**:
    *   Cashier selects "Student" (Search by Adm No).
    *   Scans "Shirt Size 12" and "Trousers".
    *   Selects Payment: "M-Pesa" (Split payment supported).
    *   Completes Sale.
    *   System: Deducts Stock, Credits Revenue, Sends Receipt via Thread.

2.  **Fee Payment (Bursary)**:
    *   Bursar selects "Student".
    *   Adds "Term 1 Tuition" (Service Item) to cart.
    *   Adds "Transport Fee" (Service Item).
    *   Payment: "Cash".
    *   System: Updates Fee Balance, Credits Revenue.

---

## Part 2: Technical Specification (The "How")

### 2.1 Database Schema

#### `pos_registers`
Physical or logical till points.
```sql
CREATE TABLE pos_registers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100), -- "Canteen Till 1", "Bursary Desk"
    location_id INT, -- Links to Inventory Location
    status ENUM('open', 'closed', 'locked') DEFAULT 'closed',
    current_cashier_id INT NULL,
    created_at DATETIME
);
```

#### `pos_shifts`
Cashier sessions.
```sql
CREATE TABLE pos_shifts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    register_id INT,
    cashier_user_id INT,
    opened_at DATETIME,
    closed_at DATETIME NULL,
    opening_cash DECIMAL(10, 2),
    closing_cash_system DECIMAL(10, 2), -- Calculated
    closing_cash_actual DECIMAL(10, 2), -- Counted
    status ENUM('open', 'closed') DEFAULT 'open'
);
```

#### `pos_sales` (Header)
The transaction record.
```sql
CREATE TABLE pos_sales (
    id INT PRIMARY KEY AUTO_INCREMENT,
    shift_id INT,
    customer_type VARCHAR(50), -- 'student', 'staff', 'walkin'
    customer_id INT NULL,
    total_amount DECIMAL(10, 2),
    tax_amount DECIMAL(10, 2),
    status ENUM('completed', 'void', 'held') DEFAULT 'completed',
    thread_id VARCHAR(36), -- Digital Receipt
    created_at DATETIME
);
```

#### `pos_sale_items` (Line Items)
```sql
CREATE TABLE pos_sale_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    sale_id INT,
    inventory_item_id INT, -- Links to Inventory (Physical or Service)
    quantity INT,
    unit_price DECIMAL(10, 2),
    total DECIMAL(10, 2),
    FOREIGN KEY (sale_id) REFERENCES pos_sales(id)
);
```

#### `pos_payments` (Split Tender)
```sql
CREATE TABLE pos_payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    sale_id INT,
    method ENUM('cash', 'mpesa', 'card', 'wallet', 'cheque'),
    amount DECIMAL(10, 2),
    reference_ref VARCHAR(100), -- M-Pesa Code
    created_at DATETIME
);
```

### 2.2 API Endpoints (Mobile-First)

| Method | Endpoint | Description | Access |
|:-------|:---------|:------------|:-------|
| POST   | `/api/pos/shifts/open` | Start Shift | Cashier |
| POST   | `/api/pos/shifts/close` | End Shift (Z-Report) | Cashier |
| GET    | `/api/pos/items` | Search Items (Barcode/Name) | Cashier |
| POST   | `/api/pos/sales` | Process Transaction | Cashier |
| GET    | `/api/pos/sales/{id}/receipt` | Get Receipt HTML | Any |

### 2.3 Universal Terminal UI (Web)
*   **Controller**: `PosWebController`
*   **View**: `app/Views/modules/pos/terminal.php`
*   **Design**:
    *   **Left**: Item Grid (Tabs for Categories).
    *   **Top**: Customer Search (Student/Staff).
    *   **Right**: Cart & Payment Buttons.
    *   **Context**: Adapts based on `register_id` (e.g., Canteen shows Snacks, Bursary shows Fees).

### 2.4 Integrations
*   **Inventory**: Deducts stock from `pos_registers.location_id`.
*   **Finance**: Posts daily totals to GL Accounts.
*   **Threads**: Sends receipt to `customer_id`'s thread.
*   **Wallets**: If method is 'wallet', checks balance and deducts.

---

## Part 3: Test Data Strategy
*   **Register**: "Main Canteen".
*   **Shift**: Opened by "Cashier Jane" with 5000 opening float.
*   **Sale**: Student John buys "Soda" (50) + "Donut" (30). Total 80. Paid via Wallet.
*   **Verification**:
    *   Stock of Soda/Donut decreases.
    *   John's Wallet balance decreases.
    *   Shift "System Cash" remains 0 (since paid by Wallet).
