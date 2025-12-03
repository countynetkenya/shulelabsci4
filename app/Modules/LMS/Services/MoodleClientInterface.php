<?php

declare(strict_types=1);

namespace Modules\LMS\Services;

/**
 * Represents the outbound connector used to communicate with Moodle.
 */
interface MoodleClientInterface
{
    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function pushGrades(array $payload): array;

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function syncEnrollments(array $payload): array;
}
