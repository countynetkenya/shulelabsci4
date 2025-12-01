<?php

declare(strict_types=1);

namespace Tests\Ci4\Foundation;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\SQLite3\Connection as SQLiteConnection;
use PHPUnit\Framework\TestCase;

abstract class FoundationDatabaseTestCase extends TestCase
{
    protected BaseConnection $db;

    /**
     * @var list<string>
     */
    private const TABLES = [
        'audit_events',
        'audit_seals',
        'ledger_entries',
        'ledger_transactions',
        'ledger_period_locks',
        'integration_dispatches',
        'qr_scans',
        'qr_tokens',
        'maker_checker_requests',
        'tenant_catalog',
        'example_records',
        'users',
        'roles',
        'user_roles',
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

    private function createSchema(): void
    {
        $prefix = $this->db->getPrefix();

        foreach (self::TABLES as $table) {
            $this->db->simpleQuery(sprintf('DROP TABLE IF EXISTS %s%s', $prefix, $table));
        }

        $this->db->simpleQuery(<<<SQL
CREATE TABLE {$prefix}audit_events (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    event_key VARCHAR(191) NOT NULL,
    event_type VARCHAR(100) NOT NULL,
    school_id INTEGER,
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
        $this->db->simpleQuery("CREATE INDEX IF NOT EXISTS {$prefix}audit_events_event_key_idx ON {$prefix}audit_events(event_key)");
        $this->db->simpleQuery("CREATE INDEX IF NOT EXISTS {$prefix}audit_events_created_idx ON {$prefix}audit_events(created_at)");

        $this->db->simpleQuery(<<<SQL
CREATE TABLE {$prefix}audit_seals (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    seal_date DATE NOT NULL,
    hash_value VARCHAR(255) NOT NULL,
    sealed_at DATETIME NOT NULL
)
SQL);
        $this->db->simpleQuery("CREATE UNIQUE INDEX IF NOT EXISTS {$prefix}audit_seals_date_uq ON {$prefix}audit_seals(seal_date)");

        $this->db->simpleQuery(<<<SQL
CREATE TABLE {$prefix}ledger_transactions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    transaction_key VARCHAR(191) NOT NULL,
    school_id INTEGER,
    currency_code CHAR(3) NOT NULL,
    transacted_at DATETIME NOT NULL,
    metadata_json TEXT,
    created_at DATETIME NOT NULL
)
SQL);
        $this->db->simpleQuery("CREATE UNIQUE INDEX IF NOT EXISTS {$prefix}ledger_transactions_key_uq ON {$prefix}ledger_transactions(transaction_key)");
        $this->db->simpleQuery("CREATE INDEX IF NOT EXISTS {$prefix}ledger_transactions_transacted_idx ON {$prefix}ledger_transactions(transacted_at)");

        $this->db->simpleQuery(<<<SQL
CREATE TABLE {$prefix}ledger_entries (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    transaction_id INTEGER NOT NULL,
    account_code VARCHAR(100) NOT NULL,
    direction VARCHAR(6) NOT NULL,
    amount DECIMAL(18,4) NOT NULL,
    memo VARCHAR(255),
    created_at DATETIME NOT NULL
)
SQL);
        $this->db->simpleQuery("CREATE INDEX IF NOT EXISTS {$prefix}ledger_entries_transaction_idx ON {$prefix}ledger_entries(transaction_id)");

        $this->db->simpleQuery(<<<SQL
CREATE TABLE {$prefix}ledger_period_locks (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    school_id INTEGER,
    period_start DATE NOT NULL,
    period_end DATE NOT NULL,
    locked_at DATETIME NOT NULL,
    locked_by VARCHAR(64)
)
SQL);
        $this->db->simpleQuery("CREATE UNIQUE INDEX IF NOT EXISTS {$prefix}ledger_period_locks_unique ON {$prefix}ledger_period_locks(school_id, period_start, period_end)");

        $this->db->simpleQuery(<<<SQL
CREATE TABLE {$prefix}integration_dispatches (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    channel VARCHAR(100) NOT NULL,
    school_id INTEGER,
    idempotency_key VARCHAR(191) NOT NULL,
    payload_json TEXT,
    response_json TEXT,
    status VARCHAR(32) NOT NULL,
    error_message TEXT,
    retry_after INTEGER,
    queued_at DATETIME NOT NULL,
    completed_at DATETIME,
    failed_at DATETIME
)
SQL);
        $this->db->simpleQuery("CREATE UNIQUE INDEX IF NOT EXISTS {$prefix}integration_dispatches_idem_uq ON {$prefix}integration_dispatches(channel, idempotency_key)");
        $this->db->simpleQuery("CREATE INDEX IF NOT EXISTS {$prefix}integration_dispatches_status_idx ON {$prefix}integration_dispatches(status)");

        $this->db->simpleQuery(<<<SQL
CREATE TABLE {$prefix}qr_tokens (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    resource_type VARCHAR(100) NOT NULL,
    resource_id VARCHAR(100) NOT NULL,
    school_id INTEGER,
    token VARCHAR(191) NOT NULL,
    issued_at DATETIME NOT NULL,
    expires_at DATETIME
)
SQL);
        $this->db->simpleQuery("CREATE UNIQUE INDEX IF NOT EXISTS {$prefix}qr_tokens_token_uq ON {$prefix}qr_tokens(token)");
        $this->db->simpleQuery("CREATE INDEX IF NOT EXISTS {$prefix}qr_tokens_resource_idx ON {$prefix}qr_tokens(resource_id)");

        $this->db->simpleQuery(<<<SQL
CREATE TABLE {$prefix}qr_scans (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    token_id INTEGER NOT NULL,
    scanned_at DATETIME NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    metadata TEXT
)
SQL);
        $this->db->simpleQuery("CREATE INDEX IF NOT EXISTS {$prefix}qr_scans_token_idx ON {$prefix}qr_scans(token_id)");

        $this->db->simpleQuery(<<<SQL
CREATE TABLE {$prefix}maker_checker_requests (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    school_id INTEGER,
    action_key VARCHAR(150) NOT NULL,
    status VARCHAR(32) NOT NULL,
    payload_json TEXT NOT NULL,
    maker_id VARCHAR(64),
    checker_id VARCHAR(64),
    rejection_reason TEXT,
    submitted_at DATETIME NOT NULL,
    processed_at DATETIME
)
SQL);
        $this->db->simpleQuery("CREATE INDEX IF NOT EXISTS {$prefix}maker_checker_requests_action_idx ON {$prefix}maker_checker_requests(action_key)");
        $this->db->simpleQuery("CREATE INDEX IF NOT EXISTS {$prefix}maker_checker_requests_status_idx ON {$prefix}maker_checker_requests(status)");

        $this->db->simpleQuery(<<<SQL
CREATE TABLE {$prefix}tenant_catalog (
    id VARCHAR(64) NOT NULL,
    tenant_type VARCHAR(32) NOT NULL,
    name VARCHAR(191) NOT NULL,
    metadata TEXT,
    created_at DATETIME NOT NULL,
    updated_at DATETIME,
    PRIMARY KEY(id, tenant_type)
)
SQL);
        $this->db->simpleQuery("CREATE INDEX IF NOT EXISTS {$prefix}tenant_catalog_type_idx ON {$prefix}tenant_catalog(tenant_type)");

        $this->db->simpleQuery(<<<SQL
CREATE TABLE {$prefix}example_records (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(191) NOT NULL,
    deleted_at DATETIME,
    deleted_by VARCHAR(64),
    delete_reason TEXT,
    updated_at DATETIME
)
SQL);

        $this->db->simpleQuery(<<<SQL
CREATE TABLE {$prefix}users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username VARCHAR(100) UNIQUE NOT NULL,
    email VARCHAR(191) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(191) NOT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME
)
SQL);

        $this->db->simpleQuery(<<<SQL
CREATE TABLE {$prefix}roles (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    role_name VARCHAR(100) NOT NULL,
    role_slug VARCHAR(100) UNIQUE NOT NULL,
    ci3_usertype_id INTEGER NOT NULL,
    description TEXT,
    created_at DATETIME NOT NULL
)
SQL);

        $this->db->simpleQuery(<<<SQL
CREATE TABLE {$prefix}user_roles (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    role_id INTEGER NOT NULL,
    created_at DATETIME NOT NULL,
    FOREIGN KEY (user_id) REFERENCES {$prefix}users(id) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES {$prefix}roles(id) ON DELETE CASCADE
)
SQL);
    }
}
