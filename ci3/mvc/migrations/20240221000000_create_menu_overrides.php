<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_menu_overrides extends CI_Migration {

    public function up()
    {
        $this->load->dbforge();

        if (!$this->db->table_exists('menu_overrides')) {
            $this->dbforge->add_field([
                'menuOverrideID' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => TRUE,
                    'auto_increment' => TRUE,
                ],
                'override_type' => [
                    'type' => 'VARCHAR',
                    'constraint' => 20,
                    'default' => 'custom',
                ],
                'menuName' => [
                    'type' => 'VARCHAR',
                    'constraint' => 100,
                ],
                'parent' => [
                    'type'    => 'VARCHAR',
                    'constraint' => 100,
                    'null'    => TRUE,
                    'default' => NULL,
                ],
                'link' => [
                    'type'    => 'VARCHAR',
                    'constraint' => 255,
                    'null'    => TRUE,
                    'default' => NULL,
                ],
                'icon' => [
                    'type'    => 'VARCHAR',
                    'constraint' => 100,
                    'null'    => TRUE,
                    'default' => NULL,
                ],
                'priority' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'default' => 0,
                ],
                'status' => [
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 1,
                ],
                'skip_permission' => [
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 0,
                ],
                'create_if_missing' => [
                    'type'    => 'TEXT',
                    'null'    => TRUE,
                    'default' => NULL,
                ],
                'notes' => [
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
            $this->dbforge->add_key('menuOverrideID', TRUE);
            $this->dbforge->create_table('menu_overrides');
        }

        $this->ensureMenuOverrideTimestamps();

        $this->config->load('menu_overrides', TRUE);
        $customNodes = $this->config->item('menu_custom_nodes', 'menu_overrides');
        $relocations = $this->config->item('menu_relocations', 'menu_overrides');

        $existing = $this->db->get('menu_overrides')->result();
        if (empty($existing)) {
            $now = date('Y-m-d H:i:s');
            $seed = [];

            if (is_array($customNodes)) {
                foreach ($customNodes as $node) {
                    $seed[] = $this->mapConfigForInsert('custom', $node, $now);
                }
            }

            if (is_array($relocations)) {
                foreach ($relocations as $relocation) {
                    $seed[] = $this->mapConfigForInsert('relocation', $relocation, $now);
                }
            }

            if (!empty($seed)) {
                $this->db->insert_batch('menu_overrides', $seed);
            }
        }

        $this->ensureDefaultOverrideEntry($customNodes);
        $this->ensurePermissions();
    }

    public function down()
    {
        $this->load->dbforge();

        if ($this->db->table_exists('menu_overrides')) {
            $this->dbforge->drop_table('menu_overrides');
        }
    }

    private function mapConfigForInsert($type, $config, $timestamp)
    {
        $createIfMissing = NULL;
        if (array_key_exists('create_if_missing', $config)) {
            $value = $config['create_if_missing'];
            if (is_array($value) || is_bool($value) || $value === 0 || $value === 1) {
                $createIfMissing = json_encode($value);
            } elseif ($value !== NULL) {
                $createIfMissing = (string) $value;
            }
        }

        return [
            'override_type' => $type,
            'menuName' => isset($config['menuName']) ? $config['menuName'] : '',
            'parent' => isset($config['parent']) ? $config['parent'] : NULL,
            'link' => isset($config['link']) ? $config['link'] : NULL,
            'icon' => isset($config['icon']) ? $config['icon'] : NULL,
            'priority' => isset($config['priority']) ? (int) $config['priority'] : 0,
            'status' => isset($config['status']) ? (int) $config['status'] : 1,
            'skip_permission' => !empty($config['skip_permission']) ? 1 : 0,
            'create_if_missing' => $createIfMissing,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ];
    }

    private function ensureDefaultOverrideEntry($customNodes)
    {
        if (!is_array($customNodes)) {
            return;
        }

        foreach ($customNodes as $node) {
            if (isset($node['menuName']) && $node['menuName'] === 'menuoverrides') {
                $existing = $this->db->get_where('menu_overrides', ['menuName' => 'menuoverrides'])->row();
                if (!$existing) {
                    $timestamp = date('Y-m-d H:i:s');
                    $payload = $this->mapConfigForInsert('custom', $node, $timestamp);
                    $this->db->insert('menu_overrides', $payload);
                }
                break;
            }
        }
    }

    private function ensurePermissions()
    {
        $permissions = [
            'menuoverrides' => 'Menu Overrides',
            'menuoverrides_add' => 'Menu Overrides Add',
            'menuoverrides_edit' => 'Menu Overrides Edit',
            'menuoverrides_delete' => 'Menu Overrides Delete',
        ];

        $relationshipFields = $this->db->list_fields('permission_relationships');
        $hasSchoolId = in_array('schoolID', $relationshipFields, true);

        foreach ($permissions as $name => $description) {
            $permission = $this->db->get_where('permissions', ['name' => $name])->row();
            if ($permission) {
                $permissionId = (int) $permission->permissionID;
            } else {
                $this->db->insert('permissions', [
                    'description' => $description,
                    'name' => $name,
                    'active' => 'yes',
                ]);
                $permissionId = (int) $this->db->insert_id();
            }

            if ($permissionId <= 0) {
                continue;
            }

            $relationship = [
                'permission_id' => $permissionId,
                'usertype_id' => 1,
            ];

            if ($hasSchoolId) {
                $relationship['schoolID'] = 0;
            }

            $existsQuery = $this->db->get_where('permission_relationships', $relationship)->row();
            if (!$existsQuery) {
                $this->db->insert('permission_relationships', $relationship);
            }
        }
    }

    private function ensureMenuOverrideTimestamps(): void
    {
        if (!$this->db->table_exists('menu_overrides')) {
            return;
        }

        $columns = [];

        if ($this->db->field_exists('created_at', 'menu_overrides')) {
            $columns['created_at'] = [
                'name'    => 'created_at',
                'type'    => 'DATETIME',
                'null'    => FALSE,
                'default' => 'CURRENT_TIMESTAMP',
            ];
        }

        if ($this->db->field_exists('updated_at', 'menu_overrides')) {
            $columns['updated_at'] = [
                'name'    => 'updated_at',
                'type'    => 'DATETIME',
                'null'    => TRUE,
                'default' => NULL,
            ];
        }

        if (!empty($columns)) {
            $this->dbforge->modify_column('menu_overrides', $columns);
        }
    }
}
