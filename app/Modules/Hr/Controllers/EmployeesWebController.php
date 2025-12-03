<?php

declare(strict_types=1);

namespace Modules\Hr\Controllers;

use App\Controllers\BaseController;
use Modules\Hr\Services\EmployeesService;

class EmployeesWebController extends BaseController
{
    protected EmployeesService $service;

    public function __construct()
    {
        $this->service = new EmployeesService();
    }

    public function index()
    {
        $page = (int) ($this->request->getGet('page') ?? 1);
        $result = $this->service->paginate($page);

        return view('Modules\Hr\Views\employees\index', [
            'employees' => $result['data'],
            'pager'     => $result['pager'],
        ]);
    }

    public function create()
    {
        return view('Modules\Hr\Views\employees\create');
    }

    public function store()
    {
        $data = $this->request->getPost();
        
        // Basic validation handled by service/model
        $errors = $this->service->validate($data);
        if (!empty($errors)) {
            return redirect()->back()->withInput()->with('errors', $errors);
        }

        $this->service->create($data);

        return redirect()->to('/hr/employees')->with('message', 'Employee created successfully.');
    }

    public function edit(int $id)
    {
        $employee = $this->service->findById($id);
        if (!$employee) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return view('Modules\Hr\Views\employees\edit', ['employee' => $employee]);
    }

    public function update(int $id)
    {
        $data = $this->request->getPost();
        
        $errors = $this->service->validate($data, $id);
        if (!empty($errors)) {
            return redirect()->back()->withInput()->with('errors', $errors);
        }

        $this->service->update($id, $data);

        return redirect()->to('/hr/employees')->with('message', 'Employee updated successfully.');
    }

    public function delete(int $id)
    {
        $this->service->delete($id);
        return redirect()->to('/hr/employees')->with('message', 'Employee deleted successfully.');
    }
}
