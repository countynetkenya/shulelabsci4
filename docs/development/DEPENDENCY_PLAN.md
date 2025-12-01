# 🗺️ Module Dependency & Development Plan

This document outlines the strategic dependency plan for the ShuleLabs CI4 project. It serves as the roadmap for the "Master Orchestration" of module development, ensuring that dependencies are met before dependent modules are built.

## 🔗 Module Dependency Matrix

Understanding these dependencies is critical for avoiding circular references and ensuring stable builds.

| Module | Depends On | Blocks |
|:-------|:-----------|:-------|
| **Foundation** | *None* | **Everything** |
| **Security** | Foundation | All user-facing modules |
| **Audit** | Foundation | Compliance modules |
| **Finance** | Foundation, Security | Wallets, POS, Reports, Transport, Hostel |
| **Learning** | Foundation, Security | Reports, Portals, Gamification |
| **HR** | Foundation, Security | Payroll, Reports |
| **Transport** | Foundation, Finance | Mobile, Reports |
| **Wallets** | Foundation, Finance | POS, Mobile |
| **Hostel** | Foundation, Finance | Reports |
| **Inventory** | Foundation | POS |
| **POS** | Inventory, Wallets | Reports |
| **Library** | Foundation | Gamification |
| **Admissions** | Foundation, Finance | Reports |
| **Scheduler** | Foundation | All async tasks |
| **Threads** | Foundation | All notifications |
| **Integrations** | Foundation | Mobile, Portals |
| **Mobile** | All core modules | *None* |
| **Portals** | All core modules | *None* |
| **Reports** | All data modules | *None* |
| **Gamification** | Learning, Library | *None* |
| **Monitoring** | Foundation | *None* |
| **Analytics/AI** | All modules | *None* |

---

## 🌊 Development Waves (Execution Plan)

Development is organized into 5 waves. Modules within a wave can be developed in parallel.

### 🟢 Wave 1: Foundation Layer
**Goal**: Establish the bedrock services (Auth, Tenant Context, Logging).

| Module | Spec | Status |
|:-------|:-----|:-------|
| **Foundation** | `docs/specs/22-FOUNDATION_SPEC.md` | ✅ Complete |
| **Security** | `docs/specs/23-SECURITY_SPEC.md` | ✅ Complete |
| **Audit** | `docs/specs/24-AUDIT_SPEC.md` | ✅ Complete |

### 🔵 Wave 2: Core Business Modules
**Goal**: Enable revenue generation and core school operations.
*Prerequisite: Wave 1 Complete*

| Module | Spec | Status |
|:-------|:-----|:-------|
| **Finance** | `docs/specs/03-FINANCE_SPEC.md` | ⚪ Pending |
| **Learning** | `docs/specs/14-LEARNING_SPEC.md` | ⚪ Pending |
| **HR** | `docs/specs/15-HR_SPEC.md` | ⚪ Pending |
| **Transport** | `docs/specs/09-TRANSPORT_SPEC.md` | ⚪ Pending |
| **Wallets** | `docs/specs/10-WALLETS_SPEC.md` | ⚪ Pending |
| **Hostel** | `docs/specs/05-HOSTEL_SPEC.md` | ⚪ Pending |

### 🟣 Wave 3: Supporting Modules
**Goal**: Enhance functionality and connect systems.
*Prerequisite: Wave 2 Complete*

| Module | Spec | Status |
|:-------|:-----|:-------|
| **Inventory** | `docs/specs/06-INVENTORY_SPEC.md` | 🟢 Complete (V2) |
| **POS** | `docs/specs/07-POS_SPEC.md` | ⚪ Pending |
| **Library** | `docs/specs/16-LIBRARY_SPEC.md` | ⚪ Pending |
| **Admissions** | `docs/specs/11-ADMISSIONS_SPEC.md` | ⚪ Pending |
| **Scheduler** | `docs/specs/12-SCHEDULER_SPEC.md` | ⚪ Pending |
| **Threads** | `docs/specs/18-THREADS_SPEC.md` | ⚪ Pending |
| **Integrations** | `docs/specs/19-INTEGRATIONS_SPEC.md` | ⚪ Pending |

### 🟠 Wave 4: User-Facing Modules
**Goal**: Provide interfaces for all user personas.
*Prerequisite: Waves 1-3 Complete*

| Module | Spec | Status |
|:-------|:-----|:-------|
| **Mobile** | `docs/specs/20-MOBILE_SPEC.md` | ⚪ Pending |
| **Portals** | `docs/specs/21-PORTALS_SPEC.md` | ⚪ Pending |
| **Parent Engagement** | `docs/specs/13-PARENT_ENGAGEMENT_SPEC.md` | ⚪ Pending |
| **Reports** | `docs/specs/08-REPORTS_SPEC.md` | ⚪ Pending |
| **Approval Workflows** | `docs/specs/25-APPROVAL_WORKFLOWS_SPEC.md` | ⚪ Pending |

### 🔴 Wave 5: Enhancement Modules
**Goal**: Polish, optimize, and future-proof.
*Prerequisite: Waves 1-4 Complete*

| Module | Spec | Status |
|:-------|:-----|:-------|
| **Gamification** | `docs/specs/17-GAMIFICATION_SPEC.md` | ⚪ Pending |
| **Monitoring** | `docs/specs/26-MONITORING_SPEC.md` | ⚪ Pending |
| **Analytics & AI** | `docs/specs/27-ANALYTICS_AI_SPEC.md` | ⚪ Pending |
| **Governance** | `docs/specs/28-GOVERNANCE_SPEC.md` | ⚪ Pending |
| **AI Extensions** | `docs/specs/29-AI_EXTENSIONS_SPEC.md` | ⚪ Pending |
| **Multi-Tenant** | `docs/specs/30-MULTI_TENANT_SPEC.md` | ⚪ Pending |

---

## 🛠️ Integration Checklist

When moving between waves, ensure the following integration points are verified:

1.  **Finance Integration**: Can the new module create invoices?
2.  **Audit Logging**: Are all critical actions logged to the Foundation Audit service?
3.  **Tenant Scoping**: Do all queries respect `school_id`?
4.  **RBAC**: Are permissions correctly defined in Security?

## 🚀 How to Use This Plan

1.  **Pick a Wave**: Start with Wave 1 if not complete.
2.  **Select a Module**: Choose a module from the current wave.
3.  **Read the Spec**: Open the linked specification file.
4.  **Execute**: Follow the "Module Development Protocol" in `docs/development/MODULES.md`.
5.  **Update Status**: Change "Pending" to "In Progress" then "Complete" in this file.
