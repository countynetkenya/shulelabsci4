<?php

namespace App\Modules\Inventory\Controllers\Web;

use App\Controllers\BaseController;
use App\Modules\Inventory\Services\InventoryService;

class InventoryController extends BaseController
{
    protected $inventoryService;

    public function __construct()
    {
        $this->inventoryService = new InventoryService();
    }

    public function index()
    {
        $schoolId = session()->get('school_id');
        if (!$schoolId) {
            return redirect()->to('/login')->with('error', 'Session expired');
        }

        $data['items'] = $this->inventoryService->getAll($schoolId);
        return view('App\Modules\Inventory\Views\index', $data);
    }

    public function create()
    {
        return view('App\Modules\Inventory\Views\create');
    }

    public function store()
    {
        $schoolId = session()->get('school_id');

        if (!$this->validate([
            'name' => 'required|min_length[3]',
            'type' => 'required|in_list[physical,service,bundle]',
            'unit_cost' => 'required|decimal',
        ])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'school_id' => $schoolId,
            'category_id' => 1, // Default for now, should be dynamic
            'name' => $this->request->getPost('name'),
            'sku' => $this->request->getPost('sku'),
            'type' => $this->request->getPost('type'),
            'unit_cost' => $this->request->getPost('unit_cost'),
            'reorder_level' => $this->request->getPost('reorder_level'),
            'description' => $this->request->getPost('description'),
        ];

        $this->inventoryService->create($data);

        return redirect()->to('/inventory')->with('message', 'Item created successfully');
    }

    public function edit($id)
    {
        $schoolId = session()->get('school_id');
        $item = $this->inventoryService->getById($id, $schoolId);

        if (!$item) {
            return redirect()->to('/inventory')->with('error', 'Item not found');
        }

        return view('App\Modules\Inventory\Views\edit', ['item' => $item]);
    }

    public function update($id)
    {
        $schoolId = session()->get('school_id');

        if (!$this->validate([
            'name' => 'required|min_length[3]',
            'type' => 'required|in_list[physical,service,bundle]',
            'unit_cost' => 'required|decimal',
        ])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'name' => $this->request->getPost('name'),
            'sku' => $this->request->getPost('sku'),
            'type' => $this->request->getPost('type'),
            'unit_cost' => $this->request->getPost('unit_cost'),
            'reorder_level' => $this->request->getPost('reorder_level'),
            'description' => $this->request->getPost('description'),
        ];

        $this->inventoryService->update($id, $schoolId, $data);

        return redirect()->to('/inventory')->with('message', 'Item updated successfully');
    }

    public function delete($id)
    {
        $schoolId = session()->get('school_id');
        $this->inventoryService->delete($id, $schoolId);
        return redirect()->to('/inventory')->with('message', 'Item deleted successfully');
    }
}
