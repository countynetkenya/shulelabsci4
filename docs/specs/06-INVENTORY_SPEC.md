# 📦 Inventory & Assets Management Specification

**Version**: 1.0.0
**Status**: Draft
**Last Updated**: 2025-11-27

---

## Part 1: Feature Definition (The "What" & "Why")
*Target Audience: Product Owners, Stakeholders, Developers*

### 1.1 Overview
The Inventory & Assets module enables the school to track physical assets (furniture, computers) and consumable stock (stationery, lab chemicals). It aims to prevent theft, reduce wastage, and ensure timely procurement of essential supplies.

### 1.2 User Stories
- As a **Store Manager**, I want to **record new stock purchases**, so that the system reflects accurate quantities.
- As a **Store Manager**, I want to **issue items to staff or students**, so that I can track who is responsible for school property.
- As an **Admin**, I want to **receive low-stock alerts**, so that I can approve purchases before we run out of critical supplies.
- As a **Teacher**, I want to **request supplies** via the portal, so that I don't have to physically visit the store for every small need.

### 1.3 User Workflows
1.  **Receiving Stock (Purchase)**:
    *   Store Manager logs in.
    *   Navigates to "Inventory > Receive Stock".
    *   Selects Supplier and Items.
    *   Enters quantity and unit cost.
    *   System updates stock levels and calculates average cost.

2.  **Issuing Stock**:
    *   Store Manager selects "Issue Item".
    *   Scans item or searches by name.
    *   Selects Recipient (Student or Staff).
    *   System deducts stock and creates a transaction record.

### 1.4 Acceptance Criteria
- [ ] System prevents issuing more items than available in stock.
- [ ] Low-stock threshold triggers a notification (visual or email).
- [ ] All transactions (In/Out) are immutable and audit-logged.
- [ ] Support for both "Consumable" (chalk, paper) and "Asset" (projector, desk) item types.

---

## Part 2: Technical Specification (The "How")
*Target Audience: Developers, Architects*

### 2.1 Database Schema

#### `inventory_categories`
Categorizes items (e.g., "Stationery", "Electronics", "Furniture").
```sql
CREATE TABLE inventory_categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

#### `inventory_suppliers`
External vendors.
```sql
CREATE TABLE inventory_suppliers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    contact_person VARCHAR(100),
    phone VARCHAR(20),
    email VARCHAR(100),
    address TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

#### `inventory_items`
The master list of items.
```sql
CREATE TABLE inventory_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    category_id INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    sku VARCHAR(50) UNIQUE, -- Stock Keeping Unit / Barcode
    description TEXT,
    type ENUM('consumable', 'asset') NOT NULL DEFAULT 'consumable',
    quantity INT DEFAULT 0,
    unit_cost DECIMAL(10, 2) DEFAULT 0.00, -- Moving average cost
    reorder_level INT DEFAULT 10,
    location VARCHAR(100), -- e.g., "Shelf A3"
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES inventory_categories(id)
);
```

#### `inventory_transactions`
Ledger of all stock movements.
```sql
CREATE TABLE inventory_transactions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    item_id INT NOT NULL,
    user_id INT NOT NULL, -- Who performed the action
    recipient_id INT NULL, -- Who received the item (if applicable)
    supplier_id INT NULL, -- Who supplied the item (if applicable)
    type ENUM('receive', 'issue', 'adjustment', 'return') NOT NULL,
    quantity INT NOT NULL, -- Positive for receive/return, Negative for issue
    unit_price DECIMAL(10, 2), -- Cost at time of transaction
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (item_id) REFERENCES inventory_items(id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (recipient_id) REFERENCES users(id),
    FOREIGN KEY (supplier_id) REFERENCES inventory_suppliers(id)
);
```

### 2.2 API Endpoints (Mobile-First)

| Method | Endpoint | Description | Access |
|:-------|:---------|:------------|:-------|
| GET    | `/api/inventory/items` | List items with stock levels | Auth |
| GET    | `/api/inventory/items/{id}` | Item details & history | Auth |
| POST   | `/api/inventory/items` | Create new item | Admin/Store |
| POST   | `/api/inventory/receive` | Record stock purchase | Store |
| POST   | `/api/inventory/issue` | Issue item to user | Store |
| GET    | `/api/inventory/alerts` | List low-stock items | Admin/Store |

### 2.3 Models & Validation

**InventoryItemModel**
- `name`: required, max_length[150]
- `sku`: is_unique[inventory_items.sku]
- `quantity`: integer, greater_than_equal_to[0]
- `reorder_level`: integer

**InventoryTransactionModel**
- `item_id`: required, exists[inventory_items.id]
- `type`: in_list[receive,issue,adjustment,return]
- `quantity`: required, integer, not_zero

### 2.4 Integration Points
- **Finance Module**: When stock is received (`receive`), a corresponding Expense record should ideally be created in Finance (future integration).
- **Users Module**: Linking `recipient_id` to Students or Staff.

---

## Part 3: Development Checklist
- [ ] **Design**: Review and approve this document.
- [ ] **Tests**: Write failing feature tests (TDD) for Issuing and Receiving.
- [ ] **Scaffold**: Generate Models, Controllers, Migrations.
- [ ] **Database**: Run migrations.
- [ ] **Code**: Implement Transaction logic (updating `inventory_items.quantity` automatically).
- [ ] **Review**: Verify stock calculations.
