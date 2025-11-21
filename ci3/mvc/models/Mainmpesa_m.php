<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Mainmpesa_m extends MY_Model {

	protected $_table_name = 'mainmpesa';
	protected $_primary_key = 'mainmpesaID';
	protected $_primary_filter = 'intval';
	protected $_order_by = "mainmpesaID desc";

	public function __construct() {
		parent::__construct();
	}

	public function insert_mainmpesa($array) {
		$error = parent::insert($array);
		return TRUE;
	}

	public function get_single_mainmpesa($array) {
		$this->db->select('*');
		$this->db->from('mainmpesa');
		$this->db->where($array);
		$this->db->order_by('mainmpesaID desc');
		$this->db->limit(1);
		$query = $this->db->get();
		return $query->row();
	}

	public function update_mainmpesa($data, $id = NULL) {
		parent::update($data, $id);
		return $id;
	}

	public function get_order_by_mainmpesa($array=NULL) {
		$query = parent::get_order_by($array);
		return $query;
	}
}
