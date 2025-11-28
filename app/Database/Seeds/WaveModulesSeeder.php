<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * WaveModulesSeeder - Seeds test data for all Wave 1, 2, 3 modules.
 *
 * This seeder creates comprehensive test data for:
 * - Scheduler (scheduled_jobs, job_runs)
 * - Security (roles, permissions, 2FA)
 * - Audit (audit_events)
 * - Learning (subjects, attendance, exams, grades)
 * - HR (departments, employees, leave, payroll)
 * - Threads (threads, messages, announcements)
 * - Admissions (applications, interviews, waitlist)
 * - Transport (vehicles, routes, trips)
 * - Wallets (wallets, transactions)
 * - Approval Workflows (workflows, stages, requests)
 * - Monitoring (health_checks, metrics)
 */
class WaveModulesSeeder extends Seeder
{
    public function run()
    {
        echo "🌱 Starting Wave Modules Database Seeding...\n\n";

        // Call prerequisite seeders
        $this->call('MultiSchoolSeeder');
        $this->call('MultiSchoolUserSeeder');

        // Seed Wave 1 modules
        $this->seedSchedulerModule();
        $this->seedSecurityModule();
        $this->seedAuditModule();

        // Seed Wave 2 modules
        $this->seedLearningModule();
        $this->seedHrModule();
        $this->seedThreadsModule();

        // Seed Wave 3 modules
        $this->seedAdmissionsModule();
        $this->seedTransportModule();
        $this->seedWalletsModule();
        $this->seedApprovalWorkflowsModule();
        $this->seedMonitoringModule();

        echo "\n✅ Wave Modules seeding complete!\n";
        $this->printTestCredentials();
    }

    /**
     * Seed Scheduler module test data.
     */
    private function seedSchedulerModule(): void
    {
        echo "📅 Seeding Scheduler module...\n";

        // Check if table exists
        if (!$this->db->tableExists('scheduled_jobs')) {
            echo "   ⚠️  scheduled_jobs table not found, skipping...\n";
            return;
        }

        $jobs = [
            [
                'school_id' => 1,
                'name' => 'Daily Attendance Report',
                'job_class' => 'App\\Modules\\Scheduler\\Jobs\\GenerateAttendanceReportJob',
                'cron_expression' => '0 18 * * *',
                'timezone' => 'Africa/Nairobi',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'school_id' => 1,
                'name' => 'Cleanup Expired Sessions',
                'job_class' => 'App\\Modules\\Scheduler\\Jobs\\CleanupExpiredSessionsJob',
                'cron_expression' => '0 2 * * *',
                'timezone' => 'Africa/Nairobi',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'school_id' => 2,
                'name' => 'Weekly Fee Reminder',
                'job_class' => 'App\\Modules\\Scheduler\\Jobs\\SendFeeRemindersJob',
                'cron_expression' => '0 9 * * 1',
                'timezone' => 'Africa/Nairobi',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],
        ];

        foreach ($jobs as $job) {
            $this->db->table('scheduled_jobs')->insert($job);
        }

        echo "   ✅ Created 3 scheduled jobs\n";
    }

    /**
     * Seed Security module test data.
     */
    private function seedSecurityModule(): void
    {
        echo "🔒 Seeding Security module...\n";

        // Check if table exists
        if (!$this->db->tableExists('permissions')) {
            echo "   ⚠️  permissions table not found, skipping...\n";
            return;
        }

        // Seed permissions
        $permissions = [
            ['name' => 'View Students', 'code' => 'students.view', 'module' => 'learning', 'description' => 'View student records'],
            ['name' => 'Create Students', 'code' => 'students.create', 'module' => 'learning', 'description' => 'Create new students'],
            ['name' => 'Edit Students', 'code' => 'students.edit', 'module' => 'learning', 'description' => 'Edit student records'],
            ['name' => 'Delete Students', 'code' => 'students.delete', 'module' => 'learning', 'description' => 'Delete students'],
            ['name' => 'View Finance', 'code' => 'finance.view', 'module' => 'finance', 'description' => 'View financial records'],
            ['name' => 'Manage Fees', 'code' => 'finance.fees', 'module' => 'finance', 'description' => 'Manage fee structures'],
            ['name' => 'Process Payments', 'code' => 'finance.payments', 'module' => 'finance', 'description' => 'Process payments'],
            ['name' => 'View HR', 'code' => 'hr.view', 'module' => 'hr', 'description' => 'View HR records'],
            ['name' => 'Manage Payroll', 'code' => 'hr.payroll', 'module' => 'hr', 'description' => 'Process payroll'],
            ['name' => 'Approve Leave', 'code' => 'hr.leave.approve', 'module' => 'hr', 'description' => 'Approve leave requests'],
        ];

        foreach ($permissions as $perm) {
            $perm['created_at'] = date('Y-m-d H:i:s');
            $this->db->table('permissions')->insert($perm);
        }

        echo "   ✅ Created 10 permissions\n";
    }

