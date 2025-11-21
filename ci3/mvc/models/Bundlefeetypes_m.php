<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class bundlefeetypes_m extends MY_Model {

	protected $_table_name = 'bundlefeetypes';
	protected $_primary_key = 'bundlefeetypesID';
	protected $_primary_filter = 'intval';
	protected $_order_by = "bundlefeetypesID asc";

	function __construct() {
		parent::__construct();
	}

	function get_bundlefeetypes($array=NULL, $signal=FALSE) {
		$query = parent::get($array, $signal);
		return $query;
	}

	function get_bundlefeetypes_with_feetypes_total() {
		$this->db->select('bundlefeetypes.*, SUM(amount) AS total');
		$this->db->from('bundlefeetypes');
		$this->db->join('bundlefeetype_feetypes', 'bundlefeetype_feetypes.bundlefeetypesID = bundlefeetypes.bundlefeetypesID', 'LEFT');
		$this->db->where('bundlefeetypes.schoolID', $this->session->userdata('schoolID'));
		$this->db->group_by('bundlefeetype_feetypes.bundlefeetypesID');
		$query = $this->db->get();
		return $query->result();
	}

	function get_order_by_bundlefeetypes($array=NULL) {
		$query = parent::get_order_by($array);
		return $query;
	}

	function get_single_bundlefeetypes($array=NULL) {
		$query = parent::get_single($array);
		return $query;
	}

	function insert_bundlefeetypes($array) {
		$error = parent::insert($array);
		return TRUE;
	}

	function update_bundlefeetypes($data, $id = NULL) {
		parent::update($data, $id);
		return $id;
	}

	public function delete_bundlefeetypes($id){
		parent::delete($id);
	}

	function allbundlefeetypes($bundlefeetypes) {
		$query = $this->db->query("SELECT * FROM bundlefeetypes WHERE bundlefeetypes LIKE '$bundlefeetypes%'");
		return $query->result();
	}
}

/* End of file credittypes_m.php */
/* Location: .//D/xampp/htdocs/school/mvc/models/credittypes_m.php */
