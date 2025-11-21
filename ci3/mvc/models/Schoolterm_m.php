<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Schoolterm_m extends MY_Model
{

	protected $_table_name = 'schoolterm';
	protected $_primary_key = 'schooltermID';
	protected $_primary_filter = 'intval';
	protected $_order_by = "schooltermID desc";

	function __construct()
	{
		parent::__construct();
	}

	public function get_schoolterm($array=NULL, $signal=FALSE)
	{
		$query = parent::get($array, $signal);
		return $query;
	}

	public function get_order_by_schoolterm($array=NULL)
	{
		$query = parent::get_order_by($array);
		return $query;
	}

	public function get_single_schoolterm($array=NULL)
	{
		$query = parent::get_single($array);
		return $query;
	}

	public function insert_schoolterm($array)
	{
		parent::insert($array);
		return TRUE;
	}

	public function update_schoolterm($data, $id = NULL)
	{
		parent::update($data, $id);
		return $id;
	}

	public function delete_schoolterm($id)
	{
		parent::delete($id);
	}

	public function get_obj_schoolterm($schooltermID = 1)
	{
		$schoolterm = $this->get_single_schoolterm(array('schooltermID' => $schooltermID));
		$schooltermArray = [];
		if(is_object($schoolterm)) {
			$startingDate = explode('-', $schoolterm->startingdate);
			$endingDate   = explode('-', $schoolterm->endingdate);

			$schooltermArray['schooltermID'] = $schoolterm->schooltermID;
			$schooltermArray['startingday'] = $startingDate[2];
			$schooltermArray['endingday']   = $endingDate[2];
			$schooltermArray['startingmonth'] = $startingDate[1];
			$schooltermArray['endingmonth']   = $endingDate[1];
			$schooltermArray['startingyear'] = $startingDate[0];
			$schooltermArray['endingyear']   = $endingDate[0];
			$schooltermArray['startingdate']   = date('d-m-Y', strtotime($schoolterm->startingdate));
			$schooltermArray['endingdate']   = date('d-m-Y', strtotime($schoolterm->endingdate));
			$schooltermArray['schooltermtitle'] = $schoolterm->schooltermtitle;
		}
		return (object) $schooltermArray;
	}
}
