<?php

namespace App\Controllers;

use App\Models\StudentEnrollmentModel;
use App\Models\GradeModel;
use App\Models\AssignmentModel;
use App\Models\ThreadMessageModel;

/**
 * ParentPortal Controller
 *
 * Parent portal for viewing children's academic progress
 */
class ParentPortal extends BaseController
{
    protected $data = [];
    protected GradeModel $gradeModel;
    protected AssignmentModel $assignmentModel;

    public function __construct()
    {
        helper(['compatibility', 'form']);
        $this->gradeModel = new GradeModel();
        $this->assignmentModel = new AssignmentModel();
    }

    /**
     * Parent dashboard
     */
    public function index(): string
    {
        $this->data['user'] = $this->getUserData();
        $parentId = session()->get('loginuserID');

        $this->data['children'] = $this->getChildren($parentId);
        $this->data['recent_updates'] = $this->getRecentUpdates($parentId);

        return view('parent/dashboard', $this->data);
    }

    /**
     * View all children
     */
    public function children(): string
    {
        $this->data['user'] = $this->getUserData();
        $parentId = session()->get('loginuserID');

        $this->data['children'] = $this->getChildren($parentId);

        return view('parent/children', $this->data);
    }

    /**
     * View child's attendance
     */
    public function attendance(int $childId): string
    {
        $this->data['user'] = $this->getUserData();
        $parentId = session()->get('loginuserID');

        // Verify this is parent's child
        if (!$this->verifyChildRelationship($parentId, $childId)) {
            return redirect()->to('/parent/children')->with('error', 'Unauthorized access');
        }

        $this->data['child'] = $this->getChildInfo($childId);
        $this->data['attendance_records'] = $this->getAttendanceRecords($childId);
        $this->data['attendance_summary'] = $this->getAttendanceSummary($childId);

        return view('parent/attendance', $this->data);
    }

    /**
     * View child's grades
     */
    public function grades(int $childId): string
    {
        $this->data['user'] = $this->getUserData();
        $parentId = session()->get('loginuserID');

        if (!$this->verifyChildRelationship($parentId, $childId)) {
            return redirect()->to('/parent/children')->with('error', 'Unauthorized access');
        }

        $this->data['child'] = $this->getChildInfo($childId);
        $this->data['grades'] = $this->gradeModel
            ->select('grades.*, assignments.title as assignment_title, ci4_users.full_name as teacher_name')
            ->join('assignments', 'assignments.assignmentsID = grades.assignment_id', 'left')
            ->join('ci4_users', 'ci4_users.id = grades.teacher_id', 'left')
            ->where('grades.student_id', $childId)
            ->orderBy('grades.created_at', 'DESC')
            ->findAll();

        $this->data['grade_summary'] = [
            'average' => $this->calculateAverageGrade($childId),
            'total_assignments' => count($this->data['grades']),
            'latest_grade' => $this->data['grades'][0] ?? null,
        ];

        return view('parent/grades', $this->data);
    }

    /**
     * View child's assignments
     */
    public function assignments(int $childId): string
    {
        $this->data['user'] = $this->getUserData();
        $parentId = session()->get('loginuserID');

        if (!$this->verifyChildRelationship($parentId, $childId)) {
            return redirect()->to('/parent/children')->with('error', 'Unauthorized access');
        }

        $this->data['child'] = $this->getChildInfo($childId);
        
        // Get child's enrolled classes
        $enrollmentModel = new StudentEnrollmentModel();
        $enrolledClasses = $enrollmentModel->where('student_id', $childId)->findColumn('class_id');

        if (!empty($enrolledClasses)) {
            $this->data['assignments'] = $this->assignmentModel
                ->whereIn('class_id', $enrolledClasses)
                ->orderBy('due_date', 'DESC')
                ->findAll();
        } else {
            $this->data['assignments'] = [];
        }

        $this->data['assignment_summary'] = [
            'total' => count($this->data['assignments']),
            'pending' => $this->countPendingAssignments($childId),
            'completed' => $this->countCompletedAssignments($childId),
        ];

        return view('parent/assignments', $this->data);
    }

