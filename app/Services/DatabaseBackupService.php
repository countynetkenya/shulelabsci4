<?php

namespace App\Services;

/**
 * Database Backup Service.
 *
 * Creates and manages database backups for safe schema migrations
 */
class DatabaseBackupService
{
    protected string $backupDir;

    /** @var array<string, mixed> */
    protected array $dbConfig;

    public function __construct(?string $backupDir = null)
    {
        $this->backupDir = $backupDir ?? WRITEPATH . 'backups/';
        $this->dbConfig = config(\Config\Database::class)->default;

        // Ensure backup directory exists
        if (!is_dir($this->backupDir)) {
            mkdir($this->backupDir, 0755, true);
        }
    }

    /**
     * Create a database backup.
     *
     * @param string|null $description Optional description for the backup
     * @return array<string, mixed> Backup info with file path
     */
    public function createBackup(?string $description = null): array
    {
        $timestamp = date('Y-m-d_H-i-s');
        $filename = "backup_{$timestamp}.sql";
        $filepath = $this->backupDir . $filename;

        // Build mysqldump command
        $command = $this->buildMysqldumpCommand($filepath);

        // Execute backup
        $output = [];
        $returnVar = 0;
        exec($command . ' 2>&1', $output, $returnVar);

        if ($returnVar !== 0) {
            throw new \RuntimeException('Backup failed: ' . implode("\n", $output));
        }

        // Verify backup file was created
        if (!file_exists($filepath) || filesize($filepath) === 0) {
            throw new \RuntimeException('Backup file was not created or is empty');
        }

        // Create metadata file
        $metadataFile = $filepath . '.meta';
        $metadata = [
            'created_at' => date('Y-m-d H:i:s'),
            'description' => $description,
            'database' => $this->dbConfig['database'],
            'filesize' => filesize($filepath),
            'filesize_human' => $this->formatBytes(filesize($filepath)),
        ];

        file_put_contents($metadataFile, json_encode($metadata, JSON_PRETTY_PRINT));

        log_message('info', 'Database backup created: ' . $filepath);

        return [
            'file' => $filepath,
            'filename' => $filename,
            'metadata' => $metadata,
        ];
    }

    /**
     * Build mysqldump command.
     *
     * @param string $filepath
     * @return string
     */
    protected function buildMysqldumpCommand(string $filepath): string
    {
        $host = $this->dbConfig['hostname'] ?? 'localhost';
        $port = $this->dbConfig['port'] ?? 3306;
        $database = $this->dbConfig['database'];
        $username = $this->dbConfig['username'];
        $password = $this->dbConfig['password'] ?? '';

        $command = sprintf(
            'mysqldump -h %s -P %d -u %s',
            escapeshellarg($host),
            $port,
            escapeshellarg($username)
        );

        if (!empty($password)) {
            $command .= sprintf(' -p%s', escapeshellarg($password));
        }

        // Add options
        $command .= ' --single-transaction --quick --lock-tables=false';

        // Add database and output file
        $command .= sprintf(' %s > %s', escapeshellarg($database), escapeshellarg($filepath));

        return $command;
    }

    /**
     * List available backups.
     *
     * @return array<int, array<string, mixed>>
     */
    public function listBackups(): array
    {
        $backups = [];
        $files = glob($this->backupDir . 'backup_*.sql');

        foreach ($files as $file) {
            $metadataFile = $file . '.meta';
            $metadata = [];

            if (file_exists($metadataFile)) {
                $metadata = json_decode(file_get_contents($metadataFile), true) ?: [];
            }

            $backups[] = [
                'file' => $file,
                'filename' => basename($file),
                'size' => filesize($file),
                'size_human' => $this->formatBytes(filesize($file)),
                'created' => filectime($file),
                'created_human' => date('Y-m-d H:i:s', filectime($file)),
                'metadata' => $metadata,
            ];
        }

        // Sort by created time, newest first
        usort($backups, function ($a, $b) {
            return $b['created'] - $a['created'];
        });

        return $backups;
    }

    /**
     * Restore from a backup.
     *
     * @param string $backupFile
     * @return bool
     */
    public function restore(string $backupFile): bool
    {
        if (!file_exists($backupFile)) {
            throw new \RuntimeException('Backup file not found: ' . $backupFile);
        }

        $host = $this->dbConfig['hostname'] ?? 'localhost';
        $port = $this->dbConfig['port'] ?? 3306;
        $database = $this->dbConfig['database'];
        $username = $this->dbConfig['username'];
        $password = $this->dbConfig['password'] ?? '';

        $command = sprintf(
            'mysql -h %s -P %d -u %s',
            escapeshellarg($host),
            $port,
            escapeshellarg($username)
        );

        if (!empty($password)) {
            $command .= sprintf(' -p%s', escapeshellarg($password));
        }

        $command .= sprintf(' %s < %s', escapeshellarg($database), escapeshellarg($backupFile));

        $output = [];
        $returnVar = 0;
        exec($command . ' 2>&1', $output, $returnVar);

        if ($returnVar !== 0) {
            throw new \RuntimeException('Restore failed: ' . implode("\n", $output));
        }

        log_message('info', 'Database restored from: ' . $backupFile);

        return true;
    }

    /**
     * Delete old backups, keeping only the specified number.
     *
     * @param int $keepCount Number of backups to keep
     * @return int Number of backups deleted
     */
    public function cleanup(int $keepCount = 10): int
    {
        $backups = $this->listBackups();
        $deleted = 0;

        if (count($backups) <= $keepCount) {
            return 0;
        }

        // Keep the newest $keepCount backups, delete the rest
        $toDelete = array_slice($backups, $keepCount);

        foreach ($toDelete as $backup) {
            if (unlink($backup['file'])) {
                $deleted++;

                // Delete metadata file if exists
                $metaFile = $backup['file'] . '.meta';
                if (file_exists($metaFile)) {
                    unlink($metaFile);
                }
            }
        }

        log_message('info', "Cleaned up {$deleted} old backup(s)");

        return $deleted;
    }

    /**
     * Format bytes to human-readable format.
     *
     * @param int $bytes
     * @return string
     */
    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $factor = floor((strlen((string) $bytes) - 1) / 3);

        return sprintf('%.2f %s', $bytes / pow(1024, $factor), $units[$factor]);
    }

    /**
     * Get backup directory.
     *
     * @return string
     */
    public function getBackupDir(): string
    {
        return $this->backupDir;
    }
}
