<?php

namespace App\Modules\Audit\Models;

use CodeIgniter\Model;

/**
 * AuditEventModel - Stores audit trail entries.
 */
class AuditEventModel extends Model
{
    protected $table = 'audit_events';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;

    protected $allowedFields = [
        'school_id', 'user_id', 'event_key', 'event_type', 'entity_type', 'entity_id',
        'before_state', 'after_state', 'changed_fields', 'ip_address', 'user_agent',
        'request_uri', 'trace_id', 'previous_hash', 'hash_value', 'metadata_json',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = '';

    protected $casts = [
        'id' => 'int',
        'school_id' => '?int',
        'user_id' => '?int',
        'entity_id' => '?int',
        'before_state' => 'json-array',
        'after_state' => 'json-array',
        'changed_fields' => 'json-array',
        'metadata_json' => 'json-array',
    ];

    /**
     * Search audit events with filters.
     */
    public function search(array $filters = [], int $limit = 100, int $offset = 0): array
    {
        $builder = $this;

        if (!empty($filters['school_id'])) {
            $builder = $builder->where('school_id', $filters['school_id']);
        }
        if (!empty($filters['user_id'])) {
            $builder = $builder->where('user_id', $filters['user_id']);
        }
        if (!empty($filters['event_type'])) {
            $builder = $builder->where('event_type', $filters['event_type']);
        }
        if (!empty($filters['entity_type'])) {
            $builder = $builder->where('entity_type', $filters['entity_type']);
        }
        if (!empty($filters['entity_id'])) {
            $builder = $builder->where('entity_id', $filters['entity_id']);
        }
        if (!empty($filters['date_from'])) {
            $builder = $builder->where('created_at >=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $builder = $builder->where('created_at <=', $filters['date_to']);
        }
        if (!empty($filters['trace_id'])) {
            $builder = $builder->where('trace_id', $filters['trace_id']);
        }

        return $builder->orderBy('created_at', 'DESC')
            ->limit($limit, $offset)
            ->findAll();
    }

    /**
     * Get entity history.
     */
    public function getEntityHistory(string $entityType, int $entityId, ?int $schoolId = null): array
    {
        $builder = $this->where('entity_type', $entityType)
            ->where('entity_id', $entityId);

        if ($schoolId !== null) {
            $builder = $builder->where('school_id', $schoolId);
        }

        return $builder->orderBy('created_at', 'DESC')->findAll();
    }

    /**
     * Get events by trace ID.
     */
    public function getByTraceId(string $traceId): array
    {
        return $this->where('trace_id', $traceId)
            ->orderBy('created_at', 'ASC')
            ->findAll();
    }
}
