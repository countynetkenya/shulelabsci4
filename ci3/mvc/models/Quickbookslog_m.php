<?php if ( !defined('BASEPATH') ) {
    exit('No direct script access allowed');
}

    class Quickbookslog_m extends MY_Model
    {

        protected $_table_name = 'quickbookslog';
        protected $_primary_key = 'quickbookslogID';
        protected $_primary_filter = 'intval';
        protected $_order_by = "quickbookslogID asc";

        public function __construct()
        {
            parent::__construct();
        }

        public function get_quickbookslog( $array = null, $signal = false )
        {
            $query = parent::get($array, $signal);
            return $query;
        }

        public function get_order_by_quickbookslog( $array = null )
        {
            $query = parent::get_order_by($array);
            return $query;
        }

        public function get_single_quickbookslog( $array = null )
        {
            $query = parent::get_single($array);
            return $query;
        }

        public function insert_quickbookslog( $array )
        {
            parent::insert($array);
            return true;
        }

        public function update_quickbookslog( $data, $id = null )
        {
            parent::update($data, $id);
            return $id;
        }

        public function delete_quickbookslog( $id )
        {
            parent::delete($id);
        }
    }
