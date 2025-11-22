<?php

declare(strict_types=1);

namespace Tests\Ci4\Reports;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\SQLite3\Connection as SQLiteConnection;
use PHPUnit\Framework\TestCase;

abstract class ReportsDatabaseTestCase extends TestCase
{
    protected BaseConnection $db;

    /**
     * @var list<string>
     */
    private const TABLES = [
        'reports_reports',
        'reports_templates',
        'reports_filters',
        'reports_results',
        'reports_schedules',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        if (!class_exists('SQLite3')) {
            $this->markTestSkipped('The SQLite3 extension is required for database integration tests.');
        }

        $this->ensurePathConstants();
        $this->db = new SQLiteConnection($this->connectionConfig());
        $this->db->initialize();
        $this->db->simpleQuery('PRAGMA foreign_keys = ON');

        $this->createSchema();
    }

    protected function tearDown(): void
    {
        $this->db->close();
        parent::tearDown();
    }

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
            'DBPrefix'    => 'ci4_',
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

    private function createSchema(): void
    {
        $prefix = $this->db->getPrefix();

        foreach (self::TABLES as $table) {
            $this->db->simpleQuery(sprintf('DROP TABLE IF EXISTS %s%s', $prefix, $table));
        }

        // Create reports table
        $this->db->simpleQuery(<<<SQL
CREATE TABLE {$prefix}reports_reports (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    type VARCHAR(50) NOT NULL,
    config_json TEXT NOT NULL,
    owner_id VARCHAR(64) NOT NULL,
    tenant_id VARCHAR(64) NOT NULL,
    template_ref INTEGER,
    is_public INTEGER DEFAULT 0,
    is_active INTEGER DEFAULT 1,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    deleted_at DATETIME
)
SQL);

        // Create templates table
        $this->db->simpleQuery(<<<SQL
CREATE TABLE {$prefix}reports_templates (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    category VARCHAR(100) NOT NULL,
    module VARCHAR(50) NOT NULL,
    config_json TEXT NOT NULL,
    is_system INTEGER DEFAULT 0,
    is_active INTEGER DEFAULT 1,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    deleted_at DATETIME
)
SQL);

        // Create filters table
        $this->db->simpleQuery(<<<SQL
CREATE TABLE {$prefix}reports_filters (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    report_id INTEGER NOT NULL,
    name VARCHAR(255) NOT NULL,
    filter_json TEXT NOT NULL,
    is_default INTEGER DEFAULT 0,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    FOREIGN KEY (report_id) REFERENCES {$prefix}reports_reports(id) ON DELETE CASCADE
)
SQL);

        // Create results table
        $this->db->simpleQuery(<<<SQL
CREATE TABLE {$prefix}reports_results (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    report_id INTEGER NOT NULL,
    filter_hash VARCHAR(64) NOT NULL,
    result_data TEXT NOT NULL,
    row_count INTEGER NOT NULL,
    generated_at DATETIME NOT NULL,
    expires_at DATETIME NOT NULL,
    FOREIGN KEY (report_id) REFERENCES {$prefix}reports_reports(id) ON DELETE CASCADE
)
SQL);

        // Create schedules table
        $this->db->simpleQuery(<<<SQL
CREATE TABLE {$prefix}reports_schedules (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    report_id INTEGER NOT NULL,
    name VARCHAR(255) NOT NULL,
    frequency VARCHAR(50) NOT NULL,
    format VARCHAR(20) NOT NULL,
    recipients TEXT NOT NULL,
    schedule_data TEXT,
    last_run_at DATETIME,
    next_run_at DATETIME,
    is_active INTEGER DEFAULT 1,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    FOREIGN KEY (report_id) REFERENCES {$prefix}reports_reports(id) ON DELETE CASCADE
)
SQL);
    }
}
