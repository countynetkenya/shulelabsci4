<?php

namespace App\Controllers;

use App\Models\AssignmentModel;
use App\Models\GradeModel;
use App\Models\SchoolClassModel;
use App\Models\StudentEnrollmentModel;
use App\Models\ThreadAnnouncementModel;

/**
 * Teacher Controller.
 *
 * Teacher portal for class management, assignments, grading
 */
class Teacher extends BaseController
{
    protected $data = [];

    protected SchoolClassModel $classModel;

    protected AssignmentModel $assignmentModel;

    protected GradeModel $gradeModel;

    public function __construct()
    {
        helper(['compatibility', 'form']);
        $this->classModel = new SchoolClassModel();
        $this->assignmentModel = new AssignmentModel();
        $this->gradeModel = new GradeModel();
    }

    /**
     * Teacher dashboard.
     */
    public function index(): string
    {
        $this->data['user'] = $this->getUserData();
        $teacherId = session()->get('loginuserID');

        // Get teacher's classes
        $this->data['my_classes'] = $this->getTeacherClasses($teacherId);
        $this->data['pending_assignments'] = $this->assignmentModel
            ->where('teacher_id', $teacherId)
            ->where('status', 'active')
            ->orderBy('due_date', 'ASC')
            ->limit(5)
            ->find();

        $this->data['stats'] = [
            'total_classes' => count($this->data['my_classes']),
            'total_assignments' => $this->assignmentModel->where('teacher_id', $teacherId)->countAllResults(),
            'pending_grading' => $this->getPendingGradingCount($teacherId),
        ];

        return view('teacher/dashboard', $this->data);
    }

    /**
     * View all classes.
     */
    public function classes(): string
    {
        $this->data['user'] = $this->getUserData();
        $teacherId = session()->get('loginuserID');

        $this->data['classes'] = $this->getTeacherClasses($teacherId);

        return view('teacher/classes', $this->data);
    }

    /**
     * View students in a class.
     */
    public function students(int $classId): string
    {
        $this->data['user'] = $this->getUserData();
        $this->data['class'] = $this->classModel->find($classId);

        if (!$this->data['class']) {
            return redirect()->to('/teacher/classes')->with('error', 'Class not found');
        }

        $enrollmentModel = new StudentEnrollmentModel();
        $this->data['students'] = $enrollmentModel
            ->select('student_enrollments.*, ci4_users.full_name, ci4_users.email')
            ->join('ci4_users', 'ci4_users.id = student_enrollments.student_id')
            ->where('student_enrollments.class_id', $classId)
            ->findAll();

        return view('teacher/students', $this->data);
    }

    /**
     * View all assignments.
     */
    public function assignments(): string
    {
        $this->data['user'] = $this->getUserData();
        $teacherId = session()->get('loginuserID');

        $this->data['assignments'] = $this->assignmentModel
            ->where('teacher_id', $teacherId)
            ->orderBy('created_at', 'DESC')
            ->findAll();

        $this->data['classes'] = $this->getTeacherClasses($teacherId);

        return view('teacher/assignments', $this->data);
    }

    /**
     * Create new assignment.
     */
    public function createAssignment()
    {
        if ($this->request->getMethod() !== 'post') {
            return redirect()->to('/teacher/assignments');
        }

        $teacherId = session()->get('loginuserID');

        $data = [
            'teacher_id' => $teacherId,
            'class_id' => $this->request->getPost('class_id'),
            'title' => $this->request->getPost('title'),
            'description' => $this->request->getPost('description'),
            'due_date' => $this->request->getPost('due_date'),
            'total_marks' => $this->request->getPost('total_marks'),
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
        ];

        if ($this->assignmentModel->insert($data)) {
            return redirect()->to('/teacher/assignments')->with('success', 'Assignment created successfully');
        }

        return redirect()->back()->with('error', 'Failed to create assignment');
    }

    /**
     * Grading interface.
     */
    public function grading(): string
    {
        $this->data['user'] = $this->getUserData();
        $teacherId = session()->get('loginuserID');

        // Get assignments for grading
        $this->data['assignments'] = $this->assignmentModel
            ->where('teacher_id', $teacherId)
            ->where('status', 'active')
            ->orderBy('due_date', 'ASC')
            ->findAll();

        // Get recent grades
        $this->data['recent_grades'] = $this->gradeModel
            ->where('teacher_id', $teacherId)
            ->orderBy('created_at', 'DESC')
            ->limit(20)
            ->findAll();

        return view('teacher/grading', $this->data);
    }

    /**
     * Submit grade for student.
     */
    public function submitGrade()
    {
        if ($this->request->getMethod() !== 'post') {
            return redirect()->to('/teacher/grading');
        }

        $teacherId = session()->get('loginuserID');

        $data = [
            'student_id' => $this->request->getPost('student_id'),
            'assignment_id' => $this->request->getPost('assignment_id'),
            'teacher_id' => $teacherId,
            'marks_obtained' => $this->request->getPost('marks_obtained'),
            'total_marks' => $this->request->getPost('total_marks'),
            'grade' => $this->calculateGrade($this->request->getPost('marks_obtained'), $this->request->getPost('total_marks')),
            'feedback' => $this->request->getPost('feedback'),
            'created_at' => date('Y-m-d H:i:s'),
        ];

        if ($this->gradeModel->insert($data)) {
            return redirect()->to('/teacher/grading')->with('success', 'Grade submitted successfully');
        }

        return redirect()->back()->with('error', 'Failed to submit grade');
    }

    /**
     * Create announcement for class.
     */
    public function createAnnouncement()
    {
        if ($this->request->getMethod() !== 'post') {
            return redirect()->to('/teacher/classes');
        }

        $teacherId = session()->get('loginuserID');
        $announcementModel = new ThreadAnnouncementModel();

        $data = [
            'class_id' => $this->request->getPost('class_id'),
            'teacher_id' => $teacherId,
            'title' => $this->request->getPost('title'),
            'message' => $this->request->getPost('message'),
            'created_at' => date('Y-m-d H:i:s'),
        ];

        if ($announcementModel->insert($data)) {
            return redirect()->back()->with('success', 'Announcement posted successfully');
        }

        return redirect()->back()->with('error', 'Failed to post announcement');
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

    private function getTeacherClasses(int $teacherId): array
    {
        // Get classes assigned to this teacher
        return $this->classModel
            ->where('teacher_id', $teacherId)
            ->orWhere('class_teacher_id', $teacherId)
            ->findAll();
    }

    private function getPendingGradingCount(int $teacherId): int
    {
        // Count assignments that need grading
        return db_connect()->table('assignments')
            ->where('teacher_id', $teacherId)
            ->where('status', 'active')
            ->where('due_date <', date('Y-m-d'))
            ->countAllResults();
    }

    private function calculateGrade(float $obtained, float $total): string
    {
        $percentage = ($obtained / $total) * 100;

        if ($percentage >= 90) {
            return 'A';
        }
        if ($percentage >= 80) {
            return 'B';
        }
        if ($percentage >= 70) {
            return 'C';
        }
        if ($percentage >= 60) {
            return 'D';
        }
        return 'F';
    }
}
