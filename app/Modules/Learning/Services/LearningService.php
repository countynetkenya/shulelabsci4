<?php

namespace Modules\Learning\Services;

use Modules\Learning\Models\CourseModel;
use Modules\Learning\Models\LessonModel;

class LearningService
{
    protected $courseModel;
    protected $lessonModel;

    public function __construct()
    {
        $this->courseModel = new CourseModel();
        $this->lessonModel = new LessonModel();
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
}
