<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Remark_m extends MY_Model {

	protected $_table_name = 'remark';
	protected $_primary_key = 'remarkID';
	protected $_primary_filter = 'intval';
	protected $_order_by = "remarkID asc";


	public function __construct() {
		parent::__construct();
	}

	public function get_remark($array=NULL, $signal=FALSE) {
		$query = parent::get($array, $signal);
		return $query;
	}

	public function get_order_by_remark($array=NULL) {
		$query = parent::get_order_by($array);
		return $query;
	}

	public function get_single_remark($array=NULL) {
		$query = parent::get_single($array);
		return $query;
	}

	public function insert_remark($array) {
		$error = parent::insert($array);
		return $error;
	}

	public function insert_batch_remark($array) {
		$id = parent::insert_batch($array);
		return $id;
	}

	public function update_remark($data, $id = NULL) {
		parent::update($data, $id);
		return $id;
	}

	public function update_batch_remark($data, $id = NULL) {
    parent::update_batch($data, $id);
    return TRUE;
  }

	public function delete_remark($id){
		parent::delete($id);
	}
}
