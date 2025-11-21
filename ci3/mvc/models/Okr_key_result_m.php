<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Okr_key_result_m extends MY_Model
{
    protected $_table_name = 'okr_key_results';
    protected $_primary_key = 'okrKeyResultID';
    protected $_primary_filter = 'intval';
    protected $_order_by = "created_at DESC";

    public function __construct()
    {
        parent::__construct();
    }

    public function get_okr_key_result($array = NULL, $signal = FALSE)
    {
        return parent::get($array, $signal);
    }

    public function get_order_by_okr_key_result($array = NULL)
    {
        return parent::get_order_by($array);
    }

    public function get_single_okr_key_result($array = NULL)
    {
        return parent::get_single($array);
    }

    public function insert_okr_key_result($array)
    {
        return parent::insert($array);
    }

    public function insert_batch_okr_key_result($array)
    {
        return parent::insert_batch($array);
    }

    public function update_okr_key_result($data, $id = NULL)
    {
        parent::update($data, $id);
        return $id;
    }

    public function delete_okr_key_result($id)
    {
        parent::delete($id);
    }

    public function delete_by_objective($objectiveID)
    {
        $this->db->where('okrObjectiveID', $objectiveID);
        $this->db->delete($this->_table_name);
    }
}
