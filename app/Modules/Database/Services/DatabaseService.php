<?php

namespace Modules\Database\Services;

use Modules\Database\Models\DatabaseBackupModel;

/**
 * DatabaseService - Business logic for database backup management
 */
class DatabaseService
{
    protected DatabaseBackupModel $model;

    public function __construct()
    {
        $this->model = new DatabaseBackupModel();
    }

    /**
     * Get all backups for a school
     */
    public function getAll(int $schoolId): array
    {
        return $this->model
            ->where('school_id', $schoolId)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    /**
     * Get a single backup by ID
     */
    public function getById(int $id, int $schoolId): ?array
    {
        return $this->model
            ->where('id', $id)
            ->where('school_id', $schoolId)
            ->first();
    }

    /**
     * Create a new backup record
     */
    public function create(array $data): int|false
    {
        // Generate backup_id if not provided
        if (empty($data['backup_id'])) {
            $data['backup_id'] = 'backup_' . date('YmdHis') . '_' . uniqid();
        }

        return $this->model->insert($data);
    }

    /**
     * Update an existing backup
     */
    public function update(int $id, array $data): bool
    {
        return $this->model->update($id, $data);
    }

    /**
     * Delete a backup
     */
    public function delete(int $id): bool
    {
        return $this->model->delete($id);
    }

    /**
     * Get backup statistics for a school
     */
    public function getStatistics(int $schoolId): array
    {
        $db = \Config\Database::connect();
        
        $totalBackups = $this->model
            ->where('school_id', $schoolId)
            ->countAllResults();
        
        $completedBackups = $this->model
            ->where('school_id', $schoolId)
            ->where('status', 'completed')
            ->countAllResults();
        
        $totalSize = $db->table('db_backups')
            ->selectSum('size', 'total_size')
            ->where('school_id', $schoolId)
            ->where('status', 'completed')
            ->get()
            ->getRow()
            ->total_size ?? 0;

        return [
            'total_backups' => $totalBackups,
            'completed_backups' => $completedBackups,
            'total_size' => $totalSize,
            'total_size_mb' => round($totalSize / (1024 * 1024), 2),
        ];
    }
}
