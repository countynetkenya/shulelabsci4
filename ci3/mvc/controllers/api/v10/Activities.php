<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *     title="Shulelabs API",
 *     version="10"
 * )
 */
class Activities extends Api_Controller
{
	public function __construct()
    {
        parent::__construct();
		$this->load->model("activities_m");
		$this->load->model("activitiescategory_m");
		$this->load->model("activitiesmedia_m");
		$this->load->model("activitiescomment_m");
	}

	/**
     * @OA\Get(
     *     path="/api/v10/activities",
     *     @OA\Response(
     *         response="200",
     *         description="Success"
     *     )
     * )
     */
	public function index_get()
    {
				$schoolID = $this->session->userdata('schoolID');
        $schoolyearID = $this->session->userdata('defaultschoolyearID');
        $this->retdata['user'] = getAllSelectUser();
        $this->retdata['userID'] = $this->session->userdata('loginuserID');
        $this->retdata['usertypeID'] = $this->session->userdata('usertypeID');
        $this->retdata['activitiescategories'] = pluck($this->activitiescategory_m->get_activitiescategory(array('schoolID' => $schoolID)), 'obj', 'activitiescategoryID');
        $this->retdata['activities'] = $this->activities_m->get_order_by_activities(array('schoolyearID' => $schoolyearID, 'schoolID' => $schoolID));
        $this->retdata['activitiesmedia'] = pluck_multi_array($this->activitiesmedia_m->get_activitiesmedia(array('schoolID' => $schoolID)), 'obj', 'activitiesID');
        $this->retdata['activitiescomments'] = pluck_multi_array($this->activitiescomment_m->get_order_by_activitiescomment(array('schoolyearID' => $schoolyearID, 'schoolID' => $schoolID)), 'obj', 'activitiesID');

        $this->response([
            'status'    => true,
            'message'   => 'Success',
            'data'      => $this->retdata
        ], REST_Controller::HTTP_OK);
	}
}
