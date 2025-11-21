<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
use OpenApi\Annotations as OA;

class Asset extends Api_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('asset_m');
    }

    /**
       * @OA\Get(
       *     path="/api/v10/asset",
       *     @OA\Response(
       *         response="200",
       *         description="Success"
       *     )
       * )
       */
    public function index_get()
    {
        $this->retdata['assets'] = $this->asset_m->get_asset_with_category_and_location(array('schoolID' => $this->session->userdata('schoolID')));
        $this->response([
            'status'    => true,
            'message'   => 'Success',
            'data'      => $this->retdata
        ], REST_Controller::HTTP_OK);
    }

    /**
       * @OA\Get(
       *     path="/api/v10/asset/view/{assetID}",
       *     @OA\Parameter(
       *         name="assetID",
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
            $this->retdata['asset'] = $this->asset_m->get_single_asset_with_category_and_location(array('asset.assetID' => $id));
            if(customCompute($this->retdata['asset'])) {
                $this->response([
                    'status'    => true,
                    'message'   => 'Success',
                    'data'      => $this->retdata
                ], REST_Controller::HTTP_OK);
            } else {
                $this->response([
                    'status'    => false,
                    'message'   => 'Error 404',
                    'data'      => [],
                ], REST_Controller::HTTP_NOT_FOUND);
            }
        } else {
            $this->response([
                'status'    => false,
                'message'   => 'Error 404',
                'data'      => [],
            ], REST_Controller::HTTP_NOT_FOUND);
        }
    }
}
