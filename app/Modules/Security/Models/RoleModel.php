<?php

namespace App\Modules\Security\Models;

use CodeIgniter\Model;

/**
 * RoleModel - Manages user roles.
 */
class RoleModel extends Model
{
    protected $table = 'roles';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;

    protected $allowedFields = [
        'school_id', 'name', 'slug', 'description', 'is_system', 'parent_role_id', 'level',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $casts = [
        'id' => 'int',
        'school_id' => '?int',
        'is_system' => 'bool',
        'parent_role_id' => '?int',
        'level' => 'int',
    ];

    /**
     * Get roles by school or system roles.
     */
    public function getBySchool(?int $schoolId = null): array
    {
        return $this->groupStart()
            ->where('school_id', $schoolId)
            ->orWhere('is_system', 1)
            ->groupEnd()
            ->orderBy('level', 'DESC')
            ->findAll();
    }

    /**
     * Get role by slug.
     */
    public function getBySlug(string $slug, ?int $schoolId = null): ?array
    {
        $builder = $this->where('slug', $slug);
        if ($schoolId !== null) {
            $builder->groupStart()
                ->where('school_id', $schoolId)
                ->orWhere('school_id IS NULL')
                ->groupEnd();
        }
        return $builder->first();
    }
}
