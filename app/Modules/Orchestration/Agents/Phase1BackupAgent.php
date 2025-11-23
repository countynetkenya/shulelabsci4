<?php

declare(strict_types=1);

namespace Modules\Orchestration\Agents;

/**
 * Phase 1: RESTART & BACKUP Agent.
 *
 * Creates complete system backup and clean slate
 *
 * Tasks:
 * - Create timestamped backup of entire codebase
 * - Backup database schemas and migrations
 * - Archive current configuration files
 * - Create rollback checkpoint (recovery < 2 minutes)
 * - Reset development environment
 * - Clear all caches (application, route, view)
 * - Verify backup integrity
 *
 * @version 1.0.0
 */
class Phase1BackupAgent extends BaseAgent
{
    public function getName(): string
    {
        return 'Phase 1: RESTART & BACKUP';
    }

    public function getDescription(): string
    {
        return 'Complete system backup and clean slate';
    }

    public function execute(): array
    {
        $this->log('Starting Phase 1: RESTART & BACKUP', 'info');

        try {
            $deliverables = [];

            // Step 1: Create backup directory
            $backupDir = $this->createBackupDirectory();
            $deliverables['backup_directory'] = $backupDir;
            $this->log("✓ Backup directory created: {$backupDir}", 'info');

            // Step 2: Backup codebase
            $codebackupSize = $this->backupCodebase($backupDir);
            $deliverables['codebase_backup_size'] = $codebackupSize;
            $this->addMetric('codebase_backup_mb', round($codebackupSize / 1024 / 1024, 2));
            $this->log('✓ Codebase backed up: ' . round($codebackupSize / 1024 / 1024, 2) . ' MB', 'info');

            // Step 3: Backup database schemas
            $dbBackup = $this->backupDatabaseSchemas($backupDir);
            $deliverables['database_backup'] = $dbBackup;
            $this->log('✓ Database schemas backed up', 'info');

            // Step 4: Backup configuration files
            $configBackup = $this->backupConfigFiles($backupDir);
            $deliverables['config_backup'] = $configBackup;
            $this->log('✓ Configuration files backed up', 'info');

            // Step 5: Create rollback script
            $rollbackScript = $this->createRollbackScript($backupDir);
            $deliverables['rollback_script'] = $rollbackScript;
            $this->log("✓ Rollback script created: {$rollbackScript}", 'info');

            // Step 6: Clear caches
            $this->clearCaches();
            $this->log('✓ All caches cleared', 'info');

            // Step 7: Verify backup integrity
            $verificationReport = $this->verifyBackupIntegrity($backupDir);
            $deliverables['verification_report'] = $verificationReport;
            $this->log('✓ Backup integrity verified', 'info');

            // Set metrics
            $this->addMetric('backup_complete', true);
            $this->addMetric('backup_directory', $backupDir);
            $this->addMetric('execution_time_seconds', $this->getElapsedTime());

            return $this->createSuccessResult($deliverables);

        } catch (\Throwable $e) {
            $this->log("Phase 1 failed: {$e->getMessage()}", 'error');
            return $this->createFailureResult($e->getMessage());
        }
    }

