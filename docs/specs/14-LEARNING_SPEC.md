# 📚 Learning & Academic Module Specification

**Version**: 1.0.0
**Status**: Implemented (Documentation)
**Last Updated**: 2025-11-27

---

## Part 1: Feature Definition (The "What" & "Why")
*Target Audience: Product Owners, Stakeholders, Developers*

### 1.1 Overview
The Learning module is the "Academic Engine" of ShuleLabs. It manages the complete academic lifecycle including class structures, subject allocation, timetabling, attendance tracking, gradebook management, examinations, and report cards. This module is the foundation upon which student academic performance is tracked and communicated.

### 1.2 User Stories

- **As an Admin**, I want to define classes, sections, and streams, so that students can be organized properly.
- **As an Academic Director**, I want to allocate subjects to teachers, so that each class has proper coverage.
- **As a Scheduler**, I want to generate timetables with conflict detection, so that there are no overlapping lessons.
- **As a Teacher**, I want to mark student attendance daily, so that absenteeism is tracked.
- **As a Teacher**, I want to enter grades into a gradebook, so that student performance is recorded.
- **As a Teacher**, I want to create and assign homework/assignments, so that students have work to complete.
- **As an Examinations Officer**, I want to schedule exams and enter marks, so that report cards can be generated.
- **As a Parent**, I want to view my child's report card, so that I understand their academic progress.
- **As a Student**, I want to see my timetable on my phone, so that I know my daily schedule.

### 1.3 User Workflows

1. **Academic Structure Setup**:
   - Admin creates academic years and terms.
   - Admin creates classes (Grade 1, Grade 2, etc.).
   - Admin creates sections/streams within classes (A, B, C).
   - Admin creates subjects and assigns to classes.
   - Admin allocates teachers to subject-class combinations.

2. **Timetable Management**:
   - Scheduler defines time slots and breaks.
   - Scheduler assigns subjects to time slots.
   - System detects conflicts (teacher double-booked, room unavailable).
   - Scheduler publishes timetable.
   - Teachers and students view timetable.

3. **Daily Attendance**:
   - Teacher opens class at period start.
   - Teacher marks students present/absent/late.
   - System records attendance with timestamp.
   - Absent student's parent receives notification.
   - Reports show attendance patterns.

4. **Gradebook & Assessment**:
   - Teacher creates assessment (quiz, test, assignment).
   - Teacher enters marks for each student.
   - System calculates averages and rankings.
   - Teacher reviews and publishes grades.
   - Students/parents view grades.

5. **Examination & Report Cards**:
   - Exam officer schedules exam dates.
   - Teachers enter exam marks.
   - System generates report cards.
   - Admin reviews and approves.
   - Parents download/view report cards.

6. **Promotion & Retention**:
   - At term/year end, system calculates eligibility.
   - Students meeting criteria are promoted.
   - Borderline cases flagged for review.
   - Admin makes final decisions.
   - New class assignments created.

### 1.4 Acceptance Criteria

- [ ] Classes, sections, and subjects are configurable per school.
- [ ] Teacher-subject-class allocations prevent conflicts.
- [ ] Timetable generates without double-booking teachers or rooms.
- [ ] Attendance can be marked via web or mobile with offline support.
- [ ] Gradebook calculates averages using configurable weightings.
- [ ] Report cards generate with school branding and grading scale.
- [ ] Academic calendar displays terms, holidays, and exam periods.
- [ ] All data scoped by school_id for multi-tenancy.
- [ ] Students and parents can only view their own data.

---

## Part 2: Technical Specification (The "How")
*Target Audience: Developers, Architects*

### 2.1 Database Schema

#### `academic_years`
Academic calendar years.
```sql
CREATE TABLE academic_years (
    id INT PRIMARY KEY AUTO_INCREMENT,
    school_id INT NOT NULL,
    name VARCHAR(50) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    is_current BOOLEAN DEFAULT FALSE,
    status ENUM('planning', 'active', 'completed') DEFAULT 'planning',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    INDEX idx_school_current (school_id, is_current)
);
```