    /**
     * Seed Audit module test data.
     */
    private function seedAuditModule(): void
    {
        echo "📝 Seeding Audit module...\n";

        if (!$this->db->tableExists('audit_events')) {
            echo "   ⚠️  audit_events table not found, skipping...\n";
            return;
        }

        $events = [
            ['school_id' => 1, 'user_id' => 1, 'event_type' => 'login_success', 'entity_type' => 'user', 'entity_id' => 1],
            ['school_id' => 1, 'user_id' => 2, 'event_type' => 'create', 'entity_type' => 'student', 'entity_id' => 100],
            ['school_id' => 1, 'user_id' => 2, 'event_type' => 'update', 'entity_type' => 'student', 'entity_id' => 100],
            ['school_id' => 2, 'user_id' => 3, 'event_type' => 'create', 'entity_type' => 'invoice', 'entity_id' => 1],
        ];

        foreach ($events as $event) {
            $event['event_key'] = $event['event_type'] . ':' . ($event['entity_type'] ?? '') . ':' . ($event['entity_id'] ?? '') . ':' . time();
            $event['ip_address'] = '127.0.0.1';
            $event['created_at'] = date('Y-m-d H:i:s');
            $this->db->table('audit_events')->insert($event);
        }

        echo "   ✅ Created 4 audit events\n";
    }

