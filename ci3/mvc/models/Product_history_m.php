<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Product_history_m extends MY_Model {

    public function __construct() {
        parent::__construct();
        $this->load->model('productpurchaseitem_m');
        $this->load->model('productsaleitem_m');
    }

    public function get_product_history($productID, $filters = []) {
        $queryArray = [
            'productID' => $productID,
            'fromdate' => isset($filters['from_date']) ? $filters['from_date'] : '',
            'todate' => isset($filters['to_date']) ? $filters['to_date'] : '',
            'productwarehouseID' => isset($filters['warehouseID']) ? $filters['warehouseID'] : 0,
        ];

        // Get Purchases
        $purchases_result = $this->productpurchaseitem_m->get_all_productpurchase_for_report($queryArray);
        $purchases = [];
        foreach ($purchases_result as $purchase) {
            $purchases[] = (object)[
                'date' => $purchase->productpurchasedate,
                'type' => 'Purchase',
                'reference' => $purchase->productpurchasereferenceno,
                'quantity' => $purchase->productpurchasequantity,
                'price' => $purchase->productpurchaseunitprice,
                'total' => $purchase->productpurchasequantity * $purchase->productpurchaseunitprice,
            ];
        }

        // Get Sales
        $sales_result = $this->productsaleitem_m->get_all_productsaleitem_for_report($queryArray);
        $sales = [];
        foreach ($sales_result as $sale) {
            $sales[] = (object)[
                'date' => $sale->productsaledate,
                'type' => 'Sale',
                'reference' => $sale->productsaleID, // No reference number in sales table, using ID
                'quantity' => $sale->productsalequantity,
                'price' => $sale->productsaleunitprice,
                'total' => $sale->productsalequantity * $sale->productsaleunitprice,
            ];
        }

        // Merge and Sort
        $history = array_merge($purchases, $sales);

        usort($history, function($a, $b) {
            return strtotime($a->date) - strtotime($b->date);
        });

        return $history;
    }
}
