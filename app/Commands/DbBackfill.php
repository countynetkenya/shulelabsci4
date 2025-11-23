<?php

namespace App\Commands;

use App\Services\DatabaseCompatibilityService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Database Backfill Command.
 *
 * Validates and backfills data in CI4 tables
 *
 * Usage:
 *   php spark db:backfill [--dry-run] [--apply]
 */
class DbBackfill extends BaseCommand
{
    protected $group = 'Database';

    protected $name = 'db:backfill';

    protected $description = 'Validate and backfill data in CI4 tables';

    protected $usage = 'db:backfill [options]';

    protected $arguments = [];

    protected $options = [
        '--dry-run' => 'Show planned operations without applying them (default)',
        '--apply' => 'Apply the data backfill operations',
        '--prefix' => 'Table prefix for CI4 tables (e.g., ci4_)',
    ];

    /**
     * Run the backfill command.
     */
    public function run(array $params)
    {
        CLI::write('Database Data Backfill', 'yellow');
        CLI::write(str_repeat('=', 50), 'yellow');
        CLI::newLine();

        $dryRun = !CLI::getOption('apply');
        $prefix = CLI::getOption('prefix') ?? '';

        // Safety check
        if (!$dryRun) {
            CLI::write('WARNING: This will modify data in your database!', 'red');
            CLI::write('Please ensure you have a backup before proceeding.', 'yellow');
            CLI::newLine();

            if (CLI::prompt('Are you sure you want to continue?', ['y', 'n']) !== 'y') {
                CLI::write('Backfill cancelled.', 'yellow');
                return 0;
            }
            CLI::newLine();
        } else {
            CLI::write('DRY RUN MODE: No changes will be applied', 'cyan');
            CLI::newLine();
        }

        // Initialize service
        $db = \Config\Database::connect();
        $service = new DatabaseCompatibilityService($db, $prefix);

        // Validate data
        CLI::write('Validating existing data...', 'cyan');
        $dataIssues = $service->validateData();

        if (empty($dataIssues)) {
            CLI::write('✓ No data issues found. Database is clean!', 'green');
            return 0;
        }

        // Display issues
        CLI::write('Data Issues Found:', 'yellow');
        CLI::newLine();

        foreach ($dataIssues as $table => $issues) {
            CLI::write("Table: {$table}", 'white');
            foreach ($issues as $issue) {
                $severity = $issue['severity'] ?? 'medium';
                $color = $severity === 'high' ? 'red' : 'yellow';
                CLI::write("  - {$issue['message']}", $color);
            }
            CLI::newLine();
        }

        // Generate backfill SQL
        CLI::write('Generating backfill operations...', 'cyan');
        $backfillSql = $service->generateBackfillSql();

        if (empty($backfillSql)) {
            CLI::write('No automatic backfill operations available.', 'yellow');
            CLI::write('Manual review and data correction may be required.', 'white');
            return 0;
        }

        CLI::write('Planned Backfill Operations:', 'yellow');
        CLI::newLine();

        foreach ($backfillSql as $index => $sql) {
            CLI::write(($index + 1) . ". {$sql}", 'white');
            CLI::newLine();
        }

        if ($dryRun) {
            CLI::write('DRY RUN: SQL statements above would be executed with --apply flag', 'cyan');
            return 0;
        }

        // Apply backfill
        CLI::write('Applying backfill operations...', 'cyan');
        $results = $service->applyBackfill($backfillSql);

        $successCount = 0;
        $totalAffected = 0;

        foreach ($results as $result) {
            if ($result['success']) {
                $successCount++;
                $totalAffected += $result['affected_rows'] ?? 0;
                CLI::write('  ✓ Success: ' . substr($result['sql'], 0, 60) . '... (' . ($result['affected_rows'] ?? 0) . ' rows)', 'green');
            } else {
                CLI::write('  ✗ Error: ' . $result['error'], 'red');
                CLI::write('    SQL: ' . $result['sql'], 'white');
            }
        }

        CLI::newLine();
        CLI::write("Applied {$successCount} operation(s) successfully", 'green');
        CLI::write("Updated {$totalAffected} row(s) in total", 'green');

        return 0;
    }
}
