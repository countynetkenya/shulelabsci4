<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
use OpenApi\Annotations as OA;

class Mailandsms extends Api_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('mailandsms_m');
    }

    /**
       * @OA\Get(
       *     path="/api/v10/mailandsms",
       *     @OA\Response(
       *         response="200",
       *         description="Success"
       *     )
       * )
       */
    public function index_get()
    {
        $schoolID = $this->session->userdata('schoolID');
        $usertypeID = $this->session->userdata("usertypeID");
        $userID = $this->session->userdata("loginuserID");
        $array = ["schoolID" => $schoolID, "reviewed" => 1];
        if ($usertypeID == 4)
          $array["userID"] = $userID;
        $this->retdata['mailandsms'] = $this->mailandsms_m->get_mailandsms_with_usertypeID($array);
        $this->response([
            'status'    => true,
            'message'   => 'Success',
            'data'      => $this->retdata
        ], REST_Controller::HTTP_OK);
    }

    /**
       * @OA\Get(
       *     path="/api/v10/mailandsms/view/{mailandsmsID}",
       *     @OA\Parameter(
       *         name="mailandsmsID",
       *         description="",
       *         in = "path",
       *         allowMultiple=false,
       *         required=true,
       *         @OA\Schema(
       *             default=null,
       *             type="integer"
       *         )
       *     ),
       *     @OA\Response(
       *         response="200",
       *         description="Success"
       *     ),
       *     @OA\Response(
       *         response="404",
       *         description="Error 404"
       *     ),
       * )
       */
    public function view_get($id = null)
    {
        if((int)$id) {
            $this->retdata['mailandsms'] = $this->mailandsms_m->get_single_mailandsms(array('mailandsmsID' => $id, 'schoolID' => $this->session->userdata('schoolID')));
            if(customCompute($this->retdata['mailandsms'])) {
                $this->response([
                    'status'    => true,
                    'message'   => 'Success',
                    'data'      => $this->retdata
                ], REST_Controller::HTTP_OK);
            } else {
                $this->response([
                    'status' => false,
                    'message' => 'Error 404',
                    'data' => []
                ], REST_Controller::HTTP_NOT_FOUND);
            }
        } else {
            $this->response([
                'status' => false,
                'message' => 'Error 404',
                'data' => []
            ], REST_Controller::HTTP_NOT_FOUND);
        }
    }
}
