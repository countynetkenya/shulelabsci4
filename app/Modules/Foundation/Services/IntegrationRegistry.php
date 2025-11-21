<?php

namespace Modules\Foundation\Services;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\ConnectionInterface;
use CodeIgniter\I18n\Time;
use Config\Database;
use RuntimeException;

/**
 * Tracks outbound integration calls with idempotency guarantees.
 */
class IntegrationRegistry
{
    /**
     * @phpstan-var BaseConnection<object, object>
     */
    private BaseConnection $db;
    private AuditService $auditService;

    /**
     * @phpstan-param ConnectionInterface<object, object>|null $connection
     */
    public function __construct(?ConnectionInterface $connection = null, ?AuditService $auditService = null)
    {
        $this->db           = $connection instanceof BaseConnection ? $connection : Database::connect();
        $this->auditService = $auditService ?? new AuditService($this->db);
    }

    /**
     * Creates or reuses an integration dispatch ensuring idempotency by unique key.
     *
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $context
     *
     * @return array<string, mixed>
     */
    public function registerDispatch(string $channel, string $idempotencyKey, array $payload, array $context): array
    {
        $existing = $this->db->table('ci4_integration_dispatches')
            ->where('idempotency_key', $idempotencyKey)
            ->where('channel', $channel)
            ->get()
            ->getFirstRow('array');

        if ($existing) {
            return $existing;
        }

        $record = [
            'channel'         => $channel,
            'idempotency_key' => $idempotencyKey,
            'tenant_id'       => $context['tenant_id'] ?? null,
            'payload_json'    => json_encode($payload, JSON_THROW_ON_ERROR),
            'status'          => 'queued',
            'queued_at'       => Time::now('UTC')->toDateTimeString(),
        ];

        $this->db->table('ci4_integration_dispatches')->insert($record);
        $dispatchId = (int) $this->db->insertID();

        $this->auditService->recordEvent(
            eventKey: sprintf('integration:%s:%s', $channel, $dispatchId),
            eventType: 'integration_dispatch_registered',
            context: $context,
            before: null,
            after: $record
        );

        return array_merge($record, ['id' => $dispatchId]);
    }

    /**
     * Marks a dispatch as completed and records the response payload.
     *
     * @param array<string, mixed> $context
     * @param array<string, mixed> $response
     */
    public function markCompleted(int $dispatchId, array $context, array $response): void
    {
        $builder = $this->db->table('ci4_integration_dispatches');
        $existing = $builder->where('id', $dispatchId)->get()->getFirstRow('array');
        if (! $existing) {
            throw new RuntimeException('Integration dispatch not found.');
        }

        $update = [
            'status'          => 'completed',
            'response_json'   => json_encode($response, JSON_THROW_ON_ERROR),
            'completed_at'    => Time::now('UTC')->toDateTimeString(),
            'error_message'   => null,
        ];

        $this->db->table('ci4_integration_dispatches')
            ->where('id', $dispatchId)
            ->set($update)
            ->update();

        $this->auditService->recordEvent(
            eventKey: sprintf('integration:%s:%s', $existing['channel'], $dispatchId),
            eventType: 'integration_dispatch_completed',
            context: $context,
            before: $existing,
            after: array_merge($existing, $update)
        );
    }

    /**
     * Marks a dispatch as failed and captures the error message.
     *
     * @param array<string, mixed> $context
     */
    public function markFailed(int $dispatchId, array $context, string $errorMessage, ?int $retryAfterSeconds = null): void
    {
        $builder = $this->db->table('ci4_integration_dispatches');
        $existing = $builder->where('id', $dispatchId)->get()->getFirstRow('array');
        if (! $existing) {
            throw new RuntimeException('Integration dispatch not found.');
        }

        $update = [
            'status'        => 'failed',
            'error_message' => $errorMessage,
            'failed_at'     => Time::now('UTC')->toDateTimeString(),
            'retry_after'   => $retryAfterSeconds,
        ];

        $this->db->table('ci4_integration_dispatches')
            ->where('id', $dispatchId)
            ->set($update)
            ->update();

        $this->auditService->recordEvent(
            eventKey: sprintf('integration:%s:%s', $existing['channel'], $dispatchId),
            eventType: 'integration_dispatch_failed',
            context: $context,
            before: $existing,
            after: array_merge($existing, $update)
        );
    }

    /**
     * Claims a batch of pending dispatches for asynchronous processing.
     *
     * @return list<array<string, mixed>>
     */
    public function claimPendingDispatches(string $channel, int $limit = 10): array
    {
        if ($limit <= 0) {
            return [];
        }

        $claimed = [];
        $queued = $this->db->table('ci4_integration_dispatches')
            ->where('channel', $channel)
            ->where('status', 'queued')
            ->orderBy('queued_at', 'ASC')
            ->limit($limit)
            ->get()
            ->getResultArray();

        foreach ($queued as $row) {
            $claimed[] = $this->lockDispatch((int) $row['id']);
            if (count($claimed) >= $limit) {
                return $this->filterLocked($claimed);
            }
        }

        $remaining = $limit - count($claimed);
        if ($remaining <= 0) {
            return $this->filterLocked($claimed);
        }

        $failedRows = $this->db->table('ci4_integration_dispatches')
            ->where('channel', $channel)
            ->where('status', 'failed')
            ->orderBy('failed_at', 'ASC')
            ->limit($remaining * 2)
            ->get()
            ->getResultArray();

        $now = Time::now('UTC');
        foreach ($failedRows as $row) {
            $retryAfter = isset($row['retry_after']) ? (int) $row['retry_after'] : null;
            $failedAtRaw = $row['failed_at'] ?? null;

            if ($retryAfter !== null && $failedAtRaw !== null) {
                $failedAt = Time::createFromFormat('Y-m-d H:i:s', (string) $failedAtRaw, 'UTC');
                if ($failedAt->addSeconds($retryAfter)->isAfter($now)) {
                    continue;
                }
            }

            $claimed[] = $this->lockDispatch((int) $row['id']);
            if (count($claimed) >= $limit) {
                break;
            }
        }

        return $this->filterLocked($claimed);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function lockDispatch(int $dispatchId): ?array
    {
        $this->db->table('ci4_integration_dispatches')
            ->where('id', $dispatchId)
            ->set(['status' => 'processing'])
            ->update();

        $fresh = $this->db->table('ci4_integration_dispatches')
            ->where('id', $dispatchId)
            ->get()
            ->getFirstRow('array');

        return $fresh && $fresh['status'] === 'processing' ? $fresh : null;
    }

    /**
     * @param array<int, array<string, mixed>|null> $claimed
     * @return list<array<string, mixed>>
     */
    private function filterLocked(array $claimed): array
    {
        $filtered = array_filter($claimed);

        return array_is_list($filtered) ? $filtered : array_values($filtered);
    }
}
