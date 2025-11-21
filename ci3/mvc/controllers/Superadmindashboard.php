<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Superadmindashboard extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $language = $this->session->userdata('lang');
        $this->lang->load('superadminusers', $language);

        require_once APPPATH . 'libraries/SidebarRegistry.php';
        require_once APPPATH . 'libraries/MenuAuthorizer.php';
    }

    public function index()
    {
        $authorizer = new MenuAuthorizer();
        $featured = SidebarRegistry::featuredSuperadminItems();
        $quickLinks = $authorizer->filter($featured);

        $this->data['quickLinks'] = $quickLinks;
        $this->data['subview'] = 'superadmin/dashboard';
        $this->load->view('_layout_main', $this->data);
    }
}
