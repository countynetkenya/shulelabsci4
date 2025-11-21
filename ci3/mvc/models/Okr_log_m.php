<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Okr_log_m extends MY_Model
{
    protected $_table_name = 'okr_logs';
    protected $_primary_key = 'okrLogID';
    protected $_primary_filter = 'intval';
    protected $_order_by = "created_at DESC";

    public function __construct()
    {
        parent::__construct();
    }

    public function get_okr_log($array = NULL, $signal = FALSE)
    {
        return parent::get($array, $signal);
    }

    public function get_order_by_okr_log($array = NULL)
    {
        return parent::get_order_by($array);
    }

    public function insert_okr_log($array)
    {
        return parent::insert($array);
    }

    public function delete_okr_log($id)
    {
        parent::delete($id);
    }
}
