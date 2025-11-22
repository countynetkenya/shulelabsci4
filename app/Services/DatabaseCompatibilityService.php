<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;

/**
 * Database Compatibility Service
 *
 * Centralizes schema specifications and compatibility checking for CI3->CI4 migration
 * Provides validation and migration generation for required tables, columns, and indexes
 */
class DatabaseCompatibilityService
{
    /** @var \CodeIgniter\Database\BaseConnection<mixed, mixed> */
    protected BaseConnection $db;
    /** @var array<string, array<string, mixed>> */
    protected array $requiredTables = [];
    /** @var array<string, mixed> */
    protected array $findings = [];
    protected string $tablePrefix = '';

    protected string $driver;

    /**
     * @param \CodeIgniter\Database\BaseConnection<mixed, mixed> $db
     * @param string $tablePrefix
     */
    public function __construct(BaseConnection $db, string $tablePrefix = '')
    {
        $this->db = $db;
        $this->driver = strtolower($this->db->DBDriver ?? '');
        $this->tablePrefix = $tablePrefix;
        $this->initializeRequiredTables();
    }

    /**
     * Set table prefix for CI4-specific tables
     *
     * @param string $prefix Prefix to add to all CI4 tables (e.g., 'ci4_')
     * @return self
     */
    public function setTablePrefix(string $prefix): self
    {
        $this->tablePrefix = $prefix;
        $this->initializeRequiredTables();
        return $this;
    }

    /**
     * Get the prefixed table name
     */
    protected function getPrefixedTableName(string $tableName): string
    {
        return $this->tablePrefix . $tableName;
    }

    protected function isSQLite(): bool
    {
        return $this->driver === 'sqlite3';
    }

