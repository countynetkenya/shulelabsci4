<?php

declare(strict_types=1);

namespace Tests\Ci4\Compat;

use App\Services\DatabaseCompatibilityService;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\SQLite3\Connection as SQLiteConnection;
use PHPUnit\Framework\TestCase;

class DatabaseCompatibilityServiceTest extends TestCase
{
    protected BaseConnection $db;
    protected DatabaseCompatibilityService $service;

    protected function setUp(): void
    {
        parent::setUp();

        if (!class_exists('SQLite3')) {
            $this->markTestSkipped('The SQLite3 extension is required for database tests.');
        }

        $this->ensurePathConstants();
        $this->db = new SQLiteConnection($this->connectionConfig());
        $this->db->initialize();
        $this->service = new DatabaseCompatibilityService($this->db);
    }

    protected function tearDown(): void
    {
        $this->db->close();
        parent::tearDown();
    }

    public function testAuditDetectsMissingTables(): void
    {
        // Don't create any tables - all should be missing
        $findings = $this->service->audit();

        $this->assertNotEmpty($findings['missing_tables']);
        
        // Check that required tables are detected as missing
        $missingTableNames = array_column($findings['missing_tables'], 'table');
        $this->assertContains('audit_events', $missingTableNames);
        $this->assertContains('school_sessions', $missingTableNames);
        $this->assertContains('idempotency_keys', $missingTableNames);
    }

    public function testAuditDetectsMissingColumns(): void
    {
        // Create audit_events table but with missing columns
        $this->db->query('CREATE TABLE audit_events (id INTEGER PRIMARY KEY)');

        $findings = $this->service->audit();

        $this->assertNotEmpty($findings['missing_columns']);
        
        // Check that missing columns are detected
        $missingColumns = array_filter($findings['missing_columns'], function ($item) {
            return $item['table'] === 'audit_events';
        });
        
        $this->assertNotEmpty($missingColumns);
    }

    public function testAuditPassesWhenSchemaIsComplete(): void
    {
        // Create all required tables with all columns
        $this->createCompleteSchema();

        $findings = $this->service->audit();

        // Should have no missing tables or columns
        $this->assertEmpty($findings['missing_tables']);
        $this->assertEmpty($findings['missing_columns']);
    }

    public function testGenerateSqlPatchesForMissingColumns(): void
    {
        // Create table but missing a column
        $this->db->query('CREATE TABLE school_sessions (id VARCHAR(128) PRIMARY KEY)');

        $this->service->audit();
        $patches = $this->service->generateSqlPatches();

        $this->assertNotEmpty($patches);
        
        // Check that SQL contains ALTER TABLE statements
        $hasAlterTable = false;
        foreach ($patches as $sql) {
            if (stripos($sql, 'ALTER TABLE') !== false) {
                $hasAlterTable = true;
                break;
            }
        }
        
        $this->assertTrue($hasAlterTable, 'Should generate ALTER TABLE statements');
    }

    public function testAddExperimentalTablesAddsOkrTables(): void
    {
        $this->service->addExperimentalTables();
        $findings = $this->service->audit();

        $missingTableNames = array_column($findings['missing_tables'], 'table');
        $this->assertContains('okr_objectives', $missingTableNames);
        $this->assertContains('okr_key_results', $missingTableNames);
    }

    public function testGenerateMigrationContent(): void
    {
        // Don't create any tables - all should be missing
        $findings = $this->service->audit();

        $this->assertNotEmpty($findings['missing_tables']);

        // Generate migrations to a temp directory
        $tempDir = sys_get_temp_dir() . '/test_migrations_' . uniqid();
        mkdir($tempDir);

        try {
            $generated = $this->service->generateMigrations($tempDir);

            $this->assertNotEmpty($generated);

            // Check that migration files were created
            foreach ($generated as $file) {
                $this->assertFileExists($file);
                $content = file_get_contents($file);
                
                // Verify migration structure
                $this->assertStringContainsString('namespace App\Database\Migrations', $content);
                $this->assertStringContainsString('extends Migration', $content);
                $this->assertStringContainsString('public function up()', $content);
                $this->assertStringContainsString('public function down()', $content);
            }
        } finally {
            // Cleanup
            array_map('unlink', glob($tempDir . '/*'));
            rmdir($tempDir);
        }
    }

