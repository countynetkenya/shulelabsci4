<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Stock_m extends MY_Model {

    protected $_table_name = 'stock';
    protected $_primary_key = 'stockID';
    protected $_primary_filter = 'intval';
    protected $_order_by = "stockID asc";

    function __construct() {
        parent::__construct();
    }

    private function prefixLoad($array) {
  		if(is_array($array)) {
  			if(customCompute($array)) {
  				foreach ($array as $arkey =>  $ar) {
  					if(str_contains($arkey, 'schoolID')) {
  						unset($array[$arkey]);
  						$array['product.'.$arkey] = $ar;
  					}
            if(str_contains($arkey, 'productID')) {
  						unset($array[$arkey]);
  						$array['product.'.$arkey] = $ar;
  					}
            if(str_contains($arkey, 'create_date')) {
  						unset($array[$arkey]);
  						$array['stock.'.$arkey] = $ar;
  					}
  				}
  			}
  		}

  		return $array;
  	}

    function get_stock($array=NULL, $signal=FALSE) {
        $query = parent::get($array, $signal);
        return $query;
    }

    function get_single_stock($array) {
        $query = parent::get_single($array);
        return $query;
    }

    function get_order_by_stock($array=NULL) {
        $query = parent::get_order_by($array);
        return $query;
    }

    function insert_stock($array) {
        $id = parent::insert($array);
        return $id;
    }

    public function insert_batch_stock($array) {
  		$id = parent::insert_batch($array);
  		return $id;
  	}

    function update_stock($data, $id = NULL) {
        parent::update($data, $id);
        return $id;
    }

    function get_all_stock_for_report($queryArray) {

        $schoolID = $this->session->userdata('schoolID');

        $this->db->select('*');
        $this->db->from('stock');

        if(isset($queryArray['fromdate']) && isset($queryArray['todate'])) {
            $fromdate = date('Y-m-d', strtotime($queryArray['fromdate']));
            $todate = date('Y-m-d', strtotime($queryArray['todate']));
            $this->db->where('create_date >=', $fromdate);
            $this->db->where('create_date <=', $todate);
        }
        if(isset($queryArray['year']) && $queryArray['year'] != 0) {
            $this->db->where('year(create_date)', $queryArray['year']);
        }
        $this->db->where('stock.schoolID',$schoolID);
        $this->db->order_by('stockID','DESC');
        $query = $this->db->get();
        return $query->result();
    }

    public function get_product_with_stock($array) {
      $array = $this->prefixLoad($array);
  		$this->db->select('product.*, IFNULL(quantity, 0) as quantity, AVG(productpurchaseunitprice) AS averageunitprice');
      $this->db->from('product');
      $this->db->join('stock', 'stock.productID = product.productID', 'LEFT');
      $this->db->join('mainstock', 'stock.mainstockID = mainstock.mainstockID AND type="adjustment"', 'LEFT');
      $this->db->join('productpurchaseitem', 'productpurchaseitem.productID = product.productID', 'LEFT');
      $this->db->where($array);
      $this->db->group_by('productpurchaseitem.productID');
  		$query = $this->db->get();
  		return $query->result();
  	}
}
