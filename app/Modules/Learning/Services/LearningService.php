<?php

namespace Modules\Learning\Services;

use Modules\Learning\Models\CourseModel;
use Modules\Learning\Models\EnrollmentModel;
use Modules\Learning\Models\LessonModel;
use Modules\Learning\Models\ProgressModel;

class LearningService
{
    protected $courseModel;

    protected $lessonModel;

    protected $enrollmentModel;

    protected $progressModel;

    public function __construct()
    {
        $this->courseModel = new CourseModel();
        $this->lessonModel = new LessonModel();
        $this->enrollmentModel = new EnrollmentModel();
        $this->progressModel = new ProgressModel();
    }

    public function getSchoolCourses(int $schoolId, ?string $status = null)
    {
        $builder = $this->courseModel->where('school_id', $schoolId);

        if ($status) {
            $builder->where('status', $status);
        }

        return $builder->findAll();
    }

    public function createCourse(array $data)
    {
        return $this->courseModel->insert($data);
    }

    public function getCourse(int $id)
    {
        return $this->courseModel->find($id);
    }

    public function getCourseLessons(int $courseId)
    {
        return $this->lessonModel->where('course_id', $courseId)
                                 ->orderBy('sequence_order', 'ASC')
                                 ->findAll();
    }

    public function addLesson(array $data)
    {
        return $this->lessonModel->insert($data);
    }

    public function enrollStudent(int $schoolId, int $studentId, int $courseId)
    {
        // Check if already enrolled
        $existing = $this->enrollmentModel->where('student_id', $studentId)
                                          ->where('course_id', $courseId)
                                          ->first();
        if ($existing) {
            return $existing['id'];
        }

        return $this->enrollmentModel->insert([
            'school_id' => $schoolId,
            'student_id' => $studentId,
            'course_id' => $courseId,
            'status' => 'active',
            'enrolled_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function getStudentEnrollments(int $studentId)
    {
        return $this->enrollmentModel->select('learning_enrollments.*, learning_courses.title as course_title, learning_courses.description as course_description')
                                     ->join('learning_courses', 'learning_courses.id = learning_enrollments.course_id')
                                     ->where('learning_enrollments.student_id', $studentId)
                                     ->findAll();
    }

    public function getCourseEnrollments(int $courseId)
    {
        return $this->enrollmentModel->where('course_id', $courseId)->findAll();
    }

    public function markLessonComplete(int $enrollmentId, int $lessonId)
    {
        $existing = $this->progressModel->where('enrollment_id', $enrollmentId)
                                        ->where('lesson_id', $lessonId)
                                        ->first();
        if ($existing) {
            return true;
        }

        return $this->progressModel->insert([
            'enrollment_id' => $enrollmentId,
            'lesson_id' => $lessonId,
            'completed_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function getCompletedLessons(int $enrollmentId)
    {
        return $this->progressModel->where('enrollment_id', $enrollmentId)->findAll();
    }

    public function updateCourse(int $id, array $data): bool
    {
        return $this->courseModel->update($id, $data);
    }

    public function deleteCourse(int $id): bool
    {
        return $this->courseModel->delete($id);
    }
}
