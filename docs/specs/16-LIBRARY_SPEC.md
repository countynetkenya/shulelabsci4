# 📚 Library Management Module Specification

**Version**: 1.0.0
**Status**: Draft
**Last Updated**: 2025-11-27

---

## Part 1: Feature Definition (The "What" & "Why")
*Target Audience: Product Owners, Stakeholders, Developers*

### 1.1 Overview
The Library module is the "Knowledge Gateway" of ShuleLabs. It manages the school's physical and digital library resources, including book cataloging, borrowing/returns, reservations, and fine collection. It supports ISBN lookup for easy cataloging, barcode/QR scanning for efficient operations, and integrates with the Finance module for fine collection.

### 1.2 User Stories

- **As a Librarian**, I want to catalog books quickly using ISBN lookup, so that I don't have to enter details manually.
- **As a Student**, I want to search for books and see if they're available, so that I can plan my library visits.
- **As a Librarian**, I want to scan a student's ID and book barcode to record borrowing, so that the process is fast.
- **As a Parent**, I want to see which books my child has borrowed, so that I can ensure they're returned on time.
- **As a Librarian**, I want to automatically calculate fines for overdue books, so that students are accountable.
- **As a Student**, I want to reserve a book that's currently borrowed, so that I get it when it's returned.

### 1.3 User Workflows

1. **Book Cataloging**:
   - Librarian scans book ISBN.
   - System fetches book details from online database.
   - Librarian reviews and adjusts details if needed.
   - Librarian assigns category and tags.
   - Librarian prints and attaches barcode label.
   - Book is available for borrowing.

2. **Borrowing Process**:
   - Student presents ID card.
   - Librarian scans student ID (or searches by name).
   - Librarian scans book barcode.
   - System validates: student eligible, book available, no outstanding fines.
   - System creates borrowing record with due date.
   - Student receives confirmation.

3. **Return Process**:
   - Librarian scans returned book barcode.
   - System identifies the borrowing record.
   - System calculates fine if overdue.
   - If fine exists, student must pay (cash or wallet).
   - Book marked as returned and available.

4. **Reservation**:
   - Student searches for book.
   - Book is currently borrowed.
   - Student clicks "Reserve".
   - When book is returned, student receives notification.
   - Student has 48 hours to collect.

5. **Fine Collection**:
   - System calculates daily fine for overdue books.
   - Student views outstanding fines.
   - Student pays via cash, wallet, or parent payment.
   - Payment recorded and linked to borrowing.
   - Student can borrow again.

### 1.4 Acceptance Criteria

- [ ] Books can be cataloged with ISBN auto-lookup.
- [ ] Categories and tags organize the collection.
- [ ] Barcode/QR scanning works for borrowing and returns.
- [ ] Due dates and loan periods are configurable.
- [ ] Fines calculated automatically based on days overdue.
- [ ] Reservations queue books for next available.
- [ ] Students can search the catalog via web/mobile.
- [ ] Parents can view children's borrowed books.
- [ ] Digital resources (e-books, links) can be cataloged.
- [ ] All data scoped by school_id for multi-tenancy.

---

## Part 2: Technical Specification (The "How")
*Target Audience: Developers, Architects*

### 2.1 Database Schema

#### `library_categories`
Book categories.
```sql
CREATE TABLE library_categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    school_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(20),
    parent_id INT,
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES library_categories(id) ON DELETE SET NULL,
    UNIQUE KEY uk_school_code (school_id, code)
);
```

#### `library_books`
Book catalog.
```sql
CREATE TABLE library_books (
    id INT PRIMARY KEY AUTO_INCREMENT,
    school_id INT NOT NULL,
    isbn VARCHAR(20),
    title VARCHAR(255) NOT NULL,
    subtitle VARCHAR(255),
    authors JSON,
    publisher VARCHAR(150),
    publication_year INT,
    edition VARCHAR(50),
    language VARCHAR(50) DEFAULT 'English',
    pages INT,
    category_id INT,
    tags JSON,
    description TEXT,
    cover_image_url VARCHAR(500),
    book_type ENUM('physical', 'ebook', 'audiobook', 'magazine', 'journal') DEFAULT 'physical',
    location VARCHAR(100),
    shelf_number VARCHAR(50),
    total_copies INT DEFAULT 1,
    available_copies INT DEFAULT 1,
    price DECIMAL(10,2),
    condition_note TEXT,
    is_reference_only BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    barcode VARCHAR(50),
    qr_code VARCHAR(50),
    digital_url VARCHAR(500),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES library_categories(id) ON DELETE SET NULL,
    INDEX idx_isbn (isbn),
    INDEX idx_barcode (barcode),
    INDEX idx_title (school_id, title(100))
);
```

