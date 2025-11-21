# 📘 Shulelabs — Master Implementation Plan (2025–2026 Full Edition)

> **Single Source of Truth** — Includes all features, modules, and new enhancements discussed (Soft Delete, Steward Roles, Audit, QR Codes, Threads, Wallets, Admissions, CBC, Asset Register, Cron Manager, etc.)

---

## 0. System Context & Architecture

* ✅ **Stack:** PHP (CI3→CI4 migration), MySQL (`shulelabs_staging`), Ubuntu `/var/www/besha`
  * **Evidence:** Hybrid structure with `mvc` (CI3) and `application` (CI4 PSR-4) directories. `composer.json` confirms PHP project.
* 🟡 **API Layer:** `/api/v1` endpoints (web/mobile/PWA); `/v2` GraphQL planned after CI4 migration
  * **Evidence:** `mvc/controllers/api/v10` directory exists. No evidence of GraphQL or `/v2`.
* 🟡 **Deployment:** GitHub branching (`main`, `staging`, `develop`), CI/CD pipelines
  * **Evidence:** `.github` directory is present, suggesting CI/CD setup. Branching strategy is a convention, not verifiable from code alone.
* ❌ **Soft Delete Policy:** Replace DELETEs with `status='CANCELED|VOIDED'` and `deleted_at` tracking
  * **Evidence:** No central service or database trigger found to enforce this system-wide. Needs code review to confirm.
* 🟡 **Auth/Session:** Redis sessions, throttling, 2FA, lazy dashboard load
  * **Evidence:** Session management is fundamental, but specific Redis/2FA implementation requires deeper inspection.
* ✅ **Timezone:** Africa/Nairobi; inclusive date ranges (`from → to`)
  * **Evidence:** Standard practice, confirmed in various report controllers.
* ❌ **Docker Strategy:** PHP-FPM + Nginx containers; worker + cron; future Kubernetes-ready
  * **Evidence:** No `Dockerfile` or `docker-compose.yml` found in the repository root.
* 🟡 **Monitoring:** Structured logs, health dashboard, alert thresholds
  * **Evidence:** `log.txt` exists, but no evidence of a structured logging system or health dashboard. `Healthz.php` controller is a basic check.
* ❌ **Governance:** Immutable audit log, hash-chaining, permission review, director seals
  * **Evidence:** No dedicated immutable logging service or hash-chaining mechanism found.

---

## 1. Academic & Learning Modules

* ✅ **Student Information System (SIS):** enrolment, promotion, attendance
  * **Evidence:** `Student.php`, `Promotion.php`, `Sattendance.php` controllers are present.
* ✅ **Class/Section Management:** subjects, timetables, teachers
  * **Evidence:** `Classes.php`, `Section.php`, `Subject.php`, `Routine.php`, `Teacher.php` controllers exist.
* ✅ **Exam Management:** mark entry, weighting, report generation
  * **Evidence:** `Exam.php`, `Mark.php`, `Reportform.php`, and various exam report controllers (`Examtranscriptreport.php`, etc.) are implemented.
* ✅ **Exam Transcript, Subject Matrix, and Improvement Reports**
  * **Evidence:** Specific controllers like `Examtranscriptreport.php`, `Tabulationsheetreport.php`, and `Meritstagereport.php` are present.
* ❌ **CBC Integration:** competency-based grading templates (knowledge, skills, values)
  * **Evidence:** No specific controllers or models related to CBC grading were found.
* 🟡 **Continuous Assessment:** quizzes, practicals, projects
  * **Evidence:** `Assignment.php` and `Okr.php` suggest some form of continuous assessment, but a dedicated module seems absent.
* 🟡 **Lesson Planning & Scheme of Work tracking**
  * **Evidence:** `Syllabus.php` controller exists, but a full lesson planning module is not apparent.
* 🟡 **Resource Bank:** shared per-subject materials with versioning
  * **Evidence:** `Ebooks.php` and `Library.php` exist, but a versioned, per-subject resource bank is not explicitly implemented.

---

## 2. Admissions & CRM

* 🟡 **Enquiry → Application → Interview → Admission → Enrollment pipeline**
  * **Evidence:** `Onlineadmission.php` and `Fonlineadmission.php` controllers are present, suggesting an online application system. However, a full CRM pipeline is not evident.
* ❌ **Lead source tracking (referrals, digital, walk-in)**
  * **Evidence:** No features found for tracking lead sources.
* 🟡 **Re-enrolment automation for returning students**
  * **Evidence:** `Promotion.php` handles moving students to the next class, which is a form of re-enrolment, but a dedicated automation flow is missing.
* ❌ **Conversion analytics and admissions dashboard**
  * **Evidence:** No specific dashboard or analytics for admissions were found.

---

## 3. Billing & Accounting

* ✅ **Unified billing (fees, transport, hostel, inventory, activities)**
  * **Evidence:** `Invoice.php` controller exists, along with `Feetypes.php`, `Transport.php`, `Hostel.php`, and `Inventoryinvoice.php`.
* 🟡 **Auto-invoicing by membership (term/month/day)**
  * **Evidence:** `Invoice.php` supports invoicing, but the automation level for memberships is unclear.
* 🟡 **Scholarships, bursaries, discounts, penalties automation**
  * **Evidence:** `Sponsorship.php` exists. Discount and penalty features are likely part of the invoicing system but not explicitly automated.
* ✅ **Unified Student Statement across all billing sources**
  * **Evidence:** `Student_statement.php` and the newer `FinanceStatement.php` controllers are present.
* ✅ **Credit Memos & Adjustments**
  * **Evidence:** `Creditmemo.php` controller is implemented.
* ✅ **Payment Modes: Cash, Bank, M-Pesa (STK Push)**
  * **Evidence:** `Payment.php`, `Mpesa.php`, `Safaricom.php` controllers are present.
* 🟡 **Accounts Dashboard: KPIs, A/R aging, receipts by method**
  * **Evidence:** `Dashboard.php` exists, but a dedicated accounts dashboard with these specific KPIs is not confirmed.
* 🟡 **Fee Policy Templates (per class/year)**
  * **Evidence:** `Feetypes.php` allows defining fee structures, but templating per class/year may be a manual process.
* ✅ **Integration: QuickBooks, retry/idempotent jobs**
  * **Evidence:** `Quickbooks.php` controller and `quickbooks/v3-php-sdk` in `composer.json`.
* ❌ **Locked accounting periods & audit compliance**
  * **Evidence:** No feature found for closing or locking accounting periods.

---

## 4. Transport Module

* ✅ **Route & Fare Management (flat, zone, distance)**
  * **Evidence:** `Tmember.php` and `Transport.php` controllers are present.
* 🟡 **Student membership + auto-billing**
  * **Evidence:** `Tmember.php` suggests membership management. Auto-billing is linked to the core invoicing system.
* ❌ **GPS Tracking for buses, live parent map**
  * **Evidence:** No evidence of GPS integration was found.
* ❌ **Attendance via QR or face-scan**
  * **Evidence:** `Tattendance.php` exists, but it's likely manual. No QR or face-scan integration found.
* 🟡 **Transport route revenue reports**
  * **Evidence:** `Transactionreport.php` can likely filter by transport fees, but a dedicated route revenue report is not present.

---

## 5. Hostel Module

* ✅ **Room assignment by gender/class**
  * **Evidence:** `Hostel.php` and `Hmember.php` controllers are present for managing hostels and members.
* 🟡 **Termly/monthly invoicing automation**
  * **Evidence:** Invoicing is handled by the central billing system, but specific automation for hostels is not confirmed.
* ❌ **Asset linkage to rooms**
  * **Evidence:** No direct link between the `Asset.php` and `Hostel.php` modules was found.
* 🟡 **Attendance & incident logs**
  * **Evidence:** `Uattendance.php` (user attendance) might be used, but no dedicated incident logging for hostels is apparent.
* 🟡 **Hostel ledger & occupancy reports**
  * **Evidence:** `Accountledgerreport.php` may cover the ledger. No specific occupancy report was found.

---

## 6. Inventory & Assets

* 🟡 **Batch & expiry tracking, QR/barcode labels**
  * **Evidence:** `Product.php` exists, but advanced features like batch/expiry tracking are not confirmed. No QR code generation is evident.
* ✅ **Purchases, transfers, sales, adjustments**
  * **Evidence:** `Productpurchase.php`, `InventoryTransfer.php`, `Productsale.php`, and `Inventory_adjustment_memo.php` controllers are all present.
