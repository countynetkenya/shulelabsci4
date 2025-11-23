<?php

namespace App\Controllers;

use App\Models\AssignmentModel;
use App\Models\GradeModel;
use App\Models\StudentEnrollmentModel;
use CodeIgniter\Files\File;

/**
 * Student Controller.
 *
 * Student portal for viewing courses, assignments, grades
 */
class Student extends BaseController
{
    protected $data = [];

    protected StudentEnrollmentModel $enrollmentModel;

    protected AssignmentModel $assignmentModel;

    protected GradeModel $gradeModel;

    public function __construct()
    {
        helper(['compatibility', 'form']);
        $this->enrollmentModel = new StudentEnrollmentModel();
        $this->assignmentModel = new AssignmentModel();
        $this->gradeModel = new GradeModel();
    }

    /**
     * Student dashboard.
     */
    public function index(): string
    {
        $this->data['user'] = $this->getUserData();
        $studentId = session()->get('loginuserID');

        $this->data['enrolled_courses'] = $this->getEnrolledCourses($studentId);
        $this->data['pending_assignments'] = $this->getPendingAssignments($studentId);
        $this->data['recent_grades'] = $this->gradeModel
            ->where('student_id', $studentId)
            ->orderBy('created_at', 'DESC')
            ->limit(5)
            ->findAll();

        $this->data['stats'] = [
            'total_courses' => count($this->data['enrolled_courses']),
            'pending_assignments' => count($this->data['pending_assignments']),
            'average_grade' => $this->getAverageGrade($studentId),
        ];

        return view('student/dashboard', $this->data);
    }

    /**
     * View all enrolled courses.
     */
    public function courses(): string
    {
        $this->data['user'] = $this->getUserData();
        $studentId = session()->get('loginuserID');

        $this->data['courses'] = $this->getEnrolledCourses($studentId);

        return view('student/courses', $this->data);
    }

    /**
     * View course materials.
     */
    public function materials(int $courseId): string
    {
        $this->data['user'] = $this->getUserData();
        $studentId = session()->get('loginuserID');

        // Verify student is enrolled
        $enrollment = $this->enrollmentModel
            ->where('student_id', $studentId)
            ->where('class_id', $courseId)
            ->first();

        if (!$enrollment) {
            return redirect()->to('/student/courses')->with('error', 'You are not enrolled in this course');
        }

        // Get course materials (assignments, announcements, etc.)
        $this->data['course'] = db_connect()->table('school_classes')->where('classesID', $courseId)->get()->getRowArray();
        $this->data['assignments'] = $this->assignmentModel->where('class_id', $courseId)->findAll();
        $this->data['announcements'] = db_connect()->table('thread_announcements')
            ->where('class_id', $courseId)
            ->orderBy('created_at', 'DESC')
            ->limit(10)
            ->get()
            ->getResultArray();

        return view('student/materials', $this->data);
    }

    /**
     * View all assignments.
     */
    public function assignments(): string
    {
        $this->data['user'] = $this->getUserData();
        $studentId = session()->get('loginuserID');

        $this->data['assignments'] = $this->getPendingAssignments($studentId);
        $this->data['submitted_assignments'] = $this->getSubmittedAssignments($studentId);

        return view('student/assignments', $this->data);
    }

    /**
     * Submit assignment.
     */
    public function submitAssignment()
    {
        if ($this->request->getMethod() !== 'post') {
            return redirect()->to('/student/assignments');
        }

        $studentId = session()->get('loginuserID');
        $assignmentId = $this->request->getPost('assignment_id');

        // Handle file upload
        $file = $this->request->getFile('submission_file');
        $fileName = null;

        if ($file && $file->isValid() && !$file->hasMoved()) {
            $fileName = $file->getRandomName();
            $file->move(WRITEPATH . 'uploads/assignments', $fileName);
        }

        $data = [
            'assignment_id' => $assignmentId,
            'student_id' => $studentId,
            'submission_text' => $this->request->getPost('submission_text'),
            'submission_file' => $fileName,
            'submitted_at' => date('Y-m-d H:i:s'),
            'status' => 'submitted',
        ];

        $db = db_connect();
        if ($db->table('assignment_submissions')->insert($data)) {
            return redirect()->to('/student/assignments')->with('success', 'Assignment submitted successfully');
        }

        return redirect()->back()->with('error', 'Failed to submit assignment');
    }

    /**
     * View all grades.
     */
    public function grades(): string
    {
        $this->data['user'] = $this->getUserData();
        $studentId = session()->get('loginuserID');

        $this->data['grades'] = $this->gradeModel
            ->select('grades.*, assignments.title as assignment_title, ci4_users.full_name as teacher_name')
            ->join('assignments', 'assignments.assignmentsID = grades.assignment_id', 'left')
            ->join('ci4_users', 'ci4_users.id = grades.teacher_id', 'left')
            ->where('grades.student_id', $studentId)
            ->orderBy('grades.created_at', 'DESC')
            ->findAll();

        $this->data['grade_summary'] = [
            'average_grade' => $this->getAverageGrade($studentId),
            'total_graded' => count($this->data['grades']),
            'highest_grade' => $this->getHighestGrade($studentId),
        ];

        return view('student/grades', $this->data);
    }

    // Helper methods

    private function getUserData(): array
    {
        $session = session();
        return [
            'name' => $session->get('name'),
            'email' => $session->get('email'),
            'usertypeID' => $session->get('usertypeID'),
            'photo' => $session->get('photo'),
        ];
    }

    private function getEnrolledCourses(int $studentId): array
    {
        return $this->enrollmentModel
            ->select('student_enrollments.*, school_classes.*')
            ->join('school_classes', 'school_classes.classesID = student_enrollments.class_id')
            ->where('student_enrollments.student_id', $studentId)
            ->findAll();
    }

    private function getPendingAssignments(int $studentId): array
    {
        // Get assignments for enrolled courses that haven't been submitted
        $enrolledClasses = $this->enrollmentModel->where('student_id', $studentId)->findColumn('class_id');

        if (empty($enrolledClasses)) {
            return [];
        }

        return $this->assignmentModel
            ->whereIn('class_id', $enrolledClasses)
            ->where('status', 'active')
            ->where('due_date >=', date('Y-m-d'))
            ->orderBy('due_date', 'ASC')
            ->findAll();
    }

    private function getSubmittedAssignments(int $studentId): array
    {
        return db_connect()->table('assignment_submissions')
            ->select('assignment_submissions.*, assignments.title')
            ->join('assignments', 'assignments.assignmentsID = assignment_submissions.assignment_id')
            ->where('assignment_submissions.student_id', $studentId)
            ->orderBy('assignment_submissions.submitted_at', 'DESC')
            ->get()
            ->getResultArray();
    }

    private function getAverageGrade(int $studentId): float
    {
        $result = $this->gradeModel
            ->selectAvg('marks_obtained')
            ->where('student_id', $studentId)
            ->first();

        return round($result['marks_obtained'] ?? 0, 2);
    }

    private function getHighestGrade(int $studentId): float
    {
        $result = $this->gradeModel
            ->selectMax('marks_obtained')
            ->where('student_id', $studentId)
            ->first();

        return $result['marks_obtained'] ?? 0;
    }
}
