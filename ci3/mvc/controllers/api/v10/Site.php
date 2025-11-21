<?php

use Restserver\Libraries\REST_Controller;

defined('BASEPATH') OR exit('No direct script access allowed');
use OpenApi\Annotations as OA;

require APPPATH . 'libraries/REST_Controller.php';
require APPPATH . 'libraries/Format.php';

class Site extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('setting_m');
    }

    /**
       * @OA\Get(
       *     path="/api/v10/site",
       *     @OA\Response(
       *         response="200",
       *         description="Success"
       *     )
       * )
       */
    public function index_get()
    {
        $setting            = $this->setting_m->get_setting(0);
        $array = [];
        if ( customCompute($setting) ) {
            $array['sitename']  = $setting->sname;
            $array['logo']      = $setting->photo;
            $array['phone']     = $setting->phone;
            $array['email']     = $setting->email;
            $array['address']   = $setting->address;
            $array['copyright'] = $setting->footer;
        }

        $this->response([
            'status'    => true,
            'message'   => 'Success',
            'data'      => (object) $array
        ], REST_Controller::HTTP_OK);
    }
}
