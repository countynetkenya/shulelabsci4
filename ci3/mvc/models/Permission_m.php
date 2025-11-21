<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Permission_m extends MY_Model {

	protected $_table_name = 'permissions';
	protected $_primary_key = 'permissionID';
	protected $_primary_filter = 'intval';
	protected $_order_by = "permissionID asc";

	function __construct() {
		parent::__construct();
	}

	public function get_all_usertype()
	{
		$this->db->select('*')->from('usertype')->order_by('usertypeID');
		$query = $this->db->get();
		return $query->result();
	}

	function get_permission($array=NULL, $signal=FALSE) {
		$query = parent::get($array, $signal);
		return $query;
	}

	public function get_modules_with_permission($array) {
		$query = "Select p1.permissionID,p1.name,p1.description, (case when p2.schoolID ='". $array['schoolID'] ."' AND p2.usertype_id ='". $array['id'] ."' then 'yes' else 'no' end) as active From permissions p1 left join permission_relationships p2 ON p1.permissionID = p2.permission_id and p2.schoolID ='". $array['schoolID'] ."' and p2.usertype_id ='". $array['id'] ."'";
		return $this->db->query($query)->result();
	}

	function get_order_by_permission($array=NULL) {
		$query = parent::get_order_by($array);
		return $query;
	}

	function insert_permission($array) {
		$id = parent::insert($array);
		return $id;
	}

	public function insert_batch_permission_relationships($array) {
		$this->db->insert_batch('permission_relationships', $array);
		return true;
	}

	function update_permission($data, $id = NULL) {
		parent::update($data, $id);
		return $id;
	}
	public function delete_all_permission($array)
	{
		$this->db->where($array);
  	$this->db->delete('permission_relationships');
  	return true;
	}
	public function insert_relation($array)
	{
		$this->db->insert("permission_relationships", $array);
		$id = $this->db->insert_id();
		return $id;
	}

	public function delete_permission($id){
		parent::delete($id);
	}


}

/* End of file permission_m.php */
/* Location: .//Applications/MAMP/htdocs/asheef-tsm/mvc/models/permission_m.php */
