<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Payment_gateway_m extends MY_Model
{

    protected $_table_name     = 'payment_gateways';
    protected $_primary_key    = 'id';
    protected $_primary_filter = 'intval';
    protected $_order_by       = "id desc";

    public function __construct()
    {
        parent::__construct();
    }

    public function get_payment_gateway($array = null, $signal = false)
    {
        return parent::get($array, $signal);
    }

    public function get_order_by_payment_gateway($array = null)
    {
        return parent::get_order_by($array);
    }

    public function get_single_payment_gateway($array = null)
    {
        return parent::get_single($array);
    }

    public function insert_payment_gateway($array)
    {
        return parent::insert($array);
    }

    public function update_payment_gateway($data, $id = null)
    {
        parent::update($data, $id);
        return $id;
    }

    public function delete_payment_gateway($id)
    {
        return parent::delete($id);
    }

    public function get_single_payment_gateway_with_option($array=null) {
        $this->db->select('payment_gateways.*,payment_gateway_option.*');
        $this->db->from('payment_gateways');
        $this->db->join('payment_gateway_option', 'payment_gateways.id=payment_gateway_option.payment_gateway_id');

        if(!is_null($array)) {
            $this->db->where($array);
        }

        $this->db->order_by('payment_gateways.id DESC');
        return $this->db->get()->result();
    }

    function get_order_by_gateway_with_values($array) {
        $this->db->from($this->_table_name);
        $this->db->join('payment_gateway_option', 'payment_gateway_option.payment_gateway_id = payment_gateways.id', 'LEFT');
        $this->db->join('payment_gateway_option_values', 'payment_gateway_option.id = payment_gateway_option_values.payment_gateway_option_id', 'LEFT');
        $this->db->where($array);
        $query = $this->db->get();
        return $query->result();
    }
}
