<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
require_once(APPPATH . 'libraries/PaymentGateway/PaymentGateway.php');
use OpenApi\Annotations as OA;

class Payment extends Api_Controller
{

	public $payment_gateway;

	public function __construct()
	{
		parent::__construct();

		$this->load->model("studentrelation_m");
		$this->load->model("schoolterm_m");
		$this->load->model('mainmpesa_m');
		$this->load->model('mpesa_m');
		$this->load->model('payment_m');
		$this->load->model('invoice_m');
		$this->load->model('creditmemo_m');

		$this->lang->load('payment', $this->data['language']);
		$this->lang->load('global_payment', $this->data['language']);
		$this->payment_gateway       = new PaymentGateway();
	}

        /**
         * @OA\Post(
         *     path="/api/v10/payment",
         *     summary="Retrieve parent payment overview",
         *     description="Returns balances for the authenticated guardian after validating student payment items.",
         *     @OA\RequestBody(
         *         required=false,
         *         @OA\MediaType(
         *             mediaType="application/json",
         *             @OA\Schema(
         *                 type="object",
         *                 @OA\Property(
         *                     property="studentitems",
         *                     type="array",
         *                     @OA\Items(
         *                         type="object",
         *                         required={"studentID", "amount"},
         *                         @OA\Property(property="studentID", type="integer", example=101),
         *                         @OA\Property(property="amount", type="number", format="float", example=2000)
         *                     )
         *                 )
         *             )
         *         )
         *     ),
         *     @OA\Response(
         *         response="200",
         *         description="Success"
         *     )
         * )
         */
	public function index_post()
	{
		if(($this->data['siteinfos']->school_year == $this->session->userdata('defaultschoolyearID') || $this->session->userdata('usertypeID') == 4)) {

			$schoolID = $this->session->userdata('schoolID');
			$schoolyearID = $this->session->userdata('defaultschoolyearID');
			$userID = $this->session->userdata('loginuserID');
			$this->retdata['schoolterms'] = $this->schoolterm_m->get_order_by_schoolterm(array('schoolyearID' => $schoolyearID, 'schoolID' => $schoolID));
			$students = $this->studentrelation_m->get_order_by_student(['srschoolyearID' => $schoolyearID, 'parentID' => $userID]);
			foreach ($students as $student) {
				$allPaymentList = $this->payment_m->get_order_by_payment(['studentID' => $student->studentID]);
				$totalPaymentAmount = $this->generateAllPaymentAmount($allPaymentList);
				$invoices = $this->invoice_m->get_order_by_invoice(['studentID' => $student->studentID]);
				$totalInvoiceAmount = $this->generateAllInvoiceAmount($invoices);
				$creditmemos = $this->creditmemo_m->get_order_by_creditmemo(['studentID' => $student->studentID]);
				$totalCreditmemoAmount = $this->generateAllCreditmemoAmount($creditmemos);
				$balance = $totalInvoiceAmount - ($totalPaymentAmount + $totalCreditmemoAmount);
				$student->balance = $balance;
			}

			$this->retdata['students'] = $students;

			$this->response([
		            'status' => true,
		            'message' => 'Success',
		            'data' => $this->retdata
		        ], REST_Controller::HTTP_OK);
		}
	}

	protected function paymentRules() {
        $rules = array(
			array(
                'field' => 'phonenumber',
                'label' => $this->lang->line("global_phone_number"),
                'rules' => 'trim|xss_clean|required|numeric|regex_match[/^254/]|exact_length[12]'
            ),
			/*array(
                'field' => 'schooltermID',
                'label' => $this->lang->line("payment_schooltermID"),
                'rules' => 'trim|required|xss_clean|numeric'
            ),*/
			array(
                'field' => 'studentitems',
                'label' => $this->lang->line("payment_studentitem"),
                'rules' => 'trim|xss_clean|required'
            ),
        );
        return $rules;
    }

	public function unique_studentitems()
    {
        $studentitems = json_decode($this->input->post('studentitems'));
        $status       = [];
        if(customCompute($studentitems)) {
            foreach($studentitems as $studentitem) {
                if($studentitem->amount == '') {
                    $status[] = FALSE;
                }
            }
        } else {
            $this->form_validation->set_message("unique_studentitems", "The payment amount is required.");
            return FALSE;
        }

        if(in_array(FALSE, $status)) {
            $this->form_validation->set_message("unique_studentitems", "The payment amount is required.");
            return FALSE;
        }
        return TRUE;
    }