    /**
     * Create complete schema for testing
     */
    protected function createCompleteSchema(): void
    {
        // School sessions table
        $this->db->query(<<<SQL
CREATE TABLE school_sessions (
    id VARCHAR(128) PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL,
    timestamp INTEGER NOT NULL DEFAULT 0,
    data BLOB NOT NULL
)
SQL);

        // Audit events table
        $this->db->query(<<<SQL
CREATE TABLE audit_events (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    event_key VARCHAR(191) NOT NULL,
    event_type VARCHAR(100) NOT NULL,
    tenant_id VARCHAR(64),
    actor_id VARCHAR(64),
    ip_address VARCHAR(45),
    user_agent TEXT,
    request_uri TEXT,
    before_state TEXT,
    after_state TEXT,
    metadata_json TEXT,
    previous_hash VARCHAR(255),
    hash_value VARCHAR(255) NOT NULL,
    created_at DATETIME NOT NULL
)
SQL);

        // Idempotency keys table
        $this->db->query(<<<SQL
CREATE TABLE idempotency_keys (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    idempotency_key VARCHAR(191) NOT NULL,
    scope VARCHAR(100) NOT NULL,
    response_data TEXT,
    status VARCHAR(50) NOT NULL,
    created_at DATETIME NOT NULL,
    expires_at DATETIME NOT NULL
)
SQL);

        // Menu overrides table
        $this->db->query(<<<SQL
CREATE TABLE menu_overrides (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    override_type VARCHAR(50) NOT NULL,
    menuName VARCHAR(100) NOT NULL,
    link VARCHAR(255) NOT NULL,
    priority INTEGER DEFAULT 0,
    status TINYINT DEFAULT 1,
    created_at DATETIME,
    updated_at DATETIME
)
SQL);
    }

    /**
     * Ensure path constants are defined
     */
    private function ensurePathConstants(): void
    {
        $monorepoRoot = dirname(__DIR__, 3);
        $embeddedCi4Root = $monorepoRoot . DIRECTORY_SEPARATOR . 'ci4';
        $standaloneRoot = dirname(__DIR__, 2);

        if (is_dir($embeddedCi4Root . '/app')) {
            $rootPath = $monorepoRoot . DIRECTORY_SEPARATOR;
            $ci4Root = $embeddedCi4Root;
        } else {
            $rootPath = $standaloneRoot . DIRECTORY_SEPARATOR;
            $ci4Root = $standaloneRoot;
        }

        if (!defined('ROOTPATH')) {
            define('ROOTPATH', $rootPath);
        }

        if (!defined('APPPATH')) {
            define('APPPATH', rtrim($ci4Root, DIRECTORY_SEPARATOR) . '/app/');
        }

        if (!defined('WRITEPATH')) {
            define('WRITEPATH', rtrim($ci4Root, DIRECTORY_SEPARATOR) . '/writable/');
        }

        if (!defined('SYSTEMPATH')) {
            $vendorRoots = [
                rtrim($ci4Root, DIRECTORY_SEPARATOR) . '/vendor',
                $rootPath . 'vendor',
            ];

            $systemPath = null;

            foreach ($vendorRoots as $vendorRoot) {
                $candidate = rtrim($vendorRoot, DIRECTORY_SEPARATOR) . '/codeigniter4/framework/system/';
                if (is_dir($candidate)) {
                    $systemPath = $candidate;
                    break;
                }
            }

            if ($systemPath === null) {
                throw new \RuntimeException('Unable to locate the CodeIgniter 4 system directory for tests.');
            }

            define('SYSTEMPATH', $systemPath);
        }

        if (!function_exists('service')) {
            require_once SYSTEMPATH . 'Common.php';
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function connectionConfig(): array
    {
        return [
            'database'    => ':memory:',
            'DBDriver'    => 'SQLite3',
            'DBPrefix'    => '',
            'DBDebug'     => true,
            'charset'     => 'utf8',
            'DBCollat'    => '',
            'swapPre'     => '',
            'encrypt'     => false,
            'compress'    => false,
            'strictOn'    => false,
            'failover'    => [],
            'port'        => 3306,
            'foreignKeys' => true,
            'busyTimeout' => 1000,
            'dateFormat'  => [
                'date'     => 'Y-m-d',
                'datetime' => 'Y-m-d H:i:s',
                'time'     => 'H:i:s',
            ],
        ];
    }
}
