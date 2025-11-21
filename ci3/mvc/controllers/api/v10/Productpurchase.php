<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
use OpenApi\Annotations as OA;

class Productpurchase extends Api_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('productsupplier_m');
        $this->load->model('productpurchase_m');
        $this->load->model('product_m');
        $this->load->model('productpurchaseitem_m');
        $this->load->model('productpurchasepaid_m');
        $this->load->model('productwarehouse_m');
    }

    /**
       * @OA\Get(
       *     path="/api/v10/productpurchase",
       *     @OA\Response(
       *         response="200",
       *         description="Success"
       *     )
       * )
       */
    public function index_get()
    {
        $schoolID = $this->session->userdata('schoolID');
        $schoolyearID = $this->session->userdata('defaultschoolyearID');
        $this->retdata['productsuppliers'] = pluck($this->productsupplier_m->get_order_by_productsupplier(array('schoolID' => $schoolID)), 'productsuppliercompanyname', 'productsupplierID');
        $this->retdata['productpurchases'] = $this->productpurchase_m->get_order_by_productpurchase(array('schoolyearID' => $schoolyearID, 'schoolID' => $schoolID));
        $this->retdata['grandtotalandpaid'] = $this->grandtotalandpaid($this->retdata['productpurchases'], $schoolyearID, $schoolID);

        $this->response([
            'status'    => true,
            'message'   => 'Success',
            'data'      => $this->retdata
        ], REST_Controller::HTTP_OK);
    }

    /**
       * @OA\Get(
       *     path="/api/v10/productpurchase/view/{productpurchaseID}",
       *     @OA\Parameter(
       *         name="productpurchaseID",
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
        $schoolyearID = $this->session->userdata('defaultschoolyearID');
        if((int)$id) {
            $this->retdata['productpurchase'] = $this->productpurchase_m->get_single_productpurchase(array('productpurchaseID' => $id, 'schoolyearID' => $schoolyearID, 'schoolID' => $schoolID));

            $this->retdata['products'] = pluck($this->product_m->get_order_by_product(array('schoolID' => $schoolID)), 'productname', 'productID');

            $this->retdata['productpurchaseitems'] = $this->productpurchaseitem_m->get_order_by_productpurchaseitem(array('productpurchaseID' => $id, 'schoolyearID' => $schoolyearID, 'schoolID' => $schoolID));

            $this->retdata['productpurchasepaid'] = $this->productpurchasepaid_m->get_productpurchasepaid_sum('productpurchasepaidamount', array('productpurchaseID' => $id, 'schoolID' => $schoolID));


            if($this->retdata['productpurchase']) {
                $this->retdata['createuser'] = getNameByUsertypeIDAndUserID($this->retdata['productpurchase']->create_usertypeID, $this->retdata['productpurchase']->create_userID);

                $this->retdata['productsupplier'] = $this->productsupplier_m->get_single_productsupplier(array('productsupplierID' => $this->retdata['productpurchase']->productsupplierID));
                $this->retdata['productwarehouse'] = $this->productwarehouse_m->get_single_productwarehouse(array('productwarehouseID' => $this->retdata['productpurchase']->productwarehouseID));

                $this->response([
                    'status'    => true,
                    'message'   => 'Success',
                    'data'      => $this->retdata
                ], REST_Controller::HTTP_OK);
            } else {
                $this->response([
                    'status'    => false,
                    'message'   => 'Error 404',
                    'data'      => []
                ], REST_Controller::HTTP_NOT_FOUND);
            }
        } else {
            $this->response([
                'status'    => false,
                'message'   => 'Error 404',
                'data'      => []
            ], REST_Controller::HTTP_NOT_FOUND);
        }
    }

    private function grandtotalandpaid($productpurchases, $schoolyearID, $schoolID)
    {
        $retArray = [];

        $productpurchaseitems = pluck_multi_array($this->productpurchaseitem_m->get_order_by_productpurchaseitem(array('schoolyearID' => $schoolyearID, 'schoolID' => $schoolID)), 'obj', 'productpurchaseID');

        $productpurchasepaids = pluck_multi_array($this->productpurchasepaid_m->get_order_by_productpurchasepaid(array('schoolyearID' => $schoolyearID, 'schoolID' => $schoolID)), 'obj', 'productpurchaseID');

        if(customCompute($productpurchases)) {
            foreach ($productpurchases as $productpurchase) {
                if(isset($productpurchaseitems[$productpurchase->productpurchaseID])) {
                    if(customCompute($productpurchaseitems[$productpurchase->productpurchaseID])) {
                        foreach ($productpurchaseitems[$productpurchase->productpurchaseID] as $productpurchaseitem) {
                            if(isset($retArray['grandtotal'][$productpurchaseitem->productpurchaseID])) {
                                $retArray['grandtotal'][$productpurchaseitem->productpurchaseID] = (($retArray['grandtotal'][$productpurchaseitem->productpurchaseID]) + ($productpurchaseitem->productpurchaseunitprice*$productpurchaseitem->productpurchasequantity));
                            } else {
                                $retArray['grandtotal'][$productpurchaseitem->productpurchaseID] = ($productpurchaseitem->productpurchaseunitprice*$productpurchaseitem->productpurchasequantity);
                            }
                        }
                    }
                }

                if(isset($productpurchasepaids[$productpurchase->productpurchaseID])) {
                    if(customCompute($productpurchasepaids[$productpurchase->productpurchaseID])) {
                        foreach ($productpurchasepaids[$productpurchase->productpurchaseID] as $productpurchasepaid) {
                            if(isset($retArray['totalpaid'][$productpurchasepaid->productpurchaseID])) {
                                $retArray['totalpaid'][$productpurchasepaid->productpurchaseID] = (($retArray['totalpaid'][$productpurchasepaid->productpurchaseID]) + ($productpurchasepaid->productpurchasepaidamount));
                            } else {
                                $retArray['totalpaid'][$productpurchasepaid->productpurchaseID] = ($productpurchasepaid->productpurchasepaidamount);
                            }
                        }
                    }
                }
            }
        }
        return $retArray;
    }

    /**
       * @OA\Get(
       *     path="/api/v10/productpurchase/paymentlist/{productpurchaseID}",
       *     @OA\Parameter(
       *         name="productpurchaseID",
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
    public function paymentlist_get($id = null)
    {
        if(permissionChecker('productpurchase')) {
            $schoolID = $this->session->userdata('schoolID');
            $schoolyearID = $this->session->userdata('defaultschoolyearID');
            $productpurchaseID = $id;

            $this->retdata['paymentmethods'] = array(
                1 => $this->lang->line('productpurchase_cash'),
                2 => $this->lang->line('productpurchase_cheque'),
                3 => $this->lang->line('productpurchase_credit_card'),
                4 => $this->lang->line('productpurchase_other'),
            );

            if(!empty($productpurchaseID) && (int)$productpurchaseID && $productpurchaseID > 0) {
                $productpurchase = $this->productpurchase_m->get_single_productpurchase(array('productpurchaseID' => $productpurchaseID, 'schoolyearID' => $schoolyearID, 'schoolID' => $schoolID));
                if(customCompute($productpurchase)) {
                    $this->retdata['productpurchasepaids'] = $this->productpurchasepaid_m->get_order_by_productpurchasepaid(array('productpurchaseID' => $productpurchaseID, 'schoolID' => $schoolID));

                    $this->response([
                        'status'    => true,
                        'message'   => 'Success',
                        'data'      => $this->retdata
                    ], REST_Controller::HTTP_OK);
                } else {
                    $this->response([
                        'status'    => false,
                        'message'   => 'Error 404',
                        'data'      => []
                    ], REST_Controller::HTTP_NOT_FOUND);
                }
            } else {
                $this->response([
                    'status'    => false,
                    'message'   => 'Error 404',
                    'data'      => []
                ], REST_Controller::HTTP_NOT_FOUND);
            }
        } else {
            $this->response([
                'status'    => false,
                'message'   => 'Permission Deny',
                'data'      => []
            ], REST_Controller::HTTP_UNAUTHORIZED);
        }
    }
}
