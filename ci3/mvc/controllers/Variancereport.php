<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Variancereport extends Admin_Controller {
	public function __construct() {
		parent::__construct();

		$this->load->model("stock_m");
		$this->load->model("product_m");
		$this->load->model("productpurchaseitem_m");
		$this->load->model("productsaleitem_m");

    $language = $this->session->userdata('lang');
		$this->lang->load('variancereport', $language);
	}

	public function rules() {
		$rules = array();
    return $rules;
  }

	public function index() {
		$this->data['headerassets'] = array(
      'css' => array(
          'assets/select2/css/select2.css',
          'assets/select2/css/select2-bootstrap.css'
      ),
      'js' => array(
          'assets/select2/select2.js'
      )
		);

		$schoolID = $this->session->userdata('schoolID');
		$this->data['products'] = $this->stock_m->get_product_with_stock(array('schoolID' => $schoolID));
		$this->data['productpurchasequintity'] = pluck($this->productpurchaseitem_m->get_productpurchaseitem_quantity(array('schoolID' => $schoolID)), 'obj', 'productID');
		$this->data['productsalequintity'] = pluck($this->productsaleitem_m->get_productsaleitem_quantity(array('schoolID' => $schoolID)), 'obj', 'productID');
		$this->data["subview"] = "report/variance/VarianceReport";
		$this->load->view('_layout_main', $this->data);
	}

}