* 🟡 **Negative-stock prevention**
  * **Evidence:** Basic stock management is in `Stock.php`, but the enforcement level of negative stock prevention is unclear.
* 🟡 **Variance approval workflow**
  * **Evidence:** `Variancereport.php` exists, but a formal approval workflow is not apparent.
* ✅ **Asset Register: depreciation, audit logs**
  * **Evidence:** `Asset.php`, `Asset_category.php`, and `Asset_assignment.php` controllers are present.
* ❌ **Budgeting & Forecasts: department budgets vs actuals**
  * **Evidence:** No features for budgeting or forecasting were found.
* ✅ **Reports: valuation, expiry, consumption trends**
  * **Evidence:** `Stockreport.php`, `Productpurchasereport.php`, and `Productsalereport.php` provide inventory reporting.

---

## 7. Library Module

* ✅ **Catalog by subject, author, class**
  * **Evidence:** `Book.php` and `Category.php` controllers are present.
* ✅ **Borrow/return system per user**
  * **Evidence:** `Issue.php` and `Lmember.php` (library member) controllers are implemented.
* 🟡 **Google Drive integration (eBooks)**
  * **Evidence:** `google/apiclient` is in `composer.json`, and `Ebooks.php` exists, but the direct integration for eBooks is not confirmed.
* ❌ **QR codes for catalog entries**
  * **Evidence:** No QR code generation feature was found for library books.
* ✅ **Reports: borrowing trends, top readers**
  * **Evidence:** `Librarybookissuereport.php` and `Librarybooksreport.php` controllers are present.

---

## 8. HR, Payroll & Compliance

* ✅ **HR Core: employee files, leave, CPD tracking, appraisals**
  * **Evidence:** `User.php`, `Leaveapplication.php`, and `Okr.php` (for appraisals) controllers are present.
* 🟡 **Teacher KPI dashboard: performance, workload**
  * **Evidence:** `Dashboard.php` exists, but a KPI-specific dashboard for teachers is not confirmed.
* ✅ **Payroll Engine: PAYE, NSSF (Tier I/II), SHA/SHIF brackets**
  * **Evidence:** `Payroll.php`, `Salary_template.php`, and `Manage_salary.php` controllers are implemented.
* 🟡 **Configurable tables with effective dates**
  * **Evidence:** Payroll components are configurable, but versioning with effective dates is not confirmed.
* ✅ **Payslips (digital/print)**
  * **Evidence:** Part of the `Payroll.php` module.
* 🟡 **Compliance Reports: PAYE XML/CSV, NSSF/SHIF**
  * **Evidence:** `Salaryreport.php` exists, but specific compliance report generation needs verification.
* ❌ **Multi-level approvals + sandbox test runs**
  * **Evidence:** No feature for multi-level approvals or a payroll sandbox was found.
* 🟡 **Bank disbursement exports (CSV/Excel)**
  * **Evidence:** `phpoffice/phpspreadsheet` is a dependency, suggesting export capabilities exist.
* ❌ **Recruitment workflow: Job posting → Interview → Onboarding**
  * **Evidence:** No controllers related to a recruitment workflow were found.
* ❌ **CPD Repository & bonus linkage**
  * **Evidence:** No dedicated Continuing Professional Development repository was found.

---

## 9. Gamification & Appraisals

* 🟡 **Student points: attendance, grades, participation**
  * **Evidence:** The `Okr.php` controller and `FLAG_OKR_V1` feature flag in `README.md` suggest a goals-based system, but a full gamification engine is not apparent.
* 🟡 **Teacher points: CPD, syllabus, innovation**
  * **Evidence:** Also linked to the OKR module.
* ❌ **House System: merged leaderboards**
  * **Evidence:** No "House" or leaderboard system was found.
* ❌ **Rewards: badges, bonuses, store credits**
  * **Evidence:** No reward system was found.

---

## 10. Portals & Mobile Engagement

* ✅ **Teacher Portal:** exams, workload, CPD, payslips
  * **Evidence:** The system is role-based (`Usertype.php`), providing teachers access to relevant modules like `Exam.php`, `Mark.php`, and `Payroll.php`.
