# 📦 Inventory & Assets Management Specification

**Version**: 2.0.0
**Status**: Draft
**Last Updated**: 2025-11-27

---

## Part 1: Feature Definition (The "What" & "Why")

### 1.1 Overview
The Inventory module is the "Supply Chain Brain" of the school. It manages **Physical Stock** (Uniforms, Books), **Service Items** (Tuition, Transport), and **Bundles** (Fee Packages). It supports **Multi-Store** operations (Main Store, Kitchen, Library) and **Paperless Transfers** using the Threads system for digital handshakes.

### 1.2 User Stories
- **As a Store Manager**, I want to transfer stock from "Main Warehouse" to "Uniform Shop" digitally, so that I don't need paper delivery notes.
- **As a Parent**, I want to "Confirm Receipt" of my child's uniform via the app, so the school knows it was delivered.
- **As a Bursar**, I want to sell a "Grade 1 Fee Package" that automatically deducts stock for the included uniform and textbooks.
- **As a Librarian**, I want to search for books by `#Author` or `#Genre` tags.

### 1.3 User Workflows
1.  **Paperless Transfer (Store-to-Store)**:
    *   Manager A (Warehouse) initiates transfer of 50 Shirts to Uniform Shop.
    *   System creates `InventoryTransfer` (Status: `in_transit`) and a **Thread**.
    *   Manager B (Uniform Shop) gets a Thread notification.
    *   Manager B counts items and clicks "Receive" in the Thread/App.
    *   System updates stock in both locations.

2.  **Digital Issue (Store-to-Student)**:
    *   Store Manager issues "Math Book" to Student.
    *   System creates `InventoryIssue` (Status: `pending_confirmation`).
    *   Student/Parent gets a Thread alert: "Please confirm receipt".
    *   Parent clicks "Confirm".
    *   System marks issue as `completed`.

---

## Part 2: Technical Specification (The "How")

### 2.1 Database Schema

#### `inventory_locations` (Strict Table)
Defines physical or logical stores.
```sql
CREATE TABLE inventory_locations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL, -- "Main Warehouse", "Uniform Shop"
    type ENUM('store', 'warehouse', 'kitchen', 'library') DEFAULT 'store',
    manager_user_id INT NULL, -- Person responsible
    created_at DATETIME
);
```

#### `inventory_items` (Enhanced)
Supports Services and Bundles.
```sql
CREATE TABLE inventory_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    category_id INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    sku VARCHAR(50) UNIQUE,
    type ENUM('physical', 'service', 'bundle') DEFAULT 'physical',
    is_billable BOOLEAN DEFAULT TRUE,
    expense_account_id INT NULL, -- For internal consumption
    income_account_id INT NULL, -- For sales revenue
    created_at DATETIME
);
```

#### `inventory_bundles`
Defines the contents of a bundle (e.g., Fee Package).
```sql
CREATE TABLE inventory_bundles (
    parent_item_id INT, -- The Bundle
    child_item_id INT,  -- The Component
    quantity INT,
    PRIMARY KEY (parent_item_id, child_item_id)
);
```

#### `inventory_stock` (Multi-Store)
Tracks quantity per location.
```sql
CREATE TABLE inventory_stock (
    item_id INT,
    location_id INT,
    quantity DECIMAL(10, 2) DEFAULT 0.00,
    reorder_level INT DEFAULT 10,
    PRIMARY KEY (item_id, location_id)
);
```

#### `inventory_transfers` (Paperless Movement)
Tracks movement between stores.
```sql
CREATE TABLE inventory_transfers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    from_location_id INT,
    to_location_id INT,
    item_id INT,
    quantity DECIMAL(10, 2),
    status ENUM('pending', 'in_transit', 'received', 'rejected') DEFAULT 'pending',
    initiated_by INT, -- User ID
    received_by INT, -- User ID
    thread_id VARCHAR(36), -- Link to Threads Module
    created_at DATETIME,
    received_at DATETIME
);
```

#### `inventory_issues` (Digital Handshake)
Tracks items given to users.
```sql
CREATE TABLE inventory_issues (
    id INT PRIMARY KEY AUTO_INCREMENT,
    item_id INT,
    recipient_type VARCHAR(50), -- 'student', 'staff'
    recipient_id INT,
    quantity DECIMAL(10, 2),
    status ENUM('issued', 'confirmed', 'returned', 'lost') DEFAULT 'issued',
    thread_id VARCHAR(36), -- Link to Threads Module
    issued_at DATETIME,
    confirmed_at DATETIME
);
```

### 2.2 API Endpoints (Mobile-First)

| Method | Endpoint | Description | Access |
|:-------|:---------|:------------|:-------|
| GET    | `/api/inventory/locations` | List all stores | Staff |
| GET    | `/api/inventory/stock/{location_id}` | Check stock in a store | Staff |
| POST   | `/api/inventory/transfer` | Initiate transfer | Manager |
| POST   | `/api/inventory/transfer/{id}/receive` | Confirm receipt | Manager |
| POST   | `/api/inventory/issue` | Issue item to user | Staff |
| POST   | `/api/inventory/issue/{id}/confirm` | User confirms receipt | Student/Parent |

### 2.3 Threads Integration
*   **Context Type**: `inventory_transfer` or `inventory_issue`.
*   **Context ID**: The ID of the record.
*   **Workflow**:
    1.  On `create`, call `ThreadService::createThread()`.
    2.  On `receive/confirm`, call `ThreadService::postMessage()` ("Item received").

### 2.4 Web Interface (Universal Terminal)
*   **Controller**: `InventoryWebController`
*   **Views**:
    *   `transfer.php`: Dual-pane view (Source -> Destination).
    *   `receive.php`: List of incoming transfers with "Accept" buttons.
    *   `issue.php`: Universal Terminal (Select User -> Scan Items).

---

## Part 3: Test Data Strategy
*   **Locations**: "Main Warehouse", "Uniform Shop".
*   **Items**: "Math Book" (Physical), "Tuition" (Service), "Grade 1 Kit" (Bundle).
*   **Scenario**: Transfer 10 Books from Warehouse to Shop. Verify stock decreases in Warehouse and increases in Shop *only after* receipt.

---

## Part 4: Architectural Safeguards (Senior Architect Review)

### 4.1 Concurrency Control (The "Race Condition" Fix)
**Risk**: Two users selling the last item simultaneously.
**Mandate**: Use **Atomic Decrements** in the Service Layer.
```php
// BAD
$item->quantity -= $qty;
$item->save();

// GOOD
$db->table('inventory_stock')
   ->where('item_id', $id)
   ->where('location_id', $loc)
   ->where('quantity >=', $qty) // Safety check
   ->decrement('quantity', $qty);
```

### 4.2 Transaction Boundaries (The "Partial Failure" Fix)
**Risk**: Stock deducted but Thread notification fails.
**Mandate**: Wrap all multi-step operations in DB Transactions.
```php
$this->db->transStart();
// 1. Deduct Stock
// 2. Create Transfer Record
// 3. Create Thread
$this->db->transComplete();
```

### 4.3 Migration Strategy (Data Loss Prevention)
**Risk**: Dropping `inventory_items.quantity` loses existing data.
**Plan**:
1.  Create `inventory_locations` and `inventory_stock` tables.
2.  **Seed**: Create default "Main Store" location.
3.  **Migrate Data**: Loop through all items, move `quantity` to `inventory_stock` (linked to Main Store).
4.  **Cleanup**: Drop `quantity` column from `inventory_items`.

