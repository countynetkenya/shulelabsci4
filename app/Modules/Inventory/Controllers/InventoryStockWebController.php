<?php

namespace Modules\Inventory\Controllers;

use App\Controllers\BaseController;
use Modules\Inventory\Models\InventoryItemModel;
use Modules\Inventory\Models\InventoryLocationModel;
use Modules\Inventory\Services\InventoryService;
use Modules\Inventory\Services\StockService;

class InventoryStockWebController extends BaseController
{
    protected $stockService;

    protected $inventoryService;

    protected $locationModel;

    protected $itemModel;

    public function __construct()
    {
        $this->stockService = new StockService();
        $this->inventoryService = new InventoryService();
        $this->locationModel = new InventoryLocationModel();
        $this->itemModel = new InventoryItemModel();
    }

    public function index()
    {
        $page = (int) ($this->request->getGet('page') ?? 1);
        $perPage = 20;

        $data = [
            'title' => 'Stock Management',
            'stock' => $this->stockService->paginate($page, $perPage),
            'pager' => $this->stockService->getPager(),
        ];

        return view('modules/inventory/stock_list', $data);
    }

    public function transfer()
    {
        $data = [
            'title' => 'Initiate Transfer',
            'locations' => $this->locationModel->findAll(),
            'items' => $this->itemModel->findAll(),
        ];

        return view('modules/inventory/transfer_form', $data);
    }

    public function processTransfer()
    {
        $rules = [
            'item_id' => 'required|integer',
            'from_location_id' => 'required|integer',
            'to_location_id' => 'required|integer',
            'quantity' => 'required|integer|greater_than[0]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        try {
            $userId = session()->get('user_id') ?? 1; // Fallback

            $this->inventoryService->transferStock(
                (int) $this->request->getPost('item_id'),
                (int) $this->request->getPost('from_location_id'),
                (int) $this->request->getPost('to_location_id'),
                (int) $this->request->getPost('quantity'),
                (int) $userId
            );

            return redirect()->to('inventory/stock')->with('message', 'Transfer initiated successfully');

        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }
}