#### `academic_terms`
Terms within an academic year.
```sql
CREATE TABLE academic_terms (
    id INT PRIMARY KEY AUTO_INCREMENT,
    academic_year_id INT NOT NULL,
    name VARCHAR(50) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    is_current BOOLEAN DEFAULT FALSE,
    term_number INT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (academic_year_id) REFERENCES academic_years(id) ON DELETE CASCADE,
    INDEX idx_year (academic_year_id)
);
```

#### `classes`
Grade levels or forms.
```sql
CREATE TABLE classes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    school_id INT NOT NULL,
    name VARCHAR(50) NOT NULL,
    code VARCHAR(20),
    level INT,
    category ENUM('pre_primary', 'primary', 'secondary', 'high_school') DEFAULT 'primary',
    class_teacher_id INT,
    capacity INT DEFAULT 40,
    is_active BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (class_teacher_id) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY uk_school_code (school_id, code)
);
```

#### `sections`
Streams or divisions within a class.
```sql
CREATE TABLE sections (
    id INT PRIMARY KEY AUTO_INCREMENT,
    class_id INT NOT NULL,
    name VARCHAR(20) NOT NULL,
    capacity INT DEFAULT 40,
    room_id INT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    UNIQUE KEY uk_class_name (class_id, name)
);
```

#### `subjects`
Academic subjects.
```sql
CREATE TABLE subjects (
    id INT PRIMARY KEY AUTO_INCREMENT,
    school_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(20),
    category ENUM('core', 'elective', 'extra_curricular') DEFAULT 'core',
    credit_hours DECIMAL(3,1) DEFAULT 1.0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    UNIQUE KEY uk_school_code (school_id, code)
);
```

#### `class_subjects`
Subjects taught in each class.
```sql
CREATE TABLE class_subjects (
    id INT PRIMARY KEY AUTO_INCREMENT,
    class_id INT NOT NULL,
    subject_id INT NOT NULL,
    is_mandatory BOOLEAN DEFAULT TRUE,
    periods_per_week INT DEFAULT 5,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    UNIQUE KEY uk_class_subject (class_id, subject_id)
);
```

#### `teacher_allocations`
Teacher assignments to subject-class combinations.
```sql
CREATE TABLE teacher_allocations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    teacher_id INT NOT NULL,
    class_id INT NOT NULL,
    section_id INT,
    subject_id INT NOT NULL,
    academic_year_id INT NOT NULL,
    is_primary BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    FOREIGN KEY (section_id) REFERENCES sections(id) ON DELETE SET NULL,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    FOREIGN KEY (academic_year_id) REFERENCES academic_years(id) ON DELETE CASCADE,
    INDEX idx_teacher (teacher_id),
    INDEX idx_class (class_id)
);
```

#### `student_classes`
Student enrollment in classes.
```sql
CREATE TABLE student_classes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    class_id INT NOT NULL,
    section_id INT,
    academic_year_id INT NOT NULL,
    roll_number VARCHAR(20),
    status ENUM('active', 'transferred', 'promoted', 'graduated', 'dropped') DEFAULT 'active',
    enrolled_at DATE NOT NULL,
    exited_at DATE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    FOREIGN KEY (section_id) REFERENCES sections(id) ON DELETE SET NULL,
    FOREIGN KEY (academic_year_id) REFERENCES academic_years(id) ON DELETE CASCADE,
    INDEX idx_student (student_id),
    INDEX idx_class_year (class_id, academic_year_id)
);
```

#### `timetable_slots`
Time slot definitions.
```sql
CREATE TABLE timetable_slots (
    id INT PRIMARY KEY AUTO_INCREMENT,
    school_id INT NOT NULL,
    name VARCHAR(50),
    day_of_week TINYINT NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    slot_type ENUM('lesson', 'break', 'lunch', 'assembly') DEFAULT 'lesson',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    INDEX idx_school_day (school_id, day_of_week)
);
```

#### `timetable_entries`
Actual lesson assignments.
```sql
CREATE TABLE timetable_entries (
    id INT PRIMARY KEY AUTO_INCREMENT,
    slot_id INT NOT NULL,
    class_id INT NOT NULL,
    section_id INT,
    subject_id INT NOT NULL,
    teacher_id INT NOT NULL,
    room_id INT,
    academic_year_id INT NOT NULL,
    effective_from DATE,
    effective_to DATE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (slot_id) REFERENCES timetable_slots(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    FOREIGN KEY (section_id) REFERENCES sections(id) ON DELETE SET NULL,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (academic_year_id) REFERENCES academic_years(id) ON DELETE CASCADE,
    INDEX idx_class (class_id, section_id),
    INDEX idx_teacher (teacher_id)
);
```

