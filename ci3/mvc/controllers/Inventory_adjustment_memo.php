<?php if(!defined('BASEPATH'))
    exit('No direct script access allowed');

class Inventory_adjustment_memo extends Admin_Controller
{
    /*
    | -----------------------------------------------------
    | PRODUCT NAME: 	INILABS SCHOOL MANAGEMENT SYSTEM
    | -----------------------------------------------------
    | AUTHOR:			INILABS TEAM
    | -----------------------------------------------------
    | EMAIL:			info@inilabs.net
    | -----------------------------------------------------
    | COPYRIGHT:		RESERVED BY INILABS IT
    | -----------------------------------------------------
    | WEBSITE:			http://inilabs.net
    | -----------------------------------------------------
    */

    function __construct()
    {
        parent::__construct();
        $this->load->model("mainstock_m");
        $this->load->model("stock_m");
        $this->load->model("product_m");
        $this->load->model("productwarehouse_m");
        $this->load->model("productpurchaseitem_m");
        $this->load->model("productsaleitem_m");
        $language = $this->session->userdata('lang');
        $this->lang->load('inventory_adjustment_memo', $language);
    }

    protected function rules()
    {
        $rules = [
            [
                'field' => 'fromproductwarehouseID',
                'label' => $this->lang->line("stock_from"),
                'rules' => 'trim|required|xss_clean|max_length[11]|numeric'
            ],
            [
                'field' => 'toproductwarehouseID',
                'label' => $this->lang->line("stock_from"),
                'rules' => 'trim|xss_clean|max_length[11]|numeric|callback_check_duplicate'
            ],
            [
                'field' => 'productitems',
                'label' => $this->lang->line("stock_productitem"),
                'rules' => 'trim|xss_clean|required|callback_unique_productitems'
            ],
        ];

        return $rules;
    }

    public function index()
    {
        $schoolID                        = $this->session->userdata('schoolID');

        $this->data['mainstocks']        = $this->mainstock_m->get_order_by_mainstock(array('schoolID' => $schoolID, 'type' => 'adjustment'));
        $this->data['totalquantities']   = $this->totalquantities($this->data['mainstocks'], $schoolID);
        $this->data['stocks']            = $this->stock_m->get_order_by_stock(array('schoolID' => $schoolID));
        $this->data['products']          = pluck($this->product_m->get_order_by_product(array('schoolID' => $schoolID)), 'productname', 'productID');
        $this->data['productwarehouses'] = pluck($this->productwarehouse_m->get_order_by_productwarehouse(array('schoolID' => $schoolID)), 'productwarehousename', 'productwarehouseID');
        $this->data["subview"]           = "inventory_adjustment_memo/index";
        $this->load->view('_layout_main', $this->data);
    }

    public function add() {
        $this->data['headerassets'] = array(
            'css' => array(
                'assets/select2/css/select2.css',
                'assets/select2/css/select2-bootstrap.css'
            ),
            'js' => array(
                'assets/select2/select2.js'
            )
        );

        $schoolID = $this->session->userdata('schoolID');
        $this->data['products'] = $this->product_m->get_order_by_product(array('schoolID' => $schoolID));
        $this->data['productbuyingprices'] = pluck($this->product_m->get_order_by_product(array('schoolID' => $schoolID)), 'productbuyingprice', 'productID');
        $this->data['productwarehouses'] = $this->productwarehouse_m->get_order_by_productwarehouse(array('schoolID' => $schoolID));
        $this->data['productpurchasequintity'] = json_encode(pluck_multi_array_key($this->productpurchaseitem_m->get_productpurchaseitem_quantity(array('schoolID' => $schoolID)), 'obj', 'productwarehouseID', 'productID'));
        $this->data['productsalequintity'] = json_encode(pluck_multi_array_key($this->productsaleitem_m->get_productsaleitem_quantity(array('schoolID' => $schoolID)), 'obj', 'productwarehouseID', 'productID'));

        $this->data["subview"] = "inventory_adjustment_memo/add";
        $this->load->view('_layout_main', $this->data);
    }

    public function saveadjustment()
    {
        $retArray['status'] = FALSE;
        if(permissionChecker('inventory_adjustment_memo')) {
            if($_POST) {
                $rules = $this->rules();
                $this->form_validation->set_rules($rules);
                if ($this->form_validation->run() == FALSE) {
                    $retArray['error']  = $this->form_validation->error_array();
                    $retArray['status'] = FALSE;
                    echo json_encode($retArray);
                    exit;
                } else {
                    $stockMainArray         = [];
                    $stockArray             = [];

                    $schoolID               = $this->session->userdata('schoolID');
                    $fromproductwarehouseID = $this->input->post("fromproductwarehouseID");
                    $memo                   = $this->input->post("memo");
                    $productitems           = json_decode($this->input->post('productitems'));

                    $stockMainArray = [
                        'stockfromwarehouseID'    => $fromproductwarehouseID,
                        'type'                    => "adjustment",
                        'memo'                    => $memo,
                        'schoolID'                => $schoolID,
                        'mainstockuserID'         => $this->session->userdata('loginuserID'),
                        'mainstockusertypeID'     => $this->session->userdata('usertypeID'),
                        'mainstockuname'          => $this->session->userdata('name'),
                        'mainstockcreate_date'    => date('Y-m-d'),
                    ];

                    $this->mainstock_m->insert_mainstock($stockMainArray);
                    $mainstockID = $this->db->insert_id();

                    if(customCompute($productitems)) {
                        foreach($productitems as $productitem) {
                            $stockArray[] = [
                              "productID" => $productitem->productID,
                              "quantity" => $productitem->amount,
                              "create_date" => date("Y-m-d H:i:s"),
                              "create_userID" => $stockMainArray['mainstockuserID'],
                              "create_usertypeID" => $stockMainArray['mainstockusertypeID'],
                              "mainstockID" => $mainstockID,
                              "schoolID" => $schoolID,
                            ];
                        }
                    }

                    $this->stock_m->insert_batch_stock($stockArray);
                    $this->session->set_flashdata('success', $this->lang->line('menu_success'));
                    $retArray['status']  = TRUE;
                    $retArray['message'] = 'Success';
                    echo json_encode($retArray);
                    exit;
                }
            } else {
              $retArray['status'] = FALSE;
              $retArray['message'] = 'No submitted data';
              echo json_encode($retArray);
              exit;
            }
        } else {
            $retArray['status'] = FALSE;
            $retArray['message'] = 'Permission denied';
            echo json_encode($retArray);
            exit;
        }
    }

