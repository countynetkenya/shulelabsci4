<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_idempotency_keys extends CI_Migration {

    public function up()
    {
        $this->load->dbforge();

        if (!$this->db->table_exists('idempotency_keys')) {
            $this->dbforge->add_field([
            'idempotencyKeyID' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'unsigned' => TRUE,
                'auto_increment' => TRUE,
            ],
            'idempotency_key' => [
                'type' => 'VARCHAR',
                'constraint' => 191,
            ],
            'scope' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'default' => '',
            ],
            'request_hash' => [
                'type'      => 'CHAR',
                'constraint' => 64,
                'null'      => TRUE,
                'default'   => NULL,
            ],
            'payload' => [
                'type'    => 'LONGTEXT',
                'null'    => TRUE,
                'default' => NULL,
            ],
            'response_code' => [
                'type'      => 'INT',
                'constraint' => 11,
                'null'      => TRUE,
                'default'   => NULL,
            ],
            'response_hash' => [
                'type'      => 'CHAR',
                'constraint' => 64,
                'null'      => TRUE,
                'default'   => NULL,
            ],
            'response_body' => [
                'type'    => 'LONGTEXT',
                'null'    => TRUE,
                'default' => NULL,
            ],
            'status' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'default' => 'pending',
            ],
            'locked_until' => [
                'type'    => 'DATETIME',
                'null'    => TRUE,
                'default' => NULL,
            ],
            'created_at' => [
                'type'    => 'DATETIME',
                'null'    => FALSE,
                'default' => 'CURRENT_TIMESTAMP',
            ],
            'updated_at' => [
                'type'    => 'DATETIME',
                'null'    => TRUE,
                'default' => NULL,
            ],
        ]);
            $this->dbforge->add_key('idempotencyKeyID', TRUE);
            $this->dbforge->create_table('idempotency_keys', TRUE, ['ENGINE' => 'InnoDB']);
        }

        $this->ensureTimestampDefaults();

        $table = 'idempotency_keys';
        $index = 'idempotency_keys_key_scope_unique';

        if (!$this->indexExists($table, $index)) {
            $this->db->query(
                sprintf(
                    'CREATE UNIQUE INDEX %s ON %s (idempotency_key, scope)',
                    $this->quoteIdentifier($index),
                    $this->quoteIdentifier($this->db->dbprefix($table))
                )
            );
        }
    }

    public function down()
    {
        $this->load->dbforge();
        if ($this->db->table_exists('idempotency_keys')) {
            $this->dbforge->drop_table('idempotency_keys', TRUE);
        }
    }

    protected function indexExists(string $table, string $indexName): bool
    {
        $tableName = $this->db->dbprefix($table);
        $sql = sprintf(
            'SHOW INDEX FROM %s WHERE Key_name = %s',
            $this->quoteIdentifier($tableName),
            $this->db->escape($indexName)
        );

        $query = $this->db->query($sql);
        if (!$query) {
            return false;
        }

        $exists = $query->num_rows() > 0;
        $query->free_result();

        return $exists;
    }

    protected function quoteIdentifier(string $identifier): string
    {
        return '`' . str_replace('`', '``', $identifier) . '`';
    }

    private function ensureTimestampDefaults(): void
    {
        if (!$this->db->table_exists('idempotency_keys')) {
            return;
        }

        $columns = [];

        if ($this->db->field_exists('created_at', 'idempotency_keys')) {
            $columns['created_at'] = [
                'name'    => 'created_at',
                'type'    => 'DATETIME',
                'null'    => FALSE,
                'default' => 'CURRENT_TIMESTAMP',
            ];
        }

        if ($this->db->field_exists('updated_at', 'idempotency_keys')) {
            $columns['updated_at'] = [
                'name'    => 'updated_at',
                'type'    => 'DATETIME',
                'null'    => TRUE,
                'default' => NULL,
            ];
        }

        if ($this->db->field_exists('locked_until', 'idempotency_keys')) {
            $columns['locked_until'] = [
                'name'    => 'locked_until',
                'type'    => 'DATETIME',
                'null'    => TRUE,
                'default' => NULL,
            ];
        }

        if (!empty($columns)) {
            $this->dbforge->modify_column('idempotency_keys', $columns);
        }
    }
}
