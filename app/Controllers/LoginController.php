<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\HTTP\RedirectResponse;

class LoginController extends BaseController
{
    public function index(): RedirectResponse|string
    {
        if (session()->get('isLoggedIn')) {
            return redirect()->route('dashboard');
        }

        return view('login');
    }

    public function authenticate(): RedirectResponse
    {
        $validationRules = [
            'username' => 'required|min_length[3]|max_length[100]',
            'password' => 'required|min_length[6]'
        ];

        $validation = service('validation');

        if (! $validation->setRules($validationRules)->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('error', implode(' ', $validation->getErrors()));
        }

        $username = (string) $this->request->getPost('username');
        $password = (string) $this->request->getPost('password');

        $user = (new UserModel())->where('username', $username)->first();

        if (! $user || ! password_verify($password, $user['password_hash'])) {
            return redirect()->back()->withInput()->with('error', 'Invalid username or password.');
        }

        session()->regenerate();
        session()->set([
            'user_id'    => $user['id'],
            'username'   => $user['username'],
            'role'       => $user['role'],
            'isLoggedIn' => true,
        ]);

        return redirect()->route('dashboard');
    }

    public function logout(): RedirectResponse
    {
        $session = session();
        $session->destroy();

        return redirect()->route('login')->with('message', 'You have been signed out.');
    }
}
