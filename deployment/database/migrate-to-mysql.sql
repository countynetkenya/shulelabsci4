-- ShuleLabs CI4 - MySQL Production Database Setup
-- Purpose: Migrate from SQLite to MySQL for production
-- Usage: mysql -u root -p < migrate-to-mysql.sql

-- Create database
CREATE DATABASE IF NOT EXISTS shulelabs_production CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

-- Create user
CREATE USER IF NOT EXISTS 'shulelabs_user'@'localhost' IDENTIFIED BY 'REPLACE_WITH_STRONG_PASSWORD';
GRANT ALL PRIVILEGES ON shulelabs_production.* TO 'shulelabs_user'@'localhost';
FLUSH PRIVILEGES;

-- Use database
USE shulelabs_production;

-- ============================================================================
-- CORE TABLES
-- ============================================================================

-- Users table
CREATE TABLE IF NOT EXISTS ci4_users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    phone VARCHAR(20),
    is_active TINYINT(1) DEFAULT 1,
    email_verified_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME NULL,
    INDEX idx_email (email),
    INDEX idx_active (is_active),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Roles table
CREATE TABLE IF NOT EXISTS ci4_roles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    permissions JSON,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- User roles junction table
CREATE TABLE IF NOT EXISTS ci4_user_roles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    role_id INT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES ci4_users(id) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES ci4_roles(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_role (user_id, role_id),
    INDEX idx_user (user_id),
    INDEX idx_role (role_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Sessions table
CREATE TABLE IF NOT EXISTS ci4_sessions (
    id VARCHAR(128) NOT NULL PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data BLOB NOT NULL,
    INDEX idx_timestamp (timestamp)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================================
-- MULTI-SCHOOL TABLES
-- ============================================================================

-- Schools table
CREATE TABLE IF NOT EXISTS ci4_schools (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    code VARCHAR(50) UNIQUE,
    address TEXT,
    phone VARCHAR(20),
    email VARCHAR(255),
    subdomain VARCHAR(100) UNIQUE,
    is_active TINYINT(1) DEFAULT 1,
    settings JSON,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME NULL,
    INDEX idx_code (code),
    INDEX idx_subdomain (subdomain),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- School users (multi-tenant user mapping)
CREATE TABLE IF NOT EXISTS ci4_school_users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    school_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    role_id INT UNSIGNED,
    is_primary TINYINT(1) DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES ci4_schools(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES ci4_users(id) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES ci4_roles(id) ON DELETE SET NULL,
    UNIQUE KEY unique_school_user (school_id, user_id),
    INDEX idx_school (school_id),
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Classes table
CREATE TABLE IF NOT EXISTS ci4_school_classes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    school_id INT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    grade_level VARCHAR(50),
    teacher_id INT UNSIGNED,
    capacity INT UNSIGNED,
    academic_year VARCHAR(20),
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME NULL,
    FOREIGN KEY (school_id) REFERENCES ci4_schools(id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES ci4_users(id) ON DELETE SET NULL,
    INDEX idx_school (school_id),
    INDEX idx_teacher (teacher_id),
    INDEX idx_academic_year (academic_year)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Student enrollments
CREATE TABLE IF NOT EXISTS ci4_student_enrollments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    school_id INT UNSIGNED NOT NULL,
    student_id INT UNSIGNED NOT NULL,
    class_id INT UNSIGNED NOT NULL,
    enrollment_date DATE NOT NULL,
    status ENUM('active', 'inactive', 'transferred', 'graduated') DEFAULT 'active',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES ci4_schools(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES ci4_users(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES ci4_school_classes(id) ON DELETE CASCADE,
    INDEX idx_school (school_id),
    INDEX idx_student (student_id),
    INDEX idx_class (class_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================================
-- FINANCE TABLES
-- ============================================================================

-- Invoices
CREATE TABLE IF NOT EXISTS ci4_invoices (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    school_id INT UNSIGNED NOT NULL,
    student_id INT UNSIGNED NOT NULL,
    invoice_number VARCHAR(50) UNIQUE NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    due_date DATE NOT NULL,
    status ENUM('pending', 'paid', 'overdue', 'cancelled') DEFAULT 'pending',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES ci4_schools(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES ci4_users(id) ON DELETE CASCADE,
    INDEX idx_school (school_id),
    INDEX idx_student (student_id),
    INDEX idx_status (status),
    INDEX idx_invoice_number (invoice_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Payments
CREATE TABLE IF NOT EXISTS ci4_payments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    school_id INT UNSIGNED NOT NULL,
    invoice_id INT UNSIGNED NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('cash', 'mpesa', 'bank', 'card') NOT NULL,
    transaction_ref VARCHAR(100),
    status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
    payment_date DATETIME NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES ci4_schools(id) ON DELETE CASCADE,
    FOREIGN KEY (invoice_id) REFERENCES ci4_invoices(id) ON DELETE CASCADE,
    INDEX idx_school (school_id),
    INDEX idx_invoice (invoice_id),
    INDEX idx_status (status),
    INDEX idx_transaction (transaction_ref)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================================
-- LEARNING TABLES
-- ============================================================================

-- Courses
CREATE TABLE IF NOT EXISTS ci4_courses (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    school_id INT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    code VARCHAR(50),
    description TEXT,
    teacher_id INT UNSIGNED,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES ci4_schools(id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES ci4_users(id) ON DELETE SET NULL,
    INDEX idx_school (school_id),
    INDEX idx_teacher (teacher_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Assignments
CREATE TABLE IF NOT EXISTS ci4_assignments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    school_id INT UNSIGNED NOT NULL,
    class_id INT UNSIGNED NOT NULL,
    teacher_id INT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    due_date DATETIME NOT NULL,
    total_marks INT UNSIGNED,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES ci4_schools(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES ci4_school_classes(id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES ci4_users(id) ON DELETE CASCADE,
    INDEX idx_school (school_id),
    INDEX idx_class (class_id),
    INDEX idx_teacher (teacher_id),
    INDEX idx_due_date (due_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Grades
CREATE TABLE IF NOT EXISTS ci4_grades (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    school_id INT UNSIGNED NOT NULL,
    student_id INT UNSIGNED NOT NULL,
    assignment_id INT UNSIGNED NOT NULL,
    marks_obtained DECIMAL(5,2),
    letter_grade VARCHAR(2),
    comments TEXT,
    graded_at DATETIME,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES ci4_schools(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES ci4_users(id) ON DELETE CASCADE,
    FOREIGN KEY (assignment_id) REFERENCES ci4_assignments(id) ON DELETE CASCADE,
    INDEX idx_school (school_id),
    INDEX idx_student (student_id),
    INDEX idx_assignment (assignment_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================================
-- PERFORMANCE INDEXES
-- ============================================================================

-- Composite indexes for common queries
ALTER TABLE ci4_student_enrollments ADD INDEX idx_school_student (school_id, student_id);
ALTER TABLE ci4_student_enrollments ADD INDEX idx_school_class (school_id, class_id);
ALTER TABLE ci4_invoices ADD INDEX idx_school_student_status (school_id, student_id, status);
ALTER TABLE ci4_assignments ADD INDEX idx_school_class_teacher (school_id, class_id, teacher_id);
ALTER TABLE ci4_grades ADD INDEX idx_school_student_assignment (school_id, student_id, assignment_id);

-- ============================================================================
-- INITIAL DATA
-- ============================================================================

-- Insert default roles
INSERT INTO ci4_roles (name, description) VALUES
('admin', 'System Administrator'),
('teacher', 'Teacher'),
('student', 'Student'),
('parent', 'Parent/Guardian')
ON DUPLICATE KEY UPDATE name=name;

-- Success message
SELECT 'MySQL database setup completed successfully!' AS status;
