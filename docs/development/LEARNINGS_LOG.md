# Project Learnings & Architectural Evolution Log

This document tracks the evolution of our development process. It is a "Living History" of lessons learned.
**AI INSTRUCTION**: Before starting any new module, review the "Latest Constraints" section below to ensure you are not repeating past mistakes.

## 🟢 Latest Constraints (Active Rules)

1.  **Dual-Interface Mandate**: All modules must implement BOTH an API Controller (JSON) and a Web Controller (HTML Views) simultaneously. Do not wait for a prompt to build the UI.
2.  **Unified Design**: Never create separate Feature/Spec files. Use `docs/templates/MODULE_DESIGN_TEMPLATE.md`.
3.  **TDD First**: Feature tests must exist and fail before Controller code is written.
4.  **SQLite Compatibility**: All migrations must be SQLite compatible (check Foreign Key constraints carefully).
5.  **Route Standardization**: `Routes.php` must explicitly define `api` and `web` route groups.
6.  **Ample Test Data**: Always create a Seeder (`Modules\X\Database\Seeds\XSeeder`) with realistic data scenarios before running tests. Document this data in the Spec.
7.  **Universal Terminal Pattern**: For any module involving "Issuing" or "Selling" (POS, Library, Inventory), use the standard "Cart-Based" UI layout.
8.  **Paperless Handshake**: Use the `Threads` module for digital confirmations (Transfers, Issues) instead of paper trails.
9.  **API Response Hygiene**: Ensure API Controllers explicitly disable Debug Toolbar and CSRF filters in `app/Config/Filters.php` to prevent HTML injection into JSON responses.
10. **Strict Schema Validation**: When writing tests, verify that Enum values in factories/seeders match the database schema exactly (e.g., `physical` vs `consumable`).

---

## 📜 History of Learnings

### Cycle 09: Inventory V2 Completion (Nov 2025)
- **Issue**: API Tests failing due to HTML being injected into JSON responses.
- **Fix**: Explicitly disabled Debug Toolbar and CSRF for `api/*` routes in `Filters.php`.
- **Issue**: SQLite `CHECK` constraint failures in tests.
- **Fix**: Ensured test data (Seeders/Factories) strictly matches the Enum values defined in Migrations (`physical` vs `consumable`).
- **Issue**: Controller naming conflicts (`StockApiController` vs `InventoryStockController`).
- **Fix**: Adopted explicit naming convention `[Module][Entity]Controller` to avoid collisions.

### Cycle 08: POS & Inventory V2 (Nov 2025)
- **Issue**: Siloed interfaces for Sales, Loans, and Issues.
- **Fix**: Adopted "Universal Terminal" design pattern.
- **Issue**: Paper-heavy workflows for stock transfer.
- **Fix**: Implemented "Paperless Handshake" using Threads integration.

### Cycle 07: Finance Module (Nov 2025)
- **Issue**: Tests were initially dry and didn't reflect real-world scenarios.
- **Fix**: Mandated the creation of a Module Seeder and a "Test Data Strategy" section in the Design Spec.
- **Issue**: JSON number formatting in tests (`15000` vs `15000.00`).
- **Fix**: Be flexible with data types in assertions or cast explicitly.

### Cycle 06: Inventory Module (Nov 2025)
- **Issue**: Frontend was initially neglected.
- **Fix**: Updated Design Template to include "Interface Design" section.
- **Issue**: SQLite Foreign Key errors in tests.
- **Fix**: Established strict Factory creation order in `setUp()` (Parents before Children).
- **Issue**: Route file was messy.
- **Fix**: Adopted standard grouping pattern.

### Cycle 05: Hostel Module (Nov 2025)
- **Issue**: "V2" language caused confusion.
- **Fix**: Banned "V2" terminology; strict CI4 standards only.
- **Issue**: Spec fragmentation.
- **Fix**: Created Unified Design Template.
