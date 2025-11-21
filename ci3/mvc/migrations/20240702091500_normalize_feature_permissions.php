<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Normalize_feature_permissions extends CI_Migration
{
    public function up()
    {
        if (!$this->db->table_exists('permissions')) {
            return;
        }

        $modules = [
            'okr' => [
                'old_keys' => ['okr.view'],
                'base_description' => 'OKR module',
                'action_description' => 'View OKRs',
            ],
            'cfr' => [
                'old_keys' => ['cfr.view'],
                'base_description' => 'CFR module',
                'action_description' => 'View CFR entries',
            ],
            'finance_statement' => [
                'old_keys' => ['finance.view_statement'],
                'base_description' => 'Unified finance statement module',
                'action_description' => 'View unified finance statement',
            ],
            'payroll' => [
                'old_keys' => ['payroll.view'],
                'base_description' => 'Payroll module',
                'action_description' => 'View payroll',
            ],
        ];

        foreach ($modules as $module => $config) {
            $baseName = $module;
            $viewName = $module . '_view';

            $baseId = $this->ensurePermission($baseName, $config['base_description']);
            $viewId = $this->ensurePermission($viewName, $config['action_description']);

            $oldPermissionIds = $this->getPermissionIds($config['old_keys']);

            $this->migrateRelationships($baseId, $viewId, $oldPermissionIds);

            $this->removePermissionsByName($config['old_keys']);
        }
    }

    public function down()
    {
        if (!$this->db->table_exists('permissions')) {
            return;
        }

        $modules = [
            'okr' => [
                'old_keys' => ['okr.view'],
                'base_description' => 'OKR module',
                'action_description' => 'View OKRs',
            ],
            'cfr' => [
                'old_keys' => ['cfr.view'],
                'base_description' => 'CFR module',
                'action_description' => 'View CFR entries',
            ],
            'finance_statement' => [
                'old_keys' => ['finance.view_statement'],
                'base_description' => 'Unified finance statement module',
                'action_description' => 'View unified finance statement',
            ],
            'payroll' => [
                'old_keys' => ['payroll.view'],
                'base_description' => 'Payroll module',
                'action_description' => 'View payroll',
            ],
        ];

        foreach ($modules as $module => $config) {
            $oldName = reset($config['old_keys']);
            $baseName = $module;
            $viewName = $module . '_view';

            $oldId = $this->ensurePermission($oldName, $config['action_description']);
            $viewId = $this->getPermissionIdByName($viewName);
            $baseId = $this->getPermissionIdByName($baseName);

            $this->restoreRelationships($viewId, $oldId);

            if ($baseId) {
                $this->removeRelationshipsByPermissionId($baseId);
            }

            $this->removePermissionsByName([$viewName, $baseName]);
        }
    }

    protected function ensurePermission($name, $description)
    {
        $permission = $this->db->get_where('permissions', ['name' => $name])->row();
        if ($permission) {
            if (isset($permission->description) && $description !== '' && $permission->description !== $description) {
                $this->db->where('permissionID', $permission->permissionID)
                    ->update('permissions', ['description' => $description]);
            }

            return (int) $permission->permissionID;
        }

        $this->db->insert('permissions', [
            'name' => $name,
            'description' => $description,
            'active' => 'yes',
        ]);

        return (int) $this->db->insert_id();
    }

    protected function getPermissionIds(array $names)
    {
        if (empty($names)) {
            return [];
        }

        $results = $this->db->where_in('name', $names)->get('permissions')->result();
        $ids = [];
        foreach ($results as $result) {
            $ids[] = (int) $result->permissionID;
        }

        return $ids;
    }

    protected function getPermissionIdByName($name)
    {
        if ($name === null || $name === '') {
            return 0;
        }

        $permission = $this->db->get_where('permissions', ['name' => $name])->row();
        return $permission ? (int) $permission->permissionID : 0;
    }

    protected function migrateRelationships($basePermissionId, $viewPermissionId, array $oldPermissionIds)
    {
        if (!$this->db->table_exists('permission_relationships')) {
            return;
        }

        if (empty($oldPermissionIds)) {
            return;
        }

        $fields = $this->db->list_fields('permission_relationships');
        $hasSchoolId = in_array('schoolID', $fields, true);

        $relationships = $this->db->where_in('permission_id', $oldPermissionIds)
            ->get('permission_relationships')
            ->result_array();

        foreach ($relationships as $relationship) {
            $payload = [
                'permission_id' => $viewPermissionId,
                'usertype_id' => $relationship['usertype_id'],
            ];

            if ($hasSchoolId && array_key_exists('schoolID', $relationship)) {
                $payload['schoolID'] = $relationship['schoolID'];
            }

            $this->ensureRelationship($payload);

            if ($basePermissionId) {
                $modulePayload = $payload;
                $modulePayload['permission_id'] = $basePermissionId;
                $this->ensureRelationship($modulePayload);
            }
        }

        $this->db->where_in('permission_id', $oldPermissionIds)
            ->delete('permission_relationships');
    }

    protected function restoreRelationships($fromPermissionId, $toPermissionId)
    {
        if (!$this->db->table_exists('permission_relationships')) {
            return;
        }

        if (!$fromPermissionId || !$toPermissionId) {
            return;
        }

        $fields = $this->db->list_fields('permission_relationships');
        $hasSchoolId = in_array('schoolID', $fields, true);

        $relationships = $this->db->get_where('permission_relationships', ['permission_id' => $fromPermissionId])
            ->result_array();

        foreach ($relationships as $relationship) {
            $payload = [
                'permission_id' => $toPermissionId,
                'usertype_id' => $relationship['usertype_id'],
            ];

            if ($hasSchoolId && array_key_exists('schoolID', $relationship)) {
                $payload['schoolID'] = $relationship['schoolID'];
            }

            $this->ensureRelationship($payload);
        }

        $this->removeRelationshipsByPermissionId($fromPermissionId);
    }

    protected function ensureRelationship(array $relationship)
    {
        if (!$this->db->table_exists('permission_relationships')) {
            return;
        }

        $existing = $this->db->get_where('permission_relationships', $relationship)->row();
        if ($existing) {
            return;
        }

        $this->db->insert('permission_relationships', $relationship);
    }

    protected function removeRelationshipsByPermissionId($permissionId)
    {
        if (!$this->db->table_exists('permission_relationships')) {
            return;
        }

        if (!$permissionId) {
            return;
        }

        $this->db->where('permission_id', $permissionId)->delete('permission_relationships');
    }

    protected function removePermissionsByName(array $names)
    {
        if (empty($names)) {
            return;
        }

        $this->db->where_in('name', $names)->delete('permissions');
    }
}
