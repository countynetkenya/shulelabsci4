<?php

declare(strict_types=1);

namespace Modules\Learning\Services;

use JsonException;
use Modules\Foundation\Services\IntegrationRegistry;
use Throwable;

/**
 * Processes queued Moodle dispatches using the integration registry.
 */
class MoodleDispatchRunner
{
    public function __construct(
        private readonly IntegrationRegistry $registry,
        private readonly MoodleClientInterface $client,
        private readonly int $retryAfterSeconds = 900,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function runGrades(int $limit = 10): array
    {
        return $this->runChannel('moodle.push_grades', 'pushGrades', $limit);
    }

    /**
     * @return array<string, mixed>
     */
    public function runEnrollments(int $limit = 10): array
    {
        return $this->runChannel('moodle.sync_enrollments', 'syncEnrollments', $limit);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function runAll(int $limit = 10): array
    {
        return [
            'grades'      => $this->runGrades($limit),
            'enrollments' => $this->runEnrollments($limit),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function runChannel(string $channel, string $clientMethod, int $limit): array
    {
        $dispatches = $this->registry->claimPendingDispatches($channel, $limit);

        $summary = [
            'channel'    => $channel,
            'dispatched' => count($dispatches),
            'completed'  => 0,
            'failed'     => 0,
            'errors'     => [],
        ];

        foreach ($dispatches as $dispatch) {
            $context = ['tenant_id' => $dispatch['tenant_id'] ?? null];
            $payload = [];

            try {
                if (! empty($dispatch['payload_json'])) {
                    $payload = json_decode((string) $dispatch['payload_json'], true, 512, JSON_THROW_ON_ERROR);
                }
            } catch (JsonException $exception) {
                $this->registry->markFailed((int) $dispatch['id'], $context, 'Invalid payload JSON: ' . $exception->getMessage(), $this->retryAfterSeconds);
                $summary['failed']++;
                $summary['errors'][] = [
                    'dispatch_id' => (int) $dispatch['id'],
                    'message'     => $exception->getMessage(),
                ];
                continue;
            }

            try {
                $response = $this->client->{$clientMethod}($payload);
                if (! is_array($response)) {
                    $response = ['status' => (string) $response];
                }

                $this->registry->markCompleted((int) $dispatch['id'], $context, $response);
                $summary['completed']++;
            } catch (Throwable $exception) {
                $this->registry->markFailed((int) $dispatch['id'], $context, $exception->getMessage(), $this->retryAfterSeconds);
                $summary['failed']++;
                $summary['errors'][] = [
                    'dispatch_id' => (int) $dispatch['id'],
                    'message'     => $exception->getMessage(),
                ];
            }
        }

        return $summary;
    }
}
