<?php

declare(strict_types=1);

namespace Modules\LMS\Services;

/**
 * Default Moodle client that acknowledges calls without performing network I/O.
 */
class NullMoodleClient implements MoodleClientInterface
{
    public function pushGrades(array $payload): array
    {
        return [
            'status'  => 'noop',
            'message' => 'No Moodle client configured. Grades were not pushed.',
            'payload' => $payload,
        ];
    }

    public function syncEnrollments(array $payload): array
    {
        return [
            'status'  => 'noop',
            'message' => 'No Moodle client configured. Enrollments were not synchronised.',
            'payload' => $payload,
        ];
    }
}