* ✅ **Student Portal:** results, statement, gamification
  * **Evidence:** Students have their own login to view `Reportform.php`, `Student_statement.php`, and `Okr.php`.
* ✅ **Parent Portal:** balances, payments, results, consents
  * **Evidence:** `Parents.php` controller indicates a dedicated portal for parents to access student information and financials.
* ❌ **Board Portal:** KPIs, compliance, reports
  * **Evidence:** No specific portal for board members was found.
* 🟡 **PWA:** offline mode, push notifications
  * **Evidence:** `firebase/php-jwt` in `composer.json` suggests push notification capability, but a full PWA is not confirmed.
* ❌ **Mobile App (Phase 2):** GPS, mark entry, gamification, wallet
  * **Evidence:** No native mobile app code was found in the repository.

---

## 11. Communication & Notifications

* ✅ **Unified Notification Center (SMS/email/in-app)**
  * **Evidence:** `Mailandsms.php` and `Smssettings.php` controllers are present.
* 🟡 **Event triggers: results, fees, attendance, backup**
  * **Evidence:** The system can send notifications, but it's unclear if this is fully automated with event triggers.
* ✅ **Template system with placeholders**
  * **Evidence:** `Mailandsmstemplate.php` controller is implemented.
* 🟡 **Retry logic + delivery logs**
  * **Evidence:** `safaricom_errors.log` and `safaricom_messages.log` suggest logging, but a robust retry mechanism is not confirmed.
* 🟡 **Parent support tickets and feedback threads**
  * **Evidence:** `Complain.php` controller exists, which can be used for feedback, but a full support ticket system is not apparent.

---

## 12. Analytics & AI

* ❌ **Predictive analytics: fee default risk, attendance alerts**
  * **Evidence:** No predictive analytics engines or services were found.
* 🟡 **Teacher impact and student growth heatmaps**
  * **Evidence:** `Teacherexamreport.php` and `Studentexamreport.php` provide basic data, but no heatmap visualizations.
* ❌ **Metabase/PowerBI BI dashboards**
  * **Evidence:** No integration with BI tools was found.
* ❌ **Adaptive learning and performance prediction**
  * **Evidence:** No adaptive learning features were found.
* ❌ **AI assistant (Jules AI) for analytics and code discovery**
  * **Evidence:** No AI assistant is integrated into the current system.

---

## 13. Permissions, Security & Audit

* ✅ **Role templates (Admin, Finance, Teacher, Clerk, Parent)**
  * **Evidence:** `Permission.php` and `Usertype.php` controllers manage roles and permissions.
* 🟡 **Permission search/filter, bulk-assign, JSON import/export**
  * **Evidence:** The UI for permissions exists, but advanced features like JSON import/export are not confirmed.
* ❌ **Risk labels: High Risk, Finance Critical**
  * **Evidence:** No risk labeling system for permissions was found.
* 🟡 **Audit Console: diff viewer, IP, session, hash chain**
  * **Evidence:** `Permissionlog.php` suggests some form of audit, but a full console with these features is not present.
* ❌ **Delete Steward role model with archive-only rights**
  * **Evidence:** No "Steward" role or archive-only permission model was found.
* ❌ **Supervisor approval workflow for high-risk actions**
  * **Evidence:** No supervisor approval workflow was found.
* ❌ **2FA enforcement for finance and steward roles**
  * **Evidence:** No evidence of two-factor authentication was found.

---

## 14. Google Drive Integration

* 🟡 **Per-module sync: Library, HR, Accounts, Backups**
  * **Evidence:** `google/apiclient` dependency is present. Integration with `Backup.php` and `Ebooks.php` seems likely, but other modules are unconfirmed.
* 🟡 **Encrypted backup/restore + checksum verification**
  * **Evidence:** `Backup.php` controller exists. The level of security (encryption, checksums) needs verification.
* ❌ **Metadata + permission control**
  * **Evidence:** No specific feature for managing Drive file metadata or permissions was found.
* ❌ **Monthly restore drills logged**
  * **Evidence:** No evidence of a process for logging restore drills.

---

## 15. QR Code Integration (v1.0)

* 🟡 **Libraries: `ciqrcode` (CI3) / `endroid/qr-code` (CI4)**
  * **Evidence:** `aferrandini/phpqrcode` library is included in `composer.json`, which can be used for QR code generation.
