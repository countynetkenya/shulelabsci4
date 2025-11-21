<?php

namespace Modules\Foundation\Services;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\ConnectionInterface;
use CodeIgniter\I18n\Time;
use Config\Database;
use RuntimeException;

/**
 * Provides four-eyes approval workflows for sensitive operations.
 */
class MakerCheckerService
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
     * Queues a maker action awaiting checker approval.
     *
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $context
     */
    public function submit(string $actionKey, array $payload, array $context): int
    {
        $record = [
            'action_key'    => $actionKey,
            'status'        => 'pending',
            'payload_json'  => json_encode($payload, JSON_THROW_ON_ERROR),
            'maker_id'      => $context['actor_id'] ?? null,
            'tenant_id'     => $context['tenant_id'] ?? null,
            'submitted_at'  => Time::now('UTC')->toDateTimeString(),
        ];

        $this->db->table('ci4_maker_checker_requests')->insert($record);
        $requestId = (int) $this->db->insertID();

        $this->auditService->recordEvent(
            eventKey: sprintf('maker_checker:%s', $requestId),
            eventType: 'maker_request_submitted',
            context: $context,
            before: null,
            after: $record
        );

        return $requestId;
    }

    /**
     * Approves a pending maker request.
     *
     * @param array<string, mixed> $context
     */
    public function approve(int $requestId, array $context): void
    {
        $request = $this->fetchRequest($requestId);
        if ($request['status'] !== 'pending') {
            throw new RuntimeException('Maker checker request already processed.');
        }

        $update = [
            'status'       => 'approved',
            'checker_id'   => $context['actor_id'] ?? null,
            'processed_at' => Time::now('UTC')->toDateTimeString(),
        ];

        $this->db->table('ci4_maker_checker_requests')
            ->where('id', $requestId)
            ->set($update)
            ->update();

        $this->auditService->recordEvent(
            eventKey: sprintf('maker_checker:%s', $requestId),
            eventType: 'maker_request_approved',
            context: $context,
            before: $request,
            after: array_merge($request, $update)
        );
    }

    /**
     * Rejects a pending maker request with a reason.
     *
     * @param array<string, mixed> $context
     */
    public function reject(int $requestId, array $context, string $reason): void
    {
        $request = $this->fetchRequest($requestId);
        if ($request['status'] !== 'pending') {
            throw new RuntimeException('Maker checker request already processed.');
        }

        $update = [
            'status'       => 'rejected',
            'checker_id'   => $context['actor_id'] ?? null,
            'processed_at' => Time::now('UTC')->toDateTimeString(),
            'rejection_reason' => $reason,
        ];

        $this->db->table('ci4_maker_checker_requests')
            ->where('id', $requestId)
            ->set($update)
            ->update();

        $this->auditService->recordEvent(
            eventKey: sprintf('maker_checker:%s', $requestId),
            eventType: 'maker_request_rejected',
            context: $context,
            before: $request,
            after: array_merge($request, $update)
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function fetchRequest(int $requestId): array
    {
        $request = $this->db->table('ci4_maker_checker_requests')
            ->where('id', $requestId)
            ->get()
            ->getFirstRow('array');

        if (! $request) {
            throw new RuntimeException('Maker checker request not found.');
        }

        return $request;
    }
}