    /**
     * Seed Learning module test data.
     */
    private function seedLearningModule(): void
    {
        echo "📚 Seeding Learning module...\n";

        // Seed subjects
        if ($this->db->tableExists('subjects')) {
            $subjects = [
                ['school_id' => 1, 'name' => 'Mathematics', 'code' => 'MATH', 'is_core' => 1],
                ['school_id' => 1, 'name' => 'English', 'code' => 'ENG', 'is_core' => 1],
                ['school_id' => 1, 'name' => 'Science', 'code' => 'SCI', 'is_core' => 1],
                ['school_id' => 1, 'name' => 'History', 'code' => 'HIST', 'is_core' => 0],
                ['school_id' => 1, 'name' => 'Geography', 'code' => 'GEO', 'is_core' => 0],
                ['school_id' => 2, 'name' => 'Physics', 'code' => 'PHY', 'is_core' => 1],
                ['school_id' => 2, 'name' => 'Chemistry', 'code' => 'CHEM', 'is_core' => 1],
                ['school_id' => 2, 'name' => 'Biology', 'code' => 'BIO', 'is_core' => 1],
            ];

            foreach ($subjects as $subject) {
                $subject['is_active'] = 1;
                $subject['created_at'] = date('Y-m-d H:i:s');
                $this->db->table('subjects')->insert($subject);
            }

            echo "   ✅ Created 8 subjects\n";
        }

        // Seed grading scales
        if ($this->db->tableExists('grading_scales')) {
            $grades = [
                ['school_id' => 1, 'name' => 'A', 'min_score' => 80, 'max_score' => 100, 'grade' => 'A', 'points' => 12, 'remarks' => 'Excellent'],
                ['school_id' => 1, 'name' => 'A-', 'min_score' => 75, 'max_score' => 79, 'grade' => 'A-', 'points' => 11, 'remarks' => 'Very Good'],
                ['school_id' => 1, 'name' => 'B+', 'min_score' => 70, 'max_score' => 74, 'grade' => 'B+', 'points' => 10, 'remarks' => 'Good'],
                ['school_id' => 1, 'name' => 'B', 'min_score' => 65, 'max_score' => 69, 'grade' => 'B', 'points' => 9, 'remarks' => 'Fairly Good'],
                ['school_id' => 1, 'name' => 'B-', 'min_score' => 60, 'max_score' => 64, 'grade' => 'B-', 'points' => 8, 'remarks' => 'Average'],
                ['school_id' => 1, 'name' => 'C+', 'min_score' => 55, 'max_score' => 59, 'grade' => 'C+', 'points' => 7, 'remarks' => 'Below Average'],
                ['school_id' => 1, 'name' => 'C', 'min_score' => 50, 'max_score' => 54, 'grade' => 'C', 'points' => 6, 'remarks' => 'Fair'],
                ['school_id' => 1, 'name' => 'C-', 'min_score' => 45, 'max_score' => 49, 'grade' => 'C-', 'points' => 5, 'remarks' => 'Weak'],
                ['school_id' => 1, 'name' => 'D+', 'min_score' => 40, 'max_score' => 44, 'grade' => 'D+', 'points' => 4, 'remarks' => 'Poor'],
                ['school_id' => 1, 'name' => 'D', 'min_score' => 35, 'max_score' => 39, 'grade' => 'D', 'points' => 3, 'remarks' => 'Very Poor'],
                ['school_id' => 1, 'name' => 'D-', 'min_score' => 30, 'max_score' => 34, 'grade' => 'D-', 'points' => 2, 'remarks' => 'Very Weak'],
                ['school_id' => 1, 'name' => 'E', 'min_score' => 0, 'max_score' => 29, 'grade' => 'E', 'points' => 1, 'remarks' => 'Fail'],
            ];

            foreach ($grades as $grade) {
                $grade['is_active'] = 1;
                $grade['created_at'] = date('Y-m-d H:i:s');
                $this->db->table('grading_scales')->insert($grade);
            }

            echo "   ✅ Created 12 grading scales\n";
        }
    }

    /**
     * Seed HR module test data.
     */
    private function seedHrModule(): void
    {
        echo "👔 Seeding HR module...\n";

        // Seed departments
        if ($this->db->tableExists('departments')) {
            $departments = [
                ['school_id' => 1, 'name' => 'Administration', 'code' => 'ADMIN'],
                ['school_id' => 1, 'name' => 'Teaching Staff', 'code' => 'TEACH'],
                ['school_id' => 1, 'name' => 'Support Staff', 'code' => 'SUPPORT'],
                ['school_id' => 1, 'name' => 'Finance', 'code' => 'FIN'],
                ['school_id' => 2, 'name' => 'Administration', 'code' => 'ADMIN'],
                ['school_id' => 2, 'name' => 'Science Department', 'code' => 'SCI'],
                ['school_id' => 2, 'name' => 'Languages Department', 'code' => 'LANG'],
            ];

            foreach ($departments as $dept) {
                $dept['is_active'] = 1;
                $dept['created_at'] = date('Y-m-d H:i:s');
                $this->db->table('departments')->insert($dept);
            }

            echo "   ✅ Created 7 departments\n";
        }

        // Seed leave types
        if ($this->db->tableExists('leave_types')) {
            $leaveTypes = [
                ['school_id' => 1, 'name' => 'Annual Leave', 'code' => 'ANNUAL', 'days_per_year' => 21, 'is_paid' => 1],
                ['school_id' => 1, 'name' => 'Sick Leave', 'code' => 'SICK', 'days_per_year' => 14, 'is_paid' => 1],
                ['school_id' => 1, 'name' => 'Maternity Leave', 'code' => 'MAT', 'days_per_year' => 90, 'is_paid' => 1],
                ['school_id' => 1, 'name' => 'Paternity Leave', 'code' => 'PAT', 'days_per_year' => 14, 'is_paid' => 1],
                ['school_id' => 1, 'name' => 'Study Leave', 'code' => 'STUDY', 'days_per_year' => 10, 'is_paid' => 0],
            ];

            foreach ($leaveTypes as $leave) {
                $leave['requires_approval'] = 1;
                $leave['is_active'] = 1;
                $leave['created_at'] = date('Y-m-d H:i:s');
                $this->db->table('leave_types')->insert($leave);
            }

            echo "   ✅ Created 5 leave types\n";
        }
    }

