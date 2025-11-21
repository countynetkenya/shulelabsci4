<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class fees_balance_tier_m extends MY_Model {

	protected $_table_name = 'fees_balance_tier';
	protected $_primary_key = 'fees_balance_tier_id';
	protected $_primary_filter = 'intval';
	protected $_order_by = "fees_balance_tier_id asc";

	function __construct() {
		parent::__construct();
	}

	function get_fees_balance_tiers($array=NULL, $signal=FALSE) {
		$query = parent::get($array, $signal);
		return $query;
	}

	function get_fees_balance_tiers_grouped($array=NULL, $signal=FALSE) {
		$this->db->from($this->_table_name);
		$this->db->group_by('name');
		$this->db->order_by('fees_balance_tier_id', 'desc');
		$query = $this->db->get();
		return $query->result();
	}

	function get_fees_balance_tier_values($array) {
			$this->db->from($this->_table_name);
			$this->db->join('fees_balance_tier_values', 'fees_balance_tier.fees_balance_tier_id = fees_balance_tier_values.fees_balance_tier_id', 'LEFT');
			$this->db->where($array);
			$query = $this->db->get();
			return $query->result();
	}

	function get_single_fees_balance_tier_values($array) {
			$this->db->from($this->_table_name);
			$this->db->join('fees_balance_tier_values', 'fees_balance_tier.fees_balance_tier_id = fees_balance_tier_values.fees_balance_tier_id', 'LEFT');
			$this->db->where($array);
			$query = $this->db->get();
			return $query->row();
	}

	public function update_batch_fees_balance_tier_values($array, $id)
	{
			$this->db->where('schoolID', $array[0]['schoolID']);
			$this->db->update_batch('fees_balance_tier_values', $array, $id);
			return true;
	}
}