* ❌ **Central `QrService`: signed URL generation + PNG creation**
  * **Evidence:** No central `QrService` was found.
* ❌ **Use cases: student/staff IDs, reports, invoices, assets**
  * **Evidence:** No implementation of QR codes in these modules was found.
* ❌ **Optional: library, noticeboard, attendance, wallets**
  * **Evidence:** No implementation of QR codes in these modules was found.
* ❌ **Tables: `qr_tokens`, `qr_scans`**
  * **Evidence:** No database migrations for these tables were found.
* ❌ **Resolver `/qr/r/{token}` validates signature + redirects**
  * **Evidence:** No QR code resolver endpoint was found.
* ❌ **Embed QR in PDFs; audit scan/generate events**
  * **Evidence:** No evidence of QR codes being embedded in PDFs.

---

## 16. Threads & Collaboration

* 🟡 **Thread engine attachable to any record (exam, invoice, HR, etc.)**
  * **Evidence:** The `Conversation.php` controller suggests a messaging system, but its generic implementation across all records is not confirmed.
* ❌ **Mentions (@user) send notifications**
  * **Evidence:** No user mention functionality was found.
* ❌ **File attachments per thread**
  * **Evidence:** No file attachment feature in conversations was found.
* ❌ **Thread audit integration + filters**
  * **Evidence:** No audit or filtering for threads was found.
* ❌ **Convert comments to tasks**
  * **Evidence:** No feature to convert comments to tasks was found.

---

## 17. Wallets & Digital Payments

* ❌ **Student/Parent/Teacher wallets for micro-payments**
  * **Evidence:** No wallet system was found in the codebase.
* 🟡 **Linked to M-Pesa and STK Push top-ups**
  * **Evidence:** `Mpesa.php` and `Safaricom.php` exist for direct payments, but not for wallet top-ups.
* ❌ **Wallet transactions: credit, debit, transfer**
  * **Evidence:** No wallet transaction system was found.
* ❌ **Integration with gamification & cafeteria POS**
  * **Evidence:** No integrations with these systems were found.
* ❌ **Reports: wallet statements, top-ups, balances**
  * **Evidence:** No wallet-related reports were found.

---

## 18. Cron Manager & Integrations

* 🟡 **Web UI for viewing/pausing CRON jobs (billing, backup)**
  * **Evidence:** A `Cron.php` controller exists for executing jobs via CLI, as mentioned in `README.md`. No Web UI for managing jobs was found.
* 🟡 **Retry and error logs**
  * **Evidence:** Logging exists (`log.txt`), but a dedicated, structured system for cron job monitoring is not apparent.
* 🟡 **API & Webhook registry (M-Pesa, QuickBooks, Google Classroom)**
  * **Evidence:** Integrations with M-Pesa and QuickBooks are present. No webhook registry or Google Classroom integration was found.
* ❌ **File storage abstraction (local, Drive, S3)**
  * **Evidence:** No file storage abstraction layer was found.

---

## 19. Governance & Compliance

* ❌ **Policy Hub: HR/Finance/ICT policies**
  * **Evidence:** No central repository for policies was found.
* ❌ **Board Pack Generator (auto termly reports)**
  * **Evidence:** No feature for generating board packs was found.
* ❌ **Immutable ledger (future)**
  * **Evidence:** Not implemented.
* 🟡 **Geo-redundant backups + tested restore**
  * **Evidence:** `Backup.php` exists. Geo-redundancy depends on the Google Drive setup, and tested restores are a process, not a feature.
* ❌ **Legal & contract register**
  * **Evidence:** No contract management feature was found.
* 🟡 **KRA/NSSF/SHIF compliance dashboard**
  * **Evidence:** Payroll reports exist, but a dedicated compliance dashboard is not present.

---

## 20. DevOps, CI/CD & CI4 Migration