#### `library_book_copies`
Individual book copies for multi-copy tracking.
```sql
CREATE TABLE library_book_copies (
    id INT PRIMARY KEY AUTO_INCREMENT,
    book_id INT NOT NULL,
    copy_number INT NOT NULL,
    barcode VARCHAR(50),
    accession_number VARCHAR(50),
    condition_status ENUM('new', 'good', 'fair', 'poor', 'damaged', 'lost') DEFAULT 'good',
    acquisition_date DATE,
    acquisition_type ENUM('purchased', 'donated', 'transferred') DEFAULT 'purchased',
    acquisition_cost DECIMAL(10,2),
    status ENUM('available', 'borrowed', 'reserved', 'maintenance', 'lost', 'retired') DEFAULT 'available',
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (book_id) REFERENCES library_books(id) ON DELETE CASCADE,
    UNIQUE KEY uk_book_copy (book_id, copy_number),
    INDEX idx_barcode (barcode),
    INDEX idx_status (status)
);
```

#### `library_members`
Library membership (may differ from school enrollment).
```sql
CREATE TABLE library_members (
    id INT PRIMARY KEY AUTO_INCREMENT,
    school_id INT NOT NULL,
    user_id INT NOT NULL,
    member_type ENUM('student', 'staff', 'parent', 'external') DEFAULT 'student',
    member_number VARCHAR(50),
    max_books INT DEFAULT 3,
    loan_period_days INT DEFAULT 14,
    membership_start DATE,
    membership_end DATE,
    status ENUM('active', 'suspended', 'expired') DEFAULT 'active',
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY uk_school_user (school_id, user_id),
    INDEX idx_member_number (member_number)
);
```

#### `library_borrowings`
Loan records.
```sql
CREATE TABLE library_borrowings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    member_id INT NOT NULL,
    book_id INT NOT NULL,
    copy_id INT,
    issued_date DATE NOT NULL,
    due_date DATE NOT NULL,
    returned_date DATE,
    renewal_count INT DEFAULT 0,
    max_renewals INT DEFAULT 2,
    status ENUM('active', 'returned', 'overdue', 'lost') DEFAULT 'active',
    fine_amount DECIMAL(10,2) DEFAULT 0,
    fine_paid BOOLEAN DEFAULT FALSE,
    fine_waived BOOLEAN DEFAULT FALSE,
    fine_waived_reason TEXT,
    issued_by INT NOT NULL,
    returned_to INT,
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (member_id) REFERENCES library_members(id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES library_books(id) ON DELETE CASCADE,
    FOREIGN KEY (copy_id) REFERENCES library_book_copies(id) ON DELETE SET NULL,
    FOREIGN KEY (issued_by) REFERENCES users(id),
    FOREIGN KEY (returned_to) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_member_status (member_id, status),
    INDEX idx_due_date (due_date, status)
);
```

#### `library_reservations`
Book reservation queue.
```sql
CREATE TABLE library_reservations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    member_id INT NOT NULL,
    book_id INT NOT NULL,
    reserved_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    queue_position INT,
    notified_at DATETIME,
    expires_at DATETIME,
    status ENUM('waiting', 'notified', 'fulfilled', 'expired', 'cancelled') DEFAULT 'waiting',
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (member_id) REFERENCES library_members(id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES library_books(id) ON DELETE CASCADE,
    INDEX idx_book_status (book_id, status),
    INDEX idx_member (member_id)
);
```

