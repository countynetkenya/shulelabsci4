<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH . '../vendor/autoload.php');
/*foreach (new DirectoryIterator($_SERVER['DOCUMENT_ROOT'] . '/shule/mvc/controllers/api/v10') as $fileInfo) {
	if($fileInfo->isDot()) continue;
	$filename = "api/v10/". $fileInfo->getFilename();
	if(str_ends_with($filename, 'php'))
		require_once $filename;
}*/

class Test extends Admin_Controller {
// class Test extends CI_Controller {
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
	public function __construct()
	{
		parent::__construct();
		/*if(config_item('demo') == FALSE || ENVIRONMENT == 'production') {
			redirect('dashboard/index');
		}*/
		$this->load->model("paymenttypes_m");
	}

	public function index()
  {
			$paymenttype = $this->paymenttypes_m->get_single_paymenttypes(array('paymenttypes' => 'mpesa'));
			dd($paymenttype);
			/*$path = [];
			foreach (new DirectoryIterator($_SERVER['DOCUMENT_ROOT'] . '/shule/mvc/controllers/api/v10') as $fileInfo) {
    		if($fileInfo->isDot()) continue;
				$filename = "api/v10/". $fileInfo->getFilename();
				if(str_ends_with($filename, 'php'))
    			$path[] = $_SERVER['DOCUMENT_ROOT'] .'/shule/mvc/controllers/'. $filename;
			}
      $openapi = \OpenApi\Generator::scan([$path]);
			header('Content-Type: application/json');
			echo $openapi->toJSON();*/
	}
}
