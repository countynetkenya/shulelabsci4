<?php

namespace App\Modules\Admin\Models;

use CodeIgniter\Model;

/**
 * AdminSettingModel - Manages system-wide and school-specific settings.
 *
 * Supports tenant-scoping through the settings table.
 */
class AdminSettingModel extends Model
{
    protected $table = 'settings';

    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType = 'array';

    protected $useSoftDeletes = false;

    protected $protectFields = true;

    protected $allowedFields = ['class', 'key', 'value', 'type', 'context'];

    // Dates
    protected $useTimestamps = true;

    protected $dateFormat = 'datetime';

    protected $createdField = 'created_at';

    protected $updatedField = 'updated_at';

    // Validation
    protected $validationRules = [
        'class' => 'required|max_length[100]',
        'key'   => 'required|max_length[100]',
        'value' => 'permit_empty',
        'type'  => 'permit_empty|in_list[string,boolean,integer,json]',
        'context' => 'permit_empty|in_list[app,user,system]',
    ];

    protected $validationMessages = [];

    protected $skipValidation = false;

    protected $cleanValidationRules = true;

    /**
     * Get settings by class (category).
     */
    public function getByClass(string $class): array
    {
        return $this->where('class', $class)->findAll();
    }

    /**
     * Get a single setting by class and key.
     */
    public function getSetting(string $class, string $key): ?array
    {
        return $this->where('class', $class)
                    ->where('key', $key)
                    ->first();
    }

    /**
     * Set or update a setting.
     */
    public function setSetting(string $class, string $key, $value, string $type = 'string', string $context = 'app'): bool
    {
        $existing = $this->getSetting($class, $key);

        $data = [
            'class'   => $class,
            'key'     => $key,
            'value'   => is_array($value) || is_object($value) ? json_encode($value) : (string) $value,
            'type'    => $type,
            'context' => $context,
        ];

        if ($existing) {
            return $this->update($existing['id'], $data);
        }

        return (bool) $this->insert($data);
    }

    /**
     * Get all unique classes (categories).
     */
    public function getClasses(): array
    {
        $results = $this->select('DISTINCT class as class_name', false)->findAll();
        return array_column($results, 'class_name');
    }
}
