<?php

namespace Modules\LMS\Services;

use Modules\LMS\Models\CourseModel;

/**
 * LMSCourseService - Business logic for LMS course management.
 */
class LMSCourseService
{
    protected CourseModel $model;

    public function __construct()
    {
        $this->model = new CourseModel();
    }

    /**
     * Get all courses for a school.
     */
    public function getAll(int $schoolId): array
    {
        return $this->model
            ->where('school_id', $schoolId)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    /**
     * Get a single course by ID.
     */
    public function getById(int $id, int $schoolId): ?array
    {
        return $this->model
            ->where('id', $id)
            ->where('school_id', $schoolId)
            ->first();
    }

    /**
     * Create a new course.
     */
    public function create(array $data): int|false
    {
        return $this->model->insert($data);
    }

    /**
     * Update an existing course.
     */
    public function update(int $id, array $data): bool
    {
        return $this->model->update($id, $data);
    }

    /**
     * Delete a course (soft delete).
     */
    public function delete(int $id): bool
    {
        return $this->model->delete($id);
    }

    /**
     * Get course statistics for a school.
     */
    public function getStatistics(int $schoolId): array
    {
        $totalCourses = $this->model
            ->where('school_id', $schoolId)
            ->countAllResults();

        $publishedCourses = $this->model
            ->where('school_id', $schoolId)
            ->where('status', 'published')
            ->countAllResults();

        $draftCourses = $this->model
            ->where('school_id', $schoolId)
            ->where('status', 'draft')
            ->countAllResults();

        return [
            'total_courses' => $totalCourses,
            'published_courses' => $publishedCourses,
            'draft_courses' => $draftCourses,
        ];
    }
}