#### `attendance_records`
Daily attendance tracking.
```sql
CREATE TABLE attendance_records (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    class_id INT NOT NULL,
    attendance_date DATE NOT NULL,
    status ENUM('present', 'absent', 'late', 'excused', 'half_day') DEFAULT 'present',
    check_in_time TIME,
    check_out_time TIME,
    reason TEXT,
    marked_by INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    FOREIGN KEY (marked_by) REFERENCES users(id),
    UNIQUE KEY uk_student_date (student_id, attendance_date),
    INDEX idx_class_date (class_id, attendance_date)
);
```

#### `assessments`
Tests, quizzes, and assignments.
```sql
CREATE TABLE assessments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    class_id INT NOT NULL,
    section_id INT,
    subject_id INT NOT NULL,
    academic_term_id INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    assessment_type ENUM('quiz', 'test', 'assignment', 'project', 'exam', 'practical') DEFAULT 'test',
    max_marks DECIMAL(6,2) NOT NULL,
    weight_percentage DECIMAL(5,2) DEFAULT 100.00,
    date_assigned DATE,
    due_date DATE,
    is_published BOOLEAN DEFAULT FALSE,
    created_by INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    FOREIGN KEY (section_id) REFERENCES sections(id) ON DELETE SET NULL,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    FOREIGN KEY (academic_term_id) REFERENCES academic_terms(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id),
    INDEX idx_class_subject (class_id, subject_id)
);
```

#### `grades`
Individual student grades.
```sql
CREATE TABLE grades (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    assessment_id INT NOT NULL,
    student_id INT NOT NULL,
    marks_obtained DECIMAL(6,2),
    grade_letter VARCHAR(5),
    remarks TEXT,
    submitted_at DATETIME,
    graded_by INT,
    graded_at DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (assessment_id) REFERENCES assessments(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (graded_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY uk_assessment_student (assessment_id, student_id),
    INDEX idx_student (student_id)
);
```

#### `grading_scales`
Grade letter definitions.
```sql
CREATE TABLE grading_scales (
    id INT PRIMARY KEY AUTO_INCREMENT,
    school_id INT NOT NULL,
    name VARCHAR(50) NOT NULL,
    is_default BOOLEAN DEFAULT FALSE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE
);
```

#### `grading_scale_ranges`
Percentage to grade mappings.
```sql
CREATE TABLE grading_scale_ranges (
    id INT PRIMARY KEY AUTO_INCREMENT,
    grading_scale_id INT NOT NULL,
    grade_letter VARCHAR(5) NOT NULL,
    min_percentage DECIMAL(5,2) NOT NULL,
    max_percentage DECIMAL(5,2) NOT NULL,
    grade_points DECIMAL(3,2),
    description VARCHAR(50),
    FOREIGN KEY (grading_scale_id) REFERENCES grading_scales(id) ON DELETE CASCADE,
    INDEX idx_scale (grading_scale_id)
);
```

#### `report_cards`
Generated report cards.
```sql
CREATE TABLE report_cards (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    academic_term_id INT NOT NULL,
    class_id INT NOT NULL,
    total_marks DECIMAL(8,2),
    percentage DECIMAL(5,2),
    grade VARCHAR(5),
    rank_in_class INT,
    rank_in_section INT,
    attendance_percentage DECIMAL(5,2),
    teacher_remarks TEXT,
    principal_remarks TEXT,
    status ENUM('draft', 'approved', 'published') DEFAULT 'draft',
    approved_by INT,
    approved_at DATETIME,
    published_at DATETIME,
    file_path VARCHAR(500),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (academic_term_id) REFERENCES academic_terms(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY uk_student_term (student_id, academic_term_id),
    INDEX idx_class_term (class_id, academic_term_id)
);
```

