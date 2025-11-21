<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class examranking_m extends MY_Model {

	protected $_table_name = 'examranking';
	protected $_primary_key = 'examrankingID';
	protected $_primary_filter = 'intval';
	protected $_order_by = "examrankingID asc";

	function __construct() {
		parent::__construct();
	}

	function get_examranking($array=NULL, $signal=FALSE) {
		$query = parent::get($array, $signal);
		return $query;
	}

	function get_order_by_examranking($array=NULL) {
		$query = parent::get_order_by($array);
		return $query;
	}

	function get_single_examranking($array=NULL) {
		$query = parent::get_single($array);
		return $query;
	}

	function insert_examranking($array) {
		$error = parent::insert($array);
		return TRUE;
	}

	function update_examranking($data, $id = NULL) {
		parent::update($data, $id);
		return $id;
	}

	public function delete_examranking($id){
		parent::delete($id);
	}
}

/* End of file credittypes_m.php */
/* Location: .//D/xampp/htdocs/school/mvc/models/credittypes_m.php */
