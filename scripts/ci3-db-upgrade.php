#!/usr/bin/env php
<?php
/**
 * CI3 Database Upgrade Standalone Script
 * 
 * Audits and upgrades CI3 database for CI4 compatibility without requiring Spark
 * 
 * Usage:
 *   php ci4/scripts/ci3-db-upgrade.php --dsn="mysql:host=localhost;dbname=shulelabs" --user=root --pass=secret [--apply]
 *   php ci4/scripts/ci3-db-upgrade.php --dsn="mysql:host=localhost;dbname=shulelabs" --user=root --pass=secret --include-experimental
 */

// Parse command line arguments
$options = getopt('', [
    'dsn:',
    'user:',
    'pass:',
    'apply',
    'include-experimental',
    'format::',
    'help',
]);

if (isset($options['help']) || empty($options['dsn']) || empty($options['user'])) {
    echo <<<HELP

CI3 Database Upgrade Tool
=========================

Audits and upgrades CI3 database schema for CI4 compatibility.

Usage:
  php ci4/scripts/ci3-db-upgrade.php [options]

Options:
  --dsn=<string>              Database DSN (required)
                              Example: mysql:host=localhost;dbname=shulelabs
  --user=<string>             Database username (required)
  --pass=<string>             Database password (optional)
  --apply                     Apply schema changes (default: dry-run)
  --include-experimental      Include experimental/feature-flagged modules
  --format=<table|json>       Output format (default: table)
  --help                      Show this help message

Examples:
  # Audit only (dry-run)
  php ci4/scripts/ci3-db-upgrade.php --dsn="mysql:host=localhost;dbname=shulelabs" --user=root --pass=secret

  # Apply changes
  php ci4/scripts/ci3-db-upgrade.php --dsn="mysql:host=localhost;dbname=shulelabs" --user=root --pass=secret --apply

  # Include experimental features
  php ci4/scripts/ci3-db-upgrade.php --dsn="mysql:host=localhost;dbname=shulelabs" --user=root --pass=secret --include-experimental

HELP;
    exit(0);
}

$dsn = $options['dsn'];
$user = $options['user'];
$pass = $options['pass'] ?? '';
$apply = isset($options['apply']);
$includeExperimental = isset($options['include-experimental']);
$format = $options['format'] ?? 'table';

echo "\n";
echo "CI3 Database Upgrade Tool\n";
echo str_repeat('=', 50) . "\n\n";

