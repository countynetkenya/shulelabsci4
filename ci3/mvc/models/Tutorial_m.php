<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Tutorial_m extends MY_Model
{
    protected $_table_name     = "tutorial";
    protected $_primary_key    = "tutorial_id";
    protected $_primary_filter = "intval";
    protected $_order_by       = "tutorial_id desc";

    public function __construct()
    {
        parent::__construct();
    }

    public function get_tutorial($array = null, $signal = false)
    {
        return parent::get($array, $signal);
    }

    public function get_single_tutorial($array)
    {
        return parent::get_single($array);
    }

    public function get_order_by_tutorial($array = null)
    {
        return parent::get_order_by($array);
    }

    public function insert_tutorial($array)
    {
        return parent::insert($array);
    }

    public function update_tutorial($data, $id = null)
    {
        return parent::update($data, $id);
    }

    public function delete_tutorial($id)
    {
        return parent::delete($id);
    }

    public function get_tutorial_with_is_public()
    {

        $classes = pluck($this->classes_m->get_classes(), 'classesID', 'classesID');

        $sections     = [];
        $usertypeID   = $this->session->userdata("usertypeID");
        $loginuserID  = $this->session->userdata('loginuserID');
        $schoolyearID = $this->session->userdata('defaultschoolyearID');
        if ($usertypeID == 3) {
            $student = $this->studentrelation_m->get_single_student(array('srstudentID' => $loginuserID, 'srschoolyearID' => $schoolyearID), false);

            $sections[$student->sectionID] = $student->sectionID;

            $subjects = pluck($this->subject_m->get_order_by_subject(['type' => 1]), 'subjectID', 'subjectID');
            if ((int) $student->sroptionalsubjectID) {
                $subjects[$student->sroptionalsubjectID] = $student->sroptionalsubjectID;
            }
        } else {
            $sections = pluck($this->section_m->get_section(), 'sectionID', 'sectionID');
            $subjects = pluck($this->subject_m->get_subject(), 'subjectID', 'subjectID');
        }

        $this->db->select('*');
        $this->db->from('tutorial');
        $this->db->where('is_public', 0);

        if (customCompute($classes) || customCompute($sections) || customCompute($subjects)) {
            $this->db->or_group_start();

            if (customCompute($classes)) {
                $this->db->group_start();
                foreach ($classes as $classesID) {
                    $this->db->or_where('classesID', $classesID);
                }
                $this->db->or_where('classesID', 0);
                $this->db->group_end();
            }

            if (customCompute($sections)) {
                $this->db->group_start();
                foreach ($sections as $sectionID) {
                    $this->db->or_where('sectionID', $sectionID);
                }
                $this->db->or_where('sectionID', 0);
                $this->db->group_end();
            }

            if (customCompute($subjects)) {
                $this->db->group_start();
                foreach ($subjects as $subjectID) {
                    $this->db->or_where('subjectID', $subjectID);
                }
                $this->db->or_where('subjectID', 0);
                $this->db->group_end();
            }

            $this->db->group_end();
        }

        $this->db->order_by('tutorial_id desc');
        $query = $this->db->get();
        return $query->result();
    }

    public function get_single_tutorial_with_is_public($array)
    {

        $classes = pluck($this->classes_m->get_classes(), 'classesID', 'classesID');

        $sections     = [];
        $usertypeID   = $this->session->userdata("usertypeID");
        $loginuserID  = $this->session->userdata('loginuserID');
        $schoolyearID = $this->session->userdata('defaultschoolyearID');
        if ($usertypeID == 3) {
            $student                       = $this->studentrelation_m->get_single_student(array('srstudentID' => $loginuserID, 'srschoolyearID' => $schoolyearID), false);
            $sections[$student->sectionID] = $student->sectionID;

            $subjects = pluck($this->subject_m->get_order_by_subject(['type' => 1]), 'subjectID', 'subjectID');
            if ((int) $student->sroptionalsubjectID) {
                $subjects[$student->sroptionalsubjectID] = $student->sroptionalsubjectID;
            }
        } else {
            $sections = pluck($this->section_m->get_section(), 'sectionID', 'sectionID');
            $subjects = pluck($this->subject_m->get_subject(), 'subjectID', 'subjectID');
        }

        $this->db->select('*');
        $this->db->from('tutorial');

        $this->db->group_start();
            $this->db->where('is_public', 0);
            if (customCompute($classes) || customCompute($sections) || customCompute($subjects)) {
                $this->db->or_group_start();

                    if (customCompute($classes)) {
                        $this->db->group_start();
                            foreach ($classes as $classesID) {
                                $this->db->or_where('classesID', $classesID);
                            }
                            $this->db->or_where('classesID', 0);
                        $this->db->group_end();
                    }

                    if (customCompute($sections)) {
                        $this->db->group_start();
                            foreach ($sections as $sectionID) {
                                $this->db->or_where('sectionID', $sectionID);
                            }
                            $this->db->or_where('sectionID', 0);
                        $this->db->group_end();
                    }

                    if (customCompute($subjects)) {
                        $this->db->group_start();
                            foreach ($subjects as $subjectID) {
                                $this->db->or_where('subjectID', $subjectID);
                            }
                            $this->db->or_where('subjectID', 0);
                        $this->db->group_end();
                    }

                $this->db->group_end();
            }
        $this->db->group_end();

        $this->db->where('tutorial_id', $array['tutorial_id']);
        $query = $this->db->get();
        return $query->row();
    }
}