    /**
     * Send message to teacher
     */
    public function sendMessage()
    {
        if ($this->request->getMethod() !== 'post') {
            return redirect()->to('/parent/children');
        }

        $parentId = session()->get('loginuserID');
        $messageModel = new ThreadMessageModel();

        $data = [
            'sender_id' => $parentId,
            'recipient_id' => $this->request->getPost('teacher_id'),
            'subject' => $this->request->getPost('subject'),
            'message' => $this->request->getPost('message'),
            'created_at' => date('Y-m-d H:i:s'),
            'status' => 'unread',
        ];

        if ($messageModel->insert($data)) {
            return redirect()->back()->with('success', 'Message sent successfully');
        }

        return redirect()->back()->with('error', 'Failed to send message');
    }

    // Helper methods

    private function getUserData(): array
    {
        $session = session();
        return [
            'name' => $session->get('name'),
            'email' => $session->get('email'),
            'usertypeID' => $session->get('usertypeID'),
            'photo' => $session->get('photo')
        ];
    }

    private function getChildren(int $parentId): array
    {
        // Get children linked to this parent
        return db_connect()->table('student_parent')
            ->select('ci4_users.id, ci4_users.full_name, ci4_users.email, ci4_users.photo, school_classes.class_name')
            ->join('ci4_users', 'ci4_users.id = student_parent.student_id')
            ->join('student_enrollments', 'student_enrollments.student_id = ci4_users.id', 'left')
            ->join('school_classes', 'school_classes.classesID = student_enrollments.class_id', 'left')
            ->where('student_parent.parent_id', $parentId)
            ->groupBy('ci4_users.id')
            ->get()
            ->getResultArray();
    }

    private function verifyChildRelationship(int $parentId, int $childId): bool
    {
        $relationship = db_connect()->table('student_parent')
            ->where('parent_id', $parentId)
            ->where('student_id', $childId)
            ->countAllResults();

        return $relationship > 0;
    }

    private function getChildInfo(int $childId): array
    {
        return db_connect()->table('ci4_users')
            ->select('ci4_users.*, school_classes.class_name')
            ->join('student_enrollments', 'student_enrollments.student_id = ci4_users.id', 'left')
            ->join('school_classes', 'school_classes.classesID = student_enrollments.class_id', 'left')
            ->where('ci4_users.id', $childId)
            ->get()
            ->getRowArray();
    }

    private function getRecentUpdates(int $parentId): array
    {
        $children = $this->getChildren($parentId);
        $childIds = array_column($children, 'id');

        if (empty($childIds)) {
            return [];
        }

        // Get recent grades
        $recentGrades = $this->gradeModel
            ->whereIn('student_id', $childIds)
            ->orderBy('created_at', 'DESC')
            ->limit(5)
            ->findAll();

        return $recentGrades;
    }

    private function getAttendanceRecords(int $childId): array
    {
        return db_connect()->table('attendance')
            ->where('student_id', $childId)
            ->orderBy('date', 'DESC')
            ->limit(30)
            ->get()
            ->getResultArray();
    }

    private function getAttendanceSummary(int $childId): array
    {
        $total = db_connect()->table('attendance')
            ->where('student_id', $childId)
            ->countAllResults();

        $present = db_connect()->table('attendance')
            ->where('student_id', $childId)
            ->where('status', 'present')
            ->countAllResults();

        return [
            'total_days' => $total,
            'present_days' => $present,
            'absent_days' => $total - $present,
            'attendance_rate' => $total > 0 ? round(($present / $total) * 100, 2) : 0,
        ];
    }

    private function calculateAverageGrade(int $childId): float
    {
        $result = $this->gradeModel
            ->selectAvg('marks_obtained')
            ->where('student_id', $childId)
            ->first();

        return round($result['marks_obtained'] ?? 0, 2);
    }

    private function countPendingAssignments(int $childId): int
    {
        $enrollmentModel = new StudentEnrollmentModel();
        $enrolledClasses = $enrollmentModel->where('student_id', $childId)->findColumn('class_id');

        if (empty($enrolledClasses)) {
            return 0;
        }

        return $this->assignmentModel
            ->whereIn('class_id', $enrolledClasses)
            ->where('status', 'active')
            ->where('due_date >=', date('Y-m-d'))
            ->countAllResults();
    }

    private function countCompletedAssignments(int $childId): int
    {
        return db_connect()->table('assignment_submissions')
            ->where('student_id', $childId)
            ->where('status', 'submitted')
            ->countAllResults();
    }
}
