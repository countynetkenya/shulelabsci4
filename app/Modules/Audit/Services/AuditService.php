<?php

namespace App\Modules\Audit\Services;

use App\Modules\Audit\Models\AuditEventModel;
use CodeIgniter\HTTP\RequestInterface;

/**
 * Enhanced AuditService - Comprehensive audit logging with GDPR compliance.
 */
class AuditService
{
    private AuditEventModel $eventModel;

    private ?RequestInterface $request;

    private ?string $traceId = null;

    public function __construct(?AuditEventModel $eventModel = null, ?RequestInterface $request = null)
    {
        $this->eventModel = $eventModel ?? new AuditEventModel();
        $this->request = $request ?? service('request');
        $this->traceId = $this->generateTraceId();
    }

    /**
     * Log an audit event.
     */
    public function log(
        string $eventType,
        ?string $entityType = null,
        ?int $entityId = null,
        ?array $before = null,
        ?array $after = null,
        array $metadata = []
    ): int {
        $changedFields = $this->computeChangedFields($before, $after);

        // Get previous hash for chain integrity
        $previousHash = $this->getLatestHash();

        $data = [
            'school_id' => session('school_id'),
            'user_id' => session('user_id'),
            'event_key' => $this->generateEventKey($eventType, $entityType, $entityId),
            'event_type' => $eventType,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'before_state' => $before ? json_encode($before) : null,
            'after_state' => $after ? json_encode($after) : null,
            'changed_fields' => $changedFields ? json_encode($changedFields) : null,
            'ip_address' => $this->request?->getIPAddress(),
            'user_agent' => $this->request?->getUserAgent()?->getAgentString(),
            'request_uri' => $this->request?->getPath(),
            'trace_id' => $this->traceId,
            'previous_hash' => $previousHash,
            'metadata_json' => !empty($metadata) ? json_encode($metadata) : null,
        ];

        // Compute hash for integrity chain
        $data['hash_value'] = $this->computeHash($data);

        $this->eventModel->insert($data);
        return (int) $this->eventModel->getInsertID();
    }

    /**
     * Log a create event.
     */
    public function logCreate(string $entityType, int $entityId, array $data, array $metadata = []): int
    {
        return $this->log('create', $entityType, $entityId, null, $data, $metadata);
    }

    /**
     * Log an update event.
     */
    public function logUpdate(string $entityType, int $entityId, array $before, array $after, array $metadata = []): int
    {
        return $this->log('update', $entityType, $entityId, $before, $after, $metadata);
    }

    /**
     * Log a delete event.
     */
    public function logDelete(string $entityType, int $entityId, array $data, array $metadata = []): int
    {
        return $this->log('delete', $entityType, $entityId, $data, null, $metadata);
    }

    /**
     * Log a view/access event.
     */
    public function logAccess(string $entityType, int $entityId, array $metadata = []): int
    {
        return $this->log('access', $entityType, $entityId, null, null, $metadata);
    }

    /**
     * Log a login event.
     */
    public function logLogin(int $userId, bool $success, ?string $reason = null): int
    {
        return $this->log(
            $success ? 'login_success' : 'login_failed',
            'user',
            $userId,
            null,
            null,
            ['success' => $success, 'reason' => $reason]
        );
    }

    /**
     * Get audit trail for an entity.
     */
    public function getEntityHistory(string $entityType, int $entityId, ?int $schoolId = null): array
    {
        return $this->eventModel->getEntityHistory($entityType, $entityId, $schoolId);
    }

    /**
     * Search audit logs.
     */
    public function search(array $filters = [], int $limit = 100, int $offset = 0): array
    {
        return $this->eventModel->search($filters, $limit, $offset);
    }

    /**
     * Export audit logs for GDPR compliance.
     */
    public function exportUserData(int $userId): array
    {
        return $this->eventModel->where('user_id', $userId)
            ->orderBy('created_at', 'ASC')
            ->findAll();
    }

