<?php

namespace App\Commands;

use App\Services\DatabaseBackupService;
use App\Services\DatabaseCompatibilityService;
use App\Services\SchemaVersionService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Database Upgrade Command.
 *
 * Generates and optionally applies database schema upgrades for CI4 compatibility
 *
 * Usage:
 *   php spark db:upgrade [--dry-run] [--apply] [--include-experimental]
 */
class DbUpgrade extends BaseCommand
{
    protected $group = 'Database';

    protected $name = 'db:upgrade';

    protected $description = 'Generate or apply database schema upgrades for CI4 compatibility';

    protected $usage = 'db:upgrade [options]';

    protected $arguments = [];

    protected $options = [
        '--dry-run' => 'Show planned operations without applying them (default)',
        '--apply' => 'Apply the schema changes to the database',
        '--include-experimental' => 'Include experimental/feature-flagged modules',
        '--migrations' => 'Generate migration files instead of direct SQL',
        '--prefix' => 'Table prefix for CI4 tables (e.g., ci4_)',
        '--no-backup' => 'Skip automatic backup (not recommended)',
    ];

    /**
     * Run the upgrade command.
     *
     * @param array<int|string, mixed> $params
     * @return int
     */
    public function run(array $params)
    {
        CLI::write('Database Compatibility Upgrade', 'yellow');
        CLI::write(str_repeat('=', 50), 'yellow');
        CLI::newLine();

        $dryRun = !CLI::getOption('apply');
        $includeExperimental = CLI::getOption('include-experimental') ?? false;
        $generateMigrations = CLI::getOption('migrations') ?? false;
        $prefix = CLI::getOption('prefix') ?? '';
        $skipBackup = CLI::getOption('no-backup') ?? false;

        // Safety check
        if (!$dryRun) {
            CLI::write('WARNING: This will modify your database schema!', 'red');

            if (!$skipBackup) {
                CLI::write('An automatic backup will be created before applying changes.', 'cyan');
            } else {
                CLI::write('WARNING: Backup has been disabled with --no-backup flag!', 'red');
            }

            CLI::newLine();

            if (CLI::prompt('Are you sure you want to continue?', ['y', 'n']) !== 'y') {
                CLI::write('Upgrade cancelled.', 'yellow');
                return 0;
            }
            CLI::newLine();
        } else {
            CLI::write('DRY RUN MODE: No changes will be applied', 'cyan');
            CLI::newLine();
        }

        // Initialize services
        $db = \Config\Database::connect();
        $service = new DatabaseCompatibilityService($db, $prefix);
        $backupService = null;
        $versionService = null;
        $backupInfo = null;

        if (!$dryRun) {
            $backupService = new DatabaseBackupService();
            $versionService = new SchemaVersionService($db);
        }

        if ($prefix) {
            CLI::write("Using table prefix: '{$prefix}'", 'cyan');
        }

        if ($includeExperimental) {
            CLI::write('Including experimental tables (OKR, etc.)', 'cyan');
            $service->addExperimentalTables();
        }

        // Run audit
        CLI::write('Analyzing database schema...', 'cyan');
        $findings = $service->audit();

        $totalIssues = count($findings['missing_tables'])
                     + count($findings['missing_columns'])
                     + count($findings['missing_indexes']);

        if ($totalIssues === 0) {
            CLI::write('✓ Database schema is already compatible!', 'green');
            return 0;
        }

        CLI::write("Found {$totalIssues} compatibility issue(s)", 'yellow');
        CLI::newLine();

        // Create backup before applying changes
        if (!$dryRun && !$skipBackup && $backupService) {
            try {
                CLI::write('Creating database backup...', 'cyan');
                $backupInfo = $backupService->createBackup('Pre-upgrade automatic backup');
                CLI::write('✓ Backup created: ' . $backupInfo['filename'], 'green');
                CLI::write('  Location: ' . $backupInfo['file'], 'white');
                CLI::write('  Size: ' . $backupInfo['metadata']['filesize_human'], 'white');
                CLI::newLine();
            } catch (\Exception $e) {
                CLI::write('✗ Backup failed: ' . $e->getMessage(), 'red');
                CLI::write('Aborting upgrade for safety.', 'yellow');
                return 1;
            }
        }

        // Generate or apply fixes
        $operations = [];
        if ($generateMigrations) {
            $this->generateMigrationFiles($service, $dryRun);
        } else {
            $operations = $this->applySqlPatches($service, $dryRun, $prefix);
        }

        // Record migration in version tracking
        if (!$dryRun && $versionService && !empty($operations)) {
            $version = 'upgrade_' . date('Y-m-d_H-i-s');
            $description = sprintf(
                'Schema upgrade: %d table(s), %d column(s), %d index(es)',
                count($findings['missing_tables']),
                count($findings['missing_columns']),
                count($findings['missing_indexes'])
            );

            $versionService->recordMigration(
                $version,
                $description,
                $operations,
                $backupInfo['file'] ?? null
            );

            CLI::newLine();
            CLI::write('✓ Migration recorded in version tracking', 'green');
            CLI::write('  Version: ' . $version, 'white');
            if ($backupInfo) {
                CLI::write('  Backup: ' . $backupInfo['filename'], 'white');
                CLI::write('  Use "php spark db:rollback" to undo if needed', 'cyan');
            }
        }

        return 0;
    }