		/**
 * @OA\Post(
 *   path="/api/v10/payment/save_payment",
 *   summary="M-Pesa STK Push",
 *   description="Make an M-Pesa STK Push request",
 *   @OA\RequestBody(
 *       required=true,
 *       @OA\MediaType(
 *           mediaType="application/json",
 *           @OA\Schema(
 *               type="object",
 *               @OA\Property(
 *                   property="phonenumber",
 *                   description="Safaricom phone number",
 *                   type="string",
 *                   example="0720000000"
 *               ),
 *               @OA\Property(
 *                   property="studentitems",
 *                   description="List of student payment instructions in KSh",
 *                   type="array",
 *                   @OA\Items(
 *                       type="object",
 *                       required={"studentID", "amount"},
 *                       @OA\Property(property="studentID", type="integer", example=101),
 *                       @OA\Property(property="amount", type="number", format="float", example=2000)
 *                   ),
 *                   example="[{"studentID":101, "amount":2000}]"
 *               ),
 *           )
 *       )
 *   ),
 *  @OA\Response(
 *         response="200",
 *         description="Success"
 *     ),
 *  @OA\Response(
 *         response="400",
 *         description="Invalid phonenumber or student item"
 *     ),
 *   @OA\Response(response="501",description="The POST method is not found"),
 * )
 */
	public function savepayment_post()
	{
		if(inputCall()) {
			$_POST = inputCall();
			$rules = $this->paymentRules();
			$this->form_validation->set_rules($rules);
			if ($this->form_validation->run() == false) {
    		$this->retdata2['validation'] = $this->form_validation->error_array();
				$this->response([
	                'status' => false,
	                'message' => 'Validation Error',
	                'data' => $this->retdata2,
	            ], REST_Controller::HTTP_NOT_FOUND);
			} else {
        $studentitems = json_decode(inputCall('studentitems'), true);
				$phonenumber  = inputCall('phonenumber');
				//$schooltermID = inputCall('schooltermID');
				$schoolID     = $this->session->userdata('schoolID');
				$schoolyearID = $this->session->userdata('defaultschoolyearID');
				$parentID     = $this->session->userdata('loginuserID');
				$paymentdate  = date("Y-m-d");
				$totalamount  = 0;

				foreach($studentitems as $studentitem) {
					$totalamount += $studentitem['amount'];
				}

				$mainmpesa['parentID']        = $parentID;
				$mainmpesa['schoolyearID']    = $schoolyearID;
				$mainmpesa['schoolID']        = $schoolID;
				$mainmpesa['amount']          = $totalamount;
				$mainmpesa['paymentdate']     = $paymentdate;
				$mainmpesa['phonenumber']     = $phonenumber;
				//$mainmpesa['memo']            = $memo;

				$this->mainmpesa_m->insert_mainmpesa($mainmpesa);
				$mainmpesaLastID = $this->db->insert_id();

				if ($mainmpesaLastID) {
					foreach($studentitems as $studentitem) {
						if ($studentitem['amount'] != '' ) {
							$mpesa = array(
								'studentID' => $studentitem['studentID'],
								'amount' => $studentitem['amount'],
								'mainmpesaID' => $mainmpesaLastID,
							);

							if(customCompute($mpesa)) {
								$this->mpesa_m->insert_mpesa($mpesa);
								$mpesaLastID = $this->db->insert_id();
							}
						}
					}
				}

				$array = array('phonenumber' => $phonenumber, 'amount' => $totalamount);

				$response = $this->payment_gateway->gateway("mpesa")->payment($array, null);

				if(isset($response['errorMessage'])){
					$this->response([
		            	'status' => false,
			            'message' => $response['errorMessage'],
			            'data' => []
			        ], REST_Controller::HTTP_BAD_REQUEST);
                }else{
					$this->response([
		            	'status' => true,
			            'message' => 'Please enter your M-PESA PIN on your device.',
			            'data' => []
			        ], REST_Controller::HTTP_OK);
                }
			}
		} else {
			$this->response([
                'status' => false,
                'message' => 'The POST method is not found',
                'data' => []
            ], REST_Controller::HTTP_NOT_IMPLEMENTED);
		}
	}

	private function generateAllInvoiceAmount($invoices) {
		$total = 0;
        if(customCompute($invoices)) {
            foreach ($invoices as $invoice) {
                $total += $invoice->amount;
            }
        }

        return $total;
    }

	private function generateAllCreditmemoAmount($creditmemos) {
		$total = 0;
        if(customCompute($creditmemos)) {
            foreach ($creditmemos as $creditmemo) {
                $total += $creditmemo->amount;
            }
        }

        return $total;
    }

	private function generateAllPaymentAmount($payments) {
		$total = 0;
        if(customCompute($payments)) {
            foreach ($payments as $payment) {
                $total += $payment->paymentamount;
            }
        }

        return $total;
    }
}
