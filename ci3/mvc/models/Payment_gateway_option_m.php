<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Payment_gateway_option_m extends MY_Model
{
    protected $_table_name     = 'payment_gateway_option';
    protected $_primary_key    = 'id';
    protected $_primary_filter = 'intval';
    protected $_order_by       = "id asc";

    public function __construct()
    {
        parent::__construct();
    }

    public function get_payment_gateway_option($array = null, $signal = false)
    {
        return parent::get($array, $signal);
    }

    public function get_order_by_payment_gateway_option($array = null)
    {
        return parent::get_order_by($array);
    }

    public function get_single_payment_gateway_option($array)
    {
        return parent::get_single($array);
    }

    function get_single_payment_gateway_option_values($array) {
        $this->db->from($this->_table_name);
        $this->db->join('payment_gateway_option_values', 'payment_gateway_option.id = payment_gateway_option_values.payment_gateway_option_id', 'LEFT');
        $this->db->where($array);
        $query = $this->db->get();
        return $query->row();
    }

    function get_payment_gateway_option_values($array) {
        $this->db->from($this->_table_name);
        $this->db->join('payment_gateway_option_values', 'payment_gateway_option.id = payment_gateway_option_values.payment_gateway_option_id', 'LEFT');
        $this->db->where($array);
        $query = $this->db->get();
        return $query->result();
    }

    public function insert_payment_gateway_option($array)
    {
        return parent::insert($array);
    }

    public function insert_batch_payment_gateway_option_values($array) {
  		$this->db->insert_batch('payment_gateway_option_values', $array);
  		return true;
  	}

    public function update_payment_gateway_option($data, $id = null)
    {
        parent::update($data, $id);
        return $id;
    }

    public function update_batch_payment_gateway_option($array, $id)
    {
        parent::update_batch($array, $id);
        return $id;
    }

    public function update_batch_payment_gateway_option_values($array, $id)
    {
        $this->db->where('schoolID', $array[0]['schoolID']);
        $this->db->update_batch('payment_gateway_option_values', $array, $id);
        return true;
    }

    public function delete_payment_gateway_option($id)
    {
        return parent::delete($id);
    }

}
