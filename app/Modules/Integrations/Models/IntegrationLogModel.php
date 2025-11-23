<?php

namespace Modules\Integrations\Models;

use CodeIgniter\Model;

/**
 * Model for integration operation logs.
 */
class IntegrationLogModel extends Model
{
    protected $table         = 'integration_logs';
    protected $primaryKey    = 'id';
    protected $allowedFields = [
        'integration_id',
        'adapter_name',
        'operation',
        'request_payload',
        'response_payload',
        'http_status',
        'error_message',
        'idempotency_key',
        'duration_ms',
        'tenant_id',
        'user_id',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = null; // Logs are immutable

    protected $returnType = 'array';

    /**
     * Log an integration operation.
     *
     * @param array<string, mixed> $data
     * @return int|false Insert ID or false on failure
     */
    public function logOperation(array $data)
    {
        return $this->insert($data);
    }

    /**
     * Get logs for a specific integration.
     *
     * @param int $integrationId
     * @param int $limit
     * @return array<array<string, mixed>>
     */
    public function getLogsByIntegration(int $integrationId, int $limit = 100): array
    {
        return $this->where('integration_id', $integrationId)
            ->orderBy('created_at', 'DESC')
            ->findAll($limit);
    }

    /**
     * Get failed operations for retry.
     *
     * @param string|null $adapterName
     * @param int $limit
     * @return array<array<string, mixed>>
     */
    public function getFailedOperations(?string $adapterName = null, int $limit = 50): array
    {
        $builder = $this->where('error_message IS NOT NULL');

        if ($adapterName !== null) {
            $builder->where('adapter_name', $adapterName);
        }

        return $builder->orderBy('created_at', 'DESC')
            ->findAll($limit);
    }
}