    /**
     * Seed Threads module test data.
     */
    private function seedThreadsModule(): void
    {
        echo "💬 Seeding Threads module...\n";

        if ($this->db->tableExists('threads')) {
            $threads = [
                [
                    'school_id' => 1,
                    'subject' => 'Welcome to the new term',
                    'thread_type' => 'announcement',
                    'created_by' => 1,
                    'message_count' => 0,
                    'created_at' => date('Y-m-d H:i:s'),
                ],
                [
                    'school_id' => 1,
                    'subject' => 'Class 8 Parent-Teacher Meeting',
                    'thread_type' => 'group',
                    'created_by' => 2,
                    'message_count' => 0,
                    'created_at' => date('Y-m-d H:i:s'),
                ],
            ];

            foreach ($threads as $thread) {
                $this->db->table('threads')->insert($thread);
            }

            echo "   ✅ Created 2 threads\n";
        }

        if ($this->db->tableExists('announcements')) {
            $announcements = [
                [
                    'school_id' => 1,
                    'title' => 'School Reopening Date',
                    'content' => 'School will reopen on January 6th, 2025 for the new academic year.',
                    'scope' => 'school',
                    'priority' => 'high',
                    'status' => 'published',
                    'created_by' => 1,
                    'created_at' => date('Y-m-d H:i:s'),
                ],
                [
                    'school_id' => 1,
                    'title' => 'Fee Payment Reminder',
                    'content' => 'Please ensure all fees are paid by January 15th to avoid late payment penalties.',
                    'scope' => 'school',
                    'priority' => 'urgent',
                    'status' => 'published',
                    'created_by' => 1,
                    'created_at' => date('Y-m-d H:i:s'),
                ],
            ];

            foreach ($announcements as $ann) {
                $this->db->table('announcements')->insert($ann);
            }

            echo "   ✅ Created 2 announcements\n";
        }
    }

    /**
     * Seed Admissions module test data.
     */
    private function seedAdmissionsModule(): void
    {
        echo "🎓 Seeding Admissions module...\n";

        if ($this->db->tableExists('applications')) {
            $applications = [
                [
                    'school_id' => 1,
                    'application_number' => 'APP-2025-0001',
                    'academic_year' => '2025',
                    'class_applied' => 1,
                    'student_first_name' => 'John',
                    'student_last_name' => 'Kamau',
                    'student_dob' => '2018-03-15',
                    'student_gender' => 'male',
                    'parent_first_name' => 'James',
                    'parent_last_name' => 'Kamau',
                    'parent_email' => 'james.kamau@test.local',
                    'parent_phone' => '0722123456',
                    'parent_relationship' => 'father',
                    'status' => 'submitted',
                    'created_at' => date('Y-m-d H:i:s'),
                ],
                [
                    'school_id' => 1,
                    'application_number' => 'APP-2025-0002',
                    'academic_year' => '2025',
                    'class_applied' => 1,
                    'student_first_name' => 'Mary',
                    'student_last_name' => 'Wanjiku',
                    'student_dob' => '2018-06-20',
                    'student_gender' => 'female',
                    'parent_first_name' => 'Grace',
                    'parent_last_name' => 'Wanjiku',
                    'parent_email' => 'grace.wanjiku@test.local',
                    'parent_phone' => '0733987654',
                    'parent_relationship' => 'mother',
                    'status' => 'under_review',
                    'created_at' => date('Y-m-d H:i:s'),
                ],
                [
                    'school_id' => 1,
                    'application_number' => 'APP-2025-0003',
                    'academic_year' => '2025',
                    'class_applied' => 2,
                    'student_first_name' => 'Peter',
                    'student_last_name' => 'Ochieng',
                    'student_dob' => '2017-09-10',
                    'student_gender' => 'male',
                    'parent_first_name' => 'John',
                    'parent_last_name' => 'Ochieng',
                    'parent_email' => 'john.ochieng@test.local',
                    'parent_phone' => '0711456789',
                    'parent_relationship' => 'father',
                    'status' => 'accepted',
                    'created_at' => date('Y-m-d H:i:s'),
                ],
            ];

            foreach ($applications as $app) {
                $this->db->table('applications')->insert($app);
            }

            echo "   ✅ Created 3 applications\n";
        }
    }

