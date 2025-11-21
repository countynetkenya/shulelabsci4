<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class systemadmin_m extends MY_Model {

	protected $_table_name = 'systemadmin';
	protected $_primary_key = 'systemadminID';
	protected $_primary_filter = 'intval';
	protected $_order_by = "systemadminID";

	function __construct() {
		parent::__construct();
	}

	private function prefixLoad($array) {
		if(is_array($array)) {
			if(customCompute($array)) {
				foreach ($array as $arkey =>  $ar) {
					if(str_contains($arkey, 'schoolID')) {
						unset($array[$arkey]);
						$array['systemadmin.'.$arkey] = $ar;
					}
				}
			}
		}

		return $array;
	}

	function get_systemadmin_by_usertype($array = null) {
		$array = $this->prefixLoad($array);
		$this->db->select('*');
		$this->db->from('systemadmin');
		$this->db->join('usertype', 'usertype.usertypeID = systemadmin.usertypeID', 'LEFT');
		$this->db->where("FIND_IN_SET(".$array['systemadmin.schoolID'].",systemadmin.schoolID) >", 0);
		if($array['systemadminID']) {
			$this->db->where(array('systemadminID' => $array['systemadminID']));
			$query = $this->db->get();
			return $query->row();
		} else {
			$query = $this->db->get();
			return $query->result();
		}
	}

	function get_username($table, $data=NULL) {
		$query = $this->db->get_where($table, $data);
		return $query->result();
	}

	function get_systemadmin($array=NULL, $signal=FALSE) {
		$query = parent::get($array, $signal);
		return $query;
	}

	function get_order_by_systemadmin($array=NULL) {
		$query = parent::get_order_by($array);
		return $query;
	}

	function get_single_systemadmin($array) {
		$query = parent::get_single($array);
		return $query;
	}

	public function get_select_systemadmin($select = NULL, $array=[]) {
		if($select == NULL) {
			$select = 'systemadminID, name, photo';
		}

		$this->db->select($select);
		$this->db->from($this->_table_name);

		if(customCompute($array)) {
			$this->db->where($array);
		}

		$query = $this->db->get();
		return $query->result();
	}

	function insert_systemadmin($array) {
		$error = parent::insert($array);
		return TRUE;
	}

	function update_systemadmin($data, $id = NULL) {
		parent::update($data, $id);
		return $id;
	}

	function delete_systemadmin($id){
		parent::delete($id);
	}

	function hash($string) {
		return parent::hash($string);
	}
}

/* End of file systemadmin_m.php */
/* Location: .//D/xampp/htdocs/school/mvc/models/systemadmin_m.php */
