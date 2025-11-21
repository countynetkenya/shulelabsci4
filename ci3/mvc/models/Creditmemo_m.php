<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Creditmemo_m extends MY_Model {

	protected $_table_name = 'creditmemo';
	protected $_primary_key = 'creditmemoID';
	protected $_primary_filter = 'intval';
	protected $_order_by = "creditmemoID asc";


	public function __construct() {
		parent::__construct();
	}

	private function prefixLoad($array) {
		if(is_array($array)) {
			if(customCompute($array)) {
				foreach ($array as $arkey =>  $ar) {
					if($arkey == "schoolID") {
						unset($array[$arkey]);
						$array['creditmemo.'.$arkey] = $ar;
					}
				}
			}
		}

		return $array;
	}

	public function get_creditmemo_with_student($array = null) {
		$array = $this->prefixLoad($array);
		$this->db->select('*');
		$this->db->from('creditmemo');
		$this->db->join('student', 'student.studentID = creditmemo.studentID', 'LEFT');
		$this->db->where($array);
		$query = $this->db->get();
		return $query->result();
	}

	public function get_creditmemo_with_studentrelation() {
		$this->db->select('*');
		$this->db->from('creditmemo');
		$this->db->join('studentrelation', 'studentrelation.srstudentID = creditmemo.studentID AND studentrelation.srclassesID = creditmemo.classesID AND studentrelation.srschoolyearID = creditmemo.schoolyearID', 'LEFT');
		$this->db->where('creditmemo.deleted_at', 1);
		$query = $this->db->get();
		return $query->result();
	}

	public function get_creditmemo_with_studentrelation_by_studentID($studentID) {
		$this->db->select('*');
		$this->db->from('creditmemo');
		$this->db->join('studentrelation', 'studentrelation.srstudentID = creditmemo.studentID AND studentrelation.srclassesID = creditmemo.classesID AND studentrelation.srschoolyearID = creditmemo.schoolyearID', 'LEFT');
		$this->db->where('creditmemo.studentID', $studentID);
		$this->db->where('creditmemo.deleted_at', 1);
		$query = $this->db->get();
		return $query->result();
	}

	public function get_creditmemo_with_studentrelation_by_creditmemoID($creditmemoID) {
		$this->db->select('*');
		$this->db->from('creditmemo');
		$this->db->join('studentrelation', 'studentrelation.srstudentID = creditmemo.studentID AND studentrelation.srclassesID = creditmemo.classesID AND studentrelation.srschoolyearID = creditmemo.schoolyearID', 'LEFT');
		$this->db->where('creditmemo.creditmemoID', $creditmemoID);
		$this->db->where('creditmemo.deleted_at', 1);
		$query = $this->db->get();
		return $query->row();
	}

	public function get_creditmemo($array=NULL, $signal=FALSE) {
		$query = parent::get($array, $signal);
		return $query;
	}

	public function get_order_by_creditmemo($array=NULL) {
		$query = parent::get_order_by($array);
		return $query;
	}

	function get_creditmemo_wherein($array, $array2) {
        $query = $this->db->select('*')->from($this->_table_name)->where($array)->where_in('studentID', $array2)->get();
        return $query->result();
    }

	public function get_single_creditmemo($array=NULL) {
		$query = parent::get_single($array);
		return $query;
	}

	public function insert_creditmemo($array) {
		$error = parent::insert($array);
		return $error;
	}

	public function insert_batch_creditmemo($array) {
		$id = parent::insert_batch($array);
		return $id;
	}

	public function update_creditmemo($data, $id = NULL) {
		parent::update($data, $id);
		return $id;
	}

	public function update_creditmemo_by_maincreditmemoID($data, $id = NULL) {
		$this->db->set($data);
		$this->db->where('maincreditmemoID', $id);
		$this->db->update($this->_table_name);
		return $id;
	}

	public function update_batch_creditmemo($data, $id = NULL) {
        parent::update_batch($data, $id);
        return TRUE;
    }

	public function delete_creditmemo($id){
		parent::delete($id);
	}

	public function delete_creditmemo_by_maincreditmemoID($id){
		$this->db->delete($this->_table_name, array('maincreditmemoID' => $id));
		return TRUE;
	}

	public function get_all_duefees_for_report($queryArray) {
		$this->db->select('*');
		$this->db->from('creditmemo');
		$this->db->where('creditmemo.schoolyearID',$queryArray['schoolyearID']);

		if((isset($queryArray['classesID']) && $queryArray['classesID'] != 0) || (isset($queryArray['sectionID']) && $queryArray['sectionID'] != 0) || (isset($queryArray['studentID']) && $queryArray['studentID'] != 0)) {

			if(isset($queryArray['classesID']) && $queryArray['classesID'] != 0) {
				$this->db->where('creditmemo.classesID', $queryArray['classesID']);
			}

			if(isset($queryArray['studentID']) && $queryArray['studentID'] != 0) {
				$this->db->where('creditmemo.studentID', $queryArray['studentID']);
			}
		}

		if(isset($queryArray['feetypeID']) && $queryArray['feetypeID'] != 0) {
			$this->db->where('creditmemo.feetypeID', $queryArray['feetypeID']);
		}

		if((isset($queryArray['fromdate']) && $queryArray['fromdate'] != 0) && (isset($queryArray['todate']) && $queryArray['todate'] != 0)) {
			$fromdate = date('Y-m-d', strtotime($queryArray['fromdate']));
			$todate = date('Y-m-d', strtotime($queryArray['todate']));
			$this->db->where('create_date >=', $fromdate);
			$this->db->where('create_date <=', $todate);
		}

		$this->db->where('creditmemo.paidstatus !=', 2);
		$this->db->where('creditmemo.deleted_at', 1);

		$query = $this->db->get();
		return $query->result();
	}

	public function get_all_balancefees_for_report($queryArray) {
		$this->db->select('*');
		$this->db->from('creditmemo');
		$this->db->join('classes', 'creditmemo.classesID = classes.classesID', 'LEFT');
		$this->db->join('studentrelation', 'creditmemo.studentID = studentrelation.srstudentID', 'LEFT');

		if((isset($queryArray['schoolID']) && $queryArray['schoolID'] != 0) || (isset($queryArray['classesID']) && $queryArray['classesID'] != 0) || (isset($queryArray['sectionID']) && $queryArray['sectionID'] != 0) || (isset($queryArray['studentID']) && $queryArray['studentID'] != 0)) {

			if(isset($queryArray['schoolID']) && $queryArray['schoolID'] != 0) {
				$this->db->where('creditmemo.schoolID', $queryArray['schoolID']);
				$this->db->where('studentrelation.srschoolID', $queryArray['schoolID']);
			}

			if(isset($queryArray['schoolyearID']) && $queryArray['schoolyearID'] != 0) {
				$this->db->where('creditmemo.schoolyearID', $queryArray['schoolyearID']);
				$this->db->where('studentrelation.srschoolyearID', $queryArray['schoolyearID']);
			}

			if(isset($queryArray['classesID']) && $queryArray['classesID'] != 0) {
				$this->db->where('creditmemo.classesID', $queryArray['classesID']);
			}

			if(isset($queryArray['studentID']) && $queryArray['studentID'] != 0) {
				$this->db->where('creditmemo.studentID', $queryArray['studentID']);
			}

			if(isset($queryArray['dateFrom']) && $queryArray['dateFrom'] != "") {
				$this->db->where('creditmemo.date >=', $queryArray['dateFrom']);
			}

			if(isset($queryArray['dateTo']) && $queryArray['dateTo'] != "") {
				$this->db->where('creditmemo.date <=', $queryArray['dateTo']);
			}

			if(isset($queryArray['termFrom']) && $queryArray['termFrom'] != "") {
				$this->db->where('creditmemo.date >=', $queryArray['termFrom']);
			}

			if(isset($queryArray['termTo']) && $queryArray['termTo'] != "") {
				$this->db->where('creditmemo.date >=', $queryArray['termTo']);
			}
		}
		if(isset($queryArray['divisionID']))
				$this->db->where('classes.divisionID', $queryArray['divisionID']);
		$this->db->where('creditmemo.deleted_at', 1);

		$query = $this->db->get();
		return $query->result();
	}

	public function get_all_balancefees_bf_for_report($queryArray) {
		$this->db->select('*');
		$this->db->from('creditmemo');

		if((isset($queryArray['schoolID']) && $queryArray['schoolID'] != 0) || (isset($queryArray['schoolyearID']) && $queryArray['schoolyearID'] != 0) || (isset($queryArray['classesID']) && $queryArray['classesID'] != 0) || (isset($queryArray['sectionID']) && $queryArray['sectionID'] != 0) || (isset($queryArray['studentID']) && $queryArray['studentID'] != 0)) {

			if(isset($queryArray['schoolID']) && $queryArray['schoolID'] != 0) {
				$this->db->where('creditmemo.schoolID', $queryArray['schoolID']);
			}

			if(isset($queryArray['classesID']) && $queryArray['classesID'] != 0) {
				$this->db->where('creditmemo.classesID', $queryArray['classesID']);
			}

			if(isset($queryArray['studentID']) && $queryArray['studentID'] != 0) {
				$this->db->where('creditmemo.studentID', $queryArray['studentID']);
			}

			if(isset($queryArray['dateFrom']) && $queryArray['dateFrom'] != "") {
				$this->db->where('creditmemo.date <', $queryArray['dateFrom']);
			}
			elseif(isset($queryArray['termFrom']) && $queryArray['termFrom'] != "") {
				$this->db->where('creditmemo.date <', $queryArray['termFrom']);
			}
			elseif(isset($queryArray['schoolyearID']) && $queryArray['schoolyearID'] != 0) {
				$this->db->where('creditmemo.schoolyearID <', $queryArray['schoolyearID']);
			}
		}
		$this->db->where('creditmemo.deleted_at', 1);

		$query = $this->db->get();
		return $query->result();
	}

	public function get_all_fees_for_report($queryArray) {
		$this->db->select('creditmemo.*, division, srstudentID, srname, srclasses, group');
		$this->db->from('creditmemo');
		$this->db->where('creditmemo.schoolyearID',$queryArray['schoolyearID']);
		$this->db->join('classes', 'creditmemo.classesID = classes.classesID', 'LEFT');
		$this->db->join('studentrelation', 'creditmemo.studentID = studentrelation.srstudentID', 'LEFT');
		$this->db->join('studentgroup', 'studentrelation.srstudentgroupID = studentgroup.studentgroupID', 'LEFT');

		if((isset($queryArray['schoolID']) && $queryArray['schoolID'] != 0) || (isset($queryArray['schoolyearID']) && $queryArray['schoolyearID'] != 0) || (isset($queryArray['classesID']) && $queryArray['classesID'] != 0) || (isset($queryArray['studentID']) && $queryArray['studentID'] != 0)) {

			if(isset($queryArray['schoolID']) && $queryArray['schoolID'] != 0) {
				$this->db->where('creditmemo.schoolID', $queryArray['schoolID']);
			}

			if(isset($queryArray['schoolyearID']) && $queryArray['schoolyearID'] != 0) {
				$this->db->where('creditmemo.schoolyearID', $queryArray['schoolyearID']);
			}

			if(isset($queryArray['classesID']) && $queryArray['classesID'] != 0) {
				$this->db->where('creditmemo.classesID', $queryArray['classesID']);
			}

			if(isset($queryArray['studentID']) && $queryArray['studentID'] != 0) {
				$this->db->where('creditmemo.studentID', $queryArray['studentID']);
			}

			if(isset($queryArray['fromdate']) && $queryArray['fromdate'] != "") {
				$this->db->where('creditmemo.date >=', $queryArray['fromdate']);
			}

			if(isset($queryArray['todate']) && $queryArray['todate'] != "") {
				$this->db->where('creditmemo.date <=', $queryArray['todate']);
			}

			if(isset($queryArray['termFrom']) && $queryArray['termFrom'] != "") {
				$this->db->where('creditmemo.date >=', $queryArray['termFrom']);
			}

			if(isset($queryArray['termTo']) && $queryArray['termTo'] != "") {
				$this->db->where('credimemo.date >=', $queryArray['termTo']);
			}
		}

		$query = $this->db->get();
		return $query->result();
	}

	public function get_dueamount($array) {
		$this->db->select('creditmemo.*,weaverandfine.weaver,weaverandfine.fine');
		$this->db->from('creditmemo');
		$this->db->join('weaverandfine','creditmemo.creditmemoID=weaverandfine.creditmemoID','LEFT');
		$this->db->where('creditmemo.schoolyearID',$array['schoolyearID']);
		$this->db->where('creditmemo.classesID',$array['classesID']);
		$this->db->where('creditmemo.deleted_at', 1);
		$query = $this->db->get();
		return $query->result();
	}

	public function get_creditmemo_sum($array = NULL) {
		if(isset($array['maincreditmemoID'])) {
			$string = "SELECT SUM(amount) AS amount, SUM(discount) AS discount, SUM((amount/100)*discount) AS discountamount, SUM(amount-((amount/100)*discount)) AS creditmemoamount FROM ".$this->_table_name." WHERE maincreditmemoID = '".$array['maincreditmemoID']."'";
		} else {
			$string = "SELECT SUM(amount) AS amount, SUM(discount) AS discount, SUM((amount/100)*discount) AS discountamount, SUM(amount-((amount/100)*discount)) AS creditmemoamount FROM ".$this->_table_name;
		}

		$query = $this->db->query($string);
		return $query->row();
	}
}
