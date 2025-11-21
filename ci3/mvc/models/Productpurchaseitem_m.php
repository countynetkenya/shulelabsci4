<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class productpurchaseitem_m extends MY_Model {

	protected $_table_name = 'productpurchaseitem';
	protected $_primary_key = 'productpurchaseitemID';
	protected $_primary_filter = 'intval';
	protected $_order_by = "productpurchaseitemID asc";

	function __construct() {
		parent::__construct();
	}

	private function prefixLoad($array) {
		if(is_array($array)) {
			if(customCompute($array)) {
				foreach ($array as $arkey =>  $ar) {
					if($arkey == "schoolID") {
						unset($array[$arkey]);
						$array['productpurchaseitem.'.$arkey] = $ar;
					}
				}
			}
		}

		return $array;
	}

	public function get_productpurchaseitem($array=NULL, $signal=FALSE) {
		$query = parent::get($array, $signal);
		return $query;
	}

	public function get_single_productpurchaseitem($array) {
		$query = parent::get_single($array);
		return $query;
	}

	public function get_order_by_productpurchaseitem($array=NULL) {
		$query = parent::get_order_by($array);
		return $query;
	}

	public function insert_productpurchaseitem($array) {
		$error = parent::insert($array);
		return TRUE;
	}

	public function insert_batch_productpurchaseitem($array) {
		$id = parent::insert_batch($array);
		return $id;
	}

	public function update_productpurchaseitem($data, $id = NULL) {
		parent::update($data, $id);
		return $id;
	}

	public function delete_productpurchaseitem($id){
		parent::delete($id);
	}

	public function delete_productpurchaseitem_by_productpurchaseID($array) {
		$this->db->delete($this->_table_name, $array);
		return TRUE;
	}

	public function get_productpurchaseitem_sum($array) {
		if(isset($array['productpurchaseID']) && isset($array['schoolyearID'])) {
			$string = "SELECT SUM(productpurchaseunitprice), SUM(productpurchasequantity), SUM(productpurchaseunitprice*productpurchasequantity) AS result FROM ".$this->_table_name." WHERE productpurchaseID = '".$array['productpurchaseID']."' && schoolyearID = '".$array['schoolyearID']."' && schoolID ='".$array['schoolID']."'";
		} else {
			$string = "SELECT SUM(productpurchaseunitprice), SUM(productpurchasequantity), SUM(productpurchaseunitprice*productpurchasequantity) AS result FROM ".$this->_table_name." WHERE schoolID ='".$array['schoolID']."'";
		}

		$query = $this->db->query($string);
		return $query->row();
	}

	public function get_productpurchaseitem_quantity($array) {
		$array = $this->prefixLoad($array);
		$this->db->select('SUM(productpurchaseitem.productpurchasequantity) AS quantity, productpurchaseitem.productID AS productID, productwarehouseID');
		$this->db->from($this->_table_name);
		$this->db->join('productpurchase', 'productpurchaseitem.productpurchaseID = productpurchase.productpurchaseID', 'LEFT');
		$this->db->where($array);
		$this->db->where('productpurchaserefund', 0);
		$this->db->group_by('productpurchaseitem.productID');
		$query = $this->db->get();
		return $query->result();
	}

	public function get_productpurchaseitem_quantity_by_warehouse($schoolID) {
		$string = "SELECT SUM(productpurchaseitem.productpurchasequantity) AS quantity, productpurchaseitem.productID AS productID, productwarehouseID FROM productpurchaseitem LEFT JOIN productpurchase on productpurchase.productpurchaseID = productpurchaseitem.productpurchaseID WHERE productpurchaseitem.schoolID = '". $schoolID ."' && productpurchaserefund = 0 GROUP BY productpurchase.productwarehouseID";
		$query = $this->db->query($string);
		return $query->result();
	}

	public function get_average_unit_cost($array) {
		$this->db->select('AVG(productpurchaseunitprice) AS averageunitprice');
		$this->db->from($this->_table_name);
		$this->db->where($array);
		$query = $this->db->get();
		return $query->row();
	}

	public function get_last_productpurchaseitem($array) {
		$this->db->select('*');
		$this->db->from($this->_table_name);
		$this->db->join('productpurchase', 'productpurchaseitem.productID = productpurchase.productsupplierID', 'LEFT');
		$this->db->join('productsupplier', 'productsupplier.productsupplierID = productpurchase.productsupplierID', 'LEFT');
		$this->db->where($array);
		$this->db->order_by('productpurchaseitemID', 'desc');
		$this->db->limit(1);
		$query = $this->db->get();
		return $query->row();
	}

	public function get_all_productpurchase_for_report($queryArray) {
			$schoolID     = $this->session->userdata('schoolID');
			$schoolyearID = $this->session->userdata('defaultschoolyearID');

			$this->db->select('*');
			$this->db->from('productpurchase');
			$this->db->join('productpurchaseitem', 'productpurchase.productpurchaseID = productpurchaseitem.productpurchaseID');
			$this->db->join('product', 'product.productID = productpurchaseitem.productID');

			if(isset($queryArray['productID']) && $queryArray['productID'] != '') {
					$this->db->where('productpurchaseitem.productID', $queryArray['productID']);
			}

			if(isset($queryArray['productname']) && $queryArray['productname'] != 0) {
					$this->db->where('productname', $queryArray['productname']);
			}

			if(isset($queryArray['productsupplierID']) && $queryArray['productsupplierID'] != 0) {
					$this->db->where('productpurchase.productsupplierID', $queryArray['productsupplierID']);
			}

			if(isset($queryArray['productwarehouseID']) && $queryArray['productwarehouseID'] != 0) {
					$this->db->where('productpurchase.productwarehouseID', $queryArray['productwarehouseID']);
			}

			if(isset($queryArray['productcategoryID']) && $queryArray['productcategoryID'] != 0) {
					$this->db->where('product.productcategoryID', $queryArray['productcategoryID']);
			}

			if(isset($queryArray['reference_no']) && !empty($queryArray['reference_no'])) {
					$this->db->where('productpurchase.productpurchasereferenceno', $queryArray['reference_no']);
			}

			if((isset($queryArray['fromdate']) && $queryArray['fromdate'] != "") && (isset($queryArray['todate']) && $queryArray['todate'] != "")) {
					$fromdate = date('Y-m-d', strtotime($queryArray['fromdate']));
					$todate = date('Y-m-d', strtotime($queryArray['todate']));
					$this->db->where('productpurchasedate >=', $fromdate);
					$this->db->where('productpurchasedate <=', $todate);
			}

			$this->db->where('productpurchase.schoolID',$schoolID);
			//$this->db->where('productpurchase.schoolyearID',$schoolyearID);
			$query = $this->db->get();
			return $query->result();
	}
}
