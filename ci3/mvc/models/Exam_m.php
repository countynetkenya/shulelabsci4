<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Exam_m extends MY_Model {

	protected $_table_name = 'exam';
	protected $_primary_key = 'examID';
	protected $_primary_filter = 'intval';
	protected $_order_by = "examID asc";

	public function __construct() {
		parent::__construct();
	}

	private function prefixLoad($array) {
		if(is_array($array)) {
			if(customCompute($array)) {
				foreach ($array as $arkey =>  $ar) {
					if(strpos($arkey, 'examID') !== false || $arkey == "schoolID") {
						unset($array[$arkey]);
						$array['exam.'.$arkey] = $ar;
					}
				}
			}
		}

		return $array;
	}

	public function get_exam($array=NULL, $signal=FALSE) {
		$query = parent::get($array, $signal);
		return $query;
	}

	public function get_single_exam($array) {
		$query = parent::get_single($array);
		return $query;
	}

	public function get_order_by_exam($array=NULL) {
		$query = parent::get_order_by($array);
		return $query;
	}

	public function get_exam_wherein($array) {
    $this->db->select('*');
		$this->db->from($this->_table_name);
		$this->db->where('schoolID', $array['schoolID']);
		$this->db->where_in('examID', $array['examID']);
		$this->db->order_by('examID', 'asc');
		$query = $this->db->get();
    return $query->result();
  }

	public function insert_exam($array) {
		$error = parent::insert($array);
		return TRUE;
	}

	public function update_exam($data, $id = NULL) {
		parent::update($data, $id);
		return $id;
	}

	public function delete_exam($id){
		parent::delete($id);
	}

	public function get_latest_exam($array) {
		$this->db->select('*');
		$this->db->from('exam');
		$this->db->join('mark', 'exam.examID = mark.examID');
		$this->db->where($array);
		$this->db->order_by('exam.examID', 'desc');
		$query = $this->db->get();
		return $query->row();
	}

	public function get_last_six_exams($array=NULL) {
		$array = $this->prefixLoad($array);
		$this->db->select('*');
		$this->db->from('exam');
		$this->db->join('mark', 'exam.examID = mark.examID');
		$this->db->where($array);
		$this->db->order_by('exam.examID', 'desc');
		$this->db->group_by("mark.examID");
		$this->db->limit(6);
		$query = $this->db->get();
		return $query->result();
	}

	public function get_exam_with_previous($array = null) {
		$array = $this->prefixLoad($array);
		$this->db->select('*');
		$this->db->from('exam');
		$this->db->join('mark', 'exam.examID = mark.examID');
		//$this->db->where('exam.examID <=', $examID);
		$this->db->where($array);
		$this->db->order_by('exam.examID', 'desc');
		$this->db->group_by("mark.examID");
		$this->db->limit(2);
		$query = $this->db->get();
		return $query->result();
	}

	public function get_last_six_exams_with_subject($teacherID) {
		$this->db->select('exam.*, mark.*');
		$this->db->from('exam');
		$this->db->join('mark', 'exam.examID = mark.examID');
		$this->db->join('subjectteacher', 'mark.subjectID = subjectteacher.subjectID', 'LEFT');
		$this->db->where('subjectteacher.teacherID', $teacherID);
		$this->db->order_by('exam.examID', 'desc');
		$this->db->group_by("mark.examID");
		$this->db->limit(6);
		$query = $this->db->get();
		return $query->result();
	}
}

/* End of file exam_m.php */
/* Location: .//D/xampp/htdocs/school/mvc/models/exam_m.php */
