<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Idempotency_key_m extends MY_Model {

    protected $_table_name = 'idempotency_keys';
    protected $_primary_key = 'idempotencyKeyID';
    protected $_primary_filter = 'intval';
    protected $_order_by = 'idempotencyKeyID desc';

    public function __construct()
    {
        parent::__construct();
    }

    public function find_by_key_scope($key, $scope)
    {
        return parent::get_single([
            'idempotency_key' => $key,
            'scope' => $scope,
        ]);
    }

    public function create_record(array $data)
    {
        return parent::insert($data);
    }

    public function update_record($id, array $data)
    {
        return parent::update($data, $id);
    }
}
