<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class credittypes_m extends MY_Model {

	protected $_table_name = 'credittypes';
	protected $_primary_key = 'credittypesID';
	protected $_primary_filter = 'intval';
	protected $_order_by = "credittypesID asc";

	function __construct() {
		parent::__construct();
	}

	function get_credittypes($array=NULL, $signal=FALSE) {
		$query = parent::get($array, $signal);
		return $query;
	}

	function get_order_by_credittypes($array=NULL) {
		$query = parent::get_order_by($array);
		return $query;
	}

	function get_single_credittypes($array=NULL) {
		$query = parent::get_single($array);
		return $query;
	}

	function insert_credittypes($array) {
		$error = parent::insert($array);
		return TRUE;
	}

	function update_credittypes($data, $id = NULL) {
		parent::update($data, $id);
		return $id;
	}

	public function delete_credittypes($id){
		parent::delete($id);
	}

	function allcredittypes($credittypes) {
		$query = $this->db->query("SELECT * FROM credittypes WHERE credittypes LIKE '$credittypes%'");
		return $query->result();
	}
}

/* End of file credittypes_m.php */
/* Location: .//D/xampp/htdocs/school/mvc/models/credittypes_m.php */