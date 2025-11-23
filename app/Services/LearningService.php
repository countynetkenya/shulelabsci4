<?php

namespace App\Services;

use App\Models\CourseModel;
use App\Models\AssignmentModel;
use App\Models\GradeModel;

/**
 * LearningService - Course and assignment management.
 */
class LearningService
{
    protected CourseModel $courseModel;
    protected AssignmentModel $assignmentModel;
    protected GradeModel $gradeModel;

    public function __construct()
    {
        $this->courseModel = model(CourseModel::class);
        $this->assignmentModel = model(AssignmentModel::class);
        $this->gradeModel = model(GradeModel::class);
    }

    /**
     * Get all courses for a school.
     */
    public function getSchoolCourses(int $schoolId, ?int $classId = null): array
    {
        $builder = $this->courseModel->forSchool($schoolId);

        if ($classId) {
            $builder->where('class_id', $classId);
        }

        return $builder->findAll();
    }

    /**
     * Create course.
     */
    public function createCourse(int $schoolId, int $classId, string $name, string $code, ?int $teacherId = null): array
    {
        $data = [
            'school_id' => $schoolId,
            'class_id' => $classId,
            'course_name' => $name,
            'course_code' => $code,
            'teacher_id' => $teacherId,
        ];

        $courseId = $this->courseModel->insert($data);

        if (!$courseId) {
            return ['success' => false, 'message' => 'Failed to create course'];
        }

        return ['success' => true, 'course_id' => $courseId];
    }

    /**
     * Get assignments for course.
     */
    public function getCourseAssignments(int $courseId): array
    {
        return $this->assignmentModel
            ->where('course_id', $courseId)
            ->orderBy('due_date', 'ASC')
            ->findAll();
    }

    /**
     * Create assignment.
     */
    public function createAssignment(int $courseId, int $schoolId, string $title, string $description, string $dueDate, int $maxPoints = 100): array
    {
        $data = [
            'course_id' => $courseId,
            'school_id' => $schoolId,
            'title' => $title,
            'description' => $description,
            'due_date' => $dueDate,
            'max_points' => $maxPoints,
            'status' => 'active',
        ];

        $assignmentId = $this->assignmentModel->insert($data);

        if (!$assignmentId) {
            return ['success' => false, 'message' => 'Failed to create assignment'];
        }

        return ['success' => true, 'assignment_id' => $assignmentId];
    }

    /**
     * Submit grade for assignment.
     */
    public function submitGrade(int $assignmentId, int $studentId, int $schoolId, float $points, ?string $feedback = null): array
    {
        // Get assignment to verify max_points
        $assignment = $this->assignmentModel->find($assignmentId);

        if (!$assignment) {
            return ['success' => false, 'message' => 'Assignment not found'];
        }

        if ($points > $assignment['max_points']) {
            return ['success' => false, 'message' => 'Points exceed maximum allowed'];
        }

        // Check if grade already exists
        $existing = $this->gradeModel
            ->where('assignment_id', $assignmentId)
            ->where('student_id', $studentId)
            ->first();

        $data = [
            'assignment_id' => $assignmentId,
            'student_id' => $studentId,
            'school_id' => $schoolId,
            'points_earned' => $points,
            'max_points' => $assignment['max_points'],
            'feedback' => $feedback,
            'graded_at' => date('Y-m-d H:i:s'),
        ];

        if ($existing) {
            $result = $this->gradeModel->update($existing['id'], $data);
        } else {
            $result = $this->gradeModel->insert($data);
        }

        if (!$result) {
            return ['success' => false, 'message' => 'Failed to submit grade'];
        }

        return ['success' => true];
    }

    /**
     * Get student grades for course.
     */
    public function getStudentGrades(int $studentId, int $courseId): array
    {
        // Get all assignments for course
        $assignments = $this->assignmentModel
            ->where('course_id', $courseId)
            ->findAll();

        $grades = [];

        foreach ($assignments as $assignment) {
            $grade = $this->gradeModel
                ->where('assignment_id', $assignment['id'])
                ->where('student_id', $studentId)
                ->first();

            $grades[] = [
                'assignment' => $assignment,
                'grade' => $grade,
            ];
        }

        return $grades;
    }

    /**
     * Calculate course average for student.
     */
    public function getCourseAverage(int $studentId, int $courseId): ?float
    {
        $grades = $this->getStudentGrades($studentId, $courseId);

        if (empty($grades)) {
            return null;
        }

        $totalPoints = 0;
        $totalMax = 0;
        $gradedCount = 0;

        foreach ($grades as $item) {
            if ($item['grade']) {
                $totalPoints += $item['grade']['points_earned'];
                $totalMax += $item['grade']['max_points'];
                $gradedCount++;
            }
        }

        if ($gradedCount === 0 || $totalMax === 0) {
            return null;
        }

        return ($totalPoints / $totalMax) * 100;
    }

    /**
     * Get assignment statistics.
     */
    public function getAssignmentStats(int $assignmentId): array
    {
        $grades = $this->gradeModel
            ->where('assignment_id', $assignmentId)
            ->findAll();

        if (empty($grades)) {
            return [
                'total_submissions' => 0,
                'average_score' => 0,
                'highest_score' => 0,
                'lowest_score' => 0,
            ];
        }

        $scores = array_column($grades, 'points_earned');

        return [
            'total_submissions' => count($grades),
            'average_score' => array_sum($scores) / count($scores),
            'highest_score' => max($scores),
            'lowest_score' => min($scores),
        ];
    }
}
