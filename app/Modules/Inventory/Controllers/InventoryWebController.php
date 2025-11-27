<?php

namespace Modules\Inventory\Controllers;

use App\Controllers\BaseController;
use Modules\Inventory\Models\InventoryItemModel;
use Modules\Inventory\Models\InventoryCategoryModel;

class InventoryWebController extends BaseController
{
    protected $itemModel;
    protected $categoryModel;

    public function __construct()
    {
        $this->itemModel = new InventoryItemModel();
        $this->categoryModel = new InventoryCategoryModel();
    }

    public function index()
    {
        $data = [
            'title' => 'Inventory Management',
            'items' => $this->itemModel->select('inventory_items.*, inventory_categories.name as category_name')
                                     ->join('inventory_categories', 'inventory_items.category_id = inventory_categories.id')
                                     ->findAll(),
        ];

        return view('modules/inventory/index', $data);
    }

    public function create()
    {
        $data = [
            'title' => 'Add New Item',
            'categories' => $this->categoryModel->findAll(),
        ];

        return view('modules/inventory/create', $data);
    }

    public function store()
    {
        $rules = [
            'name' => 'required|max_length[150]',
            'category_id' => 'required|integer',
            'quantity' => 'required|integer',
            'unit_cost' => 'required|decimal',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->itemModel->save([
            'name' => $this->request->getPost('name'),
            'category_id' => $this->request->getPost('category_id'),
            'sku' => $this->request->getPost('sku'),
            'type' => $this->request->getPost('type'),
            'quantity' => $this->request->getPost('quantity'),
            'unit_cost' => $this->request->getPost('unit_cost'),
            'reorder_level' => $this->request->getPost('reorder_level'),
            'location' => $this->request->getPost('location'),
        ]);

        return redirect()->to('/inventory/items')->with('message', 'Item created successfully');
    }
}
