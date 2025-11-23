<?php

namespace App\Commands;

use App\Services\DatabaseCompatibilityService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Database Audit and Upgrade Command.
 *
 * Audits CI3 database for CI4 compatibility and optionally applies upgrades
 *
 * Usage:
 *   php spark db:audit [--format=json|yaml|table]
 *   php spark db:upgrade [--dry-run] [--apply] [--include-experimental]
 */
class DbAudit extends BaseCommand
{
    protected $group = 'Database';

    protected $name = 'db:audit';

    protected $description = 'Audit database for CI4 compatibility';

    protected $usage = 'db:audit [options]';

    protected $arguments = [];

    protected $options = [
        '--format' => 'Output format (table|json|yaml). Default: table',
        '--include-experimental' => 'Include experimental/feature-flagged modules',
        '--prefix' => 'Table prefix for CI4 tables (e.g., ci4_)',
        '--validate-data' => 'Also validate existing data for common issues',
    ];

    /**
     * Run the audit command.
     *
     * @param array<int|string, mixed> $params
     * @return int
     */
    public function run(array $params)
    {
        CLI::write('Database Compatibility Audit', 'yellow');
        CLI::write(str_repeat('=', 50), 'yellow');
        CLI::newLine();

        $format = CLI::getOption('format') ?? 'table';
        $includeExperimental = CLI::getOption('include-experimental') ?? false;
        $prefix = CLI::getOption('prefix') ?? '';
        $validateData = CLI::getOption('validate-data') ?? false;

        // Initialize service
        $db = \Config\Database::connect();
        $service = new DatabaseCompatibilityService($db, $prefix);

        if ($prefix) {
            CLI::write("Using table prefix: '{$prefix}'", 'cyan');
        }

        if ($includeExperimental) {
            CLI::write('Including experimental tables (OKR, etc.)', 'cyan');
            $service->addExperimentalTables();
        }

        // Run audit
        CLI::write('Scanning database schema...', 'cyan');
        $findings = $service->audit();

        // Display results
        $this->displayFindings($findings, $format);

        // Validate data if requested
        if ($validateData) {
            CLI::newLine();
            CLI::write('Validating existing data...', 'cyan');
            $dataIssues = $service->validateData();

            if (!empty($dataIssues)) {
                CLI::write('Data Validation Issues:', 'yellow');
                foreach ($dataIssues as $table => $issues) {
                    CLI::write("  Table: {$table}", 'white');
                    foreach ($issues as $issue) {
                        $severity = $issue['severity'] ?? 'medium';
                        $color = $severity === 'high' ? 'red' : 'yellow';
                        CLI::write("    - {$issue['message']}", $color);
                    }
                }
                CLI::newLine();
                CLI::write('Run "php spark db:backfill --dry-run" to see suggested fixes', 'cyan');
            } else {
                CLI::write('✓ No data validation issues found', 'green');
            }
        }

        // Summary
        CLI::newLine();
        $totalIssues = count($findings['missing_tables'])
                     + count($findings['missing_columns'])
                     + count($findings['missing_indexes']);

        if ($totalIssues === 0) {
            CLI::write('✓ Database schema is fully compatible!', 'green');
        } else {
            CLI::write("Found {$totalIssues} compatibility issue(s)", 'yellow');
            CLI::write('Run "php spark db:upgrade --dry-run" to see planned fixes', 'cyan');
        }

        return 0;
    }

    /**
     * Display audit findings.
     *
     * @param array<string, mixed> $findings
     * @param string $format
     * @return void
     */
    protected function displayFindings(array $findings, string $format): void
    {
        switch ($format) {
            case 'json':
                CLI::write(json_encode($findings, JSON_PRETTY_PRINT));
                break;

            case 'yaml':
                $this->displayYaml($findings);
                break;

            case 'table':
            default:
                $this->displayTable($findings);
                break;
        }
    }

    /**
     * Display findings as table.
     *
     * @param array<string, mixed> $findings
     * @return void
     */
    protected function displayTable(array $findings): void
    {
        // Missing tables
        if (!empty($findings['missing_tables'])) {
            CLI::write('Missing Tables:', 'red');
            $tableData = [];
            foreach ($findings['missing_tables'] as $missing) {
                $tableData[] = [
                    'Table' => $missing['table'],
                    'Columns' => count($missing['spec']['columns']),
                ];
            }
            CLI::table($tableData, ['Table', 'Columns']);
            CLI::newLine();
        }

        // Missing columns
        if (!empty($findings['missing_columns'])) {
            CLI::write('Missing Columns:', 'yellow');
            $columnData = [];
            foreach ($findings['missing_columns'] as $missing) {
                $columnData[] = [
                    'Table' => $missing['table'],
                    'Column' => $missing['column'],
                    'Type' => $missing['spec']['type'] ?? 'N/A',
                ];
            }
            CLI::table($columnData, ['Table', 'Column', 'Type']);
            CLI::newLine();
        }

        // Missing indexes
        if (!empty($findings['missing_indexes'])) {
            CLI::write('Missing Indexes:', 'yellow');
            $indexData = [];
            foreach ($findings['missing_indexes'] as $missing) {
                $fields = is_array($missing['spec']['fields'])
                    ? implode(', ', $missing['spec']['fields'])
                    : $missing['spec']['fields'];
                $indexData[] = [
                    'Table' => $missing['table'],
                    'Index' => $missing['index'],
                    'Fields' => $fields,
                ];
            }
            CLI::table($indexData, ['Table', 'Index', 'Fields']);
            CLI::newLine();
        }

        // Warnings
        if (!empty($findings['warnings'])) {
            CLI::write('Warnings:', 'yellow');
            foreach ($findings['warnings'] as $warning) {
                CLI::write('  - ' . $warning, 'yellow');
            }
            CLI::newLine();
        }
    }

    /**
     * Display findings as YAML.
     *
     * @param array<string, mixed> $findings
     * @return void
     */
    protected function displayYaml(array $findings): void
    {
        $yaml = "missing_tables:\n";
        foreach ($findings['missing_tables'] as $missing) {
            $yaml .= "  - table: {$missing['table']}\n";
            $yaml .= '    columns: ' . count($missing['spec']['columns']) . "\n";
        }

        $yaml .= "\nmissing_columns:\n";
        foreach ($findings['missing_columns'] as $missing) {
            $yaml .= "  - table: {$missing['table']}\n";
            $yaml .= "    column: {$missing['column']}\n";
            $yaml .= '    type: ' . ($missing['spec']['type'] ?? 'N/A') . "\n";
        }

        $yaml .= "\nmissing_indexes:\n";
        foreach ($findings['missing_indexes'] as $missing) {
            $fields = is_array($missing['spec']['fields'])
                ? implode(', ', $missing['spec']['fields'])
                : $missing['spec']['fields'];
            $yaml .= "  - table: {$missing['table']}\n";
            $yaml .= "    index: {$missing['index']}\n";
            $yaml .= "    fields: {$fields}\n";
        }

        CLI::write($yaml);
    }
}