#### `academic_calendar`
School events and important dates.
```sql
CREATE TABLE academic_calendar (
    id INT PRIMARY KEY AUTO_INCREMENT,
    school_id INT NOT NULL,
    academic_year_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    event_type ENUM('holiday', 'exam', 'event', 'deadline', 'meeting') DEFAULT 'event',
    start_date DATE NOT NULL,
    end_date DATE,
    is_school_closed BOOLEAN DEFAULT FALSE,
    applies_to JSON,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (academic_year_id) REFERENCES academic_years(id) ON DELETE CASCADE,
    INDEX idx_school_date (school_id, start_date)
);
```

### 2.2 API Endpoints (Mobile-First)

| Method | Endpoint | Description | Access |
|:-------|:---------|:------------|:-------|
| **Classes** |
| GET | `/api/v1/learning/classes` | List classes | Staff |
| POST | `/api/v1/learning/classes` | Create class | Admin |
| GET | `/api/v1/learning/classes/{id}/students` | Get class students | Staff |
| GET | `/api/v1/learning/classes/{id}/timetable` | Get class timetable | All |
| **Subjects** |
| GET | `/api/v1/learning/subjects` | List subjects | Staff |
| POST | `/api/v1/learning/subjects` | Create subject | Admin |
| **Timetable** |
| GET | `/api/v1/learning/timetable` | Get current user's timetable | All |
| POST | `/api/v1/learning/timetable/generate` | Auto-generate timetable | Admin |
| PUT | `/api/v1/learning/timetable/entries/{id}` | Update entry | Admin |
| **Attendance** |
| GET | `/api/v1/learning/attendance/class/{id}` | Get class attendance | Teacher |
| POST | `/api/v1/learning/attendance` | Mark attendance | Teacher |
| GET | `/api/v1/learning/attendance/student/{id}` | Get student attendance | Student/Parent |
| **Gradebook** |
| GET | `/api/v1/learning/assessments` | List assessments | Teacher |
| POST | `/api/v1/learning/assessments` | Create assessment | Teacher |
| POST | `/api/v1/learning/assessments/{id}/grades` | Enter grades | Teacher |
| GET | `/api/v1/learning/grades/student/{id}` | Get student grades | Student/Parent |
| **Report Cards** |
| GET | `/api/v1/learning/report-cards` | List report cards | Staff |
| POST | `/api/v1/learning/report-cards/generate` | Generate report cards | Admin |
| GET | `/api/v1/learning/report-cards/{id}` | View report card | Student/Parent |
| POST | `/api/v1/learning/report-cards/{id}/approve` | Approve report card | Admin |

### 2.3 Module Structure

```
app/Modules/Learning/
├── Config/
│   ├── Routes.php
│   └── Services.php
├── Controllers/
│   ├── Api/
│   │   ├── ClassController.php
│   │   ├── SubjectController.php
│   │   ├── TimetableController.php
│   │   ├── AttendanceController.php
│   │   ├── AssessmentController.php
│   │   ├── GradeController.php
│   │   └── ReportCardController.php
│   └── Web/
│       └── LearningDashboardController.php
├── Models/
│   ├── AcademicYearModel.php
│   ├── AcademicTermModel.php
│   ├── ClassModel.php
│   ├── SectionModel.php
│   ├── SubjectModel.php
│   ├── TeacherAllocationModel.php
│   ├── StudentClassModel.php
│   ├── TimetableSlotModel.php
│   ├── TimetableEntryModel.php
│   ├── AttendanceRecordModel.php
│   ├── AssessmentModel.php
│   ├── GradeModel.php
│   ├── GradingScaleModel.php
│   └── ReportCardModel.php
├── Services/
│   ├── ClassService.php
│   ├── SubjectService.php
│   ├── TimetableGeneratorService.php
│   ├── ConflictDetectorService.php
│   ├── AttendanceService.php
│   ├── GradebookService.php
│   ├── GradeCalculatorService.php
│   ├── ReportCardGeneratorService.php
│   └── PromotionService.php
├── Libraries/
│   ├── TimetableOptimizer.php
│   └── ReportCardPdfGenerator.php
├── Database/
│   └── Migrations/
│       └── 2025-11-27-000001_CreateLearningTables.php
├── Views/
│   ├── dashboard/
│   ├── classes/
│   ├── timetable/
│   ├── attendance/
│   ├── gradebook/
│   └── report-cards/
└── Tests/
    ├── Unit/
    │   └── GradeCalculatorTest.php
    └── Feature/
        └── LearningApiTest.php
```