    public function view()
    {
        $schoolID = $this->session->userdata('schoolID');

        $id = htmlentities(escapeString($this->uri->segment(3)));
        if((int)$id) {
            $this->data['mainstock'] = $this->mainstock_m->get_single_mainstock(array('mainstockID' => $id, 'schoolID' => $schoolID));
            $this->data['stocks'] = $this->stock_m->get_order_by_stock(array('mainstockID' => $id, 'schoolID' => $schoolID));
            $this->data['productwarehouses'] = pluck($this->productwarehouse_m->get_order_by_productwarehouse(array('schoolID' => $schoolID)), 'productwarehousename', 'productwarehouseID');
            //$this->data['products'] = pluck($this->product_m->get_order_by_product(array('schoolID' => $schoolID)), 'productname', 'productID');
            $this->data['products'] = pluck_multi_values($this->product_m->get_order_by_product(array('schoolID' => $schoolID)), ['productname', 'productbuyingprice'], 'productID');
            if(customCompute($this->data["mainstock"])) {
                $this->data['createuser'] = getNameByUsertypeIDAndUserID($this->data['mainstock']->mainstockusertypeID, $this->data['mainstock']->mainstockuserID);

                $this->data["subview"] = "inventory_adjustment_memo/view";
                $this->load->view('_layout_main', $this->data);
            } else {
                $this->data["subview"] = "error";
                $this->load->view('_layout_main', $this->data);
            }
        } else {
            $this->data["subview"] = "error";
            $this->load->view('_layout_main', $this->data);
        }
    }

    public function approve() {
        $id = htmlentities(escapeString($this->uri->segment(3)));
        if((int)$id) {
            $this->data['stock'] = $this->stock_m->get_single_stock(array('stockID' => $id, 'schoolID' => $this->session->userdata('schoolID')));
            if($this->data['stock'] && $this->session->userdata('loginuserID') != $this->data['stock']->create_userID) {
                $this->stock_m->update_product(array('approved' => 1), $id);
                $this->session->set_flashdata('success', $this->lang->line('menu_success'));
                redirect(base_url("inventory_adjustment_memo/index"));
            } else {
                redirect(base_url("inventory_adjustment_memo/index"));
            }
        } else {
            redirect(base_url("inventory_adjustment_memo/index"));
        }
    }

    private function totalquantities( $mainstocks, $schoolID )
    {
        $retArray           = [];
        $stockitems         = pluck_multi_array_key($this->stock_m->get_order_by_stock(['schoolID' => $schoolID]), 'obj', 'mainstockID', 'stockID');
        if(customCompute($mainstocks)) {
            foreach($mainstocks as $mainstock) {
                if(isset($stockitems[$mainstock->mainstockID])) {
                    if(customCompute($stockitems[$mainstock->mainstockID])) {
                        foreach($stockitems[$mainstock->mainstockID] as $stockitem) {

                            if(isset($retArray['totalquantity'][$mainstock->mainstockID])) {
                                $retArray['totalquantity'][$mainstock->mainstockID] = (($retArray['totalquantity'][$mainstock->mainstockID]) + $stockitem->quantity);
                            } else {
                                $retArray['totalquantity'][$mainstock->mainstockID] = $stockitem->quantity;
                            }
                        }
                    }
                }
            }
        }

        return $retArray;
    }

    public function check_duplicate() {
        $fromproductwarehouseID = $this->input->post("fromproductwarehouseID");
        $toproductwarehouseID = $this->input->post("toproductwarehouseID");
        if((int)$toproductwarehouseID) {
            if($fromproductwarehouseID == $toproductwarehouseID) {
              $this->form_validation->set_message("check_duplicate", "Select a different warehouse");
              return FALSE;
            } else
              return TRUE;
        }
        return TRUE;
    }

    public function unique_productitems()
    {
        $productitems = json_decode($this->input->post('productitems'));
        $status       = [];
        if(customCompute($productitems)) {
            foreach($productitems as $productitem) {
                if($productitem->amount == '') {
                    $status[] = FALSE;
                }
            }
        } else {
            $this->form_validation->set_message("unique_productitems", "The stock item is required.");
            return FALSE;
        }

        if(in_array(FALSE, $status)) {
            $this->form_validation->set_message("unique_productitems", "The stock quantity is required.");
            return FALSE;
        }
        return TRUE;
    }
}
?>
