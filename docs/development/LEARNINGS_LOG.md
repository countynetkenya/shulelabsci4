# Project Learnings & Architectural Evolution Log

This document tracks the evolution of our development process. It is a "Living History" of lessons learned.
**AI INSTRUCTION**: Before starting any new module, review the "Latest Constraints" section below to ensure you are not repeating past mistakes.

## 🟢 Latest Constraints (Active Rules)

1.  **Dual-Interface Mandate**: All modules must implement BOTH an API Controller (JSON) and a Web Controller (HTML Views) simultaneously. Do not wait for a prompt to build the UI.
2.  **Unified Design**: Never create separate Feature/Spec files. Use `docs/templates/MODULE_DESIGN_TEMPLATE.md`.
3.  **TDD First**: Feature tests must exist and fail before Controller code is written.
4.  **SQLite Compatibility**: All migrations must be SQLite compatible (check Foreign Key constraints carefully).
5.  **Route Standardization**: `Routes.php` must explicitly define `api` and `web` route groups.

---

## 📜 History of Learnings

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