#### `library_fines`
Fine calculation and payment records.
```sql
CREATE TABLE library_fines (
    id INT PRIMARY KEY AUTO_INCREMENT,
    borrowing_id INT NOT NULL,
    member_id INT NOT NULL,
    fine_per_day DECIMAL(6,2) NOT NULL,
    days_overdue INT NOT NULL,
    total_fine DECIMAL(10,2) NOT NULL,
    amount_paid DECIMAL(10,2) DEFAULT 0,
    payment_status ENUM('unpaid', 'partial', 'paid', 'waived') DEFAULT 'unpaid',
    payment_method VARCHAR(50),
    payment_reference VARCHAR(100),
    paid_at DATETIME,
    waived_by INT,
    waived_at DATETIME,
    waive_reason TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (borrowing_id) REFERENCES library_borrowings(id) ON DELETE CASCADE,
    FOREIGN KEY (member_id) REFERENCES library_members(id) ON DELETE CASCADE,
    FOREIGN KEY (waived_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_member_status (member_id, payment_status)
);
```

#### `library_settings`
Library configuration per school.
```sql
CREATE TABLE library_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    school_id INT NOT NULL,
    default_loan_period INT DEFAULT 14,
    max_renewals INT DEFAULT 2,
    renewal_period INT DEFAULT 7,
    fine_per_day DECIMAL(6,2) DEFAULT 10.00,
    max_fine_per_book DECIMAL(8,2) DEFAULT 500.00,
    reservation_hold_days INT DEFAULT 2,
    allow_self_checkout BOOLEAN DEFAULT FALSE,
    send_due_reminders BOOLEAN DEFAULT TRUE,
    reminder_days_before INT DEFAULT 2,
    isbn_lookup_api VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    UNIQUE KEY uk_school (school_id)
);
```

### 2.2 API Endpoints (Mobile-First)

| Method | Endpoint | Description | Access |
|:-------|:---------|:------------|:-------|
| **Books** |
| GET | `/api/v1/library/books` | Search/list books | All |
| POST | `/api/v1/library/books` | Add book | Librarian |
| GET | `/api/v1/library/books/{id}` | Get book details | All |
| PUT | `/api/v1/library/books/{id}` | Update book | Librarian |
| GET | `/api/v1/library/books/lookup/{isbn}` | ISBN lookup | Librarian |
| POST | `/api/v1/library/books/{id}/copies` | Add copy | Librarian |
| **Categories** |
| GET | `/api/v1/library/categories` | List categories | All |
| POST | `/api/v1/library/categories` | Create category | Librarian |
| **Borrowing** |
| POST | `/api/v1/library/borrow` | Issue book | Librarian |
| POST | `/api/v1/library/return` | Return book | Librarian |
| POST | `/api/v1/library/renew/{id}` | Renew borrowing | Member |
| GET | `/api/v1/library/borrowings/my` | My borrowed books | Member |
| GET | `/api/v1/library/borrowings/overdue` | Overdue list | Librarian |
| **Reservations** |
| POST | `/api/v1/library/reserve` | Reserve book | Member |
| DELETE | `/api/v1/library/reservations/{id}` | Cancel reservation | Member |
| GET | `/api/v1/library/reservations/my` | My reservations | Member |
| **Fines** |
| GET | `/api/v1/library/fines/my` | My fines | Member |
| POST | `/api/v1/library/fines/{id}/pay` | Pay fine | Member |
| POST | `/api/v1/library/fines/{id}/waive` | Waive fine | Librarian |
| **Scan** |
| POST | `/api/v1/library/scan/book` | Process book scan | Librarian |
| POST | `/api/v1/library/scan/member` | Process member scan | Librarian |

### 2.3 Module Structure

