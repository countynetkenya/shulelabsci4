<?php

namespace App\Modules\Inventory\Services;

use Modules\Inventory\Models\InventoryItemModel;

class InventoryService
{
    protected $itemModel;

    public function __construct()
    {
        $this->itemModel = new InventoryItemModel();
    }

    public function getAll(int $schoolId)
    {
        return $this->itemModel->where('school_id', $schoolId)->findAll();
    }

    public function getById(int $id, int $schoolId)
    {
        return $this->itemModel->where('school_id', $schoolId)->find($id);
    }

    public function create(array $data)
    {
        if (!isset($data['school_id'])) {
            throw new \RuntimeException('School ID is required');
        }
        $this->itemModel->insert($data);
        return $this->itemModel->getInsertID();
    }

    public function update(int $id, int $schoolId, array $data)
    {
        $item = $this->getById($id, $schoolId);
        if (!$item) {
            return false;
        }
        return $this->itemModel->update($id, $data);
    }

    public function delete(int $id, int $schoolId)
    {
        $item = $this->getById($id, $schoolId);
        if (!$item) {
            return false;
        }
        return $this->itemModel->delete($id);
    }
}
