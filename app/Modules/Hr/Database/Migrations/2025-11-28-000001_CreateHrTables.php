<?php

namespace App\Modules\Hr\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Creates HR module tables for employee management, leave, payroll.
 */
class CreateHrTables extends Migration
{
    public function up(): void
    {
        // departments - Organization departments
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'school_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'name' => ['type' => 'VARCHAR', 'constraint' => 100],
            'code' => ['type' => 'VARCHAR', 'constraint' => 20],
            'head_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'parent_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'description' => ['type' => 'TEXT', 'null' => true],
            'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['school_id', 'code'], 'uk_school_code');
        $this->forge->createTable('departments', true);

        // designations - Job titles/positions
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'school_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'name' => ['type' => 'VARCHAR', 'constraint' => 100],
            'code' => ['type' => 'VARCHAR', 'constraint' => 20],
            'department_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'level' => ['type' => 'INT', 'constraint' => 11, 'default' => 1],
            'description' => ['type' => 'TEXT', 'null' => true],
            'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['school_id', 'code'], 'uk_school_code');
        $this->forge->createTable('designations', true);

        // employees - Employee profiles
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'school_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'employee_number' => ['type' => 'VARCHAR', 'constraint' => 50],
            'department_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'designation_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'reports_to' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'employment_type' => ['type' => 'ENUM', 'constraint' => ['permanent', 'contract', 'part_time', 'intern', 'probation']],
            'join_date' => ['type' => 'DATE'],
            'confirmation_date' => ['type' => 'DATE', 'null' => true],
            'end_date' => ['type' => 'DATE', 'null' => true],
            'status' => ['type' => 'ENUM', 'constraint' => ['active', 'on_leave', 'suspended', 'terminated', 'resigned'], 'default' => 'active'],
            'basic_salary' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'null' => true],
            'bank_name' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'bank_account' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'tax_id' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'social_security_id' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['school_id', 'employee_number'], 'uk_school_emp_number');
        $this->forge->addKey('user_id', false, false, 'idx_user');
        $this->forge->addKey('department_id', false, false, 'idx_department');
        $this->forge->createTable('employees', true);

        // leave_types - Leave category definitions
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'school_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'name' => ['type' => 'VARCHAR', 'constraint' => 100],
            'code' => ['type' => 'VARCHAR', 'constraint' => 20],
            'days_per_year' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'is_paid' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'requires_approval' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'can_carry_forward' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'max_carry_forward' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['school_id', 'code'], 'uk_school_code');
        $this->forge->createTable('leave_types', true);

        // leave_balances - Employee leave balances
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'employee_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'leave_type_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'year' => ['type' => 'YEAR'],
            'entitled_days' => ['type' => 'DECIMAL', 'constraint' => '5,1', 'default' => 0],
            'taken_days' => ['type' => 'DECIMAL', 'constraint' => '5,1', 'default' => 0],
            'carried_forward' => ['type' => 'DECIMAL', 'constraint' => '5,1', 'default' => 0],
            'remaining_days' => ['type' => 'DECIMAL', 'constraint' => '5,1', 'default' => 0],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['employee_id', 'leave_type_id', 'year'], 'uk_emp_type_year');
        $this->forge->createTable('leave_balances', true);

        // leave_requests - Leave applications
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'school_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'employee_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'leave_type_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'start_date' => ['type' => 'DATE'],
            'end_date' => ['type' => 'DATE'],
            'days_requested' => ['type' => 'DECIMAL', 'constraint' => '5,1'],
            'reason' => ['type' => 'TEXT', 'null' => true],
            'status' => ['type' => 'ENUM', 'constraint' => ['pending', 'approved', 'rejected', 'cancelled'], 'default' => 'pending'],
            'approved_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'approved_at' => ['type' => 'DATETIME', 'null' => true],
            'rejection_reason' => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['employee_id', 'status'], false, false, 'idx_emp_status');
        $this->forge->addKey(['school_id', 'status'], false, false, 'idx_school_status');
        $this->forge->createTable('leave_requests', true);

        // staff_attendance - Employee attendance
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'school_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'employee_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'attendance_date' => ['type' => 'DATE'],
            'check_in' => ['type' => 'DATETIME', 'null' => true],
            'check_out' => ['type' => 'DATETIME', 'null' => true],
            'status' => ['type' => 'ENUM', 'constraint' => ['present', 'absent', 'late', 'half_day', 'on_leave', 'holiday']],
            'work_hours' => ['type' => 'DECIMAL', 'constraint' => '4,2', 'null' => true],
            'overtime_hours' => ['type' => 'DECIMAL', 'constraint' => '4,2', 'default' => 0],
            'remarks' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['employee_id', 'attendance_date'], 'uk_emp_date');
        $this->forge->addKey(['school_id', 'attendance_date'], false, false, 'idx_school_date');
        $this->forge->createTable('staff_attendance', true);

        // payroll_periods - Payroll processing periods
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'school_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'name' => ['type' => 'VARCHAR', 'constraint' => 100],
            'start_date' => ['type' => 'DATE'],
            'end_date' => ['type' => 'DATE'],
            'payment_date' => ['type' => 'DATE', 'null' => true],
            'status' => ['type' => 'ENUM', 'constraint' => ['draft', 'processing', 'approved', 'paid', 'closed'], 'default' => 'draft'],
            'total_gross' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'total_deductions' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'total_net' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'employee_count' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'processed_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'processed_at' => ['type' => 'DATETIME', 'null' => true],
            'approved_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'approved_at' => ['type' => 'DATETIME', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['school_id', 'status'], false, false, 'idx_school_status');
        $this->forge->createTable('payroll_periods', true);

        // payslips - Individual employee payslips
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'payroll_period_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'employee_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'basic_salary' => ['type' => 'DECIMAL', 'constraint' => '12,2'],
            'allowances' => ['type' => 'JSON', 'null' => true],
            'total_allowances' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'deductions' => ['type' => 'JSON', 'null' => true],
            'total_deductions' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'gross_salary' => ['type' => 'DECIMAL', 'constraint' => '12,2'],
            'tax_amount' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'net_salary' => ['type' => 'DECIMAL', 'constraint' => '12,2'],
            'days_worked' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'overtime_hours' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 0],
            'overtime_amount' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'status' => ['type' => 'ENUM', 'constraint' => ['draft', 'approved', 'paid'], 'default' => 'draft'],
            'paid_at' => ['type' => 'DATETIME', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['payroll_period_id', 'employee_id'], 'uk_period_emp');
        $this->forge->addKey('employee_id', false, false, 'idx_employee');
        $this->forge->createTable('payslips', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('payslips', true);
        $this->forge->dropTable('payroll_periods', true);
        $this->forge->dropTable('staff_attendance', true);
        $this->forge->dropTable('leave_requests', true);
        $this->forge->dropTable('leave_balances', true);
        $this->forge->dropTable('leave_types', true);
        $this->forge->dropTable('employees', true);
        $this->forge->dropTable('designations', true);
        $this->forge->dropTable('departments', true);
    }
}
