# 📦 Module Development Guide

**Version**: 2.0.0
**Last Updated**: 2025-11-27
**Status**: Active

## 1. Overview
This guide outlines the standard process for developing new modules in ShuleLabs CI4. It incorporates lessons learned from previous modules (like Hostel) to ensure speed, quality, and maintainability.

## 2. The Unified Design Process
To streamline development, we now combine **Feature Definitions** (User needs) and **Technical Specifications** (Implementation details) into a single design phase.

### Why?
- Ensures the technical solution directly addresses the user problem.
- Reduces context switching between "Product" and "Engineering" docs.
- Catch architectural issues before writing code.

### How to Start
1.  Copy the template: `docs/templates/MODULE_DESIGN_TEMPLATE.md`.
2.  Create a new file: `docs/specs/XX-MODULE_NAME.md`.
3.  Fill out **Part 1 (Features)** first to define the scope.
4.  Fill out **Part 2 (Specs)** to plan the implementation.

## 3. Development Workflow (The "Hostel" Method)

### Phase 1: Design & Plan
- [ ] **Write the Design Doc**: Use the template above.
- [ ] **Review**: Get sign-off on the schema and API endpoints.

### Phase 2: Test-Driven Setup (TDD)
*Improvement: Write tests BEFORE code.*
- [ ] **Create Feature Test**: Create `tests/Feature/Module/YourModuleTest.php`.
- [ ] **Write Failing Test**: Assert that `GET /api/your-module` returns 200 (it will fail 404).
- [ ] **Write Unit Tests**: Define expected model validation failures.

### Phase 3: Scaffolding & Database
*Improvement: Automate where possible.*
- [ ] **Generate Files**: Use `spark` to create the core files.
  ```bash
  php spark make:model YourModel --namespace Modules\YourModule\Models
  # 1. API Controller (Mobile-First)
  php spark make:controller Api/YourController --namespace Modules\YourModule\Controllers\Api
  # 2. Web Controller (Admin Panel)
  php spark make:controller Web/YourWebController --namespace Modules\YourModule\Controllers\Web
  php spark make:migration CreateYourTable
  ```
- [ ] **Robust Migrations**:
  - Use `CREATE TABLE IF NOT EXISTS`.
  - Avoid database-specific SQL (like raw foreign key constraints) if possible; use CodeIgniter's Forge methods.
  - **Verify**: Run `php spark migrate` immediately to check for errors (especially SQLite vs MySQL differences).

### Phase 4: Implementation
- [ ] **Models**: Define `allowedFields` and `validationRules` immediately.
- [ ] **Routes**: Register routes in `app/Modules/YourModule/Config/Routes.php`.
- [ ] **Controllers**: Implement the logic to make your tests pass.

### Phase 5: Verification
- [ ] **Run Tests**: `php spark test`.
- [ ] **Manual Check**: Use the **Persona Traffic Simulator** to simulate real usage.
  - Add your new module's actions to `scripts/simulate_traffic.php`.

## 4. Key Improvements & Standards

### 4.1 Automation
We are moving towards automating the scaffolding process.
- **Goal**: A single command `php spark make:module Name` that creates the folder structure, routes file, and base classes.

### 4.2 Database Compatibility
- **Issue**: SQLite and MySQL handle Foreign Keys and `ALTER TABLE` differently.
- **Solution**:
  - Always check if a table/column exists before creating it in migrations.
  - When dropping columns in SQLite, be aware it may require a full table rebuild.
  - Test migrations on both engines if possible.

### 4.3 Dual-Interface Strategy
- **API (Mobile-First)**:
  - Located in `Controllers/Api`.
  - Extends `ResourceController`.
  - Returns JSON for mobile apps.
- **Web (Admin Panel)**:
  - Located in `Controllers/Web`.
  - Extends `BaseController`.
  - Returns HTML Views for school admins.
  - **Mandatory**: Every module must have a web interface.

## 5. Reference Structure
```
app/Modules/YourModule/
├── Config/
│   └── Routes.php
├── Controllers/
│   ├── Api/
│   │   └── YourApiController.php
│   └── Web/
│       └── YourWebController.php
├── Models/
│   └── YourModel.php
└── Views/ (Standard location: app/Views/modules/your_module/)
```
