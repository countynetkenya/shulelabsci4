<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
use OpenApi\Annotations as OA;

class Sponsor extends Api_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model("sponsor_m");
        $this->load->model("student_m");
        $this->retdata['titles'] = array_merge(['0' => $this->lang->line('sponsor_select_title')], $this->sponsor_m->titles);

    }

    /**
       * @OA\Get(
       *     path="/api/v10/sponsor",
       *     @OA\Response(
       *         response="200",
       *         description="Success"
       *     )
       * )
       */
    public function index_get()
    {
        $this->retdata['allcountry']           = $this->getAllCountry();
        $this->retdata['sponsors'] = $this->sponsor_m->get_order_by_sponsor(array('schoolID' => $this->session->userdata('schoolID')));
            $this->response([
                'status'    => true,
                'message'   => 'Success',
                'data'      => $this->retdata
            ], REST_Controller::HTTP_OK);
    }

    /**
       * @OA\Get(
       *     path="/api/v10/sponsor/view/{sponsorID}",
       *     @OA\Parameter(
       *         name="sponsorID",
       *         description="",
       *         in = "path",
       *         allowMultiple=false,
       *         required=true,
       *         @OA\Schema(
       *             default=0,
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
    public function view_get($id = 0)
    {

        if((int)$id) {
            $this->retdata['sponsor'] = $this->sponsor_m->get_single_sponsor(array('sponsorID' => $id, 'schoolID' => $this->session->userdata('schoolID')));
            $this->retdata['allcountry']           = $this->getAllCountry();

            if(customCompute($this->retdata['sponsor'])) {
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
