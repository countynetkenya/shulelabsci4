<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Migration_Create_okr_tables extends CI_Migration
{
    public function up()
    {
        $this->load->dbforge();

        $this->createObjectivesTable();
        $this->createKeyResultsTable();
        $this->createLogsTable();
        $this->ensureLogColumns();
    }

    public function down()
    {
        $this->load->dbforge();

        if ($this->db->table_exists('okr_logs')) {
            $this->dbforge->drop_table('okr_logs');
        }

        if ($this->db->table_exists('okr_key_results')) {
            $this->dbforge->drop_table('okr_key_results');
        }

        if ($this->db->table_exists('okr_objectives')) {
            $this->dbforge->drop_table('okr_objectives');
        }
    }

    private function createObjectivesTable(): void
    {
        if ($this->db->table_exists('okr_objectives')) {
            return;
        }

        $this->dbforge->add_field([
            'okrObjectiveID' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => TRUE,
                'auto_increment' => TRUE,
            ],
            'schoolID' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => TRUE,
            ],
            'ownerType' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
            ],
            'ownerID' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => TRUE,
                'default'    => 0,
            ],
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => 191,
            ],
            'description' => [
                'type'    => 'TEXT',
                'null'    => TRUE,
                'default' => NULL,
            ],
            'start_date' => [
                'type'    => 'DATE',
                'null'    => TRUE,
                'default' => NULL,
            ],
            'end_date' => [
                'type'    => 'DATE',
                'null'    => TRUE,
                'default' => NULL,
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'active',
            ],
            'progress_cached' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'default'    => 0,
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
        $this->dbforge->add_key('okrObjectiveID', TRUE);
        $this->dbforge->add_key('schoolID');
        $this->dbforge->add_key(['ownerType', 'ownerID']);
        $this->dbforge->create_table('okr_objectives');
    }

    private function createKeyResultsTable(): void
    {
        if ($this->db->table_exists('okr_key_results')) {
            return;
        }

        $this->dbforge->add_field([
            'okrKeyResultID' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => TRUE,
                'auto_increment' => TRUE,
            ],
            'okrObjectiveID' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => TRUE,
            ],
            'schoolID' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => TRUE,
            ],
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => 191,
            ],
            'description' => [
                'type'    => 'TEXT',
                'null'    => TRUE,
                'default' => NULL,
            ],
            'unit' => [
                'type'       => 'VARCHAR',
                'constraint' => 25,
                'null'       => TRUE,
                'default'    => NULL,
            ],
            'target_value' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'default'    => 0,
            ],
            'current_value' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'default'    => 0,
            ],
            'weight' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'default'    => 1,
            ],
            'data_source' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'default'    => 'manual',
            ],
            'data_config' => [
                'type'    => 'TEXT',
                'null'    => TRUE,
                'default' => NULL,
            ],
            'progress_cached' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'default'    => 0,
            ],
            'last_computed_at' => [
                'type'    => 'DATETIME',
                'null'    => TRUE,
                'default' => NULL,
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'active',
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
        $this->dbforge->add_key('okrKeyResultID', TRUE);
        $this->dbforge->add_key('okrObjectiveID');
        $this->dbforge->add_key('schoolID');
        $this->dbforge->create_table('okr_key_results');
    }

    private function createLogsTable(): void
    {
        if ($this->db->table_exists('okr_logs')) {
            return;
        }

        $this->dbforge->add_field([
            'okrLogID' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => TRUE,
                'auto_increment' => TRUE,
            ],
            'okrObjectiveID' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => TRUE,
                'null'       => TRUE,
                'default'    => NULL,
            ],
            'okrKeyResultID' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => TRUE,
                'null'       => TRUE,
                'default'    => NULL,
            ],
            'schoolID' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => TRUE,
            ],
            'entry_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'default'    => '',
                'null'       => FALSE,
            ],
            'message' => [
                'type'    => 'TEXT',
                'null'    => TRUE,
                'default' => NULL,
            ],
            'progress' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'null'       => TRUE,
                'default'    => NULL,
            ],
            'payload' => [
                'type'    => 'LONGTEXT',
                'null'    => TRUE,
                'default' => NULL,
            ],
            'created_by_userID' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => TRUE,
                'null'       => TRUE,
                'default'    => NULL,
            ],
            'created_by_usertypeID' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => TRUE,
                'null'       => TRUE,
                'default'    => NULL,
            ],
            'created_at' => [
                'type'    => 'DATETIME',
                'null'    => FALSE,
                'default' => 'CURRENT_TIMESTAMP',
            ],
        ]);
        $this->dbforge->add_key('okrLogID', TRUE);
        $this->dbforge->add_key('okrObjectiveID');
        $this->dbforge->add_key('okrKeyResultID');
        $this->dbforge->add_key('schoolID');
        $this->dbforge->create_table('okr_logs');
    }

    private function ensureLogColumns(): void
    {
        if (! $this->db->table_exists('okr_logs')) {
            return;
        }

        if (! $this->db->field_exists('entry_type', 'okr_logs')) {
            $this->dbforge->add_column('okr_logs', [
                'entry_type' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                    'default'    => '',
                    'null'       => FALSE,
                ],
            ]);
        }

        if (! $this->db->field_exists('message', 'okr_logs')) {
            $this->dbforge->add_column('okr_logs', [
                'message' => [
                    'type'    => 'TEXT',
                    'null'    => TRUE,
                    'default' => NULL,
                ],
            ]);
        }

        if (! $this->db->field_exists('created_at', 'okr_logs')) {
            $this->dbforge->add_column('okr_logs', [
                'created_at' => [
                    'type'    => 'DATETIME',
                    'null'    => FALSE,
                    'default' => 'CURRENT_TIMESTAMP',
                ],
            ]);
        } else {
            $this->dbforge->modify_column('okr_logs', [
                'created_at' => [
                    'name'    => 'created_at',
                    'type'    => 'DATETIME',
                    'null'    => FALSE,
                    'default' => 'CURRENT_TIMESTAMP',
                ],
            ]);
        }
    }
}
