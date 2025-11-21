<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;

/**
 * Schema Version Tracking Service
 *
 * Tracks applied schema migrations and provides rollback capability
 */
class SchemaVersionService
{
    /** @var \CodeIgniter\Database\BaseConnection<mixed, mixed> */
    protected BaseConnection $db;
    protected string $versionTable = 'ci4_schema_versions';

    /**
     * @param \CodeIgniter\Database\BaseConnection<mixed, mixed> $db
     */
    public function __construct(BaseConnection $db)
    {
        $this->db = $db;
        $this->ensureVersionTableExists();
    }

    /**
     * Ensure the schema version tracking table exists
     */
    protected function ensureVersionTableExists(): void
    {
        if (!$this->db->tableExists($this->versionTable)) {
            $forge = \Config\Database::forge();

            $forge->addField([
                'id' => [
                    'type' => 'INT',
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'version' => [
                    'type' => 'VARCHAR',
                    'constraint' => 50,
                ],
                'description' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                ],
                'operations' => [
                    'type' => 'LONGTEXT',
                    'null' => true,
                ],
                'backup_file' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                ],
                'applied_at' => [
                    'type' => 'DATETIME',
                ],
                'applied_by' => [
                    'type' => 'VARCHAR',
                    'constraint' => 100,
                    'null' => true,
                ],
                'status' => [
                    'type' => 'VARCHAR',
                    'constraint' => 20,
                    'default' => 'applied',
                ],
            ]);

            $forge->addKey('id', true);
            $forge->addKey('version');
            $forge->addKey('applied_at');

            $forge->createTable($this->versionTable, true);

            log_message('info', 'Created schema version tracking table: ' . $this->versionTable);
        }
    }

    /**
     * Record a schema migration
     *
     * @param string $version Version identifier
     * @param string $description Human-readable description
     * @param array<int, array<string, mixed>> $operations List of SQL operations performed
     * @param string|null $backupFile Path to backup file
     * @return int Version ID
     */
    public function recordMigration(
        string $version,
        string $description,
        array $operations = [],
        ?string $backupFile = null
    ): int {
        $data = [
            'version' => $version,
            'description' => $description,
            'operations' => json_encode($operations),
            'backup_file' => $backupFile,
            'applied_at' => date('Y-m-d H:i:s'),
            'applied_by' => get_current_user(),
            'status' => 'applied',
        ];

        $this->db->table($this->versionTable)->insert($data);

        return $this->db->insertID();
    }

    /**
     * Get all applied migrations
     *
     * @return array<int, array<string, mixed>>
     */
    public function getAppliedMigrations(): array
    {
        return $this->db->table($this->versionTable)
            ->orderBy('applied_at', 'DESC')
            ->get()
            ->getResultArray();
    }

    /**
     * Get the last applied migration
     *
     * @return array<string, mixed>|null
     */
    public function getLastMigration(): ?array
    {
        $result = $this->db->table($this->versionTable)
            ->orderBy('id', 'DESC')
            ->limit(1)
            ->get()
            ->getRowArray();

        return $result ?: null;
    }

    /**
     * Check if a version has been applied
     *
     * @param string $version
     * @return bool
     */
    public function hasVersion(string $version): bool
    {
        $count = $this->db->table($this->versionTable)
            ->where('version', $version)
            ->where('status', 'applied')
            ->countAllResults();

        return $count > 0;
    }

    /**
     * Mark a migration as rolled back
     *
     * @param int $versionId
     * @return bool
     */
    public function markAsRolledBack(int $versionId): bool
    {
        return $this->db->table($this->versionTable)
            ->where('id', $versionId)
            ->update([
                'status' => 'rolled_back',
            ]);
    }

    /**
     * Get migration details by ID
     *
     * @param int $versionId
     * @return array<string, mixed>|null
     */
    public function getMigrationById(int $versionId): ?array
    {
        $result = $this->db->table($this->versionTable)
            ->where('id', $versionId)
            ->get()
            ->getRowArray();

        return $result ?: null;
    }

    /**
     * Get operations from a migration
     *
     * @param int $versionId
     * @return array<int, array<string, mixed>>
     */
    public function getMigrationOperations(int $versionId): array
    {
        $migration = $this->getMigrationById($versionId);

        if (!$migration || empty($migration['operations'])) {
            return [];
        }

        return json_decode($migration['operations'], true) ?: [];
    }
}
