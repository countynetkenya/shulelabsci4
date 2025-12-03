# Learning Management System (LMS) Module Specification

**Version**: 1.0.0
**Status**: Draft
**Last Updated**: 2025-12-03

---

## Part 1: Feature Definition (The "What" & "Why")
*Target Audience: Product Owners, Stakeholders, Developers*

### 1.1 Overview
The LMS Module is designed to facilitate the delivery of educational content to students. It allows teachers to create courses and lessons, and students to access this content, primarily via mobile devices. The module focuses on a clean, mobile-first API for content consumption while providing a robust web interface for content management.

### 1.2 User Stories
- **As a Student**, I want to view a list of courses I am enrolled in, so that I can access my learning materials.
- **As a Student**, I want to view the details of a course and its lessons, so that I can study the material.
- **As a Student**, I want to mark a lesson as complete, so that I can track my progress.
- **As a Teacher**, I want to create and manage courses and lessons, so that I can provide structured learning content.
- **As a Teacher**, I want to view which students are enrolled in my courses, so that I can monitor engagement.
- **As an Admin**, I want to oversee all courses and enrollments, ensuring the system is used effectively.

### 1.3 User Workflows
1.  **Content Creation (Teacher/Admin)**:
    *   Log in to the web portal.
    *   Navigate to "LMS" -> "Courses".
    *   Click "Create Course" and fill in details.
    *   Add "Lessons" to the created course.
    *   Publish the course.

2.  **Learning (Student - Mobile)**:
    *   Log in via mobile app (API).
    *   Fetch list of enrolled courses.
    *   Select a course to view lessons.
    *   Select a lesson to view content (text/video).
    *   Mark lesson as complete when finished.

### 1.4 Acceptance Criteria
- [ ] Teachers can create, edit, and publish courses and lessons via Web UI.
- [ ] Students can retrieve their enrolled courses via API.
- [ ] Students can retrieve lesson content via API.
- [ ] System tracks lesson completion and course progress.
- [ ] API responses are optimized for mobile (JSON).
- [ ] All data is scoped to the specific School (Tenant).

---

## Part 2: Technical Specification (The "How")
*Target Audience: Developers, Architects*

### 2.1 Database Schema

#### `lms_courses`
```sql
CREATE TABLE lms_courses (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    school_id INT UNSIGNED NOT NULL,
    teacher_id INT UNSIGNED NOT NULL, -- Links to users table
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME NULL,
    CONSTRAINT fk_courses_school FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    CONSTRAINT fk_courses_teacher FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE
);
```

#### `lms_lessons`
```sql
CREATE TABLE lms_lessons (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    course_id INT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT NULL, -- HTML or Markdown content
    video_url VARCHAR(255) NULL,
    sequence_order INT UNSIGNED DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME NULL,
    CONSTRAINT fk_lessons_course FOREIGN KEY (course_id) REFERENCES lms_courses(id) ON DELETE CASCADE
);
```

#### `lms_enrollments`
```sql
CREATE TABLE lms_enrollments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    school_id INT UNSIGNED NOT NULL,
    student_id INT UNSIGNED NOT NULL, -- Links to users table
    course_id INT UNSIGNED NOT NULL,
    status ENUM('active', 'completed', 'dropped') DEFAULT 'active',
    enrolled_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    completed_at DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_enrollments_school FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    CONSTRAINT fk_enrollments_student FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_enrollments_course FOREIGN KEY (course_id) REFERENCES lms_courses(id) ON DELETE CASCADE
);
```

#### `lms_progress`
```sql
CREATE TABLE lms_progress (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    enrollment_id INT UNSIGNED NOT NULL,
    lesson_id INT UNSIGNED NOT NULL,
    completed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_progress_enrollment FOREIGN KEY (enrollment_id) REFERENCES lms_enrollments(id) ON DELETE CASCADE,
    CONSTRAINT fk_progress_lesson FOREIGN KEY (lesson_id) REFERENCES lms_lessons(id) ON DELETE CASCADE,
    UNIQUE KEY unique_progress (enrollment_id, lesson_id)
);
```

### 2.2 API Endpoints (Mobile-First)

| Method | Endpoint | Description | Access |
|:-------|:---------|:------------|:-------|
| GET    | `/api/v1/lms/courses` | List enrolled courses for current student | Student |
| GET    | `/api/v1/lms/courses/{id}` | Get course details and lesson list | Student |
| GET    | `/api/v1/lms/lessons/{id}` | Get specific lesson content | Student |
| POST   | `/api/v1/lms/lessons/{id}/complete` | Mark lesson as complete | Student |

**Response Format (Standard)**:
```json
{
    "status": 200,
    "error": null,
    "messages": {
        "success": "Data retrieved successfully"
    },
    "data": { ... }
}
```

### 2.3 Web Interface (Views & Controllers)
- **Controller**: `App\Modules\LMS\Controllers\CoursesWebController`
- **Controller**: `App\Modules\LMS\Controllers\LessonsWebController`
- **Views**:
    - `modules/LMS/Courses/index.php`: List courses
    - `modules/LMS/Courses/form.php`: Create/Edit course
    - `modules/LMS/Courses/show.php`: Course dashboard (manage lessons)
    - `modules/LMS/Lessons/form.php`: Create/Edit lesson

### 2.4 Models & Validation
- **CourseModel**:
    - `title`: required, max_length[255]
    - `status`: in_list[draft,published,archived]
- **LessonModel**:
    - `title`: required, max_length[255]
    - `course_id`: required, integer, is_not_unique[lms_courses.id]

### 2.5 Integration Points
- **Auth**: Uses `users` table for Teachers and Students.
- **Foundation**: Uses `schools` table for Tenant isolation.

---

## Part 3: Development Checklist
- [ ] Create Migration for `lms_courses`, `lms_lessons`, `lms_enrollments`, `lms_progress`.
- [ ] Update `FoundationDatabaseTestCase::createSchema` with new tables.
- [ ] Create Models (`CourseModel`, `LessonModel`, `EnrollmentModel`, `ProgressModel`).
- [ ] Create Seeder (`LMSSeeder`) with realistic data.
- [ ] Create Feature Test (`LMSApiTest`) - **TDD First!**
- [ ] Implement `CoursesApiController` and `LessonsApiController`.
- [ ] Implement Web Controllers and Views.
