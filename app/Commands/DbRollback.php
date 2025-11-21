<?php

namespace App\Commands;

use App\Services\DatabaseBackupService;
use App\Services\SchemaVersionService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Database Rollback Command
 *
 * Rollback the last schema migration
 *
 * Usage:
 *   php spark db:rollback [--steps=1] [--to-version=VERSION]
 */
class DbRollback extends BaseCommand
{
    protected $group = 'Database';
    protected $name = 'db:rollback';
    protected $description = 'Rollback database schema migrations';
    protected $usage = 'db:rollback [options]';
    protected $arguments = [];
    protected $options = [
        '--steps' => 'Number of migrations to rollback (default: 1)',
        '--to-version' => 'Rollback to a specific version',
        '--list' => 'List available migrations to rollback',
    ];

    /**
     * Run the rollback command
     */
    public function run(array $params)
    {
        CLI::write('Database Schema Rollback', 'yellow');
        CLI::write(str_repeat('=', 50), 'yellow');
        CLI::newLine();

        $listOnly = CLI::getOption('list') ?? false;
        $steps = (int) (CLI::getOption('steps') ?? 1);
        $toVersion = CLI::getOption('to-version');

        // Initialize services
        $db = \Config\Database::connect();
        $versionService = new SchemaVersionService($db);
        $backupService = new DatabaseBackupService();

        // List migrations if requested
        if ($listOnly) {
            $this->listMigrations($versionService);
            return 0;
        }

        // Get migrations to rollback
        $migrations = $versionService->getAppliedMigrations();

        if (empty($migrations)) {
            CLI::write('No migrations to rollback.', 'yellow');
            return 0;
        }

        // Determine which migrations to rollback
        $toRollback = [];

        if ($toVersion) {
            // Rollback to specific version
            foreach ($migrations as $migration) {
                if ($migration['version'] === $toVersion) {
                    break;
                }
                $toRollback[] = $migration;
            }
        } else {
            // Rollback N steps
            $toRollback = array_slice($migrations, 0, $steps);
        }

        if (empty($toRollback)) {
            CLI::write('No migrations found to rollback.', 'yellow');
            return 0;
        }

        // Display migrations to rollback
        CLI::write('Migrations to rollback:', 'yellow');
        CLI::newLine();

        foreach ($toRollback as $migration) {
            CLI::write(sprintf(
                '  - %s: %s (applied: %s)',
                $migration['version'],
                $migration['description'],
                $migration['applied_at']
            ), 'white');
        }

        CLI::newLine();

        // Confirmation
        CLI::write('WARNING: This will restore your database from backup!', 'red');
        CLI::write('All changes made after the rollback point will be lost.', 'yellow');
        CLI::newLine();

        if (CLI::prompt('Are you sure you want to continue?', ['y', 'n']) !== 'y') {
            CLI::write('Rollback cancelled.', 'yellow');
            return 0;
        }

        // Perform rollback
        foreach ($toRollback as $migration) {
            CLI::newLine();
            CLI::write("Rolling back: {$migration['description']}", 'cyan');

            if (!empty($migration['backup_file']) && file_exists($migration['backup_file'])) {
                try {
                    CLI::write('  Restoring from backup...', 'cyan');
                    $backupService->restore($migration['backup_file']);
                    CLI::write('  ✓ Backup restored successfully', 'green');

                    // Mark as rolled back
                    $versionService->markAsRolledBack($migration['id']);
                    CLI::write('  ✓ Migration marked as rolled back', 'green');
                } catch (\Exception $e) {
                    CLI::write('  ✗ Rollback failed: ' . $e->getMessage(), 'red');
                    return 1;
                }
            } else {
                CLI::write('  ⚠ No backup file found - cannot rollback this migration', 'yellow');
                CLI::write('    Manual intervention required', 'yellow');
            }
        }

        CLI::newLine();
        CLI::write('✓ Rollback completed successfully!', 'green');

        return 0;
    }

    /**
     * List available migrations
     */
    protected function listMigrations(SchemaVersionService $versionService): void
    {
        $migrations = $versionService->getAppliedMigrations();

        if (empty($migrations)) {
            CLI::write('No migrations found.', 'yellow');
            return;
        }

        CLI::write('Applied Migrations:', 'yellow');
        CLI::newLine();

        $tableData = [];
        foreach ($migrations as $migration) {
            $hasBackup = !empty($migration['backup_file']) && file_exists($migration['backup_file']) ? '✓' : '✗';

            $tableData[] = [
                'Version' => $migration['version'],
                'Description' => substr($migration['description'], 0, 40),
                'Applied' => $migration['applied_at'],
                'Status' => $migration['status'],
                'Backup' => $hasBackup,
            ];
        }

        CLI::table($tableData, ['Version', 'Description', 'Applied', 'Status', 'Backup']);
    }
}
