<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Invoice_m extends MY_Model {

	protected $_table_name = 'invoice';
	protected $_primary_key = 'invoiceID';
	protected $_primary_filter = 'intval';
	protected $_order_by = "invoiceID asc";


	public function __construct() {
		parent::__construct();
	}

	private function prefixLoad($array) {
		if(is_array($array)) {
			if(customCompute($array)) {
				foreach ($array as $arkey =>  $ar) {
					if($arkey == "schoolID") {
						unset($array[$arkey]);
						$array['invoice.'.$arkey] = $ar;
					}
				}
			}
		}

		return $array;
	}

	public function get_invoice_with_student($array = null) {
		$array = $this->prefixLoad($array);
		$this->db->select('*');
		$this->db->from('invoice');
		$this->db->join('student', 'student.studentID = invoice.studentID', 'LEFT');
		$this->db->where($array);
		$query = $this->db->get();
		return $query->result();
	}

	public function get_invoice_with_studentrelation() {
		$this->db->select('*');
		$this->db->from('invoice');
		$this->db->join('studentrelation', 'studentrelation.srstudentID = invoice.studentID AND studentrelation.srclassesID = invoice.classesID AND studentrelation.srschoolyearID = invoice.schoolyearID', 'LEFT');
		$this->db->where('invoice.deleted_at', 1);
		$query = $this->db->get();
		return $query->result();
	}

	public function get_invoice_with_studentrelation_by_studentID($studentID) {
		$this->db->select('*');
		$this->db->from('invoice');
		$this->db->join('studentrelation', 'studentrelation.srstudentID = invoice.studentID AND studentrelation.srclassesID = invoice.classesID AND studentrelation.srschoolyearID = invoice.schoolyearID', 'LEFT');
		$this->db->where('invoice.studentID', $studentID);
		$this->db->where('invoice.deleted_at', 1);
		$query = $this->db->get();
		return $query->result();
	}

	public function get_invoice_with_studentrelation_by_invoiceID($invoiceID) {
		$this->db->select('*');
		$this->db->from('invoice');
		$this->db->join('studentrelation', 'studentrelation.srstudentID = invoice.studentID AND studentrelation.srclassesID = invoice.classesID AND studentrelation.srschoolyearID = invoice.schoolyearID', 'LEFT');
		$this->db->where('invoice.invoiceID', $invoiceID);
		$this->db->where('invoice.deleted_at', 1);
		$query = $this->db->get();
		return $query->row();
	}

	public function get_invoice($array=NULL, $signal=FALSE) {
		$query = parent::get($array, $signal);
		return $query;
	}

	public function get_order_by_invoice($array=NULL) {
		$query = parent::get_order_by($array);
		return $query;
	}

	function get_invoice_wherein($array, $array2) {
    $query = $this->db->select('*')->from($this->_table_name)->where($array)->where_in('studentID', $array2)->get();
    return $query->result();
  }

	public function get_single_invoice($array=NULL) {
		$query = parent::get_single($array);
		return $query;
	}

	public function insert_invoice($array) {
		$error = parent::insert($array);
		return $error;
	}

	public function insert_batch_invoice($array) {
		$id = parent::insert_batch($array);
		return $id;
	}

	public function update_invoice($data, $id = NULL) {
		parent::update($data, $id);
		return $id;
	}

	public function update_invoice_by_maininvoiceID($data, $id = NULL) {
		$this->db->set($data);
		$this->db->where('maininvoiceID', $id);
		$this->db->update($this->_table_name);
		return $id;
	}

	public function update_batch_invoice($data, $id = NULL) {
        parent::update_batch($data, $id);
        return TRUE;
    }

	public function delete_invoice($id){
		parent::delete($id);
	}

	public function delete_invoice_by_maininvoiceID($id){
		$this->db->delete($this->_table_name, array('maininvoiceID' => $id));
		return TRUE;
	}

	public function get_all_duefees_for_report($queryArray) {
		$this->db->select('*');
		$this->db->from('invoice');
		$this->db->where('invoice.schoolyearID',$queryArray['schoolyearID']);

		if((isset($queryArray['classesID']) && $queryArray['classesID'] != 0) || (isset($queryArray['sectionID']) && $queryArray['sectionID'] != 0) || (isset($queryArray['studentID']) && $queryArray['studentID'] != 0)) {

			if(isset($queryArray['classesID']) && $queryArray['classesID'] != 0) {
				$this->db->where('invoice.classesID', $queryArray['classesID']);
			}

			if(isset($queryArray['studentID']) && $queryArray['studentID'] != 0) {
				$this->db->where('invoice.studentID', $queryArray['studentID']);
			}
		}

		if(isset($queryArray['feetypeID']) && $queryArray['feetypeID'] != 0) {
			$this->db->where('invoice.feetypeID', $queryArray['feetypeID']);
		}

		if((isset($queryArray['fromdate']) && $queryArray['fromdate'] != 0) && (isset($queryArray['todate']) && $queryArray['todate'] != 0)) {
			$fromdate = date('Y-m-d', strtotime($queryArray['fromdate']));
			$todate = date('Y-m-d', strtotime($queryArray['todate']));
			$this->db->where('create_date >=', $fromdate);
			$this->db->where('create_date <=', $todate);
		}

		$this->db->where('invoice.paidstatus !=', 2);
		$this->db->where('invoice.deleted_at', 1);

		$query = $this->db->get();
		return $query->result();
	}

	public function get_all_balancefees_for_report($queryArray) {
		$this->db->select('*');
		$this->db->from('invoice');
		$this->db->join('classes', 'invoice.classesID = classes.classesID', 'LEFT');
		$this->db->join('studentrelation', 'invoice.studentID = studentrelation.srstudentID', 'LEFT');

		if((isset($queryArray['schoolID']) && $queryArray['schoolID'] != 0) || (isset($queryArray['schoolyearID']) && $queryArray['schoolyearID'] != 0) || (isset($queryArray['classesID']) && $queryArray['classesID'] != 0) || (isset($queryArray['studentID']) && $queryArray['studentID'] != 0)) {

			if(isset($queryArray['schoolID']) && $queryArray['schoolID'] != 0) {
				$this->db->where('invoice.schoolID', $queryArray['schoolID']);
				$this->db->where('studentrelation.srschoolID', $queryArray['schoolID']);
			}

			if(isset($queryArray['schoolyearID']) && $queryArray['schoolyearID'] != 0) {
				$this->db->where('invoice.schoolyearID', $queryArray['schoolyearID']);
				$this->db->where('studentrelation.srschoolyearID', $queryArray['schoolyearID']);
			}

			if(isset($queryArray['classesID']) && $queryArray['classesID'] != 0) {
				$this->db->where('invoice.classesID', $queryArray['classesID']);
			}

			if(isset($queryArray['studentID']) && $queryArray['studentID'] != 0) {
				$this->db->where('invoice.studentID', $queryArray['studentID']);
			}

			if(isset($queryArray['fromdate']) && $queryArray['fromdate'] != "") {
				$this->db->where('invoice.date >=', $queryArray['fromdate']);
			}

			if(isset($queryArray['todate']) && $queryArray['todate'] != "") {
				$this->db->where('invoice.date <=', $queryArray['todate']);
			}

			if(isset($queryArray['termFrom']) && $queryArray['termFrom'] != "") {
				$this->db->where('invoice.date >=', $queryArray['termFrom']);
			}

			if(isset($queryArray['termTo']) && $queryArray['termTo'] != "") {
				$this->db->where('invoice.date >=', $queryArray['termTo']);
			}
		}
		if(isset($queryArray['divisionID']))
				$this->db->where('classes.divisionID', $queryArray['divisionID']);

		$this->db->where('invoice.deleted_at', 1);

		$query = $this->db->get();
		return $query->result();
	}

	public function get_all_balancefees_bf_for_report($queryArray) {
		$this->db->select('*');
		$this->db->from('invoice');

		if((isset($queryArray['schoolID']) && $queryArray['schoolID'] != 0) || (isset($queryArray['schoolyearID']) && $queryArray['schoolyearID'] != 0) || (isset($queryArray['classesID']) && $queryArray['classesID'] != 0) || (isset($queryArray['studentID']) && $queryArray['studentID'] != 0)) {

			if(isset($queryArray['schoolID']) && $queryArray['schoolID'] != 0) {
				$this->db->where('invoice.schoolID', $queryArray['schoolID']);
			}

			if(isset($queryArray['classesID']) && $queryArray['classesID'] != 0) {
				$this->db->where('invoice.classesID', $queryArray['classesID']);
			}

			if(isset($queryArray['studentID']) && $queryArray['studentID'] != 0) {
				$this->db->where('invoice.studentID', $queryArray['studentID']);
			}

			if(isset($queryArray['dateFrom']) && $queryArray['dateFrom'] != "") {
				$this->db->where('invoice.date <', $queryArray['dateFrom']);
			}
			elseif(isset($queryArray['termFrom']) && $queryArray['termFrom'] != "") {
				$this->db->where('invoice.date <', $queryArray['termFrom']);
			}
			elseif(isset($queryArray['schoolyearID']) && $queryArray['schoolyearID'] != 0) {
				$this->db->where('invoice.schoolyearID <', $queryArray['schoolyearID']);
			}
		}
		$this->db->where('invoice.deleted_at', 1);

		$query = $this->db->get();
		return $query->result();
	}

	public function get_all_fees_for_report($queryArray) {
		$this->db->select('invoice.*, division, srstudentID, srname, srclasses, group');
		$this->db->from('invoice');
		$this->db->where('invoice.schoolyearID',$queryArray['schoolyearID']);
		$this->db->join('classes', 'invoice.classesID = classes.classesID', 'LEFT');
		$this->db->join('studentrelation', 'invoice.studentID = studentrelation.srstudentID', 'LEFT');
		$this->db->join('studentgroup', 'studentrelation.srstudentgroupID = studentgroup.studentgroupID', 'LEFT');

		if((isset($queryArray['schoolID']) && $queryArray['schoolID'] != 0) || (isset($queryArray['schoolyearID']) && $queryArray['schoolyearID'] != 0) || (isset($queryArray['classesID']) && $queryArray['classesID'] != 0) || (isset($queryArray['studentID']) && $queryArray['studentID'] != 0)) {

			if(isset($queryArray['schoolID']) && $queryArray['schoolID'] != 0) {
				$this->db->where('invoice.schoolID', $queryArray['schoolID']);
			}

			if(isset($queryArray['schoolyearID']) && $queryArray['schoolyearID'] != 0) {
				$this->db->where('invoice.schoolyearID', $queryArray['schoolyearID']);
			}

			if(isset($queryArray['classesID']) && $queryArray['classesID'] != 0) {
				$this->db->where('invoice.classesID', $queryArray['classesID']);
			}

			if(isset($queryArray['studentID']) && $queryArray['studentID'] != 0) {
				$this->db->where('invoice.studentID', $queryArray['studentID']);
			}

			if(isset($queryArray['dateFrom']) && $queryArray['dateFrom'] != "") {
				$this->db->where('invoice.date >=', $queryArray['dateFrom']);
			}

			if(isset($queryArray['dateTo']) && $queryArray['dateTo'] != "") {
				$this->db->where('invoice.date <=', $queryArray['dateTo']);
			}

			if(isset($queryArray['termFrom']) && $queryArray['termFrom'] != "") {
				$this->db->where('invoice.date >=', $queryArray['termFrom']);
			}

			if(isset($queryArray['termTo']) && $queryArray['termTo'] != "") {
				$this->db->where('invoice.date >=', $queryArray['termTo']);
			}
		}
		$this->db->where('invoice.deleted_at', 1);
		$this->db->order_by('invoiceID', 'desc');
		$query = $this->db->get();
		return $query->result();
	}

	public function get_dueamount($array) {
		$this->db->select('invoice.*,weaverandfine.weaver,weaverandfine.fine');
		$this->db->from('invoice');
		$this->db->join('weaverandfine','invoice.invoiceID=weaverandfine.invoiceID','LEFT');
		$this->db->where('invoice.schoolyearID',$array['schoolyearID']);
		$this->db->where('invoice.classesID',$array['classesID']);
		$this->db->where('invoice.deleted_at', 1);
		$query = $this->db->get();
		return $query->result();
	}

	public function get_invoice_sum($array = NULL) {
		if(isset($array['maininvoiceID'])) {
			$string = "SELECT SUM(amount) AS amount, SUM(discount) AS discount, SUM((amount/100)*discount) AS discountamount, SUM(amount-((amount/100)*discount)) AS invoiceamount FROM ".$this->_table_name." WHERE schoolID = '".$array['schoolID']."' AND maininvoiceID = '".$array['maininvoiceID']."'";
		} else {
			$string = "SELECT SUM(amount) AS amount, SUM(discount) AS discount, SUM((amount/100)*discount) AS discountamount, SUM(amount-((amount/100)*discount)) AS invoiceamount FROM ".$this->_table_name." WHERE schoolID = '".$array['schoolID']."'";
		}

		$query = $this->db->query($string);
		return $query->row();
	}
}
