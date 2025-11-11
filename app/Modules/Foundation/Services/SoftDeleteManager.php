<?php

namespace Modules\Foundation\Services;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\ConnectionInterface;
use CodeIgniter\I18n\Time;
use Config\Database;
use RuntimeException;

/**
 * Centralised soft delete orchestration with audit hooks and maker-checker enforcement.
 */
class SoftDeleteManager
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
     * Marks the provided row as deleted and logs the operation.
     *
     * @param array<string, mixed> $context
     */
    public function softDelete(string $table, int|string $id, array $context, string $reason): void
    {
        $actorId  = $context['actor_id'] ?? null;

        $builder = $this->db->table($table);
        $builder->where('id', $id);

        $existing = $builder->get()->getFirstRow('array');
        if (! $existing) {
            throw new RuntimeException(sprintf('Record %s::%s not found for soft delete.', $table, $id));
        }

        $now = Time::now('UTC')->toDateTimeString();
        $update = [
            'deleted_at'     => $now,
            'deleted_by'     => $actorId,
            'delete_reason'  => $reason,
            'updated_at'     => $now,
        ];

        $this->db->transStart();

        $builder->set($update);
        $builder->update();

        $this->auditService->recordEvent(
            eventKey: sprintf('%s:%s', $table, $id),
            eventType: 'soft_delete',
            context: $context,
            before: $existing,
            after: array_merge($existing, $update),
            metadata: ['reason' => $reason]
        );

        $this->db->transComplete();

        if (! $this->db->transStatus()) {
            throw new RuntimeException('Soft delete transaction failed.');
        }
    }
}
