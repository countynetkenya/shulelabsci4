<?php

namespace App\Modules\Mobile\Services;

use CodeIgniter\Database\ConnectionInterface;
use Config\Database;

/**
 * OfflineSyncService - Handles offline data synchronization for mobile apps.
 */
class OfflineSyncService
{
    private $db;

    public function __construct(?ConnectionInterface $connection = null)
    {
        $this->db = $connection ?? Database::connect();
    }

    /**
     * Get sync snapshot for a user.
     */
    public function getSnapshot(int $userId, int $deviceId, string $snapshotType): ?array
    {
        $snapshot = $this->db->table('sync_snapshots')
            ->where('user_id', $userId)
            ->where('device_id', $deviceId)
            ->where('snapshot_type', $snapshotType)
            ->where('(expires_at IS NULL OR expires_at > NOW())')
            ->orderBy('created_at', 'DESC')
            ->get()
            ->getRowArray();

        if (!$snapshot) {
            return null;
        }

        return [
            'data' => json_decode($snapshot['data'], true),
            'version' => $snapshot['version'],
            'hash' => $snapshot['data_hash'],
            'created_at' => $snapshot['created_at'],
        ];
    }

    /**
     * Save sync snapshot.
     */
    public function saveSnapshot(int $userId, int $deviceId, string $snapshotType, array $data, ?int $ttlSeconds = null): int
    {
        $schoolId = session('school_id');
        $jsonData = json_encode($data);
        $dataHash = hash('sha256', $jsonData);

        // Check if same hash already exists (no changes)
        $existing = $this->db->table('sync_snapshots')
            ->where('user_id', $userId)
            ->where('device_id', $deviceId)
            ->where('snapshot_type', $snapshotType)
            ->orderBy('created_at', 'DESC')
            ->get()
            ->getRowArray();

        if ($existing && $existing['data_hash'] === $dataHash) {
            return (int) $existing['id'];
        }

        $version = ($existing['version'] ?? 0) + 1;

        $this->db->table('sync_snapshots')->insert([
            'school_id' => $schoolId,
            'user_id' => $userId,
            'device_id' => $deviceId,
            'snapshot_type' => $snapshotType,
            'data_hash' => $dataHash,
            'data' => $jsonData,
            'version' => $version,
            'expires_at' => $ttlSeconds ? date('Y-m-d H:i:s', time() + $ttlSeconds) : null,
        ]);

        return (int) $this->db->insertID();
    }

    /**
     * Queue offline operation.
     */
    public function queueOperation(int $deviceId, int $userId, string $operation, string $entityType, ?int $entityId, array $payload, string $clientTimestamp): int
    {
        $this->db->table('offline_queue')->insert([
            'device_id' => $deviceId,
            'user_id' => $userId,
            'operation' => $operation,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'payload' => json_encode($payload),
            'client_timestamp' => $clientTimestamp,
            'status' => 'pending',
        ]);

        return (int) $this->db->insertID();
    }

    /**
     * Process pending offline operations.
     */
    public function processPending(int $deviceId, int $limit = 50): array
    {
        $pending = $this->db->table('offline_queue')
            ->where('device_id', $deviceId)
            ->where('status', 'pending')
            ->orderBy('client_timestamp', 'ASC')
            ->limit($limit)
            ->get()
            ->getResultArray();

        $results = [];

        foreach ($pending as $operation) {
            $result = $this->processOperation($operation);
            $results[] = [
                'id' => $operation['id'],
                'operation' => $operation['operation'],
                'entity_type' => $operation['entity_type'],
                'success' => $result['success'],
                'error' => $result['error'] ?? null,
            ];
        }

        return $results;
    }

    /**
     * Process a single offline operation.
     */
    private function processOperation(array $operation): array
    {
        try {
            $this->db->table('offline_queue')
                ->where('id', $operation['id'])
                ->update(['status' => 'processing']);

            $payload = json_decode($operation['payload'], true);

            // Route to appropriate handler based on entity type
            $result = match ($operation['entity_type']) {
                'attendance' => $this->processAttendanceOperation($operation, $payload),
                'message' => $this->processMessageOperation($operation, $payload),
                'grade' => $this->processGradeOperation($operation, $payload),
                default => ['success' => false, 'error' => 'Unknown entity type'],
            };

            $this->db->table('offline_queue')
                ->where('id', $operation['id'])
                ->update([
                    'status' => $result['success'] ? 'completed' : 'failed',
                    'error_message' => $result['error'] ?? null,
                    'processed_at' => date('Y-m-d H:i:s'),
                ]);

            return $result;
        } catch (\Exception $e) {
            $this->db->table('offline_queue')
                ->where('id', $operation['id'])
                ->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                    'processed_at' => date('Y-m-d H:i:s'),
                ]);

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Process attendance offline operation.
     */
    private function processAttendanceOperation(array $operation, array $payload): array
    {
        // Implementation would connect to AttendanceService
        log_message('info', "Processing attendance operation: {$operation['operation']}");
        return ['success' => true];
    }

    /**
     * Process message offline operation.
     */
    private function processMessageOperation(array $operation, array $payload): array
    {
        // Implementation would connect to MessagingService
        log_message('info', "Processing message operation: {$operation['operation']}");
        return ['success' => true];
    }

    /**
     * Process grade offline operation.
     */
    private function processGradeOperation(array $operation, array $payload): array
    {
        // Implementation would connect to GradebookService
        log_message('info', "Processing grade operation: {$operation['operation']}");
        return ['success' => true];
    }

    /**
     * Get sync status for device.
     */
    public function getSyncStatus(int $deviceId): array
    {
        $pending = $this->db->table('offline_queue')
            ->where('device_id', $deviceId)
            ->where('status', 'pending')
            ->countAllResults();

        $failed = $this->db->table('offline_queue')
            ->where('device_id', $deviceId)
            ->where('status', 'failed')
            ->countAllResults();

        $lastSync = $this->db->table('offline_queue')
            ->select('processed_at')
            ->where('device_id', $deviceId)
            ->where('status', 'completed')
            ->orderBy('processed_at', 'DESC')
            ->get()
            ->getRowArray();

        return [
            'pending_operations' => $pending,
            'failed_operations' => $failed,
            'last_sync_at' => $lastSync['processed_at'] ?? null,
        ];
    }

    /**
     * Clear expired snapshots.
     */
    public function cleanupExpiredSnapshots(): int
    {
        return $this->db->table('sync_snapshots')
            ->where('expires_at <', date('Y-m-d H:i:s'))
            ->delete();
    }
}