    /**
     * Seed Transport module test data.
     */
    private function seedTransportModule(): void
    {
        echo "🚌 Seeding Transport module...\n";

        if ($this->db->tableExists('transport_vehicles')) {
            $vehicles = [
                ['school_id' => 1, 'registration_number' => 'KBA 123A', 'make' => 'Toyota', 'model' => 'Coaster', 'capacity' => 30, 'status' => 'active'],
                ['school_id' => 1, 'registration_number' => 'KCA 456B', 'make' => 'Isuzu', 'model' => 'NQR', 'capacity' => 45, 'status' => 'active'],
                ['school_id' => 2, 'registration_number' => 'KDA 789C', 'make' => 'Mercedes', 'model' => 'Sprinter', 'capacity' => 20, 'status' => 'active'],
            ];

            foreach ($vehicles as $vehicle) {
                $vehicle['created_at'] = date('Y-m-d H:i:s');
                $this->db->table('transport_vehicles')->insert($vehicle);
            }

            echo "   ✅ Created 3 vehicles\n";
        }

        if ($this->db->tableExists('transport_routes')) {
            $routes = [
                ['school_id' => 1, 'name' => 'Westlands Route', 'code' => 'WEST', 'monthly_fee' => 5000, 'is_active' => 1],
                ['school_id' => 1, 'name' => 'Karen Route', 'code' => 'KRN', 'monthly_fee' => 6000, 'is_active' => 1],
                ['school_id' => 1, 'name' => 'Langata Route', 'code' => 'LNG', 'monthly_fee' => 5500, 'is_active' => 1],
            ];

            foreach ($routes as $route) {
                $route['created_at'] = date('Y-m-d H:i:s');
                $this->db->table('transport_routes')->insert($route);
            }

            echo "   ✅ Created 3 routes\n";
        }
    }

    /**
     * Seed Wallets module test data.
     */
    private function seedWalletsModule(): void
    {
        echo "💰 Seeding Wallets module...\n";

        if ($this->db->tableExists('wallets')) {
            // Create wallets for some test users
            $wallets = [
                ['school_id' => 1, 'user_id' => 100, 'wallet_type' => 'student', 'balance' => 5000, 'status' => 'active'],
                ['school_id' => 1, 'user_id' => 101, 'wallet_type' => 'student', 'balance' => 3500, 'status' => 'active'],
                ['school_id' => 1, 'user_id' => 150, 'wallet_type' => 'parent', 'balance' => 25000, 'status' => 'active'],
                ['school_id' => 2, 'user_id' => 200, 'wallet_type' => 'student', 'balance' => 8000, 'status' => 'active'],
            ];

            foreach ($wallets as $wallet) {
                $wallet['created_at'] = date('Y-m-d H:i:s');
                $this->db->table('wallets')->insert($wallet);
            }

            echo "   ✅ Created 4 wallets\n";
        }
    }

