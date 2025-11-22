<?php

namespace Modules\Integrations\Services\Interfaces;

/**
 * Interface for Learning Management System integrations.
 */
interface LmsInterface extends IntegrationAdapterInterface
{
    /**
     * Sync user enrollment to LMS.
     *
     * @param array{user_id: string, course_id: string, role?: string} $payload
     * @param array<string, mixed> $context
     * @return array{enrollment_id: string, status: string}
     */
    public function enrollUser(array $payload, array $context): array;

    /**
     * Sync grades from LMS.
     *
     * @param array{course_id: string, user_id?: string} $payload
     * @param array<string, mixed> $context
     * @return array{grades: array<array{user_id: string, grade: float, course_id: string}>}
     */
    public function syncGrades(array $payload, array $context): array;

    /**
     * Sync course content from LMS.
     *
     * @param array{course_id?: string} $payload
     * @param array<string, mixed> $context
     * @return array{courses: array<array{id: string, name: string, content?: array<string, mixed>}>}
     */
    public function syncCourses(array $payload, array $context): array;

    /**
     * Create or update a course in LMS.
     *
     * @param array{name: string, description?: string, category?: string} $payload
     * @param array<string, mixed> $context
     * @return array{course_id: string, status: string}
     */
    public function createCourse(array $payload, array $context): array;
}
