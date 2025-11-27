# [Module Name] Design Document

**Version**: 1.0.0
**Status**: Draft
**Last Updated**: YYYY-MM-DD

---

## Part 1: Feature Definition (The "What" & "Why")
*Target Audience: Product Owners, Stakeholders, Developers*

### 1.1 Overview
*Briefly describe the purpose of this module and the problem it solves.*

### 1.2 User Stories
*Format: As a [Role], I want to [Action], so that [Benefit].*
- As a **Student**, I want to...
- As a **Teacher**, I want to...
- As an **Admin**, I want to...

### 1.3 User Workflows
*Describe the key steps a user takes to complete a task.*
1.  **Requesting an Item**:
    *   User logs in.
    *   Navigates to...
    *   Clicks...

### 1.4 Acceptance Criteria
*List the conditions that must be met for the feature to be considered complete.*
- [ ] User can view...
- [ ] System validates...

---

## Part 2: Technical Specification (The "How")
*Target Audience: Developers, Architects*

### 2.1 Database Schema
*Define the tables, columns, and relationships.*

#### `table_name`
```sql
CREATE TABLE table_name (
    id INT PRIMARY KEY AUTO_INCREMENT,
    ...
);
```

### 2.2 API Endpoints (Mobile-First)
*Define the JSON API endpoints.*
- `GET /api/module` - List items
- `POST /api/module` - Create item

### 2.3 Web Interface (Views & Controllers)
*Define the HTML views and controller logic.*
- **Controller**: `App\Modules\[Module]\Controllers\[Module]WebController`
- **Views**:
    - `index.php`: List view (DataTables/Grid)
    - `create.php`: Creation form
    - `edit.php`: Edit form
    - `show.php`: Detail view
- **Routes**:
    - `GET /module` -> `index`
    - `GET /module/new` -> `new`
    - `POST /module` -> `create`


---

## Part 3: Web Interface Specification (The "View")
*Target Audience: Frontend Developers, Full Stack Developers*

### 3.1 Web Routes
*Define the browser-based routes.*
- `GET /module/resource` (List View)
- `GET /module/resource/new` (Create Form)

### 3.2 Views Required
*List the HTML files needed in `app/Views/modules/[module]/`.*
1.  `index.php` - Data Table / List
2.  `create.php` - Input Form
3.  `show.php` - Detail View


| Method | Endpoint | Description | Access |
|:-------|:---------|:------------|:-------|
| GET    | /api/v1/resource | List resources | Auth |
| POST   | /api/v1/resource | Create resource | Admin |

### 2.3 Models & Validation
*Define the validation rules for the models.*
- **Field A**: required, integer
- **Field B**: max_length[255]

### 2.4 Integration Points
*How does this module interact with others (e.g., Finance, Auth)?*

---

## Part 3: Development Checklist
- [ ] **Design**: Review and approve this document.
- [ ] **Tests**: Write failing feature tests (TDD).
- [ ] **Scaffold**: Generate files (Controllers, Models, Migrations).
- [ ] **Database**: Run migrations and verify schema.
- [ ] **Code**: Implement logic to pass tests.
- [ ] **Review**: Code review and merge.