    /**
     * Generate migration files.
     *
     * @param DatabaseCompatibilityService $service
     * @param bool $dryRun
     * @return void
     */
    protected function generateMigrationFiles(DatabaseCompatibilityService $service, bool $dryRun): void
    {
        $outputDir = APPPATH . 'Database/Migrations';

        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        CLI::write('Generating migration files...', 'cyan');
        CLI::newLine();

        if ($dryRun) {
            CLI::write('DRY RUN: Would generate migrations in:', 'yellow');
            CLI::write('  ' . $outputDir, 'white');
            CLI::newLine();

            $findings = $service->getFindings();
            if (!empty($findings['missing_tables'])) {
                CLI::write('Tables to create:', 'yellow');
                foreach ($findings['missing_tables'] as $missing) {
                    CLI::write('  - ' . $missing['table'], 'white');
                }
            }
        } else {
            $generated = $service->generateMigrations($outputDir);

            if (empty($generated)) {
                CLI::write('No migration files needed.', 'green');
            } else {
                CLI::write('Generated migration files:', 'green');
                foreach ($generated as $file) {
                    CLI::write('  ✓ ' . basename($file), 'green');
                }
                CLI::newLine();
                CLI::write('Run migrations with: php spark migrate', 'cyan');
            }
        }

        // Still need to handle columns and indexes
        $sql = $service->generateSqlPatches();
        if (!empty($sql)) {
            CLI::newLine();
            CLI::write('Additional SQL patches needed:', 'yellow');
            CLI::write('(These cannot be generated as migrations and require manual SQL)', 'white');
            CLI::newLine();

            foreach ($sql as $statement) {
                CLI::write($statement, 'white');
            }

            // Save to file
            if (!$dryRun) {
                $patchFile = WRITEPATH . 'db_patches.sql';
                file_put_contents($patchFile, implode("\n\n", $sql));
                CLI::newLine();
                CLI::write("SQL patches saved to: {$patchFile}", 'cyan');
            }
        }
    }

    /**
     * Apply SQL patches directly.
     *
     * @param DatabaseCompatibilityService $service
     * @param bool $dryRun
     * @param string $prefix
     * @return array<int, array<string, mixed>>
     */
    protected function applySqlPatches(DatabaseCompatibilityService $service, bool $dryRun, string $prefix = ''): array
    {
        $sql = $service->generateSqlPatches();
        $operations = [];

        if (empty($sql)) {
            CLI::write('No SQL patches needed.', 'green');

            // Still check for missing tables
            $findings = $service->getFindings();
            if (!empty($findings['missing_tables'])) {
                $this->createMissingTables($service, $dryRun, $operations);
            }

            return $operations;
        }

        CLI::write('Planned SQL operations:', 'yellow');
        CLI::newLine();

        foreach ($sql as $index => $statement) {
            CLI::write(($index + 1) . ". {$statement}", 'white');
            CLI::newLine();
            $operations[] = ['type' => 'sql', 'statement' => $statement];
        }

        if ($dryRun) {
            CLI::write('DRY RUN: SQL statements above would be executed with --apply flag', 'cyan');
            return $operations;
        }

        // Apply patches
        CLI::write('Applying SQL patches...', 'cyan');
        $results = $service->applySqlPatches($sql);

        $successCount = 0;
        $errorCount = 0;

        foreach ($results as $result) {
            if ($result['success']) {
                $successCount++;
                CLI::write('  ✓ Success: ' . substr($result['sql'], 0, 60) . '...', 'green');
            } else {
                $errorCount++;
                CLI::write('  ✗ Error: ' . $result['error'], 'red');
                CLI::write('    SQL: ' . $result['sql'], 'white');
            }
        }

        CLI::newLine();
        CLI::write("Applied {$successCount} patch(es) successfully", 'green');
        if ($errorCount > 0) {
            CLI::write("Failed to apply {$errorCount} patch(es)", 'red');
        }

        // Handle missing tables if any
        $this->createMissingTables($service, $dryRun, $operations);

        return $operations;
    }

    /**
     * Create missing tables.
     *
     * @param DatabaseCompatibilityService $service
     * @param bool $dryRun
     * @param array<int, array<string, mixed>> $operations
     * @return void
     */
    protected function createMissingTables(DatabaseCompatibilityService $service, bool $dryRun, array &$operations): void
    {
        $findings = $service->getFindings();

        if (!empty($findings['missing_tables'])) {
            CLI::newLine();
            CLI::write('Missing tables detected. Creating tables...', 'yellow');

            foreach ($findings['missing_tables'] as $missing) {
                $tableName = $missing['table'];
                $operations[] = ['type' => 'create_table', 'table' => $tableName];

                if ($dryRun) {
                    continue;
                }

                try {
                    $this->createTable($tableName, $missing['spec']);
                    CLI::write("  ✓ Created table: {$tableName}", 'green');
                } catch (\Exception $e) {
                    CLI::write("  ✗ Failed to create {$tableName}: " . $e->getMessage(), 'red');
                }
            }
        }
    }

    /**
     * Create a table from specification.
     *
     * @param string $tableName
     * @param array<string, mixed> $spec
     * @return void
     */
    protected function createTable(string $tableName, array $spec): void
    {
        $db = \Config\Database::connect();
        $forge = \Config\Database::forge();

        $forge->addField($spec['columns']);

        if (!empty($spec['primary_key'])) {
            $forge->addKey($spec['primary_key'], true);
        }

        if (!empty($spec['indexes'])) {
            foreach ($spec['indexes'] as $index) {
                $unique = !empty($index['unique']);
                $forge->addKey($index['fields'], false, $unique);
            }
        }

        $forge->createTable($tableName, true);
    }
}
