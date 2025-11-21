<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Okr_objective_m extends MY_Model
{
    protected $_table_name = 'okr_objectives';
    protected $_primary_key = 'okrObjectiveID';
    protected $_primary_filter = 'intval';
    protected $_order_by = "created_at DESC";

    public function __construct()
    {
        parent::__construct();
    }

    public function get_okr_objective($array = NULL, $signal = FALSE)
    {
        return parent::get($array, $signal);
    }

    public function get_order_by_okr_objective($array = NULL)
    {
        return parent::get_order_by($array);
    }

    public function get_single_okr_objective($array = NULL)
    {
        return parent::get_single($array);
    }

    public function insert_okr_objective($array)
    {
        return parent::insert($array);
    }

    public function update_okr_objective($data, $id = NULL)
    {
        parent::update($data, $id);
        return $id;
    }

    public function delete_okr_objective($id)
    {
        parent::delete($id);
    }
}