    /**
     * Initialize required table specifications
     */
    protected function initializeRequiredTables(): void
    {
        // Menu overrides table
        $this->requiredTables['menu_overrides'] = [
            'columns' => [
                'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'override_type' => ['type' => 'VARCHAR', 'constraint' => 50],
                'menuName' => ['type' => 'VARCHAR', 'constraint' => 100],
                'link' => ['type' => 'VARCHAR', 'constraint' => 255],
                'priority' => ['type' => 'INT', 'default' => 0],
                'status' => ['type' => 'TINYINT', 'default' => 1],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
                'updated_at' => ['type' => 'DATETIME', 'null' => true],
            ],
            'primary_key' => 'id',
            'indexes' => [
                ['fields' => ['link', 'status'], 'name' => 'idx_link_status'],
            ],
        ];

        // Audit events table (from Foundation migrations)
        $this->requiredTables['audit_events'] = [
            'columns' => [
                'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
                'event_key' => ['type' => 'VARCHAR', 'constraint' => 191],
                'event_type' => ['type' => 'VARCHAR', 'constraint' => 100],
                'tenant_id' => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => true],
                'actor_id' => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => true],
                'ip_address' => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => true],
                'user_agent' => ['type' => 'TEXT', 'null' => true],
                'request_uri' => ['type' => 'TEXT', 'null' => true],
                'before_state' => ['type' => 'LONGTEXT', 'null' => true],
                'after_state' => ['type' => 'LONGTEXT', 'null' => true],
                'metadata_json' => ['type' => 'LONGTEXT', 'null' => true],
                'previous_hash' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                'hash_value' => ['type' => 'VARCHAR', 'constraint' => 255],
                'created_at' => ['type' => 'DATETIME'],
            ],
            'primary_key' => 'id',
            'indexes' => [
                ['fields' => ['event_key'], 'name' => 'idx_event_key'],
                ['fields' => ['actor_id'], 'name' => 'idx_actor_id'],
                ['fields' => ['created_at'], 'name' => 'idx_created_at'],
            ],
        ];

        // Idempotency keys table
        $this->requiredTables['idempotency_keys'] = [
            'columns' => [
                'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
                'idempotency_key' => ['type' => 'VARCHAR', 'constraint' => 191],
                'scope' => ['type' => 'VARCHAR', 'constraint' => 100],
                'response_data' => ['type' => 'LONGTEXT', 'null' => true],
                'status' => ['type' => 'VARCHAR', 'constraint' => 50],
                'created_at' => ['type' => 'DATETIME'],
                'expires_at' => ['type' => 'DATETIME'],
            ],
            'primary_key' => 'id',
            'indexes' => [
                ['fields' => ['idempotency_key', 'scope'], 'name' => 'idx_key_scope', 'unique' => true],
            ],
        ];

        // Session table
        $this->requiredTables['school_sessions'] = [
            'columns' => [
                'id' => ['type' => 'VARCHAR', 'constraint' => 128],
                'ip_address' => ['type' => 'VARCHAR', 'constraint' => 45],
                'timestamp' => ['type' => 'INT', 'unsigned' => true, 'default' => 0],
                'data' => ['type' => 'BLOB'],
            ],
            'primary_key' => 'id',
            'indexes' => [
                ['fields' => ['timestamp'], 'name' => 'ci_sessions_timestamp'],
            ],
        ];
    }

    /**
     * Add experimental/feature-flagged tables
     */
    public function addExperimentalTables(): void
    {
        // OKR tables (when FLAG_OKR_V1=true)
        $this->requiredTables['okr_objectives'] = [
            'columns' => [
                'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
                'title' => ['type' => 'VARCHAR', 'constraint' => 255],
                'description' => ['type' => 'TEXT', 'null' => true],
                'owner_id' => ['type' => 'INT', 'unsigned' => true],
                'parent_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
                'start_date' => ['type' => 'DATE'],
                'end_date' => ['type' => 'DATE'],
                'status' => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => 'active'],
                'progress' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 0],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
                'updated_at' => ['type' => 'DATETIME', 'null' => true],
            ],
            'primary_key' => 'id',
            'indexes' => [
                ['fields' => ['owner_id'], 'name' => 'idx_owner_id'],
                ['fields' => ['status'], 'name' => 'idx_status'],
            ],
        ];

        $this->requiredTables['okr_key_results'] = [
            'columns' => [
                'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
                'objective_id' => ['type' => 'BIGINT', 'unsigned' => true],
                'title' => ['type' => 'VARCHAR', 'constraint' => 255],
                'description' => ['type' => 'TEXT', 'null' => true],
                'target_value' => ['type' => 'DECIMAL', 'constraint' => '10,2'],
                'current_value' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0],
                'unit' => ['type' => 'VARCHAR', 'constraint' => 50],
                'status' => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => 'active'],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
                'updated_at' => ['type' => 'DATETIME', 'null' => true],
            ],
            'primary_key' => 'id',
            'indexes' => [
                ['fields' => ['objective_id'], 'name' => 'idx_objective_id'],
            ],
        ];
    }

    /**
     * Audit database schema against requirements
     *
     * @return array<string, mixed> Findings with missing tables, columns, and indexes
     */
    public function audit(): array
    {
        $this->findings = [
            'missing_tables' => [],
            'missing_columns' => [],
            'missing_indexes' => [],
            'warnings' => [],
        ];

        foreach ($this->requiredTables as $tableName => $spec) {
            $prefixedTableName = $this->getPrefixedTableName($tableName);

            // Check if table exists
            if (!$this->db->tableExists($prefixedTableName)) {
                $this->findings['missing_tables'][] = [
                    'table' => $prefixedTableName,
                    'original_name' => $tableName,
                    'spec' => $spec,
                ];
            } else {
                // Table exists, check columns
                $this->auditTableColumns($prefixedTableName, $spec['columns']);

                // Check indexes
                if (!empty($spec['indexes'])) {
                    $this->auditTableIndexes($prefixedTableName, $spec['indexes']);
                }
            }
        }

        return $this->findings;
    }

    /**
     * Audit table columns
     *
     * @param string $tableName
     * @param array<string, mixed> $requiredColumns
     * @return void
     */
    protected function auditTableColumns(string $tableName, array $requiredColumns): void
    {
        foreach ($requiredColumns as $columnName => $columnSpec) {
            if (!$this->db->fieldExists($columnName, $tableName)) {
                $this->findings['missing_columns'][] = [
                    'table' => $tableName,
                    'column' => $columnName,
                    'spec' => $columnSpec,
                ];
            }
        }
    }

    /**
     * Audit table indexes
     *
     * @param string $tableName
     * @param array<int, array<string, mixed>> $requiredIndexes
     * @return void
     */
    protected function auditTableIndexes(string $tableName, array $requiredIndexes): void
    {
        try {
            if ($this->isSQLite()) {
                $existingIndexes = $this->db->query("PRAGMA index_list('{$tableName}')")->getResultArray();
                $existingIndexNames = [];

                foreach ($existingIndexes as $index) {
                    $name = $index['name'] ?? null;
                    if ($name !== null && ! str_starts_with((string) $name, 'sqlite_')) {
                        $existingIndexNames[] = (string) $name;
                    }
                }
            } else {
                $query = $this->db->query("SHOW INDEX FROM `{$tableName}`");
                $existingIndexes = $query->getResultArray();
                $existingIndexNames = array_column($existingIndexes, 'Key_name');
            }

            foreach ($requiredIndexes as $indexSpec) {
                $indexName = $indexSpec['name'] ?? null;
                if ($indexName && !in_array($indexName, $existingIndexNames)) {
                    $this->findings['missing_indexes'][] = [
                        'table' => $tableName,
                        'index' => $indexName,
                        'spec' => $indexSpec,
                    ];
                }
            }
        } catch (\Exception $e) {
            $this->findings['warnings'][] = "Could not check indexes for {$tableName}: " . $e->getMessage();
        }
    }

    /**
     * Generate migration stubs for missing tables
     *
     * @param string $outputDir Directory to write migration files
     * @return array<int, string> List of generated migration files
     */
    public function generateMigrations(string $outputDir): array
    {
        $generated = [];

        foreach ($this->findings['missing_tables'] as $missing) {
            $tableName = $missing['table'];
            $spec = $missing['spec'];

            $className = $this->tableNameToClassName($tableName);
            $timestamp = date('Y-m-d-His');
            $filename = "{$timestamp}_{$className}.php";
            $filepath = rtrim($outputDir, '/') . '/' . $filename;

            $content = $this->generateMigrationContent($className, $tableName, $spec);

            if (file_put_contents($filepath, $content)) {
                $generated[] = $filepath;
            }
        }

        return $generated;
    }

    /**
     * Generate SQL patch statements for missing columns and indexes
     *
     * @return array<int, string> SQL statements
     */
    public function generateSqlPatches(): array
    {
        $sql = [];

        // Add columns
        foreach ($this->findings['missing_columns'] as $missing) {
            $table = $missing['table'];
            $column = $missing['column'];
            $spec = $missing['spec'];

            $sql[] = $this->generateAddColumnSql($table, $column, $spec);
        }

        // Add indexes
        foreach ($this->findings['missing_indexes'] as $missing) {
            $table = $missing['table'];
            $indexSpec = $missing['spec'];

            $sql[] = $this->generateAddIndexSql($table, $indexSpec);
        }

        return $sql;
    }

    /**
     * Apply SQL patches to database
     *
     * @param array<int, string> $sqlStatements
     * @return array<int, array<string, mixed>> Results with success/error for each statement
     */
    public function applySqlPatches(array $sqlStatements): array
    {
        $results = [];

        $this->db->transStart();

        foreach ($sqlStatements as $sql) {
            try {
                $this->db->query($sql);
                $results[] = [
                    'sql' => $sql,
                    'success' => true,
                ];
            } catch (\Exception $e) {
                $results[] = [
                    'sql' => $sql,
                    'success' => false,
                    'error' => $e->getMessage(),
                ];
            }
        }

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            $this->db->transRollback();
        }

        return $results;
    }

    /**
     * Convert table name to migration class name
     *
     * @param string $tableName
     * @return string
     */
    protected function tableNameToClassName(string $tableName): string
    {
        $parts = explode('_', $tableName);
        $parts = array_map('ucfirst', $parts);
        return 'Create' . implode('', $parts);
    }

    /**
     * Generate migration file content
     *
     * @param string $className
     * @param string $tableName
     * @param array<string, mixed> $spec
     * @return string
     */
    protected function generateMigrationContent(string $className, string $tableName, array $spec): string
    {
        $columns = $this->formatColumnsForMigration($spec['columns']);
        $primaryKey = $spec['primary_key'] ?? 'id';
        $indexes = $spec['indexes'] ?? [];

        $indexCode = '';
        foreach ($indexes as $index) {
            $fields = is_array($index['fields']) ? implode("', '", $index['fields']) : $index['fields'];
            $unique = !empty($index['unique']) ? 'true' : 'false';
            $indexCode .= "        \$this->forge->addKey(['{$fields}'], false, {$unique});\n";
        }

        return <<<PHP
<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class {$className} extends Migration
{
    public function up(): void
    {
        \$this->forge->addField([
{$columns}
        ]);
        \$this->forge->addKey('{$primaryKey}', true);
{$indexCode}
        \$this->forge->createTable('{$tableName}', true);
    }

    public function down(): void
    {
        \$this->forge->dropTable('{$tableName}', true);
    }
}

PHP;
    }

    /**
     * Format columns array for migration code
     *
     * @param array<string, mixed> $columns
     * @return string
     */
    protected function formatColumnsForMigration(array $columns): string
    {
        $lines = [];

        foreach ($columns as $name => $spec) {
            $specStr = $this->formatColumnSpec($spec);
            $lines[] = "            '{$name}' => [{$specStr}],";
        }

        return implode("\n", $lines);
    }

    /**
     * Format column specification
     *
     * @param array<string, mixed> $spec
     * @return string
     */
    protected function formatColumnSpec(array $spec): string
    {
        $parts = [];

        foreach ($spec as $key => $value) {
            if (is_bool($value)) {
                $valueStr = $value ? 'true' : 'false';
            } elseif (is_numeric($value)) {
                $valueStr = $value;
            } else {
                $valueStr = "'{$value}'";
            }
            $parts[] = "'{$key}' => {$valueStr}";
        }

        return implode(', ', $parts);
    }

    /**
     * Generate ADD COLUMN SQL
     *
     * @param string $table
     * @param string $column
     * @param array<string, mixed> $spec
     * @return string
     */
    protected function generateAddColumnSql(string $table, string $column, array $spec): string
    {
        $type = $spec['type'];

        if ($this->isSQLite()) {
            $columnDef = "\"{$column}\" {$type}";
        } else {
            $columnDef = "`{$column}` {$type}";
        }

        if (!empty($spec['constraint'])) {
            $columnDef .= "({$spec['constraint']})";
        }

        if (!empty($spec['unsigned']) && ! $this->isSQLite()) {
            $columnDef .= " UNSIGNED";
        }

        if (!empty($spec['auto_increment'])) {
            if ($this->isSQLite()) {
                $columnDef .= " PRIMARY KEY AUTOINCREMENT";
            } else {
                $columnDef .= " AUTO_INCREMENT";
            }
        }

        if (!empty($spec['null'])) {
            $columnDef .= " NULL";
        } else {
            $columnDef .= " NOT NULL";
        }

        if (isset($spec['default'])) {
            $default = is_string($spec['default']) ? "'{$spec['default']}'" : $spec['default'];
            $columnDef .= " DEFAULT {$default}";
        }

        if ($this->isSQLite()) {
            return "ALTER TABLE \"{$table}\" ADD COLUMN {$columnDef};";
        }

        return "ALTER TABLE `{$table}` ADD COLUMN {$columnDef};";
    }

    /**
     * Generate ADD INDEX SQL
     *
     * @param string $table
     * @param array<string, mixed> $indexSpec
     * @return string
     */
    protected function generateAddIndexSql(string $table, array $indexSpec): string
    {
        $indexName = $indexSpec['name'];
        $fields = is_array($indexSpec['fields']) ? implode('`, `', $indexSpec['fields']) : $indexSpec['fields'];
        $unique = !empty($indexSpec['unique']) ? 'UNIQUE ' : '';

        if ($this->isSQLite()) {
            $fields = is_array($indexSpec['fields']) ? implode('", "', $indexSpec['fields']) : $indexSpec['fields'];

            return sprintf(
                'CREATE %sINDEX IF NOT EXISTS "%s" ON "%s" ("%s");',
                $unique,
                $indexName,
                $table,
                $fields
            );
        }

        return "ALTER TABLE `{$table}` ADD {$unique}INDEX `{$indexName}` (`{$fields}`);";
    }

    /**
     * Get findings
     *
     * @return array<string, mixed>
     */
    public function getFindings(): array
    {
        return $this->findings;
    }

    /**
     * Validate and backfill data in existing tables
     *
     * @param array<string, mixed> $validationRules Rules for data validation
     * @return array<string, mixed> Validation results with issues found
     */
    public function validateData(array $validationRules = []): array
    {
        $issues = [];

        // Default validation rules for common tables
        $defaultRules = [
            'school_sessions' => [
                'check_null_data' => true,
                'check_timestamp' => true,
            ],
            'ci4_audit_events' => [
                'check_null_hash' => true,
                'check_created_at' => true,
            ],
        ];

        $rules = array_merge($defaultRules, $validationRules);

        foreach ($rules as $tableName => $tableRules) {
            $prefixedTable = $this->getPrefixedTableName($tableName);

            if (!$this->db->tableExists($prefixedTable)) {
                continue;
            }

            $tableIssues = $this->validateTableData($prefixedTable, $tableRules);
            if (!empty($tableIssues)) {
                $issues[$tableName] = $tableIssues;
            }
        }

        return $issues;
    }

    /**
     * Validate data in a specific table
     *
     * @param string $tableName
     * @param array<string, mixed> $rules
     * @return array<int, array<string, mixed>>
     */
    protected function validateTableData(string $tableName, array $rules): array
    {
        $issues = [];

        // Check for NULL values in non-nullable columns
        if (!empty($rules['check_null_data'])) {
            $query = $this->db->query("SELECT COUNT(*) as count FROM `{$tableName}` WHERE `data` IS NULL OR `data` = ''");
            $result = $query->getRow();
            if ($result && $result->count > 0) {
                $issues[] = [
                    'type' => 'null_data',
                    'count' => $result->count,
                    'message' => "Found {$result->count} row(s) with NULL or empty data",
                ];
            }
        }

        // Check for invalid timestamps
        if (!empty($rules['check_timestamp'])) {
            if ($this->db->fieldExists('timestamp', $tableName)) {
                $query = $this->db->query("SELECT COUNT(*) as count FROM `{$tableName}` WHERE `timestamp` = 0 OR `timestamp` IS NULL");
                $result = $query->getRow();
                if ($result && $result->count > 0) {
                    $issues[] = [
                        'type' => 'invalid_timestamp',
                        'count' => $result->count,
                        'message' => "Found {$result->count} row(s) with invalid timestamp",
                    ];
                }
            }
        }

        // Check for NULL hash values in audit tables
        if (!empty($rules['check_null_hash'])) {
            if ($this->db->fieldExists('hash_value', $tableName)) {
                $query = $this->db->query("SELECT COUNT(*) as count FROM `{$tableName}` WHERE `hash_value` IS NULL OR `hash_value` = ''");
                $result = $query->getRow();
                if ($result && $result->count > 0) {
                    $issues[] = [
                        'type' => 'null_hash',
                        'count' => $result->count,
                        'message' => "Found {$result->count} row(s) with NULL or empty hash_value",
                        'severity' => 'high',
                    ];
                }
            }
        }

        // Check for NULL created_at
        if (!empty($rules['check_created_at'])) {
            if ($this->db->fieldExists('created_at', $tableName)) {
                $query = $this->db->query("SELECT COUNT(*) as count FROM `{$tableName}` WHERE `created_at` IS NULL");
                $result = $query->getRow();
                if ($result && $result->count > 0) {
                    $issues[] = [
                        'type' => 'null_created_at',
                        'count' => $result->count,
                        'message' => "Found {$result->count} row(s) with NULL created_at",
                    ];
                }
            }
        }

        return $issues;
    }

    /**
     * Generate backfill SQL for common data issues
     *
     * @return array<int, string> SQL statements to fix data issues
     */
    public function generateBackfillSql(): array
    {
        $sql = [];

        // Backfill NULL timestamps in sessions table
        $sessionsTable = $this->getPrefixedTableName('school_sessions');
        if ($this->db->tableExists($sessionsTable)) {
            $sql[] = "UPDATE `{$sessionsTable}` SET `timestamp` = UNIX_TIMESTAMP() WHERE `timestamp` = 0 OR `timestamp` IS NULL;";
        }

        // Backfill NULL created_at with current timestamp
        foreach (['ci4_audit_events', 'idempotency_keys', 'menu_overrides'] as $table) {
            $prefixedTable = $this->getPrefixedTableName($table);
            if ($this->db->tableExists($prefixedTable) && $this->db->fieldExists('created_at', $prefixedTable)) {
                $sql[] = "UPDATE `{$prefixedTable}` SET `created_at` = NOW() WHERE `created_at` IS NULL;";
            }
        }

        return $sql;
    }

    /**
     * Apply backfill operations
     *
     * @param array<int, string> $sqlStatements
     * @return array<int, array<string, mixed>> Results with success/error for each statement
     */
    public function applyBackfill(array $sqlStatements): array
    {
        $results = [];

        $this->db->transStart();

        foreach ($sqlStatements as $sql) {
            try {
                $query = $this->db->query($sql);
                if (is_object($query) && method_exists($query, 'getRowCount')) {
                    $affectedRows = (int) $query->getRowCount();
                } else {
                    $affectedRows = (int) $this->db->affectedRows();
                }
                $results[] = [
                    'sql' => $sql,
                    'success' => true,
                    'affected_rows' => $affectedRows,
                ];
            } catch (\Exception $e) {
                $results[] = [
                    'sql' => $sql,
                    'success' => false,
                    'error' => $e->getMessage(),
                ];
            }
        }

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            $this->db->transRollback();
        }

        return $results;
    }
}
