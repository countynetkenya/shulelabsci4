<?php if ( !defined('BASEPATH') ) {
    exit('No direct script access allowed');
}

class Menu_override_m extends MY_Model
{
    protected $_table_name = 'menu_overrides';
    protected $_primary_key = 'menuOverrideID';
    protected $_primary_filter = 'intval';
    protected $_order_by = 'priority asc, menuOverrideID asc';

    public function __construct()
    {
        parent::__construct();
    }

    public function get_menu_overrides($array = null)
    {
        if ($array !== null) {
            return parent::get_order_by($array);
        }

        return parent::get_order_by();
    }

    public function get_menu_override($array)
    {
        return parent::get_single($array);
    }

    public function get_single_override($id)
    {
        return parent::get($id, true);
    }

    public function insert_override($data)
    {
        $payload = $this->prepare_payload($data);
        $payload['created_at'] = date('Y-m-d H:i:s');
        $payload['updated_at'] = $payload['created_at'];
        parent::insert($payload);
        return true;
    }

    public function update_override($data, $id)
    {
        $payload = $this->prepare_payload($data);
        $payload['updated_at'] = date('Y-m-d H:i:s');
        parent::update($payload, $id);
        return $id;
    }

    public function delete_override($id)
    {
        parent::delete($id);
    }

    public function get_grouped_overrides()
    {
        $records = $this->get_menu_overrides();
        $grouped = [
            'custom_nodes' => [],
            'relocations' => [],
        ];

        if (!customCompute($records)) {
            return $grouped;
        }

        foreach ($records as $record) {
            if (isset($record->status) && (int) $record->status === 0) {
                continue;
            }
            $formatted = $this->format_override($record);
            if ($record->override_type === 'relocation') {
                $grouped['relocations'][] = $formatted;
            } else {
                $grouped['custom_nodes'][] = $formatted;
            }
        }

        return $grouped;
    }

    public function format_override($record)
    {
        if (is_array($record)) {
            $record = (object) $record;
        }

        $override = [
            'menuName' => $record->menuName,
            'priority' => (int) $record->priority,
            'status' => (int) $record->status,
        ];

        if (!empty($record->parent)) {
            $override['parent'] = $record->parent;
        }

        if (!empty($record->link)) {
            $override['link'] = $record->link;
        }

        if (!empty($record->icon)) {
            $override['icon'] = $record->icon;
        }

        if (!empty($record->skip_permission)) {
            $override['skip_permission'] = true;
        }

        $createIfMissing = $this->decode_create_if_missing($record->create_if_missing);
        if ($createIfMissing !== null) {
            $override['create_if_missing'] = $createIfMissing;
        }

        $metadata = $this->decode_notes_metadata($record->notes);
        if ($metadata !== null) {
            $override = array_merge($override, $metadata);
        }

        return $override;
    }

    public function decode_create_if_missing($value)
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_array($value) || is_bool($value)) {
            return $value;
        }

        if ($value === '0' || $value === 0) {
            return false;
        }

        if ($value === '1' || $value === 1) {
            return true;
        }

        $decoded = json_decode($value, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $decoded;
        }

        return $value;
    }

    private function prepare_payload($data)
    {
        $payload = [];
        $payload['override_type'] = isset($data['override_type']) && $data['override_type'] === 'relocation' ? 'relocation' : 'custom';
        $payload['menuName'] = isset($data['menuName']) ? trim($data['menuName']) : '';
        $payload['parent'] = isset($data['parent']) && $data['parent'] !== '' ? trim($data['parent']) : null;
        $payload['link'] = isset($data['link']) && $data['link'] !== '' ? trim($data['link']) : null;
        $payload['icon'] = isset($data['icon']) && $data['icon'] !== '' ? trim($data['icon']) : null;
        $payload['priority'] = isset($data['priority']) && is_numeric($data['priority']) ? (int) $data['priority'] : 0;
        $payload['status'] = isset($data['status']) && is_numeric($data['status']) ? (int) $data['status'] : 1;
        $payload['skip_permission'] = !empty($data['skip_permission']) ? 1 : 0;
        $payload['create_if_missing'] = $this->encode_create_if_missing(isset($data['create_if_missing']) ? $data['create_if_missing'] : null);
        $payload['notes'] = isset($data['notes']) && $data['notes'] !== '' ? $data['notes'] : null;

        return $payload;
    }

    private function encode_create_if_missing($value)
    {
        if ($value === null || $value === '' || $value === false) {
            return null;
        }

        if ($value === true || $value === 1 || $value === '1') {
            return json_encode(true);
        }

        if (is_array($value)) {
            return json_encode($value);
        }

        $trimmed = trim($value);
        if ($trimmed === '') {
            return json_encode(true);
        }

        $decoded = json_decode($trimmed, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return json_encode($decoded);
        }

        return $trimmed;
    }

    private function decode_notes_metadata($notes)
    {
        if ($notes === null || $notes === '') {
            return null;
        }

        if (is_array($notes)) {
            return $notes;
        }

        $decoded = json_decode($notes, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
            return null;
        }

        return $decoded;
    }
}
