<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
use OpenApi\Annotations as OA;

class Creditmemo extends Api_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model("creditmemo_m");
        $this->load->model("credittypes_m");
        $this->load->model('payment_m');
        $this->load->model("classes_m");
        $this->load->model("student_m");
        $this->load->model("parents_m");
        $this->load->model("section_m");
        $this->load->model('user_m');
        $this->load->model('weaverandfine_m');
        $this->load->model("payment_settings_m");
        $this->load->model("globalpayment_m");
        $this->load->model("maincreditmemo_m");
        $this->load->model("studentrelation_m");
    }

    /**
       * @OA\Get(
       *     path="/api/v10/creditmemo",
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
    public function index_get()
    {
        $usertypeID = $this->session->userdata("usertypeID");
        $schoolyearID = $this->session->userdata("defaultschoolyearID");
        if($usertypeID == 3) {
            $username = $this->session->userdata("username");
            $student  = $this->student_m->get_single_student(array("username" => $username));
            if(customCompute($student)) {
                $this->retdata['maincreditmemos'] = $this->maincreditmemo_m->get_maincreditmemo_with_studentrelation_by_studentID($student->studentID, $schoolyearID);
                $this->retdata['grandtotalandpayment'] = $this->grandtotalandpaid($this->retdata['maincreditmemos'], $schoolyearID);

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
        } elseif($usertypeID == 4) {
            $parentID = $this->session->userdata("loginuserID");
            $students = $this->studentrelation_m->get_order_by_student(array('parentID' => $parentID, 'srschoolyearID' => $schoolyearID));
            if(customCompute($students)) {
                $studentArray = pluck($students, 'srstudentID');
                $this->retdata['maincreditmemos'] = $this->maincreditmemo_m->get_maincreditmemo_with_studentrelation_by_multi_studentID($studentArray, $schoolyearID);
                $this->retdata['grandtotalandpayment'] = $this->grandtotalandpaid($this->retdata['maincreditmemos'], $schoolyearID);

                $this->response([
                    'status'    => true,
                    'message'   => 'Success',
                    'data'      => $this->retdata
                ], REST_Controller::HTTP_OK);
            } else {
                $this->retdata['maincreditmemos'] = [];
                $this->retdata['grandtotalandpayment'] = [];

                $this->response([
                    'status'    => true,
                    'message'   => 'Success',
                    'data'      => $this->retdata
                ], REST_Controller::HTTP_OK);
            }
        } else {
            $this->retdata['maincreditmemos'] = $this->maincreditmemo_m->get_maincreditmemo_with_studentrelation(array('schoolyearID' => $schoolyearID, 'schoolID' => $this->session->userdata('schoolID')));
            $this->retdata['grandtotalandpayment'] = $this->grandtotalandpaid($this->retdata['maincreditmemos'], $schoolyearID);

            $this->response([
                'status'    => true,
                'message'   => 'Success',
                'data'      => $this->retdata
            ], REST_Controller::HTTP_OK);
        }
    }

    /**
       * @OA\Get(
       *     path="/api/v10/creditmemo/view/{creditmemoID}",
       *     @OA\Parameter(
       *         name="creditmemoID",
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
        $schoolID = $this->session->userdata('schoolID');
        $usertypeID = $this->session->userdata("usertypeID");
        $schoolyearID = $this->session->userdata('defaultschoolyearID');
        $this->retdata['credittypes'] = pluck($this->credittypes_m->get_order_by_credittypes(array('schoolID' => $schoolID)), 'credittypes', 'credittypesID');
        $this->retdata["siteinfos"] = $this->data['siteinfos'];

        if($usertypeID == 3) {
            if((int)$id) {
                $studentID  = $this->session->userdata("loginuserID");
                $getstudent = $this->studentrelation_m->get_single_student(array("srstudentID" => $studentID, 'srschoolyearID' => $schoolyearID, 'srschoolID' => $schoolID));
                if(customCompute($getstudent)) {
                    $this->retdata['maincreditmemo'] = $this->maininvoice_m->get_maincreditmemo_with_studentrelation_by_maincreditmemoID(array('creditmemoID' => $id, 'schoolyearID' => $schoolyearID, 'schoolID' => $schoolID));
                    if(customCompute($this->retdata['maincreditmemo']) && ($this->retdata['maincreditmemo']->maincreditmemostudentID == $getstudent->studentID)) {
                        $creditmemos = $this->creditmemo_m->get_order_by_creditmemo(array('maincreditmemoID' => $id, 'schoolID' => $schoolID));
                        if(customCompute($creditmemos)) {
                            foreach ($creditmemos as $key=> $creditmemo) {
                                $creditmemos[$key]->dicountamount = (float)(($creditmemo->discount*$creditmemo->amount) / 100);
                                $creditmemos[$key]->subtotal      = (int)$creditmemo->amount - $creditmemos[$key]->dicountamount;
                            }
                        }
                        $this->retdata['creditmemos'] = $creditmemos;

                        $this->retdata['grandtotalandpayment'] = $this->grandtotalandpaidsingle($this->retdata['maincreditmemo'], $schoolyearID, $this->retdata["maincreditmemo"]->maincreditmemostudentID);

                        $this->retdata["student"] = $this->student_m->get_single_student(array('studentID' => $this->retdata["maincreditmemo"]->maincreditmemostudentID));

                        $this->retdata['createuser'] = getNameByUsertypeIDAndUserID($this->retdata['maincreditmemo']->maininvoiceusertypeID, $this->retdata['maincreditmemo']->maincreditmemouserID);

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
            } else {
                $this->response([
                    'status' => false,
                    'message' => 'Error 404',
                    'data' => []
                ], REST_Controller::HTTP_NOT_FOUND);
            }
        } elseif($usertypeID == 4) {
            if((int)$id) {
                $parentID = $this->session->userdata("loginuserID");
                $getStudents = $this->studentrelation_m->get_order_by_student(array('parentID' => $parentID, 'srschoolyearID' => $schoolyearID, 'srschoolID' => $schoolID));
                $fetchStudent = pluck($getStudents, 'srstudentID', 'srstudentID');
                if(customCompute($fetchStudent)) {
                    $this->retdata['maincreditmemo'] = $this->maincreditmemo_m->get_maincreditmemo_with_studentrelation_by_maincreditmemoID(array('creditmemoID' => $id, 'schoolyearID' => $schoolyearID, 'schoolID' => $schoolID));
                    if($this->retdata['maincreditmemo']) {
                        if(in_array($this->retdata['maincreditmemo']->maincreditmemostudentID, $fetchStudent)) {

                            $creditmemos = $this->creditmemo_m->get_order_by_creditmemo(array('maincreditmemoID' => $id, 'schoolID' => $schoolID));
                            if(customCompute($creditmemos)) {
                                foreach ($creditmemos as $key=> $creditmemo) {
                                    $creditmemos[$key]->dicountamount = (float)(($creditmemo->discount*$creditmemo->amount) / 100);
                                    $creditmemos[$key]->subtotal      = (int)$creditmemo->amount - $creditmemos[$key]->dicountamount;
                                }
                            }
                            $this->retdata['creditmemos'] = $creditmemos;

                            $this->retdata['grandtotalandpayment'] = $this->grandtotalandpaidsingle($this->retdata['maincreditmemo'], $schoolyearID, $this->retdata["maincreditmemo"]->maincreditmemostudentID);

                            $this->retdata["student"] = $this->student_m->get_single_student(array('studentID' => $this->retdata["maincreditmemo"]->maincreditmemostudentID));

                            $this->retdata['createuser'] = getNameByUsertypeIDAndUserID($this->retdata['maincreditmemo']->maincreditmemousertypeID, $this->retdata['maincreditmemo']->maincreditmemouserID);

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
        } else {
            if((int)$id) {
                $this->retdata['maincreditmemo'] = $this->maincreditmemo_m->get_maincreditmemo_with_studentrelation_by_maincreditmemoID(array('creditmemoID' => $id, 'schoolyearID' => $schoolyearID, 'schoolID' => $schoolID));

                $creditmemos = $this->creditmemo_m->get_order_by_creditmemo(array('maincreditmemoID' => $id, 'schoolID' => $schoolID));
                if(customCompute($creditmemos)) {
                    foreach ($creditmemos as $key=> $creditmemo) {
                        $creditmemos[$key]->dicountamount = (float)(($creditmemo->discount*$creditmemo->amount) / 100);
                        $creditmemos[$key]->subtotal      = (int)$creditmemo->amount - $creditmemos[$key]->dicountamount;
                    }
                }
                $this->retdata['creditmemos'] = $creditmemos;

                if(customCompute($this->retdata["maincreditmemo"])) {
                    $this->retdata['grandtotalandpayment'] = $this->grandtotalandpaidsingle($this->retdata['maincreditmemo'], $schoolyearID, $this->retdata["maincreditmemo"]->maincreditmemostudentID);

                    $this->retdata["student"] = $this->student_m->get_single_student(array('studentID' => $this->retdata["maincreditmemo"]->maincreditmemostudentID));

                    $this->retdata['createuser'] = getNameByUsertypeIDAndUserID($this->retdata['maincreditmemo']->maincreditmemousertypeID, $this->retdata['maincreditmemo']->maincreditmemouserID);

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

    /**
       * @OA\Get(
       *     path="/api/v10/creditmemo/paymentlist/{maincreditmemoID}",
       *     @OA\Parameter(
       *         name="maincreditmemoID",
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
       *     @OA\Response(
       *         response="401",
       *         description="Permission denied"
       *     ),
       * )
       */
    public function paymentlist_get($maincreditmemoID = null)
    {
        if(permissionChecker('creditmemo_view')) {
            $schoolID     = $this->session->userdata('schoolID');
            $schoolyearID = $this->session->userdata('defaultschoolyearID');

            $globalPaymentArray = [];
            $globalpaymentobjects = [];
            $allpayments = [];
            $allweaverandfines = [];
            $paymentlists = [];

            if(!empty($maincreditmemoID) && (int)$maincreditmemoID && $maincreditmemoID > 0) {
                $maincreditmemo = $this->maincreditmemo_m->get_single_maincreditmemo(array('maincreditmemoID' => $maincreditmemoID, 'maincreditmemoschoolyearID' => $schoolyearID, 'schoolID' => $schoolID));
                if(customCompute($maincreditmemo)) {
                    $creditmemos = $this->creditmemo_m->get_order_by_creditmemo(array('maincreditmemoID' => $maincreditmemoID, 'schoolyearID' => $schoolyearID, 'schoolID' => $schoolID));
                    $globalpayments = pluck($this->globalpayment_m->get_order_by_globalpayment(array('studentID' => $maininvoice->maininvoicestudentID)), 'obj', 'globalpaymentID');

                    if(customCompute($creditmemos)) {
                        foreach ($creditmemos as $creditmemo) {
                            $payments = $this->payment_m->get_order_by_payment(array('creditmemoID' => $creditmemo->creditmemoID, 'studentID' => $maincreditmemo->maincreditmemostudentID));

                            $weaverandfines = $this->weaverandfine_m->get_order_by_weaverandfine(array('creditmemoID' => $creditmemo->creditmemoID, 'studentID' => $maincreditmemo->maincreditmemostudentID));
                            if(customCompute($payments)) {
                                foreach ($payments as $payment) {
                                    if(isset($globalpayments[$payment->globalpaymentID])) {
                                        $allpayments[$payment->globalpaymentID][] = $payment;
                                        if(!in_array($payment->globalpaymentID, $globalPaymentArray)) {
                                            $globalPaymentArray[] = $payment->globalpaymentID;
                                            $globalpaymentobjects[] = $globalpayments[$payment->globalpaymentID];
                                        }
                                    }
                                }
                            }

                            if(customCompute($weaverandfines)) {
                                foreach ($weaverandfines as $weaverandfine) {
                                    $allweaverandfines[$weaverandfine->globalpaymentID][] = $weaverandfine;
                                }
                            }
                        }
                    }

                    if(customCompute($globalpaymentobjects)) {
                        foreach ($globalpaymentobjects as $globalpaymentobject) {
                            if(isset($allpayments[$globalpaymentobject->globalpaymentID])) {
                                if(customCompute($allpayments[$globalpaymentobject->globalpaymentID])) {
                                    foreach ($allpayments[$globalpaymentobject->globalpaymentID] as $payment) {
                                        if(isset($paymentlists[$globalpaymentobject->globalpaymentID])) {
                                            $paymentlists[$globalpaymentobject->globalpaymentID]['paymentamount'] += $payment->paymentamount;
                                        } else {
                                            $paymentlists[$globalpaymentobject->globalpaymentID] = array(
                                                'globalpaymentID' => $globalpaymentobject->globalpaymentID,
                                                'paymentamount' => $payment->paymentamount,
                                                'date' => $payment->paymentdate,
                                                'paymenttype' => $payment->paymenttype,
                                            );
                                        }
                                    }


                                    if(isset($allweaverandfines[$globalpaymentobject->globalpaymentID])) {
                                        foreach ($allweaverandfines[$globalpaymentobject->globalpaymentID] as $allweaverandfine) {
                                            if(isset($paymentlists[$globalpaymentobject->globalpaymentID]['weaveramount']) && isset($paymentlists[$globalpaymentobject->globalpaymentID]['fineamount'])) {
                                                $paymentlists[$globalpaymentobject->globalpaymentID]['weaveramount'] += $allweaverandfine->weaver;
                                                $paymentlists[$globalpaymentobject->globalpaymentID]['fineamount'] += $allweaverandfine->fine;
                                            } else {
                                                if(isset($paymentlists[$globalpaymentobject->globalpaymentID])) {
                                                    $paymentlists[$globalpaymentobject->globalpaymentID]['weaveramount'] = $allweaverandfine->weaver;
                                                    $paymentlists[$globalpaymentobject->globalpaymentID]['fineamount'] = $allweaverandfine->fine;
                                                } else {
                                                    $paymentlists[$globalpaymentobject->globalpaymentID] = array(
                                                        'weaveramount' => $allweaverandfine->weaver,
                                                        'fineamount' => $allweaverandfine->fine,
                                                    );
                                                }
                                            }
                                        }
                                    } else {
                                        $paymentlists[$globalpaymentobject->globalpaymentID]['weaveramount'] = 0;
                                        $paymentlists[$globalpaymentobject->globalpaymentID]['fineamount'] = 0;
                                    }
                                }
                            }
                        }
                    }
                }

                $this->retdata['paymentlists'] = $paymentlists;
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
                'message' => 'Permission deny',
                'data' => []
            ], REST_Controller::HTTP_UNAUTHORIZED);
        }
    }

    /**
       * @OA\Get(
       *     path="/api/v10/creditmemo/viewpayment/{globalpaymentID}/{maincreditmemoID}",
       *     @OA\Parameter(
       *         name="globalpaymentID",
       *         description="",
       *         in = "path",
       *         allowMultiple=false,
       *         required=true,
       *         @OA\Schema(
       *             default=null,
       *             type="integer"
       *         )
       *     ),
       *     @OA\Parameter(
       *         name="maincreditmemoID",
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
       *     @OA\Response(
       *         response="401",
       *         description="Permission denied"
       *     ),
       * )
       */
    public function viewpayment_get($globalpaymentID = null, $maincreditmemoID = null)
    {
        if(permissionChecker('invoice_view')) {
            $schoolyearID = $this->session->userdata('defaultschoolyearID');
            if((int)$globalpaymentID && (int)$maininvoiceID) {
                $globalpayment = $this->globalpayment_m->get_single_globalpayment(array('globalpaymentID' => $globalpaymentID, 'schoolyearID' => $schoolyearID));
                $maincreditmemo = $this->maincreditmemo_m->get_single_maincreditmemo(array('maincreditmemoID' => $maincreditmemoID, 'maincreditmemoschoolyearID' => $schoolyearID));
                if(customCompute($maincreditmemo) && customCompute($globalpayment)) {
                    $usertypeID = $this->session->userdata('usertypeID');
                    $userID = $this->session->userdata('loginuserID');

                    $f = FALSE;
                    if($usertypeID == 3) {
                        $getstudent = $this->studentrelation_m->get_single_studentrelation(array('srstudentID' => $globalpayment->studentID, 'srschoolyearID' => $globalpayment->schoolyearID));
                        if(customCompute($getstudent)) {
                            if($getstudent->srstudentID == $userID) {
                                $f = TRUE;
                            }
                        }
                    } elseif($usertypeID == 4) {
                        $parentID = $this->session->userdata("loginuserID");
                        $schoolyearID = $this->session->userdata('defaultschoolyearID');
                        $getStudents = $this->studentrelation_m->get_order_by_student(array('parentID' => $parentID, 'srschoolyearID' => $schoolyearID));
                        $fetchStudent = pluck($getStudents, 'srstudentID', 'srstudentID');
                        if(customCompute($fetchStudent)) {
                            if(in_array($globalpayment->studentID, $fetchStudent)) {
                                $f = TRUE;
                            }
                        }
                    } else {
                        $f = TRUE;
                    }

                    if($f) {
                        $studentrelation = $this->studentrelation_m->get_single_studentrelation(array('srstudentID' => $globalpayment->studentID, 'srschoolyearID' => $globalpayment->schoolyearID));
                        if(customCompute($studentrelation)) {
                            $this->retdata['credittypes'] = pluck($this->credittypes_m->get_credittypes(), 'credittypes', 'credittypesID');
                            $this->retdata['student'] = $this->student_m->get_single_student(array('studentID' => $globalpayment->studentID));
                            $this->retdata['creditmemos'] = pluck($this->creditmemo_m->get_order_by_creditmemo(array('maincreditmemoID' => $maincreditmemoID)), 'obj', 'creditmemoID');

                            $this->payment_m->order_payment('paymentID', 'asc');
                            $this->retdata['payments'] = $this->payment_m->get_order_by_payment(array('globalpaymentID' => $globalpaymentID));
                            $this->retdata['weaverandfines'] = pluck($this->weaverandfine_m->get_order_by_weaverandfine(array('globalpaymentID' => $globalpaymentID)), 'obj', 'paymentID');

                            $this->retdata['paymenttype'] = '';
                            if(customCompute($this->retdata['payments'])) {
                                foreach ($this->retdata['payments'] as $payment) {
                                    $this->retdata['paymenttype'] = $payment->paymenttype;
                                    break;
                                }
                            }

                            $this->retdata['studentrelation'] = $studentrelation;
                            $this->retdata['globalpayment'] = $globalpayment;
                            $this->retdata['maininvoice'] = $maininvoice;

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
        } else {
            $this->response([
                'status' => false,
                'message' => 'Permission deny',
                'data' => []
            ], REST_Controller::HTTP_UNAUTHORIZED);
        }
    }

    private function grandtotalandpaid( $maincreditmemos, $schoolyearID )
    {
        $retArray           = [];
        $creditmemoitems       = pluck_multi_array_key($this->creditmemo_m->get_order_by_creditmemo(['schoolyearID' => $schoolyearID]), 'obj', 'maincreditmemoID', 'creditmemoID');
        if(customCompute($maincreditmemos)) {
            foreach($maincreditmemos as $maincreditmemo) {
                if(isset($creditmemoitems[$maincreditmemo->maincreditmemoID])) {
                    if(customCompute($creditmemoitems[$maincreditmemo->maincreditmemoID])) {
                        foreach($creditmemoitems[$maincreditmemo->maincreditmemoID] as $creditmemoitem) {
                            $amount = $creditmemoitem->amount;

                            if(isset($retArray['grandtotal'][$maincreditmemo->maincreditmemoID])) {
                                $retArray['grandtotal'][$maincreditmemo->maincreditmemoID] = (($retArray['grandtotal'][$maincreditmemo->maincreditmemoID]) + $amount);
                            } else {
                                $retArray['grandtotal'][$maincreditmemo->maincreditmemoID] = $amount;
                            }

                            if(isset($retArray['totalamount'][$maincreditmemo->maincreditmemoID])) {
                                $retArray['totalamount'][$maincreditmemo->maincreditmemoID] = (($retArray['totalamount'][$maincreditmemo->maincreditmemoID]) + $creditmemoitem->amount);
                            } else {
                                $retArray['totalamount'][$maincreditmemo->maincreditmemoID] = $creditmemoitem->amount;
                            }
                        }
                    }
                }
            }
        }

        return $retArray;
    }

    private function grandtotalandpaidsingle($maincreditmemo, $schoolyearID, $studentID = null)
    {
        $retArray = ['grandtotal' => 0, 'totalamount' => 0, 'totaldiscount' => 0, 'totalpayment' => 0, 'totalfine' => 0, 'totalweaver' => 0, 'balanceamount'=>0];
        if(customCompute($maincreditmemo)) {
            if((int)$studentID && $studentID != null) {
                $creditmemoitems = pluck_multi_array_key($this->creditmemo_m->get_order_by_creditmemo(array('studentID' => $studentID, 'maincreditmemoID' => $maincreditmemo->maincreditmemoID,  'schoolyearID' => $schoolyearID)), 'obj', 'maincreditmemoID', 'creditmemoID');
                $paymentitems = pluck_multi_array($this->payment_m->get_order_by_payment(array('schoolyearID' => $schoolyearID, 'paymentamount !=' => NULL)), 'obj', 'invoiceID');
                $weaverandfineitems = pluck_multi_array($this->weaverandfine_m->get_order_by_weaverandfine(array('schoolyearID' => $schoolyearID)), 'obj', 'invoiceID');
            } else {
                $invoiceitem = [];
                $paymentitems = [];
                $weaverandfineitems = [];
            }

            if(isset($invoiceitems[$maininvoice->maininvoiceID])) {
                if(customCompute($invoiceitems[$maininvoice->maininvoiceID])) {
                    foreach ($invoiceitems[$maininvoice->maininvoiceID] as $invoiceitem) {
                        $amount = $invoiceitem->amount;
                        if($invoiceitem->discount > 0) {
                            $amount = ($invoiceitem->amount - (($invoiceitem->amount/100) *$invoiceitem->discount));
                        }

                        if(isset($retArray['grandtotal'])) {
                            $retArray['grandtotal'] = ($retArray['grandtotal'] + $amount);
                        } else {
                            $retArray['grandtotal'] = $amount;
                        }

                        if(isset($retArray['totalamount'])) {
                            $retArray['totalamount'] = ($retArray['totalamount'] + $invoiceitem->amount);
                        } else {
                            $retArray['totalamount'] = $invoiceitem->amount;
                        }

                        if(isset($retArray['totaldiscount'])) {
                            $retArray['totaldiscount'] = ($retArray['totaldiscount'] + (($invoiceitem->amount/100) *$invoiceitem->discount));
                        } else {
                            $retArray['totaldiscount'] = (($invoiceitem->amount/100) *$invoiceitem->discount);
                        }

                        if(isset($paymentitems[$invoiceitem->invoiceID])) {
                            if(customCompute($paymentitems[$invoiceitem->invoiceID])) {
                                foreach ($paymentitems[$invoiceitem->invoiceID] as $paymentitem) {
                                    if(isset($retArray['totalpayment'])) {
                                        $retArray['totalpayment'] = ($retArray['totalpayment'] + $paymentitem->paymentamount);
                                    } else {
                                        $retArray['totalpayment'] = $paymentitem->paymentamount;
                                    }
                                }
                            }
                        }

                        if(isset($weaverandfineitems[$invoiceitem->invoiceID])) {
                            if(customCompute($weaverandfineitems[$invoiceitem->invoiceID])) {
                                foreach ($weaverandfineitems[$invoiceitem->invoiceID] as $weaverandfineitem) {
                                    if(isset($retArray['totalweaver'])) {
                                        $retArray['totalweaver'] = ($retArray['totalweaver'] + $weaverandfineitem->weaver);
                                    } else {
                                        $retArray['totalweaver'] = $weaverandfineitem->weaver;
                                    }

                                    if(isset($retArray['totalfine'])) {
                                        $retArray['totalfine'] = ($retArray['totalfine'] + $weaverandfineitem->fine);
                                    } else {
                                        $retArray['totalfine'] = $weaverandfineitem->fine;
                                    }
                                }
                            }
                        }

                        $retArray['balanceamount'] = $retArray['grandtotal'] - ($retArray['totalpayment'] + $retArray['totalweaver']);
                    }
                }
            }
        }

        return $retArray;
    }


}
