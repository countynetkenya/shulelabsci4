<?php

namespace Modules\Foundation\Controllers;

use App\Controllers\BaseController;
use Modules\Foundation\Services\UsersService;

class UsersController extends BaseController
{
    protected $usersService;

    public function __construct()
    {
        $this->usersService = new UsersService();
    }

    public function index()
    {
        $users = $this->usersService->getAllUsers();
        return view('Modules\Foundation\Views\users\index', ['users' => $users]);
    }

    public function create()
    {
        $roles = $this->usersService->getAllRoles();
        return view('Modules\Foundation\Views\users\create', ['roles' => $roles]);
    }

    public function store()
    {
        $data = $this->request->getPost();
        
        if ($this->usersService->createUser($data)) {
            return redirect()->to('/system/users')->with('success', 'User created successfully.');
        }
        
        return redirect()->back()->withInput()->with('error', 'Failed to create user.');
    }

    public function edit($id)
    {
        $user = $this->usersService->getUserById($id);
        if (!$user) {
            return redirect()->to('/system/users')->with('error', 'User not found.');
        }
        
        $roles = $this->usersService->getAllRoles();
        return view('Modules\Foundation\Views\users\edit', ['user' => $user, 'roles' => $roles]);
    }

    public function update($id)
    {
        $data = $this->request->getPost();
        
        if ($this->usersService->updateUser($id, $data)) {
            return redirect()->to('/system/users')->with('success', 'User updated successfully.');
        }
        
        return redirect()->back()->withInput()->with('error', 'Failed to update user.');
    }

    public function delete($id)
    {
        if ($this->usersService->deleteUser($id)) {
            return redirect()->to('/system/users')->with('success', 'User deleted successfully.');
        }
        
        return redirect()->to('/system/users')->with('error', 'Failed to delete user.');
    }
}
