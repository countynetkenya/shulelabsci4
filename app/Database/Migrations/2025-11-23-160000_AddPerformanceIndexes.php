<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Add Performance Indexes Migration
 * 
 * Adds database indexes to improve query performance across all modules
 */
class AddPerformanceIndexes extends Migration
{
    public function up()
    {
        // Schools table indexes
        if ($this->db->tableExists('schools')) {
            // Index for status lookups
            if (!$this->db->indexExists('schools', 'idx_schools_status')) {
                $this->forge->addKey('status', false, false, 'idx_schools_status');
            }
            // Index for school type filtering
            if (!$this->db->indexExists('schools', 'idx_schools_type')) {
                $this->forge->addKey('school_type', false, false, 'idx_schools_type');
            }
        }

        // School users table indexes
        if ($this->db->tableExists('school_users')) {
            // Index for school lookups
            if (!$this->db->indexExists('school_users', 'idx_school_users_school')) {
                $this->forge->addKey('school_id', false, false, 'idx_school_users_school');
            }
            // Index for user lookups
            if (!$this->db->indexExists('school_users', 'idx_school_users_user')) {
                $this->forge->addKey('user_id', false, false, 'idx_school_users_user');
            }
            // Composite index for role filtering per school
            if (!$this->db->indexExists('school_users', 'idx_school_users_school_role')) {
                $this->forge->addKey(['school_id', 'role'], false, false, 'idx_school_users_school_role');
            }
        }

        // School classes table indexes
        if ($this->db->tableExists('school_classes')) {
            // Index for school lookups
            if (!$this->db->indexExists('school_classes', 'idx_school_classes_school')) {
                $this->forge->addKey('school_id', false, false, 'idx_school_classes_school');
            }
            // Index for grade level filtering
            if (!$this->db->indexExists('school_classes', 'idx_school_classes_grade')) {
                $this->forge->addKey('grade_level', false, false, 'idx_school_classes_grade');
            }
        }

        // Student enrollments table indexes
        if ($this->db->tableExists('student_enrollments')) {
            // Index for school lookups
            if (!$this->db->indexExists('student_enrollments', 'idx_enrollments_school')) {
                $this->forge->addKey('school_id', false, false, 'idx_enrollments_school');
            }
            // Index for student lookups
            if (!$this->db->indexExists('student_enrollments', 'idx_enrollments_student')) {
                $this->forge->addKey('user_id', false, false, 'idx_enrollments_student');
            }
            // Index for class lookups
            if (!$this->db->indexExists('student_enrollments', 'idx_enrollments_class')) {
                $this->forge->addKey('class_id', false, false, 'idx_enrollments_class');
            }
            // Index for status filtering
            if (!$this->db->indexExists('student_enrollments', 'idx_enrollments_status')) {
                $this->forge->addKey('status', false, false, 'idx_enrollments_status');
            }
        }

        // Invoices table indexes
        if ($this->db->tableExists('invoices')) {
            // Index for school lookups
            if (!$this->db->indexExists('invoices', 'idx_invoices_school')) {
                $this->forge->addKey('school_id', false, false, 'idx_invoices_school');
            }
            // Index for student lookups
            if (!$this->db->indexExists('invoices', 'idx_invoices_student')) {
                $this->forge->addKey('student_id', false, false, 'idx_invoices_student');
            }
            // Index for status filtering
            if (!$this->db->indexExists('invoices', 'idx_invoices_status')) {
                $this->forge->addKey('status', false, false, 'idx_invoices_status');
            }
            // Index for due date sorting
            if (!$this->db->indexExists('invoices', 'idx_invoices_due_date')) {
                $this->forge->addKey('due_date', false, false, 'idx_invoices_due_date');
            }
        }

        // Payments table indexes
        if ($this->db->tableExists('payments')) {
            // Index for school lookups
            if (!$this->db->indexExists('payments', 'idx_payments_school')) {
                $this->forge->addKey('school_id', false, false, 'idx_payments_school');
            }
            // Index for invoice lookups
            if (!$this->db->indexExists('payments', 'idx_payments_invoice')) {
                $this->forge->addKey('invoice_id', false, false, 'idx_payments_invoice');
            }
            // Index for payment method filtering
            if (!$this->db->indexExists('payments', 'idx_payments_method')) {
                $this->forge->addKey('payment_method', false, false, 'idx_payments_method');
            }
            // Index for payment date sorting
            if (!$this->db->indexExists('payments', 'idx_payments_date')) {
                $this->forge->addKey('payment_date', false, false, 'idx_payments_date');
            }
        }

        // Courses table indexes
        if ($this->db->tableExists('courses')) {
            // Index for school lookups
            if (!$this->db->indexExists('courses', 'idx_courses_school')) {
                $this->forge->addKey('school_id', false, false, 'idx_courses_school');
            }
            // Index for teacher lookups
            if (!$this->db->indexExists('courses', 'idx_courses_teacher')) {
                $this->forge->addKey('teacher_id', false, false, 'idx_courses_teacher');
            }
            // Index for status filtering
            if (!$this->db->indexExists('courses', 'idx_courses_status')) {
                $this->forge->addKey('status', false, false, 'idx_courses_status');
            }
        }

        // Assignments table indexes
        if ($this->db->tableExists('assignments')) {
            // Index for school lookups
            if (!$this->db->indexExists('assignments', 'idx_assignments_school')) {
                $this->forge->addKey('school_id', false, false, 'idx_assignments_school');
            }
            // Index for course lookups
            if (!$this->db->indexExists('assignments', 'idx_assignments_course')) {
                $this->forge->addKey('course_id', false, false, 'idx_assignments_course');
            }
            // Index for due date sorting
            if (!$this->db->indexExists('assignments', 'idx_assignments_due_date')) {
                $this->forge->addKey('due_date', false, false, 'idx_assignments_due_date');
            }
        }

        // Grades table indexes
        if ($this->db->tableExists('grades')) {
            // Index for school lookups
            if (!$this->db->indexExists('grades', 'idx_grades_school')) {
                $this->forge->addKey('school_id', false, false, 'idx_grades_school');
            }
            // Index for assignment lookups
            if (!$this->db->indexExists('grades', 'idx_grades_assignment')) {
                $this->forge->addKey('assignment_id', false, false, 'idx_grades_assignment');
            }
            // Index for student lookups
            if (!$this->db->indexExists('grades', 'idx_grades_student')) {
                $this->forge->addKey('student_id', false, false, 'idx_grades_student');
            }
            // Composite index for student assignments
            if (!$this->db->indexExists('grades', 'idx_grades_student_assignment')) {
                $this->forge->addKey(['student_id', 'assignment_id'], false, false, 'idx_grades_student_assignment');
            }
        }

        // Library books table indexes
        if ($this->db->tableExists('library_books')) {
            // Index for school lookups
            if (!$this->db->indexExists('library_books', 'idx_library_books_school')) {
                $this->forge->addKey('school_id', false, false, 'idx_library_books_school');
            }
            // Index for ISBN lookups
            if (!$this->db->indexExists('library_books', 'idx_library_books_isbn')) {
                $this->forge->addKey('isbn', false, false, 'idx_library_books_isbn');
            }
            // Index for category filtering
            if (!$this->db->indexExists('library_books', 'idx_library_books_category')) {
                $this->forge->addKey('category', false, false, 'idx_library_books_category');
            }
        }

        // Library borrowings table indexes
        if ($this->db->tableExists('library_borrowings')) {
            // Index for school lookups
            if (!$this->db->indexExists('library_borrowings', 'idx_library_borrowings_school')) {
                $this->forge->addKey('school_id', false, false, 'idx_library_borrowings_school');
            }
            // Index for book lookups
            if (!$this->db->indexExists('library_borrowings', 'idx_library_borrowings_book')) {
                $this->forge->addKey('book_id', false, false, 'idx_library_borrowings_book');
            }
            // Index for student lookups
            if (!$this->db->indexExists('library_borrowings', 'idx_library_borrowings_student')) {
                $this->forge->addKey('student_id', false, false, 'idx_library_borrowings_student');
            }
            // Index for status filtering
            if (!$this->db->indexExists('library_borrowings', 'idx_library_borrowings_status')) {
                $this->forge->addKey('status', false, false, 'idx_library_borrowings_status');
            }
            // Index for due date sorting (overdue books)
            if (!$this->db->indexExists('library_borrowings', 'idx_library_borrowings_due_date')) {
                $this->forge->addKey('due_date', false, false, 'idx_library_borrowings_due_date');
            }
        }

        // Inventory assets table indexes
        if ($this->db->tableExists('inventory_assets')) {
            // Index for school lookups
            if (!$this->db->indexExists('inventory_assets', 'idx_inventory_assets_school')) {
                $this->forge->addKey('school_id', false, false, 'idx_inventory_assets_school');
            }
            // Index for asset code lookups
            if (!$this->db->indexExists('inventory_assets', 'idx_inventory_assets_code')) {
                $this->forge->addKey('asset_code', false, false, 'idx_inventory_assets_code');
            }
            // Index for category filtering
            if (!$this->db->indexExists('inventory_assets', 'idx_inventory_assets_category')) {
                $this->forge->addKey('category', false, false, 'idx_inventory_assets_category');
            }
            // Index for status filtering
            if (!$this->db->indexExists('inventory_assets', 'idx_inventory_assets_status')) {
                $this->forge->addKey('status', false, false, 'idx_inventory_assets_status');
            }
        }

        // Inventory transactions table indexes
        if ($this->db->tableExists('inventory_transactions')) {
            // Index for school lookups
            if (!$this->db->indexExists('inventory_transactions', 'idx_inventory_trans_school')) {
                $this->forge->addKey('school_id', false, false, 'idx_inventory_trans_school');
            }
            // Index for asset lookups
            if (!$this->db->indexExists('inventory_transactions', 'idx_inventory_trans_asset')) {
                $this->forge->addKey('asset_id', false, false, 'idx_inventory_trans_asset');
            }
            // Index for transaction type filtering
            if (!$this->db->indexExists('inventory_transactions', 'idx_inventory_trans_type')) {
                $this->forge->addKey('transaction_type', false, false, 'idx_inventory_trans_type');
            }
            // Index for transaction date sorting
            if (!$this->db->indexExists('inventory_transactions', 'idx_inventory_trans_date')) {
                $this->forge->addKey('transaction_date', false, false, 'idx_inventory_trans_date');
            }
        }

        // Thread messages table indexes
        if ($this->db->tableExists('thread_messages')) {
            // Index for school lookups
            if (!$this->db->indexExists('thread_messages', 'idx_thread_messages_school')) {
                $this->forge->addKey('school_id', false, false, 'idx_thread_messages_school');
            }
            // Index for sender lookups
            if (!$this->db->indexExists('thread_messages', 'idx_thread_messages_sender')) {
                $this->forge->addKey('sender_id', false, false, 'idx_thread_messages_sender');
            }
            // Index for recipient lookups
            if (!$this->db->indexExists('thread_messages', 'idx_thread_messages_recipient')) {
                $this->forge->addKey('recipient_id', false, false, 'idx_thread_messages_recipient');
            }
            // Index for thread grouping
            if (!$this->db->indexExists('thread_messages', 'idx_thread_messages_thread')) {
                $this->forge->addKey('thread_id', false, false, 'idx_thread_messages_thread');
            }
            // Index for read status filtering
            if (!$this->db->indexExists('thread_messages', 'idx_thread_messages_read')) {
                $this->forge->addKey('is_read', false, false, 'idx_thread_messages_read');
            }
        }

        // Thread announcements table indexes
        if ($this->db->tableExists('thread_announcements')) {
            // Index for school lookups
            if (!$this->db->indexExists('thread_announcements', 'idx_thread_announcements_school')) {
                $this->forge->addKey('school_id', false, false, 'idx_thread_announcements_school');
            }
            // Index for author lookups
            if (!$this->db->indexExists('thread_announcements', 'idx_thread_announcements_author')) {
                $this->forge->addKey('created_by', false, false, 'idx_thread_announcements_author');
            }
            // Index for target audience filtering
            if (!$this->db->indexExists('thread_announcements', 'idx_thread_announcements_target')) {
                $this->forge->addKey('target_audience', false, false, 'idx_thread_announcements_target');
            }
            // Index for active status filtering
            if (!$this->db->indexExists('thread_announcements', 'idx_thread_announcements_active')) {
                $this->forge->addKey('is_active', false, false, 'idx_thread_announcements_active');
            }
        }
    }

