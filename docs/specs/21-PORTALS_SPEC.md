# 🖥️ Student & Parent Portals Module Specification

**Version**: 1.0.0
**Status**: Draft
**Last Updated**: 2025-11-27

---

## Part 1: Feature Definition (The "What" & "Why")
*Target Audience: Product Owners, Stakeholders, Developers*

### 1.1 Overview
The Portals module provides dedicated web interfaces for Students and Parents. These portals offer self-service capabilities, allowing users to view information, perform actions, and communicate with the school without requiring staff intervention. The portals are responsive, mobile-friendly, and provide a seamless experience across devices.

### 1.2 User Stories

#### Student Portal
- **As a Student**, I want to view my daily timetable, so that I know my class schedule.
- **As a Student**, I want to see my grades and report cards, so that I can track my performance.
- **As a Student**, I want to check my attendance record, so that I know if I have any absences.
- **As a Student**, I want to view my fee balance, so that I can inform my parents.
- **As a Student**, I want to see books I've borrowed from the library, so that I can return them on time.
- **As a Student**, I want to view my assignments and submit homework online.

#### Parent Portal
- **As a Parent**, I want a dashboard showing all my children's key information at a glance.
- **As a Parent**, I want to view and pay school fees, so that payments are convenient.
- **As a Parent**, I want to see my child's attendance and receive alerts for absences.
- **As a Parent**, I want to communicate with teachers, so that I can discuss my child's progress.
- **As a Parent with multiple children**, I want to switch between children easily.
- **As a Parent**, I want to download invoices and receipts for my records.

### 1.3 User Workflows

1. **Student Dashboard View**:
   - Student logs into portal.
   - Dashboard displays today's timetable, upcoming assignments, recent grades.
   - Notifications show unread messages, library due dates.
   - Quick links to frequently accessed features.

2. **Parent Fee Payment**:
   - Parent logs into portal.
   - Parent views fee summary for selected child.
   - Parent clicks "Pay Now" on outstanding invoice.
   - Payment gateway (M-Pesa) initiated.
   - Payment confirmed, receipt generated.
   - Email/SMS confirmation sent.

3. **Multi-Child Management**:
   - Parent with multiple children logs in.
   - Child selector shows all children with status indicators.
   - Parent selects child to view details.
   - Context switches to selected child.
   - Parent can quickly switch to another child.

4. **Teacher Communication**:
   - Parent views child's class teachers.
   - Parent starts message thread with teacher.
   - Teacher receives notification and responds.
   - Conversation continues in thread.

### 1.4 Acceptance Criteria

- [ ] Student portal shows all relevant academic and financial information.
- [ ] Parent portal supports multi-child views.
- [ ] Fee payment integrated with M-Pesa.
- [ ] Document downloads (invoices, receipts, report cards) work.
- [ ] Responsive design works on mobile and desktop.
- [ ] Role-based access controls enforced.
- [ ] Notification center shows alerts and messages.
- [ ] Language toggle available (English/Swahili).
- [ ] All data scoped by school_id and user permissions.

---

## Part 2: Technical Specification (The "How")
*Target Audience: Developers, Architects*

### 2.1 Portal Architecture

The portals are built as separate themed views within the main ShuleLabs application, using shared components and services.

```
app/
├── Controllers/
│   ├── Portal/
│   │   ├── StudentPortalController.php
│   │   └── ParentPortalController.php
├── Views/
│   ├── portal/
│   │   ├── student/
│   │   │   ├── layout.php
│   │   │   ├── dashboard.php
│   │   │   ├── timetable.php
│   │   │   ├── grades.php
│   │   │   ├── attendance.php
│   │   │   ├── fees.php
│   │   │   ├── library.php
│   │   │   ├── assignments.php
│   │   │   ├── messages.php
│   │   │   └── profile.php
│   │   └── parent/
│   │       ├── layout.php
│   │       ├── dashboard.php
│   │       ├── child-selector.php
│   │       ├── child-overview.php
│   │       ├── fees.php
│   │       ├── pay.php
│   │       ├── academic.php
│   │       ├── attendance.php
│   │       ├── messages.php
│   │       ├── documents.php
│   │       └── settings.php
```

### 2.2 Student Portal Features

#### Dashboard
- Today's timetable with current/next class highlighted
- Upcoming assignments with due dates
- Recent grades with subject breakdown
- Unread messages count
- Library books due soon
- Gamification points and badges

#### Timetable
- Weekly view with day tabs
- Subject, teacher, and room information
- Current period indicator
- Print/export option

#### Grades
- Current term grades by subject
- Historical term comparison
- Assessment breakdown
- Grade distribution charts
- Report card download

#### Attendance
- Calendar view with status indicators
- Monthly summary statistics
- Detailed day-by-day list
- Excuse request submission

#### Fees
- Current balance summary
- Invoice list with status
- Payment history
- View invoice PDF

#### Library
- Currently borrowed books
- Due date reminders
- Borrowing history
- Search catalog
- Reserve books

#### Assignments
- Pending assignments list
- Assignment details and instructions
- File upload for submission
- Submission status and feedback

