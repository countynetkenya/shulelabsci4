<?php

namespace Modules\Inventory\Services;

use Modules\Inventory\Models\InventoryItemModel;

class ItemsService
{
    protected $itemModel;

    public function __construct()
    {
        $this->itemModel = new InventoryItemModel();
    }

    public function paginate(int $page = 1, int $perPage = 20): array
    {
        return $this->itemModel->paginate($perPage, 'default', $page);
    }

    public function findById(int $id)
    {
        return $this->itemModel->find($id);
    }

    public function validate(array $data, ?int $id = null): array
    {
        // Basic validation rules
        $rules = [
            'name' => 'required|min_length[3]',
            'sku' => 'required|is_unique[inventory_items.sku,id,' . ($id ?? 0) . ']',
            'category_id' => 'required|integer',
        ];
        
        $validation = \Config\Services::validation();
        $validation->setRules($rules);

        if (!$validation->run($data)) {
            return $validation->getErrors();
        }

        return [];
    }

    public function create(array $data)
    {
        return $this->itemModel->insert($data);
    }

    public function update(int $id, array $data)
    {
        return $this->itemModel->update($id, $data);
    }

    public function delete(int $id)
    {
        return $this->itemModel->delete($id);
    }
}
