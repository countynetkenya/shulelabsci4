<?php

namespace Modules\Foundation\Controllers;

use App\Controllers\BaseController;
use Modules\Foundation\Services\RolesService;

class RolesController extends BaseController
{
    protected $rolesService;

    public function __construct()
    {
        $this->rolesService = new RolesService();
    }

    public function index()
    {
        $roles = $this->rolesService->getAllRoles();
        return view('Modules\Foundation\Views\roles\index', ['roles' => $roles]);
    }

    public function create()
    {
        return view('Modules\Foundation\Views\roles\create');
    }

    public function store()
    {
        $rules = [
            'role_name' => 'required|min_length[3]',
            'role_slug' => 'required|alpha_dash|is_unique[roles.role_slug]',
            'description' => 'permit_empty|string',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->rolesService->createRole($this->request->getPost());

        return redirect()->to('/system/roles')->with('success', 'Role created successfully.');
    }

    public function edit($id)
    {
        $role = $this->rolesService->getRoleById($id);
        if (!$role) {
            return redirect()->to('/system/roles')->with('error', 'Role not found.');
        }
        return view('Modules\Foundation\Views\roles\edit', ['role' => $role]);
    }

    public function update($id)
    {
        $rules = [
            'role_name' => 'required|min_length[3]',
            'role_slug' => "required|alpha_dash|is_unique[roles.role_slug,id,{$id}]",
            'description' => 'permit_empty|string',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->rolesService->updateRole($id, $this->request->getPost());

        return redirect()->to('/system/roles')->with('success', 'Role updated successfully.');
    }

    public function delete($id)
    {
        $this->rolesService->deleteRole($id);
        return redirect()->to('/system/roles')->with('success', 'Role deleted successfully.');
    }
}
