<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Site Model.
 *
 * Handles school/site information
 * Compatible with CI3 database schema
 */
class SiteModel extends Model
{
    protected $table = 'setting';

    protected $primaryKey = 'settingID';

    protected $returnType = 'object';

    /**
     * Get site/school information.
     *
     * @param int $schoolID
     * @return object|null
     */
    public function getSite(int $schoolID = 0): ?object
    {
        $builder = $this->db->table('setting');

        if ($schoolID > 0) {
            $builder->where('schoolID', $schoolID);
        } else {
            // For super admin, get first setting or default
            // Use dynamic primary key discovery to handle missing settingID column
            $pk = $this->getSettingPrimaryKey();
            if ($pk !== null) {
                $builder->orderBy($pk, 'ASC');
            }
            $builder->limit(1);
        }

        return $builder->get()->getRow();
    }

    /**
     * Discover the primary key column for the setting table
     * Handles cases where settingID column may not exist in CI3 schema.
     *
     * @return string|null The primary key column name, or null if not found
     */
    private function getSettingPrimaryKey(): ?string
    {
        static $cached = null;

        if ($cached !== null) {
            return $cached;
        }

        try {
            // First, try to get primary key from database metadata
            $fields = $this->db->getFieldData($this->table);
            foreach ($fields as $field) {
                if (!empty($field->primary_key)) {
                    $cached = $field->name;
                    return $cached;
                }
            }

            // Heuristic fallback: check for common primary key column names
            foreach (['settingID', 'id', 'schoolID'] as $candidate) {
                if ($this->db->fieldExists($candidate, $this->table)) {
                    $cached = $candidate;
                    return $cached;
                }
            }
        } catch (\Exception $e) {
            log_message('error', 'Failed to discover primary key for setting table: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Get all sites for a user.
     *
     * @param string $schoolIDs Comma-separated school IDs
     * @return array<int, object>
     */
    public function getSitesForUser(string $schoolIDs): array
    {
        if (empty($schoolIDs)) {
            return [];
        }

        $schoolIDArray = explode(',', $schoolIDs);
        $schoolIDArray = array_filter(array_map('intval', $schoolIDArray));

        if (empty($schoolIDArray)) {
            return [];
        }

        $builder = $this->db->table('setting');
        return $builder
            ->whereIn('schoolID', $schoolIDArray)
            ->get()
            ->getResult();
    }
}
