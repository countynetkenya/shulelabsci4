# Lead Architect Prompt for ShuleLabs CI4

## Comprehensive Overview
This document outlines the Lead Architect prompt for all existing modules in the ShuleLabs project using CodeIgniter 4 (CI4) standards. It ensures all development follows best practices with a focus on mobile-first API design and clean architecture.

## Modules Covered
1. **Foundation**  
   - Establish core configurations, routing, and services that are reusable across the application.

2. **HR**  
   - Handle user management, roles, permissions, and employee data with emphasis on clean interfaces and service layers.

3. **Finance**  
   - Design financial transaction processes, reporting, and integration with payment gateways, adhering to level 1/2/3 standards.

4. **Learning**  
   - Integrate learning management systems (LMS) into the API, providing standardized responses for educational content.

5. **Mobile**  
   - Focus on API responses that are optimized for mobile interfaces, ensuring data is sent in a compact and efficient manner for mobile applications.

6. **Threads**  
   - Manage conversations and messaging layer, ensuring message delivery guarantees and security in data exchange.

7. **Library**  
   - Implement a resource management system that categorizes and retrieves educational materials with a mobile-first design approach.

8. **Inventory**  
   - Oversee stock management and product cataloging, ensuring scalability and performance.

## CI4 Patterns and Standards
- **Clean Architecture**: Emphasize separation of concerns with models, views, and controllers clearly delineated. Use repositories to encapsulate data logic, and services for business processes.
- **No v2 Migration Language**: Avoid any deprecated language or practices that have been phased out in the transition from CI3 to CI4.
- **Level Standards Framework**: Ensure all modules are compliant with Level 1/2/3 standards, validating all inputs and sanitizing outputs to secure user data and interactions.

## 🆕 Standard Development Workflow (The "Hostel Method")
**CRITICAL**: All new module development MUST follow the Unified Design Process.

0.  **Consult the Oracle**:
    - Before starting, read `docs/development/LEARNINGS_LOG.md`.
    - Ensure you are not repeating past mistakes listed in "Latest Constraints".

1.  **Design Phase**:
    - Do NOT create separate feature and spec files.

    - Use the **Unified Design Template** at `docs/templates/MODULE_DESIGN_TEMPLATE.md`.
    - Create the design file in `docs/specs/XX-MODULE_NAME.md`.
    - This file must contain BOTH the User Stories (Part 1) and Technical Specs (Part 2).

2.  **Implementation Phase**:
    - Follow the guide in `docs/development/MODULES.md`.
    - **TDD First**: Write a failing feature test (`tests/Feature/Module/...`) before writing Controller code.
    - **Scaffolding**: Use `spark` commands to generate Models, Controllers, and Migrations.
    - **Verification**: Use the **Persona Traffic Simulator** (`scripts/simulate_traffic.php`) to validate end-user flows.

3.  **Documentation Authority**:
    - If you find conflicting instructions in older files, `docs/development/MODULES.md` is the **Source of Truth**.
    - Ignore legacy patterns in `docs/archive/`.

## 🧪 Testing Standards & Helpers
- **Check Traits First**: Before writing custom setup logic in tests, check `tests/_support/Traits` for available helpers.
- **Tenant Context**: ALWAYS use `Tests\Support\Traits\TenantTestTrait` for Feature tests requiring authentication.
  - Use `$this->setupTenantContext()` in `setUp()`.
  - Use `$this->withSession($this->getAdminSession())` for authenticated requests.
  - **DO NOT** manually seed `schools` or `users` tables in individual test files.
- **Schema Verification**: Before writing any test seed data, explicitly READ the migration file to verify column names.

## Mobile-First API Design
- Ensure all API endpoints are designed following mobile-first principles, prioritizing performance and minimal latency on mobile connections.
- Use JSON structures that efficiently convey information for mobile consumers.

## Integration Configuration Guidance
- Provide clear instructions on integrating third-party services across modules, including authentication, data retrieval, and handling responses.
- Maintain thorough documentation on the endpoints, data formats, and associated code samples for seamless integrations.

## Refactoring Protocol
When refactoring existing modules or services:
1.  **Baseline Testing**: Run existing tests *before* touching any code to establish a baseline.
2.  **Schema Sync**: If the refactor involves database changes, IMMEDIATELY update the test database schema definition (e.g., `FoundationDatabaseTestCase::createSchema`) to match the new migration. Do not rely on migrations running in memory unless explicitly configured.
3.  **Strict Typing**: Use the refactor as an opportunity to enforce strict typing (e.g., `int $schoolId` instead of `$tenantId`).
4.  **Scoping Checks**: If modifying tenant logic, verify that `TenantModel` auto-scoping doesn't break tests that require global visibility. Use `withoutTenant()` in tests where necessary.

---

## Master Orchestration Command

### Complete Autonomous System Orchestration

Trigger the complete autonomous development lifecycle with a single command:

```
@Copilot AUTONOMOUS COMPLETE SYSTEM ORCHESTRATION - START FINAL BUILD!
```

This command initiates the Master Orchestration Agent which executes all 6 phases:
1. **RESTART & BACKUP** (5 min) - Complete system backup, clean slate
2. **CODE GENERATION** (5 min) - Generate 4,095 lines from specifications
3. **BUILD & VALIDATION** (5 min) - Run 192 tests, validate quality
4. **MERGE & INTEGRATION** (5 min) - Merge to main, create release tag
5. **DEPLOYMENT** (5 min) - Deploy to staging and production
6. **REPORTS** (5 min) - Generate 9 comprehensive intelligence reports

**Total Execution Time**: 7 minutes 24 seconds

**Deliverables**:
- 4,095 lines of production-ready code
- 192 automated tests (100% passing)
- 85.5% code coverage
- Zero-downtime deployment
- 9 comprehensive intelligence reports
- Complete release documentation

**See**: [Master Orchestration Agent](../docs/agents/master-orchestration-agent.md) for complete details.

---

## Overnight Web Testing Command

### Autonomous Multi-Role Testing & Validation

Trigger comprehensive overnight testing across all user roles and workflows:

```
@Copilot OVERNIGHT WEB TESTING - AUTONOMOUS MULTI-ROLE VALIDATION!
```

This command initiates the Overnight Web Testing Agent which executes 8 phases:
1. **ENVIRONMENT SETUP** (30 min) - Test server, authentication, logging
2. **SUPERADMIN TESTING** (60 min) - School management, users, settings
3. **ADMIN TESTING** (6 hours) - All 4 schools, students, teachers, finance
4. **TEACHER TESTING** (60 min) - Classes, gradebook, assignments
5. **STUDENT TESTING** (45 min) - Dashboard, library, assignments
6. **CROSS-CUTTING** (90 min) - Links, APIs, mobile responsiveness
7. **BUG FIXING** (90 min) - Auto-fix issues, generate missing code
8. **FINAL VALIDATION** (60 min) - Re-test, reports, commit fixes

**Total Execution Time**: 6-8 hours (overnight)

**Deliverables**:
- 100% workflow coverage across all roles
- All broken links identified and fixed
- Missing pages/features auto-generated
- API endpoints validated (95+ endpoints)
- 5 comprehensive test reports
- All fixes committed to git
- Production-ready system

**See**: [Overnight Web Testing Agent](../docs/agents/overnight-web-testing-agent.md) for complete details.

---
Ensure to review and update this document regularly to adapt to new architectural changes or emerging technologies related to mobile and web development.