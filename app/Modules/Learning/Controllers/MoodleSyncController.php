<?php

declare(strict_types=1);

namespace Modules\Learning\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;
use Config\Services;
use InvalidArgumentException;
use Modules\Foundation\Controllers\InteractsWithIncomingRequest;
use Modules\Learning\Services\MoodleSyncService;
use RuntimeException;

class MoodleSyncController extends ResourceController
{
    use InteractsWithIncomingRequest;

    protected $format = 'json';

    private MoodleSyncService $syncService;

    public function __construct(?MoodleSyncService $syncService = null)
    {
        $this->syncService = $syncService ?? Services::moodleSync();
    }

    public function pushGrades(): ResponseInterface
    {
        $payload = $this->incomingRequest()->getJSON(true) ?? [];

        try {
            $course = $this->extractCourse($payload);
            $grades = $this->extractList($payload, 'grades');
            $response = $this->syncService->pushGrades($course, $grades, $this->buildContext());
        } catch (InvalidArgumentException $exception) {
            return $this->failValidationErrors([$exception->getMessage()]);
        } catch (RuntimeException $exception) {
            return $this->failServerError($exception->getMessage());
        }

        return $this->respond(
            [
                'status'   => 'dispatched',
                'course'   => $course['id'] ?? null,
                'response' => $response,
            ],
            ResponseInterface::HTTP_ACCEPTED
        );
    }

    public function syncEnrollments(): ResponseInterface
    {
        $payload = $this->incomingRequest()->getJSON(true) ?? [];

        try {
            $course = $this->extractCourse($payload);
            $enrollments = $this->extractList($payload, 'enrollments');
            $response = $this->syncService->syncEnrollments($course, $enrollments, $this->buildContext());
        } catch (InvalidArgumentException $exception) {
            return $this->failValidationErrors([$exception->getMessage()]);
        } catch (RuntimeException $exception) {
            return $this->failServerError($exception->getMessage());
        }

        return $this->respond(
            [
                'status'   => 'dispatched',
                'course'   => $course['id'] ?? null,
                'response' => $response,
            ],
            ResponseInterface::HTTP_ACCEPTED
        );
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     */
    private function extractCourse(array $payload): array
    {
        $course = $payload['course'] ?? [];
        if (!is_array($course)) {
            throw new InvalidArgumentException('Course payload must be an object.');
        }

        return $course;
    }

    /**
     * @param array<string, mixed> $payload
     * @return list<array<string, mixed>>
     */
    private function extractList(array $payload, string $key): array
    {
        $entries = $payload[$key] ?? [];
        if (!is_array($entries)) {
            throw new InvalidArgumentException(sprintf('%s payload must be an array.', ucfirst($key)));
        }

        return $entries;
    }

    /**
     * @return array<string, mixed>
     */
    private function buildContext(): array
    {
        $request = $this->incomingRequest();

        return [
            'tenant_id'      => $request->getHeaderLine('X-Tenant-ID') ?: null,
            'actor_id'       => $request->getHeaderLine('X-Actor-ID') ?: null,
            'request_origin' => $request->getIPAddress(),
            'trace_id'       => $request->getHeaderLine('X-Request-ID') ?: null,
        ];
    }
}
