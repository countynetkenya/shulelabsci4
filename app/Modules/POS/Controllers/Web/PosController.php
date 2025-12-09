<?php

namespace App\Modules\POS\Controllers\Web;

use App\Controllers\BaseController;
use App\Modules\POS\Services\PosService;

class PosController extends BaseController
{
    protected $service;

    public function __construct()
    {
        $this->service = new PosService();
    }

    public function index()
    {
        $schoolId = session()->get('school_id') ?? 1;
        $data['products'] = $this->service->getAll($schoolId);
        return view('App\Modules\POS\Views\index', $data);
    }

    public function create()
    {
        return view('App\Modules\POS\Views\create');
    }

    public function store()
    {
        $schoolId = session()->get('school_id') ?? 1;
        
        if (!$this->validate([
            'name' => 'required|min_length[3]',
            'price' => 'required|numeric',
            'stock' => 'required|integer'
        ])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'school_id' => $schoolId,
            'name' => $this->request->getPost('name'),
            'price' => $this->request->getPost('price'),
            'stock' => $this->request->getPost('stock'),
            'description' => $this->request->getPost('description'),
        ];

        $this->service->create($data);

        return redirect()->to('/pos')->with('message', 'Product created successfully');
    }

    public function edit($id)
    {
        $schoolId = session()->get('school_id') ?? 1;
        $data['product'] = $this->service->getById($id, $schoolId);
        
        if (!$data['product']) {
            return redirect()->to('/pos')->with('error', 'Product not found');
        }

        return view('App\Modules\POS\Views\edit', $data);
    }

    public function update($id)
    {
        $schoolId = session()->get('school_id') ?? 1;

        if (!$this->validate([
            'name' => 'required|min_length[3]',
            'price' => 'required|numeric',
            'stock' => 'required|integer'
        ])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'name' => $this->request->getPost('name'),
            'price' => $this->request->getPost('price'),
            'stock' => $this->request->getPost('stock'),
            'description' => $this->request->getPost('description'),
        ];

        $this->service->update($id, $data, $schoolId);

        return redirect()->to('/pos')->with('message', 'Product updated successfully');
    }

    public function delete($id)
    {
        $schoolId = session()->get('school_id') ?? 1;
        $this->service->delete($id, $schoolId);
        return redirect()->to('/pos')->with('message', 'Product deleted successfully');
    }
}
