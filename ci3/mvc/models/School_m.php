<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class School_m extends MY_Model
{

	protected $_table_name = 'school';
	protected $_primary_key = 'schoolID';
	protected $_primary_filter = 'intval';
	protected $_order_by = "schoolID desc";

	function __construct()
	{
		parent::__construct();
	}

	public function get_school($array=NULL, $signal=FALSE)
	{
		$query = parent::get($array, $signal);
		return $query;
	}

	public function get_order_by_school($array=NULL)
	{
		$query = parent::get_order_by($array);
		return $query;
	}

	public function get_school_wherein($array, $limit=NULL, $offset=NULL) {
    $this->db->select('*');
		$this->db->from($this->_table_name);
		$this->db->where_in('schoolID', $array);
		$this->db->order_by('schoolID', 'asc');
    if($limit) {
        $this->db->limit($limit, $offset);
    }
		$query = $this->db->get();
    return $query->result();
  }

  public function count_school_wherein($array) {
      $this->db->from($this->_table_name);
      $this->db->where_in('schoolID', $array);
      return $this->db->count_all_results();
  }

	public function get_single_school($array=NULL)
	{
		$query = parent::get_single($array);
		return $query;
	}

	public function insert_school($array)
	{
		parent::insert($array);
		return TRUE;
	}

	public function update_school($data, $id = NULL)
	{
		parent::update($data, $id);
		return $id;
	}

	public function delete_school($id)
	{
		parent::delete($id);
	}
}
