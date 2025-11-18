<?php

namespace App\Controllers;

class DashboardController extends BaseController
{
    public function index(): string
    {
        $session = session();

        return view('dashboard', [
            'username' => (string) $session->get('username'),
            'role'     => (string) $session->get('role'),
        ]);
    }
}