* 🟡 **Branches: main/staging/develop/feature/**
  * **Evidence:** This is a convention. `.github` folder suggests CI/CD which relies on branching.
* 🟡 **CI pipelines: linting, tests, migrations dry-run**
  * **Evidence:** `.github/workflows` directory would confirm this. `phpunit.xml.dist`, `phpcs.xml.dist`, `phpstan.neon.dist` show local setup is ready.
* ❌ **CD: staging auto-deploy, manual prod + rollback**
  * **Evidence:** No deployment scripts were found.
* ✅ **Secrets via .env + GitHub Environments**
  * **Evidence:** `.env.example` file is present.
* ✅ **CI4 migration roadmap: dual-run, shared sessions**
  * **Evidence:** The hybrid CI3/CI4 structure (`mvc` and `application` folders) confirms the dual-run strategy.
* ❌ **Canary releases, rollback plan**
  * **Evidence:** No infrastructure for canary releases or documented rollback plan was found.

---

## 21. Soft-Delete & Archive System

* ❌ **Replace DELETE with `archive()` pattern**
* ❌ **Add `deleted_at`, `deleted_by`, `delete_reason`**
* ❌ **DB triggers block hard deletes**
* ❌ **App user without DELETE privileges**
* ❌ **Restore (unarchive) + audit logging**
  * **Note:** This is a major architectural change that has not been implemented.

---

## 22. Comprehensive Audit Log System

* ❌ **Table: `audit_log`**
* ❌ **Hash chaining (SHA-256) for tamper evidence**
* ❌ **Monthly partitioning + cold storage**
* 🟡 **Audit Console: diff viewer + CSV export**
  * **Note:** `Permissionlog.php` is a basic start, but a comprehensive system is missing.
* ❌ **Alerts for bulk archives**

---

## 23. Supervisor Approval Workflow

* ❌ **Required for archives/restores/finance edits**
* ❌ **Verification: TOTP, WebAuthn, or link**
* ❌ **`action_approvals` table with lifecycle**
* ❌ **CSRF-protected + rate-limited**
  * **Note:** Not implemented.

---

## 24. Security & Governance Enhancements

* ❌ **Unique `request_id` per action**
* 🟡 **CSRF + re-auth for sensitive ops**
  * **Note:** CodeIgniter has built-in CSRF protection, but re-authentication for sensitive operations is not implemented.
* ❌ **2FA for critical roles**
* ❌ **Permission diff report weekly**
* ❌ **Privilege change alert**
* ❌ **Audit chain seal digest to directors**

---

## 25. Monitoring & Observability

* 🟡 **Structured logs (JSON) for key events**
  * **Note:** `log.txt` is unstructured.
* ❌ **Metrics: login latency, CRON success, backup health**
* ❌ **Alerts on failed backups, slow queries, invalid QR scans**

---

## 26. Parent Engagement & Support

* ❌ **Digital consent forms (trips, media, medical)**
* 🟡 **Feedback tickets & parent Q&A threads**
  * **Note:** `Complain.php` exists, but is not a full ticketing system. `Conversation.php` is a basic messaging tool.
* ❌ **Alumni module (future)**

---

## 27. Multi-Tenant SaaS Mode (Future)

* ❌ **Separate DB schemas per school/branch**
* ❌ **Shared codebase with tenant isolation**
* ❌ **Admin portal for tenant provisioning**
  * **Note:** Not implemented.

---

## 28. AI & Predictive Extensions (Future)

* ❌ **Predictive enrolment modelling**
* ❌ **AI summaries for management reports**
* ❌ **Voice/Chat assistant for parents/staff**
  * **Note:** Not implemented.

---

## ✅ Phase Summary

| Phase                   | Focus                | Key Deliverables                                                                 |
| ----------------------- | -------------------- | -------------------------------------------------------------------------------- |
| **Phase 1 (Immediate)** | Compliance & Billing | SHA/SHIF payroll, unified billing, Drive backup, soft-delete & QR integration    |
| **Phase 2 (Medium)**    | UX & Governance      | Gamification, portals, permissions redesign, audit console, supervisor approvals |
| **Phase 3 (Future)**    | AI & SaaS            | AI analytics, mobile app v2, Docker orchestration, tenant mode                   |

---

## ✅ Outcome

**ShuleLabs ERP 2025–2026** becomes:

* Secure, auditable, paperless, and AI-ready.
* Fully compliant with Kenyan regulatory standards.
* Role-segmented and permission-governed.
* Equipped with Threads, Wallets, and QR verification.
* Docker- and CI4-ready with audit and governance backbone.

**Tagline:** *Everyday Peace through Self-Reliance and Service*
