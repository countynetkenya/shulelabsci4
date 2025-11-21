<?php

class Mpesa extends Admin_Controller
{
    /*
    | -----------------------------------------------------
    | PRODUCT NAME: 	INILABS SCHOOL MANAGEMENT SYSTEM
    | -----------------------------------------------------
    | AUTHOR:			INILABS TEAM
    | -----------------------------------------------------
    | EMAIL:			info@inilabs.net
    | -----------------------------------------------------
    | COPYRIGHT:		RESERVED BY INILABS IT
    | -----------------------------------------------------
    | WEBSITE:			http://inilabs.net
    | -----------------------------------------------------
    */

    function __construct()
    {
        parent::__construct();
        $this->load->model("payment_m");
        $language = $this->session->userdata('lang');
        $this->lang->load('mpesa', $language);
    }

    public function index()
    {
        $schoolID                      = $this->session->userdata('schoolID');
        $this->data['allocated']       = $this->payment_m->get_payment_with_studentrelation(array('srstudentID !=' => null, 'schoolID' => $schoolID));
        $this->data['unallocated']     = $this->payment_m->get_payment_with_studentrelation(array('srstudentID' => null, 'schoolID' => $schoolID));
  			$this->data["subview"]         = "mpesa/index";
        $this->load->view('_layout_main', $this->data);
    }
}
