<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Maincreditmemo_m extends MY_Model {

	protected $_table_name = 'maincreditmemo';
	protected $_primary_key = 'maincreditmemoID';
	protected $_primary_filter = 'intval';
	protected $_order_by = "maincreditmemoID desc";


	public function __construct() {
		parent::__construct();
	}

	public function get_maincreditmemo_with_studentrelation($array) {
		$this->db->select('*');
		$this->db->from($this->_table_name);
		$this->db->join('studentrelation', 'studentrelation.srstudentID = maincreditmemo.maincreditmemostudentID AND studentrelation.srschoolyearID = maincreditmemo.maincreditmemoschoolyearID', 'LEFT');
		$this->db->where('maincreditmemo.maincreditmemodeleted_at', 1);
		$this->db->where('maincreditmemo.schoolID', $array['schoolID']);
		$this->db->where('studentrelation.srschoolID', $array['schoolID']);
		if($array['schoolyearID'] != NULL) {
			$this->db->where('maincreditmemo.maincreditmemoschoolyearID', $array['schoolyearID']);
			$this->db->where('studentrelation.srschoolyearID', $array['schoolyearID']);
		}
		$this->db->order_by('maincreditmemo.maincreditmemoID', 'desc');
		$query = $this->db->get();
		return $query->result();
	}

	public function get_maincreditmemo_with_studentrelation_by_studentID($studentID, $schoolyearID = NULL) {
		$this->db->select('*');
		$this->db->from($this->_table_name);
		$this->db->join('studentrelation', 'studentrelation.srstudentID = maincreditmemo.maincreditmemostudentID AND studentrelation.srschoolyearID = maincreditmemo.maincreditmemoschoolyearID', 'LEFT');
		$this->db->where('maincreditmemo.maincreditmemostudentID', $studentID);
		$this->db->where('maincreditmemo.maincreditmemodeleted_at', 1);

		if($schoolyearID != NULL) {
			$this->db->where('maincreditmemo.maincreditmemoschoolyearID', $schoolyearID);
			$this->db->where('studentrelation.srschoolyearID', $schoolyearID);
		}
		$this->db->order_by('maincreditmemo.maincreditmemoID', 'desc');
		$query = $this->db->get();
		return $query->result();
	}


	public function get_maincreditmemo_with_studentrelation_by_multi_studentID($studentIDArrays, $schoolyearID = NULL) {
		$this->db->select('*');
		$this->db->from($this->_table_name);
		$this->db->join('studentrelation', 'studentrelation.srstudentID = maincreditmemo.maincreditmemostudentID AND studentrelation.srschoolyearID = maincreditmemo.maincreditmemoschoolyearID', 'LEFT');
		$this->db->where('maincreditmemo.maincreditmemodeleted_at', 1);

		if(customCompute($studentIDArrays)) {
			$this->db->where_in('maincreditmemo.maincreditmemostudentID', $studentIDArrays);
		}

		if($schoolyearID != NULL) {
			$this->db->where('maincreditmemo.maincreditmemoschoolyearID', $schoolyearID);
			$this->db->where('studentrelation.srschoolyearID', $schoolyearID);
		}

		$this->db->order_by('maincreditmemo.maincreditmemoID', 'desc');
		$query = $this->db->get();
		return $query->result();
	}

	public function get_maincreditmemo_with_studentrelation_by_maincreditmemoID($array) {
		$this->db->select('*');
		$this->db->from($this->_table_name);
		$this->db->join('studentrelation', 'studentrelation.srstudentID = maincreditmemo.maincreditmemostudentID AND studentrelation.srschoolyearID = maincreditmemo.maincreditmemoschoolyearID', 'LEFT');
		$this->db->where('maincreditmemo.maincreditmemoID', $array['creditmemoID']);
		$this->db->where('maincreditmemo.maincreditmemodeleted_at', 1);

		$this->db->where('maincreditmemo.schoolID', $array['schoolID']);
		$this->db->where('studentrelation.srschoolID', $array['schoolID']);

		if($array['schoolyearID'] != NULL) {
			$this->db->where('maincreditmemo.maincreditmemoschoolyearID', $array['schoolyearID']);
			$this->db->where('studentrelation.srschoolyearID', $array['schoolyearID']);
		}

		$this->db->order_by('maincreditmemo.maincreditmemoID', 'desc');
		$query = $this->db->get();
		return $query->row();
	}

	public function get_maincreditmemo($array=NULL, $signal=FALSE) {
		$query = parent::get($array, $signal);
		return $query;
	}

	public function get_order_by_maincreditmemo($array=NULL) {
		$query = parent::get_order_by($array);
		return $query;
	}

	public function get_single_maincreditmemo($array=NULL) {
		$query = parent::get_single($array);
		return $query;
	}

	public function insert_maincreditmemo($array) {
		$error = parent::insert($array);
		return $error;
	}

	public function insert_batch_maincreditmemo($array) {
		$id = parent::insert_batch($array);
		return $id;
	}

	public function update_maincreditmemo($data, $id = NULL) {
		parent::update($data, $id);
		return $id;
	}

	public function delete_maincreditmemo($id){
		parent::delete($id);
	}
}

/* End of file creditmemo_m.php */
/* Location: .//D/xampp/htdocs/school/mvc/models/creditmemo_m.php */
