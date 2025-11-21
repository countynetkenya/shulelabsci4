<?php if ( !defined('BASEPATH') ) {
    exit('No direct script access allowed');
}

    class Site_m extends MY_Model
    {

        protected $_table_name = 'setting_values';
        protected $_primary_filter = 'intval';
        protected $_order_by = "fieldoption asc";

        public function __construct()
        {
            parent::__construct();
        }

        public function get_site( $schoolID = 0 )
        {
            $compress = [];
            $this->db->from($this->_table_name);
            $this->db->where('schoolID', $schoolID);
            $query = $this->db->get();
            foreach ( $query->result() as $row ) {
                $compress[ $row->fieldoption ] = $row->value;
            }
            return (object) $compress;
        }
    }
