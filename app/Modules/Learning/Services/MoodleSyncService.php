<?php

declare(strict_types=1);

namespace Modules\Learning\Services;

use InvalidArgumentException;
use Modules\Foundation\Services\AuditService;
use Modules\Foundation\Services\IntegrationRegistry;
use RuntimeException;
use Throwable;

/**
 * Coordinates Moodle synchronisation using the integration registry for idempotency.
 */
class MoodleSyncService
{
    public function __construct(
        private readonly MoodleClientInterface $client,
        private readonly IntegrationRegistry $registry,
        private readonly AuditService $auditService,
    ) {
    }

    /**
     * @param array<string, mixed> $course
     * @param list<array<string, mixed>> $grades
     * @param array<string, mixed> $context
     *
     * @return array<string, mixed>
     */
    public function pushGrades(array $course, array $grades, array $context): array
    {
        if ($grades === []) {
            throw new InvalidArgumentException('At least one grade entry is required.');
        }

        $payload = $this->buildCoursePayload($course);
        $payload['grades'] = $this->normaliseGrades($grades);

        $idempotencyPayload = $payload;
        $payload['dispatched_at'] = gmdate(DATE_ATOM);

        $idempotencyKey = $this->buildIdempotencyKey('grades', $idempotencyPayload);
        $dispatch       = $this->registry->registerDispatch('moodle.push_grades', $idempotencyKey, $payload, $context);

        try {
            $response = $this->client->pushGrades($payload);
            $this->registry->markCompleted($dispatch['id'], $context, $response);

            $this->auditService->recordEvent(
                eventKey: sprintf('learning.moodle.course.%s', $payload['course']['id']),
                eventType: 'grades_pushed',
                context: $context,
                before: null,
                after: [
                    'course'   => $payload['course'],
                    'grades'   => $payload['grades'],
                    'response' => $response,
                ],
                metadata: [
                    'dispatch_id' => $dispatch['id'],
                    'tenant_id'   => $context['tenant_id'] ?? null,
                ],
            );

            return $response;
        } catch (Throwable $exception) {
            $this->registry->markFailed($dispatch['id'], $context, $exception->getMessage());

            throw new RuntimeException('Failed to push grades to Moodle.', 0, $exception);
        }
    }

    /**
     * @param array<string, mixed> $course
     * @param list<array<string, mixed>> $enrollments
     * @param array<string, mixed> $context
     *
     * @return array<string, mixed>
     */
    public function syncEnrollments(array $course, array $enrollments, array $context): array
    {
        if ($enrollments === []) {
            throw new InvalidArgumentException('At least one enrolment entry is required.');
        }

        $payload = $this->buildCoursePayload($course);
        $payload['enrollments'] = $this->normaliseEnrollments($enrollments);

        $idempotencyPayload = $payload;
        $payload['dispatched_at'] = gmdate(DATE_ATOM);

        $idempotencyKey = $this->buildIdempotencyKey('enrollments', $idempotencyPayload);
        $dispatch       = $this->registry->registerDispatch('moodle.sync_enrollments', $idempotencyKey, $payload, $context);

        try {
            $response = $this->client->syncEnrollments($payload);
            $this->registry->markCompleted($dispatch['id'], $context, $response);

            $this->auditService->recordEvent(
                eventKey: sprintf('learning.moodle.course.%s', $payload['course']['id']),
                eventType: 'enrollments_synced',
                context: $context,
                before: null,
                after: [
                    'course'      => $payload['course'],
                    'enrollments' => $payload['enrollments'],
                    'response'    => $response,
                ],
                metadata: [
                    'dispatch_id' => $dispatch['id'],
                    'tenant_id'   => $context['tenant_id'] ?? null,
                ],
            );

            return $response;
        } catch (Throwable $exception) {
            $this->registry->markFailed($dispatch['id'], $context, $exception->getMessage());

            throw new RuntimeException('Failed to sync enrollments to Moodle.', 0, $exception);
        }
    }

    /**
     * @param array<string, mixed> $course
     * @return array{course: array{id: string, name: string}}
     */
    private function buildCoursePayload(array $course): array
    {
        $courseId = trim((string) ($course['id'] ?? ''));
        $name     = trim((string) ($course['name'] ?? ''));

        if ($courseId === '') {
            throw new InvalidArgumentException('Course ID is required.');
        }

        if ($name === '') {
            throw new InvalidArgumentException('Course name is required.');
        }

        return [
            'course' => [
                'id'   => $courseId,
                'name' => $name,
            ],
        ];
    }

    /**
     * @param list<array<string, mixed>> $grades
     * @return list<array{user_id: string, grade: float, graded_at?: string}>
     */
    private function normaliseGrades(array $grades): array
    {
        $normalised = [];
        foreach ($grades as $grade) {
            $userId = trim((string) ($grade['user_id'] ?? ''));
            if ($userId === '') {
                throw new InvalidArgumentException('Grade entries require a user_id.');
            }

            if (! array_key_exists('grade', $grade) || ! is_numeric($grade['grade'])) {
                throw new InvalidArgumentException('Grade entries require a numeric grade.');
            }

            $value = (float) $grade['grade'];
            $entry = [
                'user_id'  => $userId,
                'grade'    => $value,
            ];

            if (isset($grade['graded_at'])) {
                $entry['graded_at'] = (string) $grade['graded_at'];
            }

            $normalised[] = $entry;
        }

        return $normalised;
    }

    /**
     * @param list<array<string, mixed>> $enrollments
     * @return list<array{user_id: string, role: string}>
     */
    private function normaliseEnrollments(array $enrollments): array
    {
        $normalised = [];
        foreach ($enrollments as $enrollment) {
            $userId = trim((string) ($enrollment['user_id'] ?? ''));
            $role   = trim((string) ($enrollment['role'] ?? ''));

            if ($userId === '') {
                throw new InvalidArgumentException('Enrollment entries require a user_id.');
            }

            if ($role === '') {
                throw new InvalidArgumentException('Enrollment entries require a role.');
            }

            $normalised[] = [
                'user_id' => $userId,
                'role'    => $role,
            ];
        }

        return $normalised;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function buildIdempotencyKey(string $operation, array $payload): string
    {
        $hashSource = json_encode([$operation, $payload], JSON_THROW_ON_ERROR);

        return hash('sha256', $hashSource);
    }
}
