<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Studentrelation_m extends MY_Model {

	protected $_table_name = 'studentrelation';
	protected $_primary_key = 'studentrelationID';
	protected $_primary_filter = 'intval';
	protected $_order_by = "srstudentID asc";

	protected $_relation_array = [];
	protected $_user_role_array = [2, 3, 4];


	function __construct() {
		parent::__construct();
	}

	protected $_extend_array = [
		'studentextendID',
		'studentgroupID',
		'optionalsubjectID',
		'nonexaminablesubjectID',
		'extracurricularactivities',
		'remarks'
	];

	private function prefixLoad($array) {
		if(is_array($array)) {
			if(customCompute($array)) {
				foreach ($array as $arkey =>  $ar) {
					if(in_array($arkey, $this->_extend_array)) {
						unset($array[$arkey]);
						$array['studentextend.'.$arkey] = $ar;
					} elseif(substr($arkey, 0, 2) == 'sr') {
						unset($array[$arkey]);
						$array['studentrelation.'.$arkey] = $ar;
					} elseif($arkey == 'divisionID') {
						unset($array[$arkey]);
						$array['classes.'.$arkey] = $ar;
					} else {
						unset($array[$arkey]);
						$array['student.'.$arkey] = $ar;
					}
				}
			}
		}

		return $array;
	}

	private function userRelation() {
		$usertypeID = $this->session->userdata('usertypeID');
		$userID = $this->session->userdata('loginuserID');
		if($usertypeID == 2) {
			$this->db->from('classes')->where(array('teacherID' => $userID))->order_by('classesID');
			$classQuery = $this->db->get();
			$classResult = $classQuery->result();

			$this->db->from('routine')->where(array('teacherID' => $userID))->order_by('classesID');
			$routineQuery = $this->db->get();
			$routineResult = $routineQuery->result();

			return mergeArray($classResult, $routineResult, 'classesID');
		} elseif($usertypeID == 3) {
			$schoolyearID = $this->session->userdata('defaultschoolyearID');
			$this->db->from('studentrelation');
    	$this->db->join('student', 'student.studentID = studentrelation.srstudentID', 'LEFT');
   		$this->db->where('studentrelation.srschoolyearID =', $schoolyearID);
   		$this->db->where('student.studentID !=', NULL);
   		$this->db->where('studentrelation.srstudentID', $userID);
   		$this->db->order_by($_order_by);
			$studentrelationQuery = $this->db->get();
			$studentrelationResult = $studentrelationQuery->result();

			$classesArray = pluck($studentrelationResult, 'srclassesID', 'srclassesID');
			return $classesArray;
		} elseif($usertypeID == 4) {
			$schoolyearID = $this->session->userdata('defaultschoolyearID');
			$this->db->from('studentrelation');
    	$this->db->join('student', 'student.studentID = studentrelation.srstudentID', 'LEFT');
   		$this->db->where('studentrelation.srschoolyearID =', $schoolyearID);
   		$this->db->where('student.parentID =', $userID);
   		$this->db->where('student.studentID !=', NULL);
   		$this->db->order_by($_order_by);
			$studentrelationQuery = $this->db->get();
			$studentrelationResult = $studentrelationQuery->result();

			$studentArray = array_unique(pluck($studentrelationResult, 'srstudentID', 'srstudentID'));
			ksort($studentArray);
			return $studentArray;
		}
	}

	public function general_get_student($studentExtend = FALSE) {
    $this->db->select('*');
    $this->db->from('studentrelation');
    $this->db->join('student', 'student.studentID = studentrelation.srstudentID', 'LEFT');

    if($studentExtend) {
    	$this->db->join('studentextend', 'studentextend.studentID = studentrelation.srstudentID', 'LEFT');
    }

    $this->db->where('student.studentID !=', NULL);
    $this->db->order_by($_order_by);
    $query = $this->db->get();
    return $query->result();
  }

	public function general_get_single_student($arrays = [], $studentExtend = FALSE) {
		$arrays = $this->prefixLoad($arrays);
    $this->db->select('*');
    $this->db->from('studentrelation');
    $this->db->join('student', 'student.studentID = studentrelation.srstudentID', 'LEFT');

    if($studentExtend) {
    	$this->db->join('studentextend', 'studentextend.studentID = studentrelation.srstudentID', 'LEFT');
    }

    if(customCompute($arrays)) {
        $this->db->where($arrays);
    }
    $this->db->where('student.studentID !=', NULL);
    $this->db->order_by($_order_by);
    $query = $this->db->get();
    return $query->row();
	}

	public function general_get_order_by_student($arrays = [], $studentExtend = FALSE) {
		$arrays = $this->prefixLoad($arrays);
    $this->db->select('*');
    $this->db->from('studentrelation');
    $this->db->join('student', 'student.studentID = studentrelation.srstudentID', 'LEFT');

    if($studentExtend) {
    	$this->db->join('studentextend', 'studentextend.studentID = studentrelation.srstudentID', 'LEFT');
    }

    if(customCompute($arrays)) {
        $this->db->where($arrays);
    }
    $this->db->where('student.studentID !=', NULL);
    $this->db->order_by($_order_by);
    $query = $this->db->get();
    return $query->result();
  }

	public function general_get_order_by_student_with_parent($arrays = [], $studentExtend = FALSE) {
		$arrays = $this->prefixLoad($arrays);
    $this->db->select('studentrelation.*, student.*, parents.name as parent_name, parents.father_name, parents.mother_name, parents.father_profession, parents.mother_profession, parents.email as parent_email, parents.phone as parent_phone, parents.address as parent_address, parents.photo as parent_photo, parents.username as parent_username');
    $this->db->from('studentrelation');
    $this->db->join('student', 'student.studentID = studentrelation.srstudentID', 'LEFT');
		$this->db->join('parents', 'parents.parentsID = student.parentID', 'LEFT');
		$this->db->join('studentgroup', 'studentrelation.srstudentgroupID = studentgroup.studentgroupID', 'LEFT');

    if($studentExtend) {
    	$this->db->join('studentextend', 'studentextend.studentID = studentrelation.srstudentID', 'LEFT');
    }

    if(customCompute($arrays)) {
      $this->db->where($arrays);
    }
    $this->db->where('student.studentID !=', NULL);
    $this->db->order_by($_order_by);
    $query = $this->db->get();
    return $query->result();
  }

  public function get_single_student($arrays = [], $studentExtend = FALSE) {
    $this->_relation_array = $this->userRelation();
		if(!customCompute($this->_relation_array) && in_array($this->session->userdata('usertypeID'), $this->_user_role_array)) {
			return [];
		}

		$arrays = $this->prefixLoad($arrays);
    $this->db->select('*');
    $this->db->from('studentrelation');
    $this->db->join('student', 'student.studentID = studentrelation.srstudentID', 'LEFT');

    if($studentExtend) {
    	$this->db->join('studentextend', 'studentextend.studentID = studentrelation.srstudentID', 'LEFT');
    }

    if($this->session->userdata('usertypeID') == 2) {
    	$this->db->where_in('studentrelation.srclassesID', $this->_relation_array);
    } elseif($this->session->userdata('usertypeID') == 3) {
    	$this->db->where_in('studentrelation.srclassesID', $this->_relation_array);
    } elseif($this->session->userdata('usertypeID') == 4) {
    	$this->db->where_in('studentrelation.srstudentID', $this->_relation_array);
    }

    if(customCompute($arrays)) {
      $this->db->where($arrays);
    }
    $this->db->where('student.studentID !=', NULL);
    $this->db->order_by($_order_by);
    $query = $this->db->get();
    return $query->row();
  }

	public function get_single_student2($arrays = [], $studentExtend = FALSE) {
		$arrays = $this->prefixLoad($arrays);
    $this->db->select('*');
    $this->db->from('studentrelation');
    $this->db->join('student', 'student.studentID = studentrelation.srstudentID', 'LEFT');

    if($studentExtend) {
    	$this->db->join('studentextend', 'studentextend.studentID = studentrelation.srstudentID', 'LEFT');
    }

    if(customCompute($arrays)) {
      $this->db->where($arrays);
    }
    $this->db->where('student.studentID !=', NULL);
    $this->db->order_by($_order_by);
    $query = $this->db->get();
    return $query->row();
  }

	public function get_order_by_student($arrays = [], $studentExtend = FALSE) {
		$this->_relation_array = $this->userRelation();
		/*if(!customCompute($this->_relation_array) && in_array($this->session->userdata('usertypeID'), $this->_user_role_array)) {
			return [];
		}*/

		$arrays = $this->prefixLoad($arrays);
    $this->db->select('studentrelation.*, student.*, classes.*, parents.name as parent_name, parents.phone as parent_phone, parents.email as parent_email');
    $this->db->from('studentrelation');
    $this->db->join('student', 'student.studentID = studentrelation.srstudentID', 'LEFT');
		$this->db->join('classes', 'classes.classesID = studentrelation.srclassesID', 'LEFT');
		$this->db->join('parents', 'student.parentID = parents.parentsID', 'LEFT');

    if($studentExtend) {
    	$this->db->join('studentextend', 'studentextend.studentID = studentrelation.srstudentID', 'LEFT');
    }

    /*if($this->session->userdata('usertypeID') == 2) {
    	$this->db->where_in('studentrelation.srclassesID', $this->_relation_array);
    }*/ if($this->session->userdata('usertypeID') == 3) {
    	$this->db->where_in('studentrelation.srclassesID', $this->_relation_array);
    } elseif($this->session->userdata('usertypeID') == 4) {
    	$this->db->where_in('studentrelation.srstudentID', $this->_relation_array);
    }

		if(customCompute($arrays)) {
			$arrays = array_filter($arrays);
			if(isset($arrays['studentrelation.sroptionalsubjectID'])) {
				$this->db->where("FIND_IN_SET(".$arrays['studentrelation.sroptionalsubjectID'].",studentrelation.sroptionalsubjectID) >", 0);
				unset($arrays['studentrelation.sroptionalsubjectID']);
			}
			if(isset($arrays['studentrelation.srnonexaminablesubjectID'])) {
				$this->db->where("FIND_IN_SET(".$arrays['studentrelation.srnonexaminablesubjectID'].",studentrelation.srnonexaminablesubjectID) >", 0);
				unset($arrays['studentrelation.srnonexaminablesubjectID']);
			}
      $this->db->where($arrays);
    }
    $this->db->where('student.studentID !=', NULL);
    $this->db->order_by($_order_by);
    $query = $this->db->get();
    return $query->result();
  }

	public function get_studentrelation_join_student($arrays = [], $single = FALSE) {
    $arrays = $this->prefixLoad($arrays);
    $this->db->select('*');
    $this->db->from('studentrelation');
    $this->db->join('student', 'student.studentID = studentrelation.srstudentID', 'LEFT');
    if(customCompute($arrays)) {
        $this->db->where($arrays);
    }
    $this->db->order_by($_order_by);
    $query = $this->db->get();

    if($single) {
        return $query->row();
    } else {
        return $query->result();
    }
	}

	public function get_studentrelation_join_with_student_student_extend($arrays = [], $single = FALSE) {
		$arrays = $this->prefixLoad($arrays);
		$this->db->select('*');
		$this->db->from('studentrelation');
        $this->db->join('student', 'student.studentID = studentrelation.srstudentID', 'LEFT');
		$this->db->join('studentextend', 'studentextend.studentID = studentrelation.srstudentID', 'LEFT');
		if(customCompute($arrays)) {
			$this->db->where($arrays);
		}
		$query = $this->db->get();

		if($single) {
			return $query->row();
		} else {
			return $query->result();
		}
	}

	public function update_studentrelation_with_multicondition($array, $multiCondition) {
		$this->db->update($this->_table_name, $array, $multiCondition);
	}

	public function get_studentrelation($array=NULL, $signal=FALSE) {
		$query = parent::get($array, $signal);
		return $query;
	}

	public function get_order_by_studentrelation($array=NULL) {
		$query = parent::get_order_by($array);
		return $query;
	}

	public function get_single_studentrelation($array=NULL) {
		$query = parent::get_single($array);
		return $query;
	}

	public function insert_studentrelation($array) {
		$error = parent::insert($array);
		return $error;
	}

	public function update_studentrelation($data, $id = NULL) {
		parent::update($data, $id);
		return $id;
	}

	public function delete_studentrelation($id){
		parent::delete($id);
	}

  public function get_studentrelation_studentArray($array=NULL, $key=FALSE) {
      $query = parent::get_where_in($array, $key);
      return $query;
  }
}
