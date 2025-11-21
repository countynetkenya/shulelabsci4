<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
use OpenApi\Annotations as OA;

class Expense extends Api_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('expense_m');
    }

    /**
       * @OA\Get(
       *     path="/api/v10/expense",
       *     @OA\Response(
       *         response="200",
       *         description="Success"
       *     )
       * )
       */
    public function index_get()
    {
        $schoolyearID = $this->session->userdata('defaultschoolyearID');
        $this->retdata['expenses'] = $this->expense_m->get_expense_with_user(array('expense.schoolyearID' => $schoolyearID, 'schoolID' => $this->session->userdata('schoolID')));

        $this->response([
            'status'    => true,
            'message'   => 'Success',
            'data'      => $this->retdata
        ], REST_Controller::HTTP_OK);
    }
}
