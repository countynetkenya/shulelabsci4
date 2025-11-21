<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
use OpenApi\Annotations as OA;

class Mailandsmstemplate extends Api_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('usertype_m');
        $this->load->model("mailandsmstemplate_m");
        $this->load->model("mailandsmstemplatetag_m");
    }

    /**
       * @OA\Get(
       *     path="/api/v10/mailandsmstemplate",
       *     @OA\Response(
       *         response="200",
       *         description="Success"
       *     )
       * )
       */
    public function index_get()
    {
        $this->retdata['mailandsmstemplates'] = $this->mailandsmstemplate_m->get_order_by_mailandsmstemplate_with_usertypeID(array('schoolID' => $this->session->userdata('schoolID')));
        $this->response([
            'status'    => true,
            'message'   => 'Success',
            'data'      => $this->retdata
        ], REST_Controller::HTTP_OK);
    }

    /**
       * @OA\Get(
       *     path="/api/v10/mailandsmstemplate/view/{mailandsmstemplateID}",
       *     @OA\Parameter(
       *         name="mailandsmstemplateID",
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
    public function view_get($id=null)
    {
        if((int)$id) {
            $this->retdata['mailandsmstemplate'] = $this->mailandsmstemplate_m->get_single_mailandsmstemplate(array('mailandsmstemplateID' => $id, 'schoolID' => $this->session->userdata('schoolID')));
            if($this->retdata['mailandsmstemplate']) {
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
