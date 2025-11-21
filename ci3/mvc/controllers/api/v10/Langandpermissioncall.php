<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
use OpenApi\Annotations as OA;

class Langandpermissioncall extends Api_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->methods['users_get']['limit']    = 500;
        $this->methods['users_post']['limit']   = 100;
        $this->methods['users_delete']['limit'] = 50;
    }

    /**
       * @OA\Get(
       *     path="/api/v10/langandpermissioncall",
       *     @OA\Response(
       *         response="200",
       *         description="Success"
       *     )
       * )
       */
    public function index_get($langnName = null)
    {
        $this->retdata['permission'] = $this->data['permission'];

        $this->lang->load('topbar_menu', $this->data["language"]);
        $this->retdata['language'] = $this->lang->language;

        if($langnName) {
            $this->lang->load($langnName, $this->data['language']);
            $this->retdata['language'] = $this->lang->language;
        }

        $this->response([
            'status'    => true,
            'message'   => 'Success',
            'data'      => $this->retdata
        ], REST_Controller::HTTP_OK);
    }

    /**
       * @OA\Get(
       *     path="/api/v10/langandpermissioncall/permission",
       *     @OA\Response(
       *         response="200",
       *         description="Success"
       *     )
       * )
       */
    public function permission_get()
    {
        $this->retdata['permission'] = $this->data['permission'];

        $this->response([
            'status'    => true,
            'message'   => 'Success',
            'data'      => $this->retdata
        ], REST_Controller::HTTP_OK);
    }

    /**
       * @OA\Get(
       *     path="/api/v10/langandpermissioncall/language/{langname}",
       *     @OA\Parameter(
       *         name="langname",
       *         description="Language name e.g. english",
       *         in = "path",
       *         allowMultiple=false,
       *         required=true,
       *         @OA\Schema(
       *             default=null,
       *             type="string"
       *         )
       *     ),
       *     @OA\Response(
       *         response="200",
       *         description="Success"
       *     )
       * )
       */
    public function language_get($langnName = null)
    {
        $this->lang->load('topbar_menu', $this->data["language"]);
        $this->retdata['language'] = $this->lang->language;

        if($langnName) {
            $this->lang->load($langnName, $this->data['language']);
            $this->retdata['language'] = $this->lang->language;
        }

        $this->response([
            'status'    => true,
            'message'   => 'Success',
            'data'      => $this->retdata
        ], REST_Controller::HTTP_OK);
    }
}
