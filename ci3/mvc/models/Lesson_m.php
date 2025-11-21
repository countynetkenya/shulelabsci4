<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Lesson_m extends MY_Model
{
    protected $_table_name     = "lesson";
    protected $_primary_key    = "lesson_id";
    protected $_primary_filter = "intval";
    protected $_order_by       = "lesson_id desc";

    public function __construct()
    {
        parent::__construct();
    }

    public function get_lesson($array = null, $signal = false)
    {
        return parent::get($array, $signal);
    }

    public function get_single_lesson($array)
    {
        return parent::get_single($array);
    }

    public function get_order_by_lesson($array = null)
    {
        return parent::get_order_by($array);
    }

    public function insert_lesson($array)
    {
        return parent::insert($array);
    }

    public function update_lesson($data, $id = null)
    {
        return parent::update($data, $id);
    }

    public function delete_lesson($id)
    {
        return parent::delete($id);
    }
}