    /**
     * Anonymize user data for GDPR right to erasure.
     */
    public function anonymizeUser(int $userId): int
    {
        return $this->eventModel->where('user_id', $userId)
            ->set('user_id', null)
            ->set('ip_address', 'ANONYMIZED')
            ->set('user_agent', 'ANONYMIZED')
            ->update();
    }

    /**
     * Verify integrity of audit chain.
     */
    public function verifyIntegrity(?int $schoolId = null, ?string $dateFrom = null, ?string $dateTo = null): array
    {
        $builder = $this->eventModel;

        if ($schoolId !== null) {
            $builder = $builder->where('school_id', $schoolId);
        }
        if ($dateFrom !== null) {
            $builder = $builder->where('created_at >=', $dateFrom);
        }
        if ($dateTo !== null) {
            $builder = $builder->where('created_at <=', $dateTo);
        }

        $events = $builder->orderBy('id', 'ASC')->findAll();

        $verified = 0;
        $failed = 0;
        $errors = [];

        $previousHash = null;
        foreach ($events as $event) {
            // Verify hash chain
            if ($previousHash !== null && $event['previous_hash'] !== $previousHash) {
                $failed++;
                $errors[] = [
                    'event_id' => $event['id'],
                    'error' => 'Chain broken: previous_hash mismatch',
                ];
                continue;
            }

            // Verify event hash
            $computedHash = $this->computeHash($event);
            if ($event['hash_value'] !== $computedHash) {
                $failed++;
                $errors[] = [
                    'event_id' => $event['id'],
                    'error' => 'Hash mismatch: event may have been tampered',
                ];
                continue;
            }

            $verified++;
            $previousHash = $event['hash_value'];
        }

        return [
            'total_events' => count($events),
            'verified' => $verified,
            'failed' => $failed,
            'integrity_intact' => $failed === 0,
            'errors' => $errors,
        ];
    }

    /**
     * Set trace ID for request correlation.
     */
    public function setTraceId(string $traceId): void
    {
        $this->traceId = $traceId;
    }

    /**
     * Get current trace ID.
     */
    public function getTraceId(): string
    {
        return $this->traceId;
    }

    /**
     * Compute changed fields between before and after state.
     */
    private function computeChangedFields(?array $before, ?array $after): ?array
    {
        if ($before === null || $after === null) {
            return null;
        }

        $changed = [];
        $allKeys = array_unique(array_merge(array_keys($before), array_keys($after)));

        foreach ($allKeys as $key) {
            $beforeVal = $before[$key] ?? null;
            $afterVal = $after[$key] ?? null;

            if ($beforeVal !== $afterVal) {
                $changed[$key] = [
                    'before' => $beforeVal,
                    'after' => $afterVal,
                ];
            }
        }

        return empty($changed) ? null : $changed;
    }

    /**
     * Generate a unique event key.
     */
    private function generateEventKey(string $eventType, ?string $entityType, ?int $entityId): string
    {
        $parts = [$eventType];
        if ($entityType) {
            $parts[] = $entityType;
        }
        if ($entityId) {
            $parts[] = $entityId;
        }
        $parts[] = time();
        return implode(':', $parts);
    }

    /**
     * Generate a trace ID for request correlation.
     */
    private function generateTraceId(): string
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
    }

    /**
     * Get the latest hash for chain integrity.
     */
    private function getLatestHash(): ?string
    {
        $latest = $this->eventModel->orderBy('id', 'DESC')->first();
        return $latest['hash_value'] ?? null;
    }

    /**
     * Compute hash for an event.
     */
    private function computeHash(array $data): string
    {
        $hashData = [
            'school_id' => $data['school_id'] ?? null,
            'user_id' => $data['user_id'] ?? null,
            'event_key' => $data['event_key'] ?? null,
            'event_type' => $data['event_type'] ?? null,
            'entity_type' => $data['entity_type'] ?? null,
            'entity_id' => $data['entity_id'] ?? null,
            'before_state' => $data['before_state'] ?? null,
            'after_state' => $data['after_state'] ?? null,
            'previous_hash' => $data['previous_hash'] ?? null,
        ];

        ksort($hashData);
        return hash('sha256', json_encode($hashData));
    }
}
