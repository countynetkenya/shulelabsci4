<?php

namespace App\Controllers;

class LoginController extends BaseController
{
    /**
     * Render the login form.
     */
    public function index()
    {
        return view('login');
    }

    /**
     * Basic login handler placeholder.
     */
    public function authenticate()
    {
        $username = trim((string) $this->request->getPost('username'));
        $password = trim((string) $this->request->getPost('password'));

        if ($username === '' || $password === '') {
            return redirect()->back()->withInput()->with('error', 'Username and password are required.');
        }

        log_message('info', 'Login attempt recorded for user: ' . $username);

        return redirect()->back()->with('message', 'Login attempt recorded. Replace this with real authentication logic.');
    }
}
