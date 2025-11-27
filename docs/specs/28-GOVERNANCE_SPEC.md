# 🏛️ Governance Module Specification

**Version**: 1.0.0
**Status**: Phase 3 (Future)
**Last Updated**: 2025-11-27

---

## Part 1: Feature Definition (The "What" & "Why")
*Target Audience: Product Owners, Stakeholders, Developers*

### 1.1 Overview
The Governance module is the "Board Room" of ShuleLabs. It provides tools for school governance including board management, meeting scheduling, minutes tracking, document repository, voting systems, resolution tracking, committee management, policy documentation, and compliance tracking. This module supports school boards and management committees in their oversight responsibilities.

### 1.2 User Stories

- **As a Board Chair**, I want to schedule board meetings with agendas, so that members are prepared.
- **As a Board Member**, I want to access meeting documents before meetings, so that I can review them.
- **As a Board Secretary**, I want to record minutes and track action items, so that decisions are documented.
- **As a Committee Chair**, I want to manage my committee's activities, so that we fulfill our mandate.
- **As a Principal**, I want to track board resolutions, so that I can implement decisions.
- **As an Administrator**, I want to maintain policy documents, so that governance is transparent.

### 1.3 User Workflows

1. **Meeting Management**:
   - Secretary schedules meeting with date, time, venue.
   - Secretary creates agenda with items.
   - System notifies board members.
   - Members access documents before meeting.
   - Meeting conducted with attendance tracking.
   - Minutes recorded and distributed.

2. **Resolution Tracking**:
   - Board discusses and votes on resolution.
   - Resolution recorded with voting outcome.
   - Action items assigned to responsible parties.
   - Progress tracked until implementation.
   - Status reported in subsequent meetings.

3. **Document Management**:
   - Administrator uploads policy document.
   - Document versioned and tagged.
   - Board approves document.
   - Document published to appropriate audience.
   - Acknowledgment tracked.

### 1.4 Acceptance Criteria

- [ ] Board members can be managed with roles.
- [ ] Meetings scheduled with agenda creation.
- [ ] Documents shared securely with members.
- [ ] Voting captured with electronic signatures.
- [ ] Minutes recorded and approved.
- [ ] Resolutions tracked to completion.
- [ ] Committees managed independently.
- [ ] Policies versioned and approved.
- [ ] Compliance reports generated.
- [ ] All data scoped by school_id.

---

## Part 2: Technical Specification (The "How")
*Target Audience: Developers, Architects*

### 2.1 Database Schema

#### `governance_boards`
Board configurations.
```sql
CREATE TABLE governance_boards (
    id INT PRIMARY KEY AUTO_INCREMENT,
    school_id INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    board_type ENUM('main', 'advisory', 'committee') DEFAULT 'main',
    description TEXT,
    term_start DATE,
    term_end DATE,
    meeting_frequency VARCHAR(50),
    quorum_percentage INT DEFAULT 50,
    is_active BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    INDEX idx_school_active (school_id, is_active)
);
```

#### `board_members`
Board membership.
```sql
CREATE TABLE board_members (
    id INT PRIMARY KEY AUTO_INCREMENT,
    board_id INT NOT NULL,
    user_id INT,
    external_name VARCHAR(150),
    external_email VARCHAR(255),
    external_phone VARCHAR(20),
    role ENUM('chair', 'vice_chair', 'secretary', 'treasurer', 'member') DEFAULT 'member',
    term_start DATE,
    term_end DATE,
    status ENUM('active', 'inactive', 'resigned', 'removed') DEFAULT 'active',
    bio TEXT,
    photo_path VARCHAR(500),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (board_id) REFERENCES governance_boards(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_board_status (board_id, status)
);
```

#### `board_meetings`
Meeting records.
```sql
CREATE TABLE board_meetings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    board_id INT NOT NULL,
    meeting_type ENUM('regular', 'special', 'emergency', 'annual') DEFAULT 'regular',
    title VARCHAR(255) NOT NULL,
    meeting_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME,
    venue VARCHAR(255),
    is_virtual BOOLEAN DEFAULT FALSE,
    virtual_link VARCHAR(500),
    status ENUM('scheduled', 'in_progress', 'completed', 'cancelled', 'postponed') DEFAULT 'scheduled',
    quorum_met BOOLEAN,
    created_by INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (board_id) REFERENCES governance_boards(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id),
    INDEX idx_board_date (board_id, meeting_date)
);
```

#### `meeting_agenda_items`
Agenda items.
```sql
CREATE TABLE meeting_agenda_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    meeting_id INT NOT NULL,
    item_number VARCHAR(20) NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    item_type ENUM('information', 'discussion', 'decision', 'action') DEFAULT 'discussion',
    presenter_id INT,
    time_allocated_minutes INT,
    documents JSON,
    status ENUM('pending', 'discussed', 'deferred', 'completed') DEFAULT 'pending',
    outcome TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (meeting_id) REFERENCES board_meetings(id) ON DELETE CASCADE,
    FOREIGN KEY (presenter_id) REFERENCES board_members(id) ON DELETE SET NULL,
    INDEX idx_meeting (meeting_id)
);
```

#### `meeting_attendance`
Attendance tracking.
```sql
CREATE TABLE meeting_attendance (
    id INT PRIMARY KEY AUTO_INCREMENT,
    meeting_id INT NOT NULL,
    member_id INT NOT NULL,
    status ENUM('present', 'absent', 'excused', 'proxy') DEFAULT 'present',
    proxy_name VARCHAR(150),
    arrival_time TIME,
    departure_time TIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (meeting_id) REFERENCES board_meetings(id) ON DELETE CASCADE,
    FOREIGN KEY (member_id) REFERENCES board_members(id) ON DELETE CASCADE,
    UNIQUE KEY uk_meeting_member (meeting_id, member_id)
);
```

