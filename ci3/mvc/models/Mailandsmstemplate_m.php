<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Mailandsmstemplate_m extends MY_Model {

	protected $_table_name = 'mailandsmstemplate';
	protected $_primary_key = 'mailandsmstemplateID';
	protected $_primary_filter = 'intval';
	protected $_order_by = "mailandsmstemplateID asc";

	function __construct() {
		parent::__construct();
	}

	private function prefixLoad($array) {
		if(is_array($array)) {
			if(customCompute($array)) {
				foreach ($array as $arkey =>  $ar) {
					if($arkey == 'schoolID') {
						unset($array[$arkey]);
						$array['mailandsmstemplate.'.$arkey] = $ar;
					}
				}
			}
		}

		return $array;
	}

	function get_mailandsmstemplate($array=NULL, $signal=FALSE) {
		$query = parent::get($array, $signal);
		return $query;
	}

	function get_single_mailandsmstemplate($array) {
		$query = parent::get_single($array);
		return $query;
	}

	function get_order_by_mailandsmstemplate($array=NULL) {
		$query = parent::get_order_by($array);
		return $query;
	}

	function insert_mailandsmstemplate($array) {
		$error = parent::insert($array);
		return TRUE;
	}

	function update_mailandsmstemplate($data, $id = NULL) {
		parent::update($data, $id);
		return $id;
	}

	public function delete_mailandsmstemplate($id){
		parent::delete($id);
	}

	function get_order_by_mailandsmstemplate_with_usertypeID($array = null) {
		$array = $this->prefixLoad($array);
		$this->db->select('*');
		$this->db->from('mailandsmstemplate');
		$this->db->join('usertype', 'usertype.usertypeID = mailandsmstemplate.usertypeID', 'LEFT');
		$this->db->where($array);
		$this->db->order_by('mailandsmstemplateID','DESC');
		$query = $this->db->get();
		return $query->result();
	}
}

/* End of file notice_m.php */
/* Location: .//D/xampp/htdocs/school/mvc/models/notice_m.php */
