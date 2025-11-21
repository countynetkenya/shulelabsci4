<?php if ( !defined('BASEPATH') ) {
    exit('No direct script access allowed');
}

class Setting_m extends MY_Model
{

    protected $_table_name = 'setting_values';
    protected $_primary_filter = 'intval';
    protected $_order_by = "fieldoption asc";

    public function __construct()
    {
        parent::__construct();
    }

    public function get_setting( $schoolID = 0 )
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

    public function get_setting_array( $schoolID = 0 )
    {
        $compress = [];
    		$this->db->from($this->_table_name);
        $this->db->where('schoolID', $schoolID);
        $query = $this->db->get();
        foreach ( $query->result() as $row ) {
            $compress[ $row->fieldoption ] = $row->value;
        }
        return $compress;
    }

    public function get_setting_where( $data, $schoolID = 0 )
    {
        $this->db->from($this->_table_name);
        $this->db->where('schoolID', $schoolID);
        $this->db->where('fieldoption', $data);
        $query = $this->db->get();
        return $query->row();
    }

    public function insertorupdate( $schoolID, $arrays )
    {
        foreach ( $arrays as $key => $array ) {
            $this->db->from($this->_table_name);
            $this->db->where('fieldoption', $key);
            $this->db->where('schoolID', $schoolID);
            $rows = $this->db->get()->num_rows();
            if($rows > 0) {
              $set = array('value' => $array);
              $where = array('fieldoption' => $key, 'schoolID' => $schoolID);
              $this->db->set($set);
              $this->db->where($where);
              $this->db->update($this->_table_name);
            }
            else {
              $set = array('fieldoption' => $key, 'value' => $array, 'schoolID' => $schoolID);
              $this->db->set($set);
              $this->db->insert($this->_table_name);
            }
        }
        return true;
    }

    public function delete_setting( $optionname )
    {
        $this->db->delete('setting_values', [ 'fieldoption' => $optionname ]);
        return true;
    }

    public function insert_setting( $array )
    {
        $this->db->insert('setting_values', $array);
        return true;
    }

    public function update_setting( $array, $schoolID )
    {
        $this->db->where('schoolID', $schoolID);
        $this->db->update($this->_table_name, $array);
        return true;
    }
}