#### `meeting_minutes`
Meeting minutes.
```sql
CREATE TABLE meeting_minutes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    meeting_id INT NOT NULL,
    content TEXT NOT NULL,
    status ENUM('draft', 'submitted', 'approved') DEFAULT 'draft',
    submitted_at DATETIME,
    approved_at DATETIME,
    approved_in_meeting_id INT,
    prepared_by INT NOT NULL,
    approved_by INT,
    file_path VARCHAR(500),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (meeting_id) REFERENCES board_meetings(id) ON DELETE CASCADE,
    FOREIGN KEY (prepared_by) REFERENCES users(id),
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY uk_meeting (meeting_id)
);
```

#### `resolutions`
Board resolutions.
```sql
CREATE TABLE resolutions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    school_id INT NOT NULL,
    meeting_id INT,
    resolution_number VARCHAR(50) NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    category VARCHAR(100),
    proposed_by INT,
    seconded_by INT,
    votes_for INT DEFAULT 0,
    votes_against INT DEFAULT 0,
    votes_abstain INT DEFAULT 0,
    status ENUM('proposed', 'approved', 'rejected', 'deferred', 'implemented') DEFAULT 'proposed',
    implementation_deadline DATE,
    responsible_party INT,
    implementation_notes TEXT,
    implemented_at DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (meeting_id) REFERENCES board_meetings(id) ON DELETE SET NULL,
    UNIQUE KEY uk_school_number (school_id, resolution_number),
    INDEX idx_status (status)
);
```

#### `governance_documents`
Policy and governance documents.
```sql
CREATE TABLE governance_documents (
    id INT PRIMARY KEY AUTO_INCREMENT,
    school_id INT NOT NULL,
    category ENUM('policy', 'procedure', 'bylaw', 'report', 'template', 'other') NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    version VARCHAR(20) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    effective_date DATE,
    review_date DATE,
    status ENUM('draft', 'under_review', 'approved', 'archived') DEFAULT 'draft',
    approved_by INT,
    approved_at DATETIME,
    requires_acknowledgment BOOLEAN DEFAULT FALSE,
    created_by INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id),
    INDEX idx_school_category (school_id, category)
);
```

### 2.2 API Endpoints

| Method | Endpoint | Description | Access |
|:-------|:---------|:------------|:-------|
| **Boards** |
| GET | `/api/v1/governance/boards` | List boards | Admin |
| POST | `/api/v1/governance/boards` | Create board | Admin |
| GET | `/api/v1/governance/boards/{id}/members` | List members | Board |
| **Meetings** |
| GET | `/api/v1/governance/meetings` | List meetings | Board |
| POST | `/api/v1/governance/meetings` | Schedule meeting | Secretary |
| GET | `/api/v1/governance/meetings/{id}` | Meeting details | Board |
| POST | `/api/v1/governance/meetings/{id}/agenda` | Add agenda item | Secretary |
| POST | `/api/v1/governance/meetings/{id}/attendance` | Mark attendance | Secretary |
| **Minutes** |
| GET | `/api/v1/governance/meetings/{id}/minutes` | Get minutes | Board |
| POST | `/api/v1/governance/meetings/{id}/minutes` | Save minutes | Secretary |
| POST | `/api/v1/governance/minutes/{id}/approve` | Approve minutes | Chair |
| **Resolutions** |
| GET | `/api/v1/governance/resolutions` | List resolutions | Board |
| POST | `/api/v1/governance/resolutions` | Create resolution | Secretary |
| PUT | `/api/v1/governance/resolutions/{id}/status` | Update status | Admin |
| **Documents** |
| GET | `/api/v1/governance/documents` | List documents | Board |
| POST | `/api/v1/governance/documents` | Upload document | Admin |
| POST | `/api/v1/governance/documents/{id}/approve` | Approve document | Board |

### 2.3 Module Structure

```
app/Modules/Governance/
├── Config/
│   └── Routes.php
├── Controllers/
│   ├── Api/
│   │   ├── BoardController.php
│   │   ├── MeetingController.php
│   │   ├── MinutesController.php
│   │   ├── ResolutionController.php
│   │   └── DocumentController.php
│   └── Web/
│       └── GovernanceDashboardController.php
├── Models/
│   ├── GovernanceBoardModel.php
│   ├── BoardMemberModel.php
│   ├── BoardMeetingModel.php
│   ├── AgendaItemModel.php
│   ├── MeetingAttendanceModel.php
│   ├── MeetingMinutesModel.php
│   ├── ResolutionModel.php
│   └── GovernanceDocumentModel.php
├── Services/
│   ├── BoardService.php
│   ├── MeetingService.php
│   ├── MinutesService.php
│   ├── ResolutionService.php
│   └── DocumentService.php
├── Database/
│   └── Migrations/
│       └── 2025-11-27-000001_CreateGovernanceTables.php
└── Views/
    ├── dashboard/
    ├── meetings/
    └── documents/
```

---

## Part 3: Development Checklist

- [ ] **Boards**: CRUD and membership.
- [ ] **Meetings**: Scheduling and agenda.
- [ ] **Attendance**: Tracking and quorum.
- [ ] **Minutes**: Recording and approval.
- [ ] **Resolutions**: Voting and tracking.
- [ ] **Documents**: Upload and versioning.
- [ ] **Notifications**: Meeting reminders.
- [ ] **Reports**: Governance reports.