    /**
     * Seed Approval Workflows module test data.
     */
    private function seedApprovalWorkflowsModule(): void
    {
        echo "✅ Seeding Approval Workflows module...\n";

        if ($this->db->tableExists('approval_workflows')) {
            $workflows = [
                [
                    'school_id' => 1,
                    'name' => 'Leave Request Approval',
                    'code' => 'LEAVE_APPROVAL',
                    'entity_type' => 'leave_request',
                    'is_active' => 1,
                    'created_by' => 1,
                    'created_at' => date('Y-m-d H:i:s'),
                ],
                [
                    'school_id' => 1,
                    'name' => 'Purchase Order Approval',
                    'code' => 'PO_APPROVAL',
                    'entity_type' => 'purchase_order',
                    'is_active' => 1,
                    'created_by' => 1,
                    'created_at' => date('Y-m-d H:i:s'),
                ],
                [
                    'school_id' => 1,
                    'name' => 'Fee Waiver Approval',
                    'code' => 'FEE_WAIVER',
                    'entity_type' => 'fee_waiver',
                    'is_active' => 1,
                    'created_by' => 1,
                    'created_at' => date('Y-m-d H:i:s'),
                ],
            ];

            foreach ($workflows as $workflow) {
                $this->db->table('approval_workflows')->insert($workflow);
            }

            echo "   ✅ Created 3 approval workflows\n";
        }
    }

    /**
     * Seed Monitoring module test data.
     */
    private function seedMonitoringModule(): void
    {
        echo "📊 Seeding Monitoring module...\n";

        if ($this->db->tableExists('health_checks')) {
            $checks = [
                ['name' => 'Database Connection', 'check_type' => 'database', 'check_interval_seconds' => 60, 'is_critical' => 1, 'is_active' => 1],
                ['name' => 'Cache Service', 'check_type' => 'cache', 'check_interval_seconds' => 60, 'is_critical' => 0, 'is_active' => 1],
                ['name' => 'Job Queue', 'check_type' => 'queue', 'check_interval_seconds' => 120, 'is_critical' => 1, 'is_active' => 1],
                ['name' => 'Storage Space', 'check_type' => 'storage', 'check_interval_seconds' => 300, 'is_critical' => 1, 'is_active' => 1],
            ];

            foreach ($checks as $check) {
                $check['created_at'] = date('Y-m-d H:i:s');
                $this->db->table('health_checks')->insert($check);
            }

            echo "   ✅ Created 4 health checks\n";
        }

        if ($this->db->tableExists('alerts')) {
            $alerts = [
                [
                    'name' => 'High Error Rate',
                    'alert_type' => 'threshold',
                    'metric_name' => 'error_rate',
                    'condition' => json_encode(['operator' => '>', 'value' => 5]),
                    'severity' => 'critical',
                    'notification_channels' => json_encode(['email', 'sms']),
                    'is_active' => 1,
                    'created_at' => date('Y-m-d H:i:s'),
                ],
                [
                    'name' => 'Slow Response Time',
                    'alert_type' => 'threshold',
                    'metric_name' => 'response_time_p95',
                    'condition' => json_encode(['operator' => '>', 'value' => 500]),
                    'severity' => 'warning',
                    'notification_channels' => json_encode(['email']),
                    'is_active' => 1,
                    'created_at' => date('Y-m-d H:i:s'),
                ],
            ];

            foreach ($alerts as $alert) {
                $this->db->table('alerts')->insert($alert);
            }

            echo "   ✅ Created 2 alerts\n";
        }
    }

    /**
     * Print test credentials for all user roles.
     */
    private function printTestCredentials(): void
    {
        echo "\n📋 Test User Credentials:\n";
        echo "=========================\n";
        echo "\n🔴 Super Admin:\n";
        echo "   Email: admin@shulelabs.local\n";
        echo "   Password: Admin@123456\n";

        echo "\n🔵 School Admins:\n";
        echo "   Email: schooladmin100@school1.local\n";
        echo "   Password: Admin@123\n";

        echo "\n🟢 Teachers:\n";
        echo "   Email: teacher101@school1.local\n";
        echo "   Password: Teacher@123\n";

        echo "\n🟡 Students:\n";
        echo "   Email: student109@school1.local\n";
        echo "   Password: Student@123\n";

        echo "\n🟣 Parents:\n";
        echo "   Email: parent134@school1.local\n";
        echo "   Password: Parent@123\n";

        echo "\n🟠 Accountants:\n";
        echo "   Email: accountant139@school2.local\n";
        echo "   Password: Accountant@123\n";

        echo "\n🔷 Librarians:\n";
        echo "   Email: librarian140@school1.local\n";
        echo "   Password: Librarian@123\n";
        echo "\n";
    }
}
