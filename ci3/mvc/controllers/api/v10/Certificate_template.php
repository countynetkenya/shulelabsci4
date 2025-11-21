<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
use OpenApi\Annotations as OA;

class Certificate_template extends Api_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('certificate_template_m');
        $this->load->model('mailandsmstemplatetag_m');

        $this->lang->load('certificate_template', $this->data['language']);
        $this->retdata['buildinThemes'] = $array = array(
            '0' => $this->lang->line('certificate_template_select_theme'),
            '1' => $this->lang->line('certificate_template_theme1'),
            '2' => $this->lang->line('certificate_template_theme2')
        );
    }

    /**
       * @OA\Get(
       *     path="/api/v10/certificate_template",
       *     @OA\Response(
       *         response="200",
       *         description="Success"
       *     )
       * )
       */
    public function index_get()
    {
        $this->retdata['certificatetemplates'] = $this->certificate_template_m->get_order_by_certificate_template(array('schoolID' => $this->session->userdata('schoolID')));
        $this->response([
            'status'    => true,
            'message'   => 'Success',
            'data'      => $this->retdata
        ], REST_Controller::HTTP_OK);
    }

    /**
       * @OA\Get(
       *     path="/api/v10/certificate_template/view/{certificate_templateID}",
       *     @OA\Parameter(
       *         name="certificate_templateID",
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
            $this->retdata['themes'] = array(
                '1' => 'theme1',
                '2' => 'theme2'
            );
            $this->retdata['certificatetemplate'] = $this->certificate_template_m->get_single_certificate_template(array('certificate_templateID' => $id, 'schoolID' => $this->session->userdata('schoolID')));
            if(customCompute($this->retdata['certificatetemplate'])) {
                $this->retdata['templateconvert'] = $this->studentTagHiglightForTemplate($this->retdata['certificatetemplate']->template);
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

    private function studentTagHiglightForTemplate($message)
    {
        return $message;
    }
}
