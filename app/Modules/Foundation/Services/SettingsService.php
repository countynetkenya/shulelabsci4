<?php

namespace Modules\Foundation\Services;

use Modules\Foundation\Models\SettingModel;

class SettingsService
{
    protected $model;
    protected $cache = [];

    public function __construct()
    {
        $this->model = new SettingModel();
    }

    /**
     * Get a setting value by class and key.
     *
     * @param string $class The group/category (e.g., 'mail')
     * @param string $key   The specific setting key (e.g., 'host')
     * @param mixed $default Default value if not found
     * @return mixed
     */
    public function get(string $class, string $key, $default = null)
    {
        // Check memory cache first
        if (isset($this->cache[$class][$key])) {
            return $this->cache[$class][$key];
        }

        $setting = $this->model->where('class', $class)
                               ->where('key', $key)
                               ->first();

        if (!$setting) {
            return $default;
        }

        $value = $this->castValue($setting['value'], $setting['type']);
        $this->cache[$class][$key] = $value;

        return $value;
    }

    /**
     * Set a setting value.
     *
     * @param string $class
     * @param string $key
     * @param mixed $value
     * @param string $type (string, boolean, integer, json)
     * @return bool
     */
    public function set(string $class, string $key, $value, string $type = 'string'): bool
    {
        $existing = $this->model->where('class', $class)
                                ->where('key', $key)
                                ->first();

        $data = [
            'class' => $class,
            'key'   => $key,
            'value' => $this->prepareValue($value, $type),
            'type'  => $type,
        ];

        if ($existing) {
            $this->model->update($existing['id'], $data);
        } else {
            $this->model->insert($data);
        }

        // Update cache
        $this->cache[$class][$key] = $value;

        return true;
    }

    /**
     * Get all settings for a specific class (group).
     *
     * @param string $class
     * @return array
     */
    public function getGroup(string $class): array
    {
        $settings = $this->model->where('class', $class)->findAll();
        $result = [];

        foreach ($settings as $setting) {
            $result[$setting['key']] = $this->castValue($setting['value'], $setting['type']);
        }

        return $result;
    }

    private function castValue($value, $type)
    {
        switch ($type) {
            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            case 'integer':
                return (int) $value;
            case 'json':
                return json_decode($value, true);
            default:
                return $value;
        }
    }

    private function prepareValue($value, $type)
    {
        switch ($type) {
            case 'boolean':
                return $value ? '1' : '0';
            case 'json':
                return json_encode($value);
            default:
                return (string) $value;
        }
    }
}
