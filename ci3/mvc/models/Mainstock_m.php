<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Mainstock_m extends MY_Model {

    protected $_table_name = 'mainstock';
    protected $_primary_key = 'mainstockID';
    protected $_primary_filter = 'intval';
    protected $_order_by = "mainstockID asc";

    function __construct() {
        parent::__construct();
    }

    function get_mainstock($array=NULL, $signal=FALSE) {
        $query = parent::get($array, $signal);
        return $query;
    }

    function get_single_mainstock($array) {
        $query = parent::get_single($array);
        return $query;
    }

    function get_order_by_mainstock($array=NULL) {
        $query = parent::get_order_by($array);
        return $query;
    }

    function insert_mainstock($array) {
        $id = parent::insert($array);
        return $id;
    }

    function update_mainstock($data, $id = NULL) {
        parent::update($data, $id);
        return $id;
    }
}
