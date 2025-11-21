<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class paymenttypes_m extends MY_Model {

	protected $_table_name = 'paymenttypes';
	protected $_primary_key = 'paymenttypesID';
	protected $_primary_filter = 'intval';
	protected $_order_by = "paymenttypesID asc";

	function __construct() {
		parent::__construct();
	}

	function get_paymenttypes($array=NULL, $signal=FALSE) {
		$query = parent::get($array, $signal);
		return $query;
	}

	function get_order_by_paymenttypes($array=NULL) {
		$query = parent::get_order_by($array);
		return $query;
	}

	function get_single_paymenttypes($array=NULL) {
		$query = parent::get_single($array);
		return $query;
	}

	function insert_paymenttypes($array) {
		$error = parent::insert($array);
		return TRUE;
	}

	function update_paymenttypes($data, $id = NULL) {
		parent::update($data, $id);
		return $id;
	}

	public function delete_paymenttypes($id){
		parent::delete($id);
	}

	function allpaymenttypes($paymenttypes) {
		$query = $this->db->query("SELECT * FROM paymenttypes WHERE paymenttypes LIKE '$paymenttypes%'");
		return $query->result();
	}
}

/* End of file paymenttypes_m.php */
/* Location: .//D/xampp/htdocs/school/mvc/models/paymenttypes_m.php */
