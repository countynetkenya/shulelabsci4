<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_audit_events extends CI_Migration {

    public function up()
    {
        $this->load->dbforge();

        if ($this->db->table_exists('audit_events')) {
            return;
        }

        $this->dbforge->add_field([
            'auditEventID' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'unsigned' => TRUE,
                'auto_increment' => TRUE,
            ],
            'event_key' => [
                'type' => 'VARCHAR',
                'constraint' => 150,
            ],
            'actor_type' => [
                'type'    => 'VARCHAR',
                'constraint' => 50,
                'null'    => TRUE,
                'default' => NULL,
            ],
            'actor_id' => [
                'type'    => 'VARCHAR',
                'constraint' => 191,
                'null'    => TRUE,
                'default' => NULL,
            ],
            'context_type' => [
                'type'    => 'VARCHAR',
                'constraint' => 100,
                'null'    => TRUE,
                'default' => NULL,
            ],
            'context_id' => [
                'type'    => 'VARCHAR',
                'constraint' => 191,
                'null'    => TRUE,
                'default' => NULL,
            ],
            'description' => [
                'type'    => 'TEXT',
                'null'    => TRUE,
                'default' => NULL,
            ],
            'metadata' => [
                'type'    => 'LONGTEXT',
                'null'    => TRUE,
                'default' => NULL,
            ],
            'ip_address' => [
                'type'    => 'VARCHAR',
                'constraint' => 45,
                'null'    => TRUE,
                'default' => NULL,
            ],
            'user_agent' => [
                'type'    => 'TEXT',
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

        $this->dbforge->add_key('auditEventID', TRUE);
        $this->dbforge->add_key('event_key');
        $this->dbforge->add_key('actor_id');
        $this->dbforge->add_key('created_at');

        $this->dbforge->create_table('audit_events', TRUE, ['ENGINE' => 'InnoDB']);

        $this->ensureTimestampDefaults();
    }

    public function down()
    {
        $this->load->dbforge();

        if ($this->db->table_exists('audit_events')) {
            $this->dbforge->drop_table('audit_events', TRUE);
        }
    }

    private function ensureTimestampDefaults(): void
    {
        if (!$this->db->table_exists('audit_events')) {
            return;
        }

        $columns = [];

        if ($this->db->field_exists('created_at', 'audit_events')) {
            $columns['created_at'] = [
                'name'    => 'created_at',
                'type'    => 'DATETIME',
                'null'    => FALSE,
                'default' => 'CURRENT_TIMESTAMP',
            ];
        }

        if ($this->db->field_exists('updated_at', 'audit_events')) {
            $columns['updated_at'] = [
                'name'    => 'updated_at',
                'type'    => 'DATETIME',
                'null'    => TRUE,
                'default' => NULL,
            ];
        }

        if (!empty($columns)) {
            $this->dbforge->modify_column('audit_events', $columns);
        }
    }
}