### 2.3 Parent Portal Features

#### Dashboard
- All children cards with key metrics
- Quick actions (Pay Fees, Message Teacher)
- School announcements
- Upcoming events

#### Child Overview
- Selected child's summary
- Academic snapshot
- Attendance summary
- Fee balance
- Recent activity

#### Fees
- All children's fee summaries
- Invoice list with filters
- Pay individual or multiple invoices
- Payment history
- Download statements

#### Payment Flow
```
[Select Invoice] → [Review Amount] → [Choose Method] 
    → [M-Pesa STK Push] → [Confirm on Phone] 
    → [Wait for Callback] → [Show Receipt]
```

#### Academic
- Grades per child
- Report cards
- Teacher comments
- Performance trends

#### Attendance
- All children's attendance
- Absence alerts
- Submit excuse notes

#### Messages
- Thread view with teachers
- Start new conversation
- Read receipts
- File attachments

#### Documents
- Invoices and receipts
- Report cards
- School letters
- Admission documents

### 2.4 API Endpoints for Portals

| Method | Endpoint | Description | Access |
|:-------|:---------|:------------|:-------|
| **Student Portal** |
| GET | `/portal/student/dashboard` | Dashboard data | Student |
| GET | `/portal/student/timetable` | Full timetable | Student |
| GET | `/portal/student/grades` | Grades summary | Student |
| GET | `/portal/student/attendance` | Attendance record | Student |
| GET | `/portal/student/fees` | Fee balance | Student |
| GET | `/portal/student/library` | Library status | Student |
| GET | `/portal/student/assignments` | Assignment list | Student |
| POST | `/portal/student/assignments/{id}/submit` | Submit work | Student |
| **Parent Portal** |
| GET | `/portal/parent/dashboard` | Dashboard data | Parent |
| GET | `/portal/parent/children` | Children list | Parent |
| GET | `/portal/parent/children/{id}/overview` | Child overview | Parent |
| GET | `/portal/parent/fees` | All fees | Parent |
| POST | `/portal/parent/fees/pay` | Initiate payment | Parent |
| GET | `/portal/parent/children/{id}/grades` | Child grades | Parent |
| GET | `/portal/parent/children/{id}/attendance` | Child attendance | Parent |
| GET | `/portal/parent/documents` | Documents list | Parent |
| GET | `/portal/parent/documents/{id}/download` | Download document | Parent |

### 2.5 Shared Components

#### Notification Center
```php
// Views/portal/components/notification-center.php
- Unread count badge
- Dropdown with recent notifications
- Mark as read
- View all link
```

#### Child Selector (Parent)
```php
// Views/portal/components/child-selector.php
- Avatar + name
- Status indicator (fees due, grades)
- Dropdown or sidebar
- Store selection in session
```

#### Document Viewer
```php
// Views/portal/components/document-viewer.php
- PDF preview
- Download button
- Share option
```

### 2.6 Integration Points

- **Finance Module**: Fee data, payment processing.
- **Learning Module**: Timetable, grades, attendance.
- **Library Module**: Borrowings, reservations.
- **Threads Module**: Messages between parents and teachers.
- **Integrations Module**: M-Pesa payments, notifications.
- **Reports Module**: Report card generation.

---

## Part 3: Architectural Safeguards
*Target Audience: Architects, Security Engineers*

### 3.1 Access Control
- Students can only view their own data.
- Parents can only view their children's data.
- Parent-child relationships verified on every request.
- Session-based child selection validated.

### 3.2 Payment Security
- Payment requests validated server-side.
- Amount verification before processing.
- Idempotency keys prevent duplicate payments.
- Callback verification with signature.

### 3.3 Document Access
- Documents served through controller, not direct links.
- Access verified before download.
- Audit log for document access.

### 3.4 Session Management
- Secure session cookies.
- CSRF protection on all forms.
- Session timeout with warning.
- Remember-me with secure token.

---

## Part 4: Test Data Strategy

### 4.1 Seeding Strategy
- Sample students with complete profiles.
- Parents linked to children.
- Full term of academic data.
- Sample invoices and payments.
- Library borrowings.

### 4.2 Testing Scenarios
| Scenario | Expected Outcome |
|:---------|:-----------------|
| Student views grades | Own grades displayed |
| Parent pays invoice | Payment processed, receipt shown |
| Parent switches child | Context updates |
| Access other student's data | Access denied |
| Download report card | PDF generated |

---

## Part 5: Development Checklist

- [ ] **Student Portal**: Layout and navigation.
- [ ] **Student Portal**: Dashboard with widgets.
- [ ] **Student Portal**: All feature pages.
- [ ] **Parent Portal**: Layout and navigation.
- [ ] **Parent Portal**: Child selector.
- [ ] **Parent Portal**: Dashboard.
- [ ] **Parent Portal**: Fee payment flow.
- [ ] **Parent Portal**: All feature pages.
- [ ] **Shared**: Notification center.
- [ ] **Shared**: Document viewer.
- [ ] **Responsive**: Mobile testing.
- [ ] **i18n**: Language support.
