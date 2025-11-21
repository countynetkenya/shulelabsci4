<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class quickbookssettings_m extends MY_Model
{
	protected $_table_name     = 'quickbookssettings';

	function get_order_by_quickbooksettings() {
		$query = $this->db->get_where($this->_table_name);
		return $query->result();
	}

	function get_quickbooksetting_values($array) {
			$this->db->from($this->_table_name);
			$this->db->join('quickbookssetting_values', 'quickbookssettings.quickbookssettingsID = quickbookssetting_values.quickbookssettingsID', 'LEFT');
			$this->db->where($array);
			$query = $this->db->get();
			return $query->result();
	}

	public function insert_batch_quickbookssettings_values($array) {
		$this->db->insert_batch('quickbookssetting_values', $array);
		return true;
	}

	function update_quickbookssettings($array) {
		$this->db->update_batch('quickbookssettings', $array, 'field_names');
	}

	public function update_batch_quickbookssetting_values($array, $id)
	{
			$this->db->where('schoolID', $array[0]['schoolID']);
			$this->db->update_batch('quickbookssetting_values', $array, $id);
			return true;
	}
}
