<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class examcompilation_m extends MY_Model {

	protected $_table_name = 'examcompilation';
	protected $_primary_key = 'examcompilationID';
	protected $_primary_filter = 'intval';
	protected $_order_by = "examcompilationID asc";

	function __construct() {
		parent::__construct();
	}

	function get_examcompilation($array=NULL, $signal=FALSE) {
		$query = parent::get($array, $signal);
		return $query;
	}

	function get_examcompilation_with_exams_total() {
		$this->db->select('examcompilations.*, SUM(amount) AS total');
		$this->db->from('bundlefeetypes');
		$this->db->join('bundlefeetype_feetypes', 'bundlefeetype_feetypes.bundlefeetypesID = bundlefeetypes.bundlefeetypesID', 'LEFT');
		$this->db->where('bundlefeetypes.schoolID', $this->session->userdata('schoolID'));
		$this->db->group_by('bundlefeetype_feetypes.bundlefeetypesID');
		$query = $this->db->get();
		return $query->result();
	}

	function get_order_by_examcompilation($array=NULL) {
		$query = parent::get_order_by($array);
		return $query;
	}

	function get_single_examcompilation($array=NULL) {
		$query = parent::get_single($array);
		return $query;
	}

	function insert_examcompilation($array) {
		$error = parent::insert($array);
		return TRUE;
	}

	function update_examcompilation($data, $id = NULL) {
		parent::update($data, $id);
		return $id;
	}

	public function delete_examcompilation($id){
		parent::delete($id);
	}

	function allexamcompilation($examcompilation) {
		$query = $this->db->query("SELECT * FROM examcompilation WHERE examcompilation LIKE '$examcompilation%'");
		return $query->result();
	}
}

/* End of file credittypes_m.php */
/* Location: .//D/xampp/htdocs/school/mvc/models/credittypes_m.php */