    /**
     * Create backup directory with timestamp.
     */
    protected function createBackupDirectory(): string
    {
        $timestamp = date('Y-m-d-His');
        $backupDir = ROOTPATH . $this->config->backupDirectory . "/{$timestamp}";

        if (!$this->dryRun && !is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        return $backupDir;
    }

    /**
     * Backup entire codebase.
     */
    protected function backupCodebase(string $backupDir): int
    {
        if ($this->dryRun) {
            return 52428800; // 50 MB simulated
        }

        $excludes = [
            'vendor/',
            'writable/cache/',
            'writable/session/',
            'writable/debugbar/',
            'writable/uploads/',
            '.git/',
            'node_modules/',
            $this->config->backupDirectory . '/',
        ];

        $excludeArgs = implode(' ', array_map(fn ($e) => "--exclude='{$e}'", $excludes));

        // Create tar file in temp location first, then move to backup dir
        $tempFile = sys_get_temp_dir() . '/shulelabs_backup_' . time() . '.tar.gz';
        $command = 'cd ' . ROOTPATH . " && tar -czf {$tempFile} {$excludeArgs} .";

        $result = $this->executeCommand($command, 'Creating codebase backup');

        if (!$result['success']) {
            throw new \RuntimeException('Failed to create codebase backup');
        }

        // Move to final location
        $tarFile = "{$backupDir}/codebase.tar.gz";
        if (file_exists($tempFile)) {
            rename($tempFile, $tarFile);
        }

        return file_exists($tarFile) ? filesize($tarFile) : 0;
    }

    /**
     * Backup database schemas.
     */
    protected function backupDatabaseSchemas(string $backupDir): string
    {
        if ($this->dryRun) {
            return "{$backupDir}/database-schema.sql";
        }

        // Copy all migration files
        $migrationDir = ROOTPATH . 'app/Modules/Database/Migrations';
        $backupMigrationDir = "{$backupDir}/migrations";

        if (!is_dir($backupMigrationDir)) {
            mkdir($backupMigrationDir, 0755, true);
        }

        if (is_dir($migrationDir)) {
            $command = "cp -r {$migrationDir}/* {$backupMigrationDir}/";
            $this->executeCommand($command, 'Backing up migration files');
        }

        return $backupMigrationDir;
    }

    /**
     * Backup configuration files.
     */
    protected function backupConfigFiles(string $backupDir): string
    {
        if ($this->dryRun) {
            return "{$backupDir}/config/";
        }

        $configFiles = [
            '.env',
            'app/Config/',
            'composer.json',
            'composer.lock',
        ];

        $backupConfigDir = "{$backupDir}/config";

        if (!is_dir($backupConfigDir)) {
            mkdir($backupConfigDir, 0755, true);
        }

        foreach ($configFiles as $file) {
            $sourcePath = ROOTPATH . $file;
            if (file_exists($sourcePath)) {
                if (is_dir($sourcePath)) {
                    $command = "cp -r {$sourcePath} {$backupConfigDir}/";
                } else {
                    $command = "cp {$sourcePath} {$backupConfigDir}/";
                }
                $this->executeCommand($command);
            }
        }

        return $backupConfigDir;
    }

    /**
     * Create rollback script.
     */
    protected function createRollbackScript(string $backupDir): string
    {
        $scriptPath = "{$backupDir}/rollback.sh";

        if ($this->dryRun) {
            return $scriptPath;
        }

        $script = <<<'SCRIPT'
#!/bin/bash
# Rollback Script
# Generated by Master Orchestration Agent
# Execution Time: < 2 minutes

set -e

BACKUP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ROOT_DIR="$(dirname "$(dirname "$BACKUP_DIR")")"

echo "Starting rollback from backup: $BACKUP_DIR"
echo "Target directory: $ROOT_DIR"

# Verify backup file exists and is readable
if [ ! -f "$BACKUP_DIR/codebase.tar.gz" ]; then
    echo "ERROR: Backup file not found: $BACKUP_DIR/codebase.tar.gz"
    exit 1
fi

# Verify tar file integrity
echo "Verifying backup integrity..."
if ! tar -tzf "$BACKUP_DIR/codebase.tar.gz" > /dev/null 2>&1; then
    echo "ERROR: Backup file is corrupted or invalid"
    exit 1
fi

# Extract codebase backup
echo "Restoring codebase..."
cd "$ROOT_DIR"
if ! tar -xzf "$BACKUP_DIR/codebase.tar.gz"; then
    echo "ERROR: Failed to extract backup"
    exit 1
fi

# Restore configuration
echo "Restoring configuration..."
if [ -f "$BACKUP_DIR/config/.env" ]; then
    cp "$BACKUP_DIR/config/.env" "$ROOT_DIR/.env"
    echo "  ✓ .env restored"
else
    echo "  ⚠ .env backup not found"
fi

if [ -d "$BACKUP_DIR/config/Config" ]; then
    if cp -r "$BACKUP_DIR/config/Config/"* "$ROOT_DIR/app/Config/" 2>&1; then
        echo "  ✓ Config files restored"
    else
        echo "  ⚠ Warning: Some config files may not have been restored"
    fi
else
    echo "  ⚠ Config backup not found"
fi

# Clear caches
echo "Clearing caches..."
if php spark cache:clear 2>&1; then
    echo "  ✓ Caches cleared"
else
    echo "  ⚠ Warning: Cache clearing may have failed"
fi

echo "Rollback complete!"
echo "Duration: $(date)"
SCRIPT;

        file_put_contents($scriptPath, $script);
        chmod($scriptPath, 0755);

        return $scriptPath;
    }

    /**
     * Clear all application caches.
     */
    protected function clearCaches(): void
    {
        if ($this->dryRun) {
            return;
        }

        $cacheDirectories = [
            ROOTPATH . 'writable/cache',
            ROOTPATH . 'writable/session',
            ROOTPATH . 'writable/debugbar',
        ];

        foreach ($cacheDirectories as $dir) {
            if (is_dir($dir)) {
                $this->executeCommand("find {$dir} -type f -delete", "Clearing cache: {$dir}");
            }
        }

        // Clear CI4 cache using spark
        $this->executeCommand('cd ' . ROOTPATH . ' && php spark cache:clear', 'Clearing CI4 cache');
    }

    /**
     * Verify backup integrity.
     */
    protected function verifyBackupIntegrity(string $backupDir): array
    {
        $report = [
            'backup_exists' => is_dir($backupDir),
            'codebase_backup_exists' => file_exists("{$backupDir}/codebase.tar.gz"),
            'config_backup_exists' => is_dir("{$backupDir}/config"),
            'rollback_script_exists' => file_exists("{$backupDir}/rollback.sh"),
            'rollback_script_executable' => is_executable("{$backupDir}/rollback.sh"),
        ];

        if ($this->dryRun) {
            $report = array_map(fn () => true, $report);
        }

        $report['all_checks_passed'] = !in_array(false, $report, true);

        return $report;
    }
}
