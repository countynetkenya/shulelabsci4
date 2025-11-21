<?php

namespace App\Controllers;

/**
 * Admin Controller
 *
 * Admin panel for system administration
 */
class Admin extends BaseController
{
    protected $data = [];

    public function __construct()
    {
        helper(['compatibility']);
    }

    /**
     * Admin panel index
     *
     * @return string
     */
    public function index()
    {
        $session = session();

        // Get admin info
        $this->data['user'] = [
            'name' => $session->get('name'),
            'email' => $session->get('email'),
            'usertypeID' => $session->get('usertypeID'),
            'photo' => $session->get('photo')
        ];

        return view('admin/index', $this->data);
    }
}
