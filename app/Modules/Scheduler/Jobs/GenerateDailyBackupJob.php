<?php

namespace App\Modules\Scheduler\Jobs;

/**
 * Generates daily database backups.
 */
class GenerateDailyBackupJob extends BaseJob
{
    public function handle(array $parameters = []): string
    {
        $this->log('Starting daily backup');

        $backupPath = $parameters['backup_path'] ?? WRITEPATH . 'backups/';
        if (!is_dir($backupPath)) {
            mkdir($backupPath, 0755, true);
        }

        $filename = 'backup_' . date('Y-m-d_His') . '.sql';
        $filepath = $backupPath . $filename;

        // Get database configuration
        $db = \Config\Database::connect();
        $dbName = $db->getDatabase();

        // For MySQL, use mysqldump (simplified - in production use proper backup library)
        $tables = $db->listTables();
        $backup = "-- ShuleLabs Database Backup\n";
        $backup .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
        $backup .= "-- Database: {$dbName}\n\n";

        foreach ($tables as $table) {
            $backup .= "-- Table: {$table}\n";
            $backup .= "DROP TABLE IF EXISTS `{$table}`;\n";

            $createTable = $db->query("SHOW CREATE TABLE `{$table}`")->getRow();
            if ($createTable) {
                $backup .= $createTable->{'Create Table'} . ";\n\n";
            }
        }

        file_put_contents($filepath, $backup);

        // Cleanup old backups (keep last 7 days)
        $this->cleanupOldBackups($backupPath, 7);

        $this->log("Backup created: {$filename}");
        return "Backup created: {$filename}";
    }

    private function cleanupOldBackups(string $path, int $keepDays): void
    {
        $cutoff = strtotime("-{$keepDays} days");
        $files = glob($path . 'backup_*.sql');

        foreach ($files as $file) {
            if (filemtime($file) < $cutoff) {
                unlink($file);
                $this->log("Removed old backup: " . basename($file));
            }
        }
    }
}
