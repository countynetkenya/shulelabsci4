<?php

namespace App\Controllers;

/**
 * Dashboard Controller.
 *
 * Main dashboard for authenticated users
 */
class Dashboard extends BaseController
{
    protected $data = [];

    public function __construct()
    {
        helper(['compatibility']);
    }

    /**
     * Dashboard index.
     *
     * @return string
     */
    public function index()
    {
        $session = session();

        // Get user info
        $this->data['user'] = [
            'name' => $session->get('name'),
            'email' => $session->get('email'),
            'usertypeID' => $session->get('usertypeID'),
            'schoolID' => $session->get('schoolID'),
            'photo' => $session->get('photo'),
        ];

        // Get usertype name
        $usertypeID = (int) $session->get('usertypeID');
        $usertypes = [
            0 => 'Super Admin',
            1 => 'Admin',
            2 => 'Teacher',
            3 => 'Student',
            4 => 'Parent',
            5 => 'Accountant',
            6 => 'Librarian',
            7 => 'Receptionist',
        ];

        $this->data['usertype'] = $usertypes[$usertypeID] ?? 'User';

        return view('dashboard/index', $this->data);
    }
}
