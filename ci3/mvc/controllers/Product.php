<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Product extends Admin_Controller {
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

    function __construct() {
        parent::__construct();
        $this->load->model("product_m");
        $this->load->model("productcategory_m");
        $this->load->model("productsaleitem_m");
        $this->load->model("productpurchaseitem_m");
        $this->load->model("productwarehouse_m");
        $this->load->model("stock_m");
        $this->load->model("product_history_m");
        $language = $this->session->userdata('lang');
        $this->lang->load('product', $language);
    }

    public function index() {
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

        // NEW: support /product/index?warehouse=ID as well as /product/index/ID
        $qsWarehouse = $this->input->get('warehouse', true);
        $id = ($qsWarehouse !== null && $qsWarehouse !== '')
              ? htmlentities(escapeString($qsWarehouse))
              : htmlentities(escapeString($this->uri->segment(3)));

        $this->data['set'] = $id;

        $this->data['productcategorys'] = pluck(
            $this->productcategory_m->get_order_by_productcategory(array('schoolID' => $schoolID)),
            'productcategoryname',
            'productcategoryID'
        );

        $this->data['products'] = $this->product_m->get_order_by_product(array('schoolID' => $schoolID));

        if((int)$id) {
            $this->data['productpurchasequintity'] = pluck(
                $this->productpurchaseitem_m->get_productpurchaseitem_quantity(array('schoolID' => $schoolID, 'productwarehouseID' => $id)),
                'obj',
                'productID'
            );
            $this->data['productsalequintity'] = pluck(
                $this->productsaleitem_m->get_productsaleitem_quantity(array('schoolID' => $schoolID, 'productwarehouseID' => $id)),
                'obj',
                'productID'
            );
        } else {
            $this->data['productpurchasequintity'] = pluck(
                $this->productpurchaseitem_m->get_productpurchaseitem_quantity(array('schoolID' => $schoolID)),
                'obj',
                'productID'
            );
            $this->data['productsalequintity'] = pluck(
                $this->productsaleitem_m->get_productsaleitem_quantity(array('schoolID' => $schoolID)),
                'obj',
                'productID'
            );
        }

        $this->data['productwarehouses'] = $this->productwarehouse_m->get_order_by_productwarehouse(array('schoolID' => $schoolID));
        $this->data["subview"] = "product/index";
        $this->load->view('_layout_main', $this->data);
    }

    protected function rules() {
        $rules = array(
            array(
                'field' => 'productname',
                'label' => $this->lang->line("product_product"),
                'rules' => 'trim|required|xss_clean|max_length[60]|callback_unique_productname'
            ),
            array(
                'field' => 'productcategoryID',
                'label' => $this->lang->line("product_category"),
                'rules' => 'trim|required|xss_clean|numeric|max_length[11]|callback_unique_prodectcategory'
            ),
            array(
                'field' => 'productbuyingprice',
                'label' => $this->lang->line("product_buyingprice"),
                'rules' => 'trim|required|xss_clean|max_length[15]|numeric'
            ),
            array(
                'field' => 'productsellingprice',
                'label' => $this->lang->line("product_sellingprice"),
                'rules' => 'trim|required|xss_clean|max_length[15]|numeric'
            ),
            array(
                'field' => 'productdesc',
                'label' => $this->lang->line("product_desc"),
                'rules' => 'trim|xss_clean|max_length[250]'
            ),
            array(
                'field' => 'is_billable_default',
                'label' => $this->lang->line('product_is_billable_default'),
                'rules' => 'trim|xss_clean|in_list[0,1]'
            )
        );
        return $rules;
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
        $this->data['productcategorys'] = $this->productcategory_m->get_order_by_productcategory(array('schoolID' => $schoolID));
        if($_POST) {
            $rules = $this->rules();
            $this->form_validation->set_rules($rules);
            if ($this->form_validation->run() == FALSE) {
                $this->data["subview"] = "product/add";
                $this->load->view('_layout_main', $this->data);
            } else {
                $array = array(
                    "productname" => $this->input->post("productname"),
                    "productcategoryID" => $this->input->post("productcategoryID"),
                    "productbuyingprice" => $this->input->post("productbuyingprice"),
                    "productsellingprice" => $this->input->post("productsellingprice"),
                    "productdesc" => $this->input->post("productdesc"),
                    "is_billable_default" => (int) $this->input->post('is_billable_default'),
                    "create_date" => date("Y-m-d H:i:s"),
                    "modify_date" => date("Y-m-d H:i:s"),
                    "create_userID" => $this->session->userdata('loginuserID'),
                    "create_usertypeID" => $this->session->userdata('usertypeID'),
                    "schoolID" => $schoolID,
                );
                $this->product_m->insert_product($array);
                $this->session->set_flashdata('success', $this->lang->line('menu_success'));
                redirect(base_url("product/index"));
            }
        } else {
            $this->data["subview"] = "product/add";
            $this->load->view('_layout_main', $this->data);
        }
    }

    public function edit() {
        $this->data['headerassets'] = array(
            'css' => array(
                'assets/select2/css/select2.css',
                'assets/select2/css/select2-bootstrap.css'
            ),
            'js' => array(
                'assets/select2/select2.js'
            )
        );

        $id = htmlentities(escapeString($this->uri->segment(3)));
        if((int)$id) {
            $schoolID = $this->session->userdata('schoolID');
            $this->data['product'] = $this->product_m->get_single_product(array('productID' => $id, 'schoolID' => $schoolID));
            $this->data['productcategorys'] = $this->productcategory_m->get_order_by_productcategory(array('schoolID' => $schoolID));
            if($this->data['product']) {
                if($_POST) {
                    $rules = $this->rules();
                    $this->form_validation->set_rules($rules);
                    if ($this->form_validation->run() == FALSE) {
                        $this->data["subview"] = "product/edit";
                        $this->load->view('_layout_main', $this->data);
                    } else {
                        $array = array(
                            "productname" => $this->input->post("productname"),
                            "productcategoryID" => $this->input->post("productcategoryID"),
                            "productbuyingprice" => $this->input->post("productbuyingprice"),
                            "productsellingprice" => $this->input->post("productsellingprice"),
                            "productdesc" => $this->input->post("productdesc"),
                            "is_billable_default" => (int) $this->input->post('is_billable_default'),
                            "modify_date" => date("Y-m-d H:i:s"),
                        );

                        $this->product_m->update_product($array, $id);
                        $this->session->set_flashdata('success', $this->lang->line('menu_success'));
                        redirect(base_url("product/index"));
                    }
                } else {
                    $this->data["subview"] = "product/edit";
                    $this->load->view('_layout_main', $this->data);
                }
            } else {
                $this->data["subview"] = "error";
                $this->load->view('_layout_main', $this->data);
            }
        } else {
            $this->data["subview"] = "error";
            $this->load->view('_layout_main', $this->data);
        }
    }

    public function view() {
        $this->data['headerassets'] = array(
          'css' => [
              'assets/select2/css/select2.css',
              'assets/select2/css/select2-bootstrap.css',
              'assets/datepicker/datepicker.css'
          ],
          'js'  => [
              'assets/select2/select2.js',
              'assets/datepicker/datepicker.js',
              'assets/chartjs/chart.js'
          ]
        );

        $id = htmlentities(escapeString($this->uri->segment(3)));
        $warehouseID = (int) $this->uri->segment(4);

        $this->getView($id, $warehouseID);
    }

    private function getView($id, $warehouseID = 0) {
        $schoolID = $this->session->userdata('schoolID');

        if((int)$id) {
            $productInfo = $this->product_m->get_single_product(array('productID' => $id, 'schoolID' => $schoolID), TRUE);

            $this->basicInfo($productInfo, $warehouseID);
            $this->historyInfo($productInfo, $warehouseID);
            $this->get_warehouse_stock($productInfo);

            if(customCompute($productInfo)) {
                $this->data['set']     = $warehouseID;
                $this->data["subview"] = "product/getView";
                $this->load->view('_layout_main', $this->data);
            } else {
                $this->data["subview"] = "error";
                $this->load->view('_layout_main', $this->data);
            }
        }
    }

    private function get_warehouse_stock($productInfo) {
        $schoolID = $this->session->userdata('schoolID');
        $this->data['productwarehouseslist'] = $this->productwarehouse_m->get_order_by_productwarehouse(array('schoolID' => $schoolID));
        $warehouse_stocks = array();
        foreach ($this->data['productwarehouseslist'] as $warehouse) {
            $productpurchaseitemArray = array('productID' => $productInfo->productID, 'schoolID' => $schoolID, 'productwarehouseID' => $warehouse->productwarehouseID);
            $productsaleitemArray = array('productID' => $productInfo->productID, 'schoolID' => $schoolID, 'productwarehouseID' => $warehouse->productwarehouseID);

            $received = $this->productpurchaseitem_m->get_productpurchaseitem_quantity($productpurchaseitemArray);
            $sold = $this->productsaleitem_m->get_productsaleitem_quantity($productsaleitemArray);

            $received_qty = customCompute($received) ? (int)current($received)->quantity : 0;
            $sold_qty = customCompute($sold) ? (int)current($sold)->quantity : 0;

            $warehouse_stocks[$warehouse->productwarehousename] = $received_qty - $sold_qty;
        }
        $this->data['warehouse_stocks'] = $warehouse_stocks;
    }

    public function delete() {
        $id = htmlentities(escapeString($this->uri->segment(3)));
        if((int)$id) {
            $this->data['product'] = $this->product_m->get_single_product(array('productID' => $id, 'schoolID' => $this->session->userdata('schoolID')));
            if($this->data['product']) {
                $this->product_m->delete_product($id);
                $this->session->set_flashdata('success', $this->lang->line('menu_success'));
                redirect(base_url("product/index"));
            } else {
                redirect(base_url("product/index"));
            }
        } else {
            redirect(base_url("product/index"));
        }
    }

    public function unique_productname() {
        $id = htmlentities(escapeString($this->uri->segment(3)));
        $schoolID = $this->session->userdata('schoolID');
        if((int)$id) {
            $product = $this->product_m->get_order_by_product(array("productname" => $this->input->post("productname"), "productID !=" => $id, 'schoolID' => $schoolID));
            if(customCompute($product)) {
                $this->form_validation->set_message("unique_productname", "The %s is already exists.");
                return FALSE;
            }
            return TRUE;
        } else {
            $product = $this->product_m->get_order_by_product(array("productname" => $this->input->post("productname"), 'schoolID' => $schoolID));
            if(customCompute($product)) {
                $this->form_validation->set_message("unique_productname", "The %s is already exists.");
                return FALSE;
            }
            return TRUE;
        }
    }

    public function unique_prodectcategory() {
        if($this->input->post("productcategoryID") == 0) {
            $this->form_validation->set_message("unique_prodectcategory", "The %s field is required");
            return FALSE;
        }
        return TRUE;
    }

    public function product_list() {
        $productwarehouseID = $this->input->post('id');
        if((int)$productwarehouseID) {
            $string = base_url("product/index?warehouse=$productwarehouseID");
            echo $string;
        } else {
              redirect(base_url("product/index"));
        }
    }

    private function basicInfo($productInfo, $warehouseID = 0) {
        if(customCompute($productInfo)) {
            $schoolID = $this->session->userdata('schoolID');
            $this->data['profile'] = $productInfo;
            $this->data['productcategories'] = pluck($this->productcategory_m->get_order_by_productcategory(array('schoolID' => $schoolID)), 'productcategoryname', 'productcategoryID');
            $this->data['lastbuyingprice'] = $this->productpurchaseitem_m->get_last_productpurchaseitem(array('productID' => $productInfo->productID));
            $this->data['averageunitprice'] = $this->productpurchaseitem_m->get_average_unit_cost(array('productID' => $productInfo->productID));
            $this->data['lastsupplier'] = $this->productpurchaseitem_m->get_last_productpurchaseitem(array('productID' => $productInfo->productID));

            $purchase_filter = array('schoolID' => $schoolID, 'productID' => $productInfo->productID);
            if ($warehouseID) {
                $purchase_filter['productwarehouseID'] = $warehouseID;
            }

            $sale_filter = array('schoolID' => $schoolID, 'productID' => $productInfo->productID);
            if ($warehouseID) {
                $sale_filter['productwarehouseID'] = $warehouseID;
            }

            $productpurchasequintity = $this->productpurchaseitem_m->get_productpurchaseitem_quantity($purchase_filter);
            $productsalequintity = $this->productsaleitem_m->get_productsaleitem_quantity($sale_filter);

            $productpurchasequintity = customCompute($productpurchasequintity) ? (int)current($productpurchasequintity)->quantity : 0;
            $productsalequintity = customCompute($productsalequintity) ? (int)current($productsalequintity)->quantity : 0;

            $this->data['productquantity'] = $productpurchasequintity - $productsalequintity;
        }
    }

    private function historyInfo($productInfo, $warehouseID = 0, $from = '', $to = '', $month = '') {
        if(customCompute($productInfo)) {
            $schoolID = $this->session->userdata('schoolID');
            $this->data['productwarehouses'] = $this->productwarehouse_m->get_order_by_productwarehouse(array('schoolID' => $schoolID));

            $filters = [
                'warehouseID' => $warehouseID,
                'from_date' => $from,
                'to_date' => $to,
            ];
            $this->data['history'] = $this->product_history_m->get_product_history($productInfo->productID, $filters);
        }
    }

    public function get_history() {
        $id = $this->input->post('id');
        $date_from = $this->input->post('date_from');
        $date_to = $this->input->post('date_to');
        $warehouseID = $this->input->post('warehouseID');

        $schoolID = $this->session->userdata('schoolID');
        $productInfo = $this->product_m->get_single_product(array('productID' => $id, 'schoolID' => $schoolID), TRUE);

        if(customCompute($productInfo)) {
            $this->data['averageunitprice'] = $this->productpurchaseitem_m->get_average_unit_cost(array('productID' => $id));
            $this->historyInfo($productInfo, $warehouseID, $date_from, $date_to, '');

            $response = [
                'status' => 'success',
                'html' => $this->load->view('product/history', $this->data, true),
                'historyData' => $this->data['history']
            ];
            header("Content-Type: application/json");
            echo json_encode($response);
            exit;
        } else {
            header("Content-Type: application/json");
            echo json_encode(['status' => 'error', 'message' => 'Product not found']);
            exit;
        }
    }

    public function pdf() {
        if(permissionChecker('product_view')) {
            $productID = htmlentities(escapeString($this->uri->segment(3)));
            $warehouseID = htmlentities(escapeString($this->uri->segment(4)));
            $from_date_str = htmlentities(escapeString($this->uri->segment(5)));
            $to_date_str = htmlentities(escapeString($this->uri->segment(6)));

            if ((int)$productID) {
                $from_date = ($from_date_str != '0') ? date('d-m-Y', strtotime($from_date_str)) : '';
                $to_date = ($to_date_str != '0') ? date('d-m-Y', strtotime($to_date_str)) : '';

                $filters = [
                    'warehouseID' => $warehouseID,
                    'from_date' => $from_date,
                    'to_date' => $to_date,
                ];

                $this->data['history'] = $this->product_history_m->get_product_history($productID, $filters);
                $this->data['product'] = $this->product_m->get_single_product(['productID' => $productID]);
                $this->data['from_date'] = $from_date;
                $this->data['to_date'] = $to_date;

                $this->reportPDF('historyreport.css', $this->data, 'product/history_pdf');
            } else {
                $this->data["subview"] = "error";
                $this->load->view('_layout_main', $this->data);
            }
        } else {
            $this->data["subview"] = "errorpermission";
            $this->load->view('_layout_main', $this->data);
        }
    }

    public function csv() {
        if(permissionChecker('product_view')) {
            $productID = htmlentities(escapeString($this->uri->segment(3)));
            $warehouseID = htmlentities(escapeString($this->uri->segment(4)));
            $from_date_str = htmlentities(escapeString($this->uri->segment(5)));
            $to_date_str = htmlentities(escapeString($this->uri->segment(6)));

            if ((int)$productID) {
                $from_date = ($from_date_str != '0') ? date('d-m-Y', strtotime($from_date_str)) : '';
                $to_date = ($to_date_str != '0') ? date('d-m-Y', strtotime($to_date_str)) : '';

                $filters = [
                    'warehouseID' => $warehouseID,
                    'from_date' => $from_date,
                    'to_date' => $to_date,
                ];

                $history = $this->product_history_m->get_product_history($productID, $filters);

                $this->load->library('phpspreadsheet');
                $sheet = $this->phpspreadsheet->spreadsheet->getActiveSheet();

                $sheet->setCellValue('A1', 'Date');
                $sheet->setCellValue('B1', 'Type');
                $sheet->setCellValue('C1', 'Reference');
                $sheet->setCellValue('D1', 'Quantity');
                $sheet->setCellValue('E1', 'Unit Price');
                $sheet->setCellValue('F1', 'Total');

                $row = 2;
                foreach ($history as $item) {
                    $sheet->setCellValue('A'.$row, date('d M Y', strtotime($item->date)));
                    $sheet->setCellValue('B'.$row, $item->type);
                    $sheet->setCellValue('C'.$row, $item->reference);
                    $sheet->setCellValue('D'.$row, $item->quantity);
                    $sheet->setCellValue('E'.$row, number_format($item->price, 2));
                    $sheet->setCellValue('F'.$row, number_format($item->total, 2));
                    $row++;
                }

                $writer = new \PhpOffice\PhpSpreadsheet\Writer\Csv($this->phpspreadsheet->spreadsheet);

                header('Content-Type: text/csv');
                header('Content-Disposition: attachment;filename="product_history.csv"');
                header('Cache-Control: max-age=0');

                $writer->save('php://output');
                exit;
            } else {
                $this->data["subview"] = "error";
                $this->load->view('_layout_main', $this->data);
            }
        } else {
            $this->data["subview"] = "errorpermission";
            $this->load->view('_layout_main', $this->data);
        }
    }
}