// Connect to database
try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    echo "✓ Connected to database\n\n";
} catch (PDOException $e) {
    echo "✗ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

if (!$apply) {
    echo "DRY RUN MODE: No changes will be applied\n";
    echo "(Use --apply flag to apply changes)\n\n";
} else {
    echo "WARNING: This will modify your database schema!\n";
    echo "Please ensure you have a backup before proceeding.\n\n";
    
    echo "Are you sure you want to continue? (yes/no): ";
    $handle = fopen("php://stdin", "r");
    $line = trim(fgets($handle));
    fclose($handle);
    
    if (strtolower($line) !== 'yes') {
        echo "Upgrade cancelled.\n";
        exit(0);
    }
    echo "\n";
}

// Define required tables
$requiredTables = [
    'menu_overrides' => [
        'columns' => [
            'id' => 'INT UNSIGNED AUTO_INCREMENT',
            'override_type' => 'VARCHAR(50)',
            'menuName' => 'VARCHAR(100)',
            'link' => 'VARCHAR(255)',
            'priority' => 'INT DEFAULT 0',
            'status' => 'TINYINT DEFAULT 1',
            'created_at' => 'DATETIME NULL',
            'updated_at' => 'DATETIME NULL',
        ],
        'primary_key' => 'id',
        'indexes' => [
            'idx_link_status' => ['link', 'status'],
        ],
    ],
    'audit_events' => [
        'columns' => [
            'id' => 'BIGINT UNSIGNED AUTO_INCREMENT',
            'event_key' => 'VARCHAR(191)',
            'event_type' => 'VARCHAR(100)',
            'tenant_id' => 'VARCHAR(64) NULL',
            'actor_id' => 'VARCHAR(64) NULL',
            'ip_address' => 'VARCHAR(45) NULL',
            'user_agent' => 'TEXT NULL',
            'request_uri' => 'TEXT NULL',
            'before_state' => 'LONGTEXT NULL',
            'after_state' => 'LONGTEXT NULL',
            'metadata_json' => 'LONGTEXT NULL',
            'previous_hash' => 'VARCHAR(255) NULL',
            'hash_value' => 'VARCHAR(255)',
            'created_at' => 'DATETIME',
        ],
        'primary_key' => 'id',
        'indexes' => [
            'idx_event_key' => ['event_key'],
            'idx_actor_id' => ['actor_id'],
            'idx_created_at' => ['created_at'],
        ],
    ],
    'idempotency_keys' => [
        'columns' => [
            'id' => 'BIGINT UNSIGNED AUTO_INCREMENT',
            'idempotency_key' => 'VARCHAR(191)',
            'scope' => 'VARCHAR(100)',
            'response_data' => 'LONGTEXT NULL',
            'status' => 'VARCHAR(50)',
            'created_at' => 'DATETIME',
            'expires_at' => 'DATETIME',
        ],
        'primary_key' => 'id',
        'indexes' => [
            'idx_key_scope' => ['idempotency_key', 'scope', 'UNIQUE'],
        ],
    ],
    'school_sessions' => [
        'columns' => [
            'id' => 'VARCHAR(128)',
            'ip_address' => 'VARCHAR(45)',
            'timestamp' => 'INT UNSIGNED DEFAULT 0',
            'data' => 'BLOB',
        ],
        'primary_key' => 'id',
        'indexes' => [
            'ci_sessions_timestamp' => ['timestamp'],
        ],
    ],
];

if ($includeExperimental) {
    echo "Including experimental tables (OKR, etc.)\n\n";
    
    $requiredTables['okr_objectives'] = [
        'columns' => [
            'id' => 'BIGINT UNSIGNED AUTO_INCREMENT',
            'title' => 'VARCHAR(255)',
            'description' => 'TEXT NULL',
            'owner_id' => 'INT UNSIGNED',
            'parent_id' => 'BIGINT UNSIGNED NULL',
            'start_date' => 'DATE',
            'end_date' => 'DATE',
            'status' => 'VARCHAR(50) DEFAULT "active"',
            'progress' => 'DECIMAL(5,2) DEFAULT 0',
            'created_at' => 'DATETIME NULL',
            'updated_at' => 'DATETIME NULL',
        ],
        'primary_key' => 'id',
        'indexes' => [
            'idx_owner_id' => ['owner_id'],
            'idx_status' => ['status'],
        ],
    ];
    
    $requiredTables['okr_key_results'] = [
        'columns' => [
            'id' => 'BIGINT UNSIGNED AUTO_INCREMENT',
            'objective_id' => 'BIGINT UNSIGNED',
            'title' => 'VARCHAR(255)',
            'description' => 'TEXT NULL',
            'target_value' => 'DECIMAL(10,2)',
            'current_value' => 'DECIMAL(10,2) DEFAULT 0',
            'unit' => 'VARCHAR(50)',
            'status' => 'VARCHAR(50) DEFAULT "active"',
            'created_at' => 'DATETIME NULL',
            'updated_at' => 'DATETIME NULL',
        ],
        'primary_key' => 'id',
        'indexes' => [
            'idx_objective_id' => ['objective_id'],
        ],
    ];
}

// Audit database
echo "Scanning database schema...\n\n";

$findings = [
    'missing_tables' => [],
    'missing_columns' => [],
    'missing_indexes' => [],
];

foreach ($requiredTables as $tableName => $spec) {
    // Check if table exists
    $stmt = $pdo->query("SHOW TABLES LIKE '{$tableName}'");
    $tableExists = $stmt->fetch() !== false;
    
    if (!$tableExists) {
        $findings['missing_tables'][] = ['table' => $tableName, 'spec' => $spec];
    } else {
        // Check columns
        $stmt = $pdo->query("SHOW COLUMNS FROM `{$tableName}`");
        $existingColumns = [];
        while ($row = $stmt->fetch()) {
            $existingColumns[] = $row['Field'];
        }
        
        foreach ($spec['columns'] as $columnName => $columnDef) {
            if (!in_array($columnName, $existingColumns)) {
                $findings['missing_columns'][] = [
                    'table' => $tableName,
                    'column' => $columnName,
                    'definition' => $columnDef,
                ];
            }
        }
        
        // Check indexes
        if (!empty($spec['indexes'])) {
            $stmt = $pdo->query("SHOW INDEX FROM `{$tableName}`");
            $existingIndexes = [];
            while ($row = $stmt->fetch()) {
                $existingIndexes[] = $row['Key_name'];
            }
            
            foreach ($spec['indexes'] as $indexName => $fields) {
                if (!in_array($indexName, $existingIndexes)) {
                    $findings['missing_indexes'][] = [
                        'table' => $tableName,
                        'index' => $indexName,
                        'fields' => $fields,
                    ];
                }
            }
        }
    }
}

// Display results
$totalIssues = count($findings['missing_tables']) + count($findings['missing_columns']) + count($findings['missing_indexes']);

if ($format === 'json') {
    echo json_encode($findings, JSON_PRETTY_PRINT) . "\n";
} else {
    // Display as table
    if (!empty($findings['missing_tables'])) {
        echo "Missing Tables:\n";
        echo str_repeat('-', 50) . "\n";
        foreach ($findings['missing_tables'] as $missing) {
            echo "  - {$missing['table']} (" . count($missing['spec']['columns']) . " columns)\n";
        }
        echo "\n";
    }
    
    if (!empty($findings['missing_columns'])) {
        echo "Missing Columns:\n";
        echo str_repeat('-', 50) . "\n";
        foreach ($findings['missing_columns'] as $missing) {
            echo "  - {$missing['table']}.{$missing['column']} ({$missing['definition']})\n";
        }
        echo "\n";
    }
    
    if (!empty($findings['missing_indexes'])) {
        echo "Missing Indexes:\n";
        echo str_repeat('-', 50) . "\n";
        foreach ($findings['missing_indexes'] as $missing) {
            $fields = implode(', ', array_filter($missing['fields'], 'is_string'));
            echo "  - {$missing['table']}.{$missing['index']} ({$fields})\n";
        }
        echo "\n";
    }
}

if ($totalIssues === 0) {
    echo "✓ Database schema is fully compatible!\n";
    exit(0);
}

echo "Found {$totalIssues} compatibility issue(s)\n\n";

if (!$apply) {
    echo "Run with --apply flag to fix these issues.\n";
    exit(0);
}

// Apply fixes
echo "Applying fixes...\n\n";

$pdo->beginTransaction();

try {
    // Create missing tables
    foreach ($findings['missing_tables'] as $missing) {
        $tableName = $missing['table'];
        $spec = $missing['spec'];
        
        $sql = "CREATE TABLE `{$tableName}` (\n";
        $columnDefs = [];
        
        foreach ($spec['columns'] as $name => $def) {
            $columnDefs[] = "  `{$name}` {$def}";
        }
        
        if (!empty($spec['primary_key'])) {
            $columnDefs[] = "  PRIMARY KEY (`{$spec['primary_key']}`)";
        }
        
        $sql .= implode(",\n", $columnDefs);
        $sql .= "\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        
        $pdo->exec($sql);
        echo "  ✓ Created table: {$tableName}\n";
    }
    
    // Add missing columns
    foreach ($findings['missing_columns'] as $missing) {
        $sql = "ALTER TABLE `{$missing['table']}` ADD COLUMN `{$missing['column']}` {$missing['definition']};";
        $pdo->exec($sql);
        echo "  ✓ Added column: {$missing['table']}.{$missing['column']}\n";
    }
    
    // Add missing indexes
    foreach ($findings['missing_indexes'] as $missing) {
        $fields = array_filter($missing['fields'], 'is_string');
        $unique = in_array('UNIQUE', $missing['fields']) ? 'UNIQUE ' : '';
        $fieldList = implode('`, `', $fields);
        
        $sql = "ALTER TABLE `{$missing['table']}` ADD {$unique}INDEX `{$missing['index']}` (`{$fieldList}`);";
        $pdo->exec($sql);
        echo "  ✓ Added index: {$missing['table']}.{$missing['index']}\n";
    }
    
    $pdo->commit();
    echo "\n✓ Database upgrade completed successfully!\n";
    
} catch (Exception $e) {
    $pdo->rollBack();
    echo "\n✗ Error during upgrade: " . $e->getMessage() . "\n";
    exit(1);
}
