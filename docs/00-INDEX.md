# 🧠 ShuleLabs CI4 Documentation Index

Welcome to the ShuleLabs CI4 "Brain". This index serves as the central map for all documentation, specifications, and architectural decisions.

## 📂 Structure

### 1. 🏗️ Architecture (`docs/architecture/`)
System design, database schemas, and security protocols.
- [System Architecture](architecture/ARCHITECTURE.md)
- [Database Schema](architecture/DATABASE.md)
- [Security Protocols](architecture/SECURITY.md)
- [API Reference](architecture/API-REFERENCE.md)

### 2. 🤖 Orchestration (`docs/orchestration/`)
Automated build systems, agents, and workflows.
- [Orchestration README](orchestration/README.md)
- [Quickstart Guide](orchestration/QUICKSTART.md)
- [Agents](orchestration/agents/)

### 3. 📘 Developer Guides (`docs/guides/`)
How-to guides for developers.
- [Start Here](guides/START_HERE.md)
- [**Module Development Standard**](development/MODULES.md) 👈 **(Read this for new features)**
- [**Reports Development Guide**](development/REPORTS.md) 👈 **(Read this for reporting features)**
- [Reports Integration Checklist](development/REPORTS_INTEGRATION_CHECKLIST.md)
- [Developer Guide](guides/DEVELOPER_GUIDE.md)
- [Testing Guide](guides/TESTING.md)
- [Docker Setup](guides/DOCKER.md)

### 4. ⚡ Prompts & AI Instructions (`docs/prompts/`)
Context files for AI agents.
- [Master Orchestrator](prompts/MASTER_ORCHESTRATOR.md)
- [Super Developer Prompt](prompts/SUPER_DEVELOPER.md)

### 5. 📊 Reports (`docs/reports/`)
System status and historical logs.
- [Latest System Status](reports/LATEST_STATUS.md)
### 6. 📝 Specifications (`docs/specs/`)
Detailed feature specifications.
- [Finance Module](specs/03-FINANCE_SPEC.md)
- [Hostel Module](specs/05-HOSTEL_SPEC.md)
- [Inventory Module](specs/06-INVENTORY_SPEC.md)
- [POS Module](specs/07-POS_SPEC.md)
- [**Reports Module**](specs/08-REPORTS_SPEC.md) 👈 **NEW**

---

## 📊 Reports Architecture Quick Reference

### Entity Views & Available Tabs

| Entity | Available Tabs |
|:-------|:---------------|
| **Student** | Overview, Finance, Academic, Library, Transport, Inventory, Hostel, Threads |
| **Parent** | Overview, Children, Finance, Academic, Threads |
| **Staff** | Overview, HR, Payroll, Classes, Threads |
| **Class** | Overview, Students, Finance, Academic, Attendance, Threads |
| **Book** | Details, Availability, Borrowing History |
| **Inventory Item** | Details, Stock, Transactions, Issued To |
| **Room** | Details, Occupants, History, Finance |
| **Route** | Overview, Stops, Students, Schedule, Finance |
| **School** | Overview, Enrollment, Finance, Staff, Performance |

### Report Development Quick Checklist

| Step | Standalone Report | Embedded Report |
|:-----|:------------------|:----------------|
| 1. Create Class | `Reports/Standalone/{Module}/{Name}Report.php` | `Reports/Embedded/{Entity}/{Name}Tab.php` |
| 2. Interface | `ReportDefinitionInterface` | `EmbeddedReportInterface` |
| 3. Register | Add to `standalone_reports` array | Add to `entity_tabs` array |
| 4. Tenant Scope | ✅ All queries use `school_id` | ✅ All queries use `school_id` |
| 5. Caching | Use `CacheService` | Use `CacheService` |
| 6. Invalidation | Fire events on data change | Fire events on data change |
| 7. Testing | Unit + Feature + Performance | Unit + Feature + Permissions |

**Full Guide**: [Reports Development Guide](development/REPORTS.md)

---

## 🚀 Quick Links
- **Main README**: [README.md](../README.md)
- **API Docs**: [OpenAPI Spec](../docs/openapi.yaml)
