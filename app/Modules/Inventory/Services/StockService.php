<?php

namespace Modules\Inventory\Services;

use Modules\Inventory\Models\InventoryStockModel;

class StockService
{
    protected $stockModel;

    public function __construct()
    {
        $this->stockModel = new InventoryStockModel();
    }

    /**
     * Paginate stock records.
     *
     * @param int $page
     * @param int $perPage
     * @return array
     */
    public function paginate(int $page = 1, int $perPage = 20): array
    {
        return $this->stockModel->select('inventory_stock.*, inventory_items.name as item_name, inventory_locations.name as location_name')
                                ->join('inventory_items', 'inventory_items.id = inventory_stock.item_id')
                                ->join('inventory_locations', 'inventory_locations.id = inventory_stock.location_id')
                                ->paginate($perPage, 'default', $page);
    }

    /**
     * Get stock by location.
     *
     * @param int $locationId
     * @return array
     */
    public function getByLocation(int $locationId): array
    {
        return $this->stockModel->where('location_id', $locationId)->findAll();
    }

    /**
     * Get the pager.
     *
     * @return \CodeIgniter\Pager\Pager
     */
    public function getPager()
    {
        return $this->stockModel->pager;
    }
}
