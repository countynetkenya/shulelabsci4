<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class divisions_m extends MY_Model {

	protected $_table_name = 'divisions';
	protected $_primary_key = 'divisionsID';
	protected $_primary_filter = 'intval';
	protected $_order_by = "divisionsID asc";

	function __construct() {
		parent::__construct();
	}

	function get_divisions($array=NULL, $signal=FALSE) {
		$query = parent::get($array, $signal);
		return $query;
	}

	function get_order_by_divisions($array=NULL) {
		$query = parent::get_order_by($array);
		return $query;
	}

	function get_single_divisions($array=NULL) {
		$query = parent::get_single($array);
		return $query;
	}

	function insert_divisions($array) {
		$error = parent::insert($array);
		return TRUE;
	}

	function update_divisions($data, $id = NULL) {
		parent::update($data, $id);
		return $id;
	}

	public function delete_divisions($id){
		parent::delete($id);
	}
}

/* End of file divisions_m.php */