    public function down()
    {
        // Drop all indexes created in up() method
        $indexes = [
            'schools' => ['idx_schools_status', 'idx_schools_type'],
            'school_users' => ['idx_school_users_school', 'idx_school_users_user', 'idx_school_users_school_role'],
            'school_classes' => ['idx_school_classes_school', 'idx_school_classes_grade'],
            'student_enrollments' => ['idx_enrollments_school', 'idx_enrollments_student', 'idx_enrollments_class', 'idx_enrollments_status'],
            'invoices' => ['idx_invoices_school', 'idx_invoices_student', 'idx_invoices_status', 'idx_invoices_due_date'],
            'payments' => ['idx_payments_school', 'idx_payments_invoice', 'idx_payments_method', 'idx_payments_date'],
            'courses' => ['idx_courses_school', 'idx_courses_teacher', 'idx_courses_status'],
            'assignments' => ['idx_assignments_school', 'idx_assignments_course', 'idx_assignments_due_date'],
            'grades' => ['idx_grades_school', 'idx_grades_assignment', 'idx_grades_student', 'idx_grades_student_assignment'],
            'library_books' => ['idx_library_books_school', 'idx_library_books_isbn', 'idx_library_books_category'],
            'library_borrowings' => ['idx_library_borrowings_school', 'idx_library_borrowings_book', 'idx_library_borrowings_student', 'idx_library_borrowings_status', 'idx_library_borrowings_due_date'],
            'inventory_assets' => ['idx_inventory_assets_school', 'idx_inventory_assets_code', 'idx_inventory_assets_category', 'idx_inventory_assets_status'],
            'inventory_transactions' => ['idx_inventory_trans_school', 'idx_inventory_trans_asset', 'idx_inventory_trans_type', 'idx_inventory_trans_date'],
            'thread_messages' => ['idx_thread_messages_school', 'idx_thread_messages_sender', 'idx_thread_messages_recipient', 'idx_thread_messages_thread', 'idx_thread_messages_read'],
            'thread_announcements' => ['idx_thread_announcements_school', 'idx_thread_announcements_author', 'idx_thread_announcements_target', 'idx_thread_announcements_active'],
        ];

        foreach ($indexes as $table => $tableIndexes) {
            if ($this->db->tableExists($table)) {
                foreach ($tableIndexes as $indexName) {
                    if ($this->db->indexExists($table, $indexName)) {
                        $this->forge->dropKey($table, $indexName);
                    }
                }
            }
        }
    }
}