```
app/Modules/Library/
├── Config/
│   ├── Routes.php
│   └── Services.php
├── Controllers/
│   ├── Api/
│   │   ├── BookController.php
│   │   ├── CategoryController.php
│   │   ├── BorrowingController.php
│   │   ├── ReservationController.php
│   │   ├── FineController.php
│   │   └── ScanController.php
│   └── Web/
│       └── LibraryDashboardController.php
├── Models/
│   ├── LibraryCategoryModel.php
│   ├── LibraryBookModel.php
│   ├── LibraryBookCopyModel.php
│   ├── LibraryMemberModel.php
│   ├── LibraryBorrowingModel.php
│   ├── LibraryReservationModel.php
│   ├── LibraryFineModel.php
│   └── LibrarySettingsModel.php
├── Services/
│   ├── BookCatalogService.php
│   ├── ISBNLookupService.php
│   ├── BorrowingService.php
│   ├── ReturnService.php
│   ├── RenewalService.php
│   ├── ReservationService.php
│   ├── FineCalculatorService.php
│   ├── BarcodeService.php
│   └── MembershipService.php
├── Libraries/
│   ├── OpenLibraryClient.php
│   ├── GoogleBooksClient.php
│   └── BarcodeGenerator.php
├── Database/
│   └── Migrations/
│       └── 2025-11-27-000001_CreateLibraryTables.php
├── Views/
│   ├── dashboard/
│   ├── books/
│   ├── borrowings/
│   └── fines/
└── Tests/
    ├── Unit/
    │   └── FineCalculatorTest.php
    └── Feature/
        └── LibraryApiTest.php
```

### 2.4 Integration Points

- **Finance Module**: Fine payments linked to invoices/wallets.
- **Wallets Module**: Students pay fines from wallet balance.
- **Threads Module**: Due date reminders, reservation notifications.
- **Reports Module**: Library tab in student view.
- **Learning Module**: Student class info for borrowing limits.
- **Inventory Module**: Library books as inventory items (optional).

---

## Part 3: Architectural Safeguards
*Target Audience: Architects, Security Engineers*

### 3.1 Borrowing Constraints
- Check member has no unpaid fines before issuing.
- Check member hasn't reached max borrowed books.
- Check book copy is available.
- Use database transaction for borrow/return.

### 3.2 Available Copies Sync
- Trigger updates to `available_copies` on borrow/return.
- Use atomic decrement/increment to prevent race conditions.

```php
public function borrowBook(int $copyId, int $memberId): Borrowing
{
    $this->db->transStart();
    
    // Lock the book copy
    $copy = $this->db->query('SELECT * FROM library_book_copies WHERE id = ? FOR UPDATE', [$copyId])->getRow();
    
    if ($copy->status !== 'available') {
        throw new \Exception('Book is not available');
    }
    
    // Update copy status
    $this->db->table('library_book_copies')->where('id', $copyId)->update(['status' => 'borrowed']);
    
    // Decrement available copies
    $this->db->table('library_books')->where('id', $copy->book_id)->decrement('available_copies');
    
    // Create borrowing record
    // ...
    
    $this->db->transComplete();
}
```

### 3.3 Fine Calculation
- Calculate fines daily via scheduled job.
- Cap fines at `max_fine_per_book` to prevent excessive amounts.
- Consider weekends/holidays based on school settings.

### 3.4 Reservation Queue
- FIFO queue for reservations.
- Auto-expire uncollected reservations.
- Notify next in queue when reservation expires.

---

## Part 4: Test Data Strategy
*Target Audience: QA, Developers*

### 4.1 Seeding Strategy
Use `Modules\Library\Database\Seeds\LibrarySeeder`:

#### Books
- 200 books across 10 categories.
- Mix of fiction, non-fiction, textbooks.
- Some with multiple copies.

#### Members
- All students auto-enrolled.
- Staff with extended limits.

#### Transactions
- 50 active borrowings.
- 20 overdue with fines.
- 10 reservations.

### 4.2 Testing Scenarios
| Scenario | Expected Outcome |
|:---------|:-----------------|
| Borrow with unpaid fine | Blocked |
| Borrow exceeding limit | Blocked |
| Reserve borrowed book | Added to queue |
| Return overdue book | Fine calculated |
| Renew max times | Blocked |

---

## Part 5: Development Checklist

- [ ] **Database**: Create migrations.
- [ ] **Books**: CRUD with ISBN lookup.
- [ ] **Categories**: Category management.
- [ ] **Borrowing**: Issue and return flow.
- [ ] **Renewals**: Renewal with limits.
- [ ] **Reservations**: Queue with notifications.
- [ ] **Fines**: Calculation and payment.
- [ ] **Scanning**: Barcode integration.
- [ ] **Search**: Full-text book search.
- [ ] **Reports**: Library statistics.
