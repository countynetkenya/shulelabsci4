<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Productsaleitem_m extends MY_Model {

	protected $_table_name = 'productsaleitem';
	protected $_primary_key = 'productsaleitemID';
	protected $_primary_filter = 'intval';
	protected $_order_by = "productsaleitemID asc";

	function __construct() {
		parent::__construct();
	}

	private function prefixLoad($array) {
		if(is_array($array)) {
			if(customCompute($array)) {
				foreach ($array as $arkey =>  $ar) {
					if($arkey == "schoolID") {
						unset($array[$arkey]);
						$array['productsaleitem.'.$arkey] = $ar;
					}
				}
			}
		}

		return $array;
	}

	public function get_productsaleitem($array=NULL, $signal=FALSE) {
		$query = parent::get($array, $signal);
		return $query;
	}

	public function get_single_productsaleitem($array) {
		$query = parent::get_single($array);
		return $query;
	}

	public function get_order_by_productsaleitem($array=NULL) {
		$query = parent::get_order_by($array);
		return $query;
	}

	public function insert_productsaleitem($array) {
		$error = parent::insert($array);
		return TRUE;
	}

	public function insert_batch_productsaleitem($array) {
		$id = parent::insert_batch($array);
		return $id;
	}

	public function update_productsaleitem($data, $id = NULL) {
		parent::update($data, $id);
		return $id;
	}

	public function delete_productsaleitem($id){
		parent::delete($id);
	}

	public function delete_productsaleitem_by_productsaleID($id) {
		$this->db->delete($this->_table_name, array('productsaleID' => $id));
		return TRUE;
	}

        public function get_productsaleitem_sum($array) {
                $this->db->select(array(
                        'SUM(productsaleunitprice) AS sum_unitprice',
                        'SUM(productsalequantity) AS sum_quantity',
                        "SUM(CASE WHEN COALESCE(billing_type, 'BILLABLE') = 'BILLABLE' THEN productsaleunitprice * productsalequantity ELSE 0 END) AS result"
                ));
                $this->db->from($this->_table_name);

                if(isset($array['productsaleID'])) {
                        $this->db->where('productsaleID', $array['productsaleID']);
                }

                if(isset($array['schoolyearID'])) {
                        $this->db->where('schoolyearID', $array['schoolyearID']);
                }

                if(isset($array['schoolID'])) {
                        $this->db->where('schoolID', $array['schoolID']);
                }

                $query = $this->db->get();
                return $query->row();
        }

	/*public function get_productsaleitem_quantity($array) {
		if(!isset($array['productsaleID'])) {
			$string = 'SELECT SUM(productsaleitem.productsalequantity) AS quantity, productsaleitem.productID AS productID, productwarehouseID FROM productsaleitem LEFT JOIN productsale on productsale.productsaleID = productsaleitem.productsaleID WHERE productsaleitem.schoolID = "'.$array['schoolID'].'" && productsalerefund = 0 GROUP BY productsaleitem.productID';
		} else {
			$string = 'SELECT SUM(productsaleitem.productsalequantity) AS quantity, productsaleitem.productID AS productID, productwarehouseID FROM productsaleitem LEFT JOIN productsale on productsale.productsaleID = productsaleitem.productsaleID WHERE productsalerefund = 0 && productsaleitem.schoolID = "'.$array['schoolID'].'" && productsaleitem.productsaleID = "'.$array['productsaleID'].'" GROUP BY productsaleitem.productID';
		}

		$query = $this->db->query($string);
		return $query->result();
	}*/

	public function get_productsaleitem_quantity($array) {
		$array = $this->prefixLoad($array);
                $this->db->select(array(
                        'SUM(productsaleitem.productsalequantity) AS quantity',
                        "SUM(CASE WHEN COALESCE(productsaleitem.billing_type, 'BILLABLE') = 'BILLABLE' THEN productsaleitem.productsalequantity * productsaleitem.productsaleunitprice ELSE 0 END) AS netsales",
                        'productsaleitem.productID AS productID',
                        'productwarehouseID'
                ));
		$this->db->from($this->_table_name);
		$this->db->join('productsale', 'productsaleitem.productsaleID = productsale.productsaleID', 'LEFT');
		$this->db->where($array);
		$this->db->where('productsalerefund', 0);
		$this->db->group_by('productsaleitem.productID');
		$query = $this->db->get();
		return $query->result();
	}

	public function get_all_productsaleitem_for_report($queryArray) {

			$schoolID = $this->session->userdata('schoolID');
			$schoolyearID = $this->session->userdata('defaultschoolyearID');

			$this->db->select('productsale.*,productsaleitem.*,product.productdesc,product.productcategoryID');
			$this->db->from('productsaleitem');
			$this->db->join('productsale', 'productsale.productsaleID = productsaleitem.productsaleID');
			$this->db->join('product', 'product.productID = productsaleitem.productID');

			if(isset($queryArray['productID']) && $queryArray['productID'] != '') {
					$this->db->where('productsaleitem.productID', $queryArray['productID']);
			}

			if(isset($queryArray['productname']) && $queryArray['productname'] != 0) {
					$this->db->where('productname', $queryArray['productname']);
			}

			if(isset($queryArray['productwarehouseID']) && $queryArray['productwarehouseID'] != 0) {
					$this->db->where('productsale.productwarehouseID', $queryArray['productwarehouseID']);
			}

			if(isset($queryArray['productcategoryID']) && $queryArray['productcategoryID'] != 0) {
					$this->db->where('product.productcategoryID', $queryArray['productcategoryID']);
			}

			if(isset($queryArray['productsalecustomertypeID']) && $queryArray['productsalecustomertypeID'] != 0) {
					$this->db->where('productsale.productsalecustomertypeID', $queryArray['productsalecustomertypeID']);
			}

			if(isset($queryArray['productsalecustomerID']) && $queryArray['productsalecustomerID'] != 0) {
					$this->db->where('productsale.productsalecustomerID', $queryArray['productsalecustomerID']);
			}

			if((isset($queryArray['fromdate']) && $queryArray['fromdate'] != "") && (isset($queryArray['todate']) && $queryArray['todate'] != "")) {
					$fromdate = date('Y-m-d', strtotime($queryArray['fromdate']));
					$todate = date('Y-m-d', strtotime($queryArray['todate']));
					$this->db->where('productsaledate >=', $fromdate);
					$this->db->where('productsaledate <=', $todate);
			}
			$this->db->where('productsale.schoolID',$schoolID);
			//$this->db->where('productsale.schoolyearID',$schoolyearID);
			$this->db->order_by('productsaleitem.productsaleitemID','DESC');
			$query = $this->db->get();
			return $query->result();
	}


	//

}
