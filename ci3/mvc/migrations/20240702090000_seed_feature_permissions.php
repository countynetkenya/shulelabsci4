<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Seed_feature_permissions extends CI_Migration
{
    public function up()
    {
        if (!$this->db->table_exists('permissions')) {
            if (function_exists('log_message')) {
                log_message('debug', 'Skipping feature permission seed because permissions table is missing.');
            }
            return;
        }

        $permissionMap = [
            'okr.view' => 'View OKRs',
            'cfr.view' => 'View CFR entries',
            'finance.view_statement' => 'View unified finance statement',
            'payroll.view' => 'View payroll',
        ];

        $permissionIds = [];
        foreach ($permissionMap as $name => $description) {
            $existing = $this->db->get_where('permissions', ['name' => $name])->row();
            if ($existing) {
                $permissionIds[$name] = (int) $existing->permissionID;

                if (isset($existing->description) && $existing->description !== $description && $description !== '') {
                    $this->db->where('permissionID', $existing->permissionID)
                        ->update('permissions', ['description' => $description]);
                }

                continue;
            }

            $this->db->insert('permissions', [
                'name' => $name,
                'description' => $description,
                'active' => 'yes',
            ]);

            $permissionId = (int) $this->db->insert_id();
            if ($permissionId > 0) {
                $permissionIds[$name] = $permissionId;
            }
        }

        if (empty($permissionIds)) {
            return;
        }

        if (!$this->db->table_exists('permission_relationships')) {
            if (function_exists('log_message')) {
                log_message('debug', 'Skipping permission relationship seeding because permission_relationships table is missing.');
            }
            return;
        }

        $fields = $this->db->list_fields('permission_relationships');
        $hasSchoolId = in_array('schoolID', $fields, true);

        foreach ($permissionIds as $permissionId) {
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

            $existingRelationship = $this->db->get_where('permission_relationships', $relationship)->row();
            if ($existingRelationship) {
                continue;
            }

            $this->db->insert('permission_relationships', $relationship);
        }
    }

    public function down()
    {
        if (!$this->db->table_exists('permissions')) {
            return;
        }

        $permissionNames = [
            'okr.view',
            'cfr.view',
            'finance.view_statement',
            'payroll.view',
        ];

        $permissionIds = [];
        $permissions = $this->db->where_in('name', $permissionNames)->get('permissions')->result();
        foreach ($permissions as $permission) {
            $permissionIds[] = (int) $permission->permissionID;
        }

        if (!empty($permissionIds) && $this->db->table_exists('permission_relationships')) {
            $this->db->where_in('permission_id', $permissionIds)->delete('permission_relationships');
        }

        if (!empty($permissionIds)) {
            $this->db->where_in('permissionID', $permissionIds)->delete('permissions');
        }
    }
}
