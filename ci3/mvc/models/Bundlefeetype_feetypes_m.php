<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class bundlefeetype_feetypes_m extends MY_Model {

	protected $_table_name = 'bundlefeetype_feetypes';
	protected $_primary_key = 'bundlefeetype_feetypesID';
	protected $_primary_filter = 'intval';
	protected $_order_by = "bundlefeetype_feetypesID asc";

	function __construct() {
		parent::__construct();
	}

	function get_bundlefeetype_feetypes($array=NULL, $signal=FALSE) {
		$query = parent::get($array, $signal);
		return $query;
	}

	function get_order_by_bundlefeetype_feetypes($array=NULL) {
		$query = parent::get_order_by($array);
		return $query;
	}

	function get_single_bundlefeetype_feetypes($array=NULL) {
		$query = parent::get_single($array);
		return $query;
	}

	function insert_bundlefeetype_feetypes($array) {
		$error = parent::insert($array);
		return TRUE;
	}

	public function insert_batch_bundlefeetype_feetypes($array) {
		$id = parent::insert_batch($array);
		return $id;
	}

	function update_bundlefeetype_feetypes($data, $id = NULL) {
		parent::update($data, $id);
		return $id;
	}

	public function delete_bundlefeetype_feetypes($id){
		parent::delete($id);
	}

	public function delete_bundlefeetype_feetypes_by_bundlefeetypeID($id){
		$this->db->delete($this->_table_name, array('bundlefeetypesID' => $id));
		return TRUE;
	}
}

/* End of file bundlefeetype_feetypes_m.php */
/* Location: .//D/xampp/htdocs/school/mvc/models/bundlefeetype_feetypes_m.php */
