<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Mpesa_m extends MY_Model {

	protected $_table_name = 'mpesa';
	protected $_primary_key = 'mpesaID';
	protected $_primary_filter = 'intval';
	protected $_order_by = "mpesaID desc";

	public function __construct() {
		parent::__construct();
	}

	public function insert_mpesa($array) {
		$error = parent::insert($array);
		return TRUE;
	}

	public function get_order_by_mpesa($array=NULL) {
		$query = parent::get_order_by($array);
		return $query;
	}

	public function get_mpesa_with_studentrelation($array=NULL) {
		$this->db->select('*');
		$this->db->from('mpesa');
		$this->db->join('mainmpesa', 'mpesa.mainmpesaID = mainmpesa.mainmpesaID', 'LEFT');
		$this->db->join('studentrelation', 'studentrelation.srstudentID = mpesa.studentID AND studentrelation.srschoolyearID = mainmpesa.schoolyearID', 'LEFT');
		$this->db->where($array);
		$query = $this->db->get();
		return $query->result();
	}
}
