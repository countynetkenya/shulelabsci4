<?php

namespace Modules\Integrations\Models;

use CodeIgniter\Model;

/**
 * Model for offline sync queue.
 */
class OfflineQueueModel extends Model
{
    protected $table         = 'integration_offline_queue';
    protected $primaryKey    = 'id';
    protected $allowedFields = [
        'adapter_name',
        'operation',
        'payload',
        'context',
        'status',
        'retry_count',
        'max_retries',
        'last_error',
        'tenant_id',
        'user_id',
        'priority',
        'scheduled_at',
        'completed_at',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $returnType = 'array';

    /**
     * Queue an operation for offline sync.
     *
     * @param array<string, mixed> $data
     * @return int|false Insert ID or false on failure
     */
    public function queueOperation(array $data)
    {
        $defaults = [
            'status'      => 'queued',
            'retry_count' => 0,
            'max_retries' => 3,
            'priority'    => 5,
        ];

        return $this->insert(array_merge($defaults, $data));
    }

    /**
     * Get pending operations to process.
     *
     * @param int $limit
     * @return array<array<string, mixed>>
     */
    public function getPendingOperations(int $limit = 100): array
    {
        return $this->where('status', 'queued')
            ->where('(scheduled_at IS NULL OR scheduled_at <= NOW())')
            ->orderBy('priority', 'ASC')
            ->orderBy('created_at', 'ASC')
            ->findAll($limit);
    }

    /**
     * Mark an operation as completed.
     *
     * @param int $id
     * @return bool
     */
    public function markCompleted(int $id): bool
    {
        return $this->update($id, [
            'status'       => 'completed',
            'completed_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Mark an operation as failed.
     *
     * @param int $id
     * @param string $error
     * @return bool
     */
    public function markFailed(int $id, string $error): bool
    {
        $record = $this->find($id);

        if (! $record) {
            return false;
        }

        $retryCount = ((int) $record['retry_count']) + 1;
        $maxRetries = (int) $record['max_retries'];

        $status = $retryCount >= $maxRetries ? 'failed' : 'queued';

        return $this->update($id, [
            'status'      => $status,
            'retry_count' => $retryCount,
            'last_error'  => $error,
        ]);
    }
}
