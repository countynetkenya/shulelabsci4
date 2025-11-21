<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class smssettings_m extends MY_Model {

	function get_order_by_bongasms($array) {
		$this->db->from('smssettings');
		$this->db->join('smssetting_values', 'smssettings.smssettingsID = smssetting_values.smssettingsID', 'LEFT');
		$this->db->where($array);
		$query = $this->db->get();
		return $query->result();
	}

	function update_bongasms($array) {
		$this->db->where('schoolID', $array[0]['schoolID']);
		$this->db->update_batch('smssettings', $array, 'smssettingsID');
	}

	function get_order_by_smsleopard($array) {
		$this->db->from('smssettings');
		$this->db->join('smssetting_values', 'smssettings.smssettingsID = smssetting_values.smssettingsID', 'LEFT');
		$this->db->where($array);
		$query = $this->db->get();
		return $query->result();
	}

	function update_smsleopard($array) {
		$this->db->where('schoolID', $array[0]['schoolID']);
		$this->db->update_batch('smssettings', $array, 'smssettingsID');
	}

	function get_order_by_clickatell() {
		$query = $this->db->get_where('smssettings', array('types' => 'clickatell'));
		return $query->result();
	}

	function update_clickatell($array) {
		$this->db->update_batch('smssettings', $array, 'field_names');
	}

	function get_order_by_twilio() {
		$query = $this->db->get_where('smssettings', array('types' => 'twilio'));
		return $query->result();
	}

	function update_twilio($array) {
		$this->db->update_batch('smssettings', $array, 'field_names');
	}

	function get_order_by_bulk() {
		$query = $this->db->get_where('smssettings', array('types' => 'bulk'));
		return $query->result();
	}

	function update_bulk($array) {
		$this->db->update_batch('smssettings', $array, 'field_names');
	}

  function get_order_by_msg91() {
    $query = $this->db->get_where('smssettings', array('types' => 'msg91'));
    return $query->result();
  }

  function update_msg91($array) {
		$this->db->update_batch('smssettings', $array, 'field_names');
	}


}
