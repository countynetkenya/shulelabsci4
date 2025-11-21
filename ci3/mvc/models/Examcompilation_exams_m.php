<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class examcompilation_exams_m extends MY_Model {

	protected $_table_name = 'examcompilation_exams';
	protected $_primary_key = 'examcompilation_examID';
	protected $_primary_filter = 'intval';
	protected $_order_by = "examcompilation_examID desc";

	function __construct() {
		parent::__construct();
	}

	function get_examcompilation_exams($array=NULL, $signal=FALSE) {
		$query = parent::get($array, $signal);
		return $query;
	}

	function get_order_by_examcompilation_exams($array=NULL) {
		$query = parent::get_order_by($array);
		return $query;
	}

	function get_single_examcompilation_exams($array=NULL) {
		$query = parent::get_single($array);
		return $query;
	}

	function insert_examcompilation_exams($array) {
		$error = parent::insert($array);
		return TRUE;
	}

	public function insert_batch_examcompilation_exams($array) {
		$id = parent::insert_batch($array);
		return $id;
	}

	function update_examcompilation_exams($data, $id = NULL) {
		parent::update($data, $id);
		return $id;
	}

	public function delete_examcompilation_exams($id){
		parent::delete($id);
	}

	public function delete_examcompilation_exams_by_examcompilationID($id){
		$this->db->delete($this->_table_name, array('examcompilationID' => $id));
		return TRUE;
	}
}

/* End of file bundlefeetype_feetypes_m.php */
/* Location: .//D/xampp/htdocs/school/mvc/models/bundlefeetype_feetypes_m.php */