### 2.4 Integration Points

- **HR Module**: Retrieves teacher information for allocations.
- **Finance Module**: Links to fee structures by class.
- **Threads Module**: Sends attendance alerts to parents.
- **Reports Module**: Academic tabs in student/class views.
- **Admissions Module**: Receives new students on enrollment.
- **Transport Module**: Class information for route assignments.

---

## Part 3: Architectural Safeguards
*Target Audience: Architects, Security Engineers*

### 3.1 Timetable Conflict Detection
- Check teacher availability before assignment.
- Check room availability if rooms are managed.
- Validate periods per week against allocation.

```php
public function validateEntry(TimetableEntry $entry): array
{
    $conflicts = [];
    
    // Teacher conflict
    $teacherBusy = $this->db->table('timetable_entries')
        ->where('slot_id', $entry->slot_id)
        ->where('teacher_id', $entry->teacher_id)
        ->where('id !=', $entry->id ?? 0)
        ->countAllResults();
    
    if ($teacherBusy > 0) {
        $conflicts[] = 'Teacher is already assigned to another class at this time';
    }
    
    return $conflicts;
}
```

### 3.2 Attendance Uniqueness
- Only one attendance record per student per day.
- Use UNIQUE constraint and handle duplicate key gracefully.
- Allow updates within same day.

### 3.3 Grade Calculation Consistency
- Use consistent rounding (HALF_UP to 2 decimals).
- Weighted averages must total 100%.
- Null grades excluded from average calculation.

### 3.4 Report Card Immutability
- Once published, report cards should not be editable.
- Create new version if corrections needed.
- Store PDF for official record.

### 3.5 Data Access Control
- Students see only their own grades and attendance.
- Parents see only their children's data.
- Teachers see only their assigned classes.
- Class teachers have full access to their class.

---

## Part 4: Embedded Reports for Entity Views
*Target Audience: Frontend Developers, Product Owners*

### 4.1 Student View - Academic Tab
| Field | Description |
|:------|:------------|
| Current Class | Class and section |
| Today's Timetable | Schedule for today |
| Attendance Summary | Present/Absent counts this term |
| Recent Grades | Last 5 assessments with marks |
| Current GPA/Average | Calculated from term grades |

### 4.2 Class View - Performance Tab
| Field | Description |
|:------|:------------|
| Class Average | Average percentage across subjects |
| Top Performers | Top 5 students |
| Subject Breakdown | Average per subject |
| Attendance Rate | Class attendance percentage |

---

## Part 5: Test Data Strategy
*Target Audience: QA, Developers*

### 5.1 Seeding Strategy
Use `Modules\Learning\Database\Seeds\LearningSeeder`:

#### Structure
- 1 academic year with 3 terms.
- 6 classes (Grade 1-6).
- 3 sections per class (A, B, C).
- 10 subjects.
- Teacher allocations for all.

#### Students
- 20 students per section (360 total).
- Proper student_classes assignments.

#### Data
- Attendance records for past 30 days.
- 5 assessments per subject per term.
- Grades for all assessments.
- Report cards for completed term.

### 5.2 Testing Scenarios
| Scenario | Expected Outcome |
|:---------|:-----------------|
| Generate timetable with conflict | Conflict detected and reported |
| Mark student absent | Parent notification sent |
| Enter grade above max_marks | Validation error |
| Generate report card | PDF created with correct data |
| View grades as parent | Only child's grades visible |

---

## Part 6: Development Checklist

- [x] **Database**: Core tables created and migrated.
- [x] **Classes**: CRUD for classes and sections.
- [x] **Subjects**: Subject management implemented.
- [x] **Timetable**: Basic timetable entry working.
- [ ] **Timetable**: Auto-generation with conflict detection.
- [x] **Attendance**: Daily marking implemented.
- [ ] **Attendance**: Mobile offline support.
- [x] **Gradebook**: Assessment and grade entry.
- [ ] **Gradebook**: Weighted average calculation.
- [ ] **Report Cards**: Generation with PDF export.
- [ ] **Report Cards**: Bulk generation and approval.
- [ ] **Promotion**: End-of-year promotion logic.
- [ ] **Integration**: Connect with all dependent modules.
