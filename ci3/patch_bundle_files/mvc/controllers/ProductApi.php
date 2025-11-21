<?php if(!defined('BASEPATH')) exit('No direct script access allowed');
class ProductApi extends CI_Controller {
  public function __construct(){ parent::__construct(); $this->load->database(); }
  public function movement_series($productID){
    $start = $this->input->get('start') ?: date('Y-m-01', strtotime('-5 months'));
    $end   = $this->input->get('end')   ?: date('Y-m-t');
    $warehouse = $this->input->get('warehouse') ?: null;

    $params = [$productID, $start, $end];
    $wh_sql = "";
    if ($warehouse) { $wh_sql = " AND productwarehouseID=? "; $params = [$productID, $warehouse, $start, $end]; }

    $sql = "SELECT month, SUM(purchases) purchases, SUM(sales) sales, SUM(adjustments) adjustments, SUM(nonbillable_issues) nonbillable
            FROM inventory_monthly
            WHERE productID=? " + ($warehouse and " AND productwarehouseID=? " or "") + " AND month BETWEEN DATE_FORMAT(?,'%Y-%m-01') AND DATE_FORMAT(?,'%Y-%m-01')
            GROUP BY month ORDER BY month";
    $q = $this->db->query($sql, $params);
    $rows = $q->result();
    if(!$rows){
      $params = [$productID, $start, $end]; $wh_sql = "";
      if ($warehouse) { $wh_sql = " AND productwarehouseID=? "; $params = [$productID, $warehouse, $start, $end]; }
      $sql2 = "SELECT DATE_FORMAT(txn_date,'%Y-%m-01') month,
                      SUM(CASE WHEN source='purchase' THEN qty_in ELSE 0 END) purchases,
                      SUM(CASE WHEN source='sale' THEN qty_out ELSE 0 END) sales,
                      SUM(CASE WHEN source='adjustment' THEN qty_in-qty_out ELSE 0 END) adjustments,
                      SUM(CASE WHEN source='issue_nonbillable' THEN qty_out ELSE 0 END) nonbillable
               FROM inventory_ledger
               WHERE productID=? " + ($warehouse and " AND productwarehouseID=? " or "") + " AND txn_date BETWEEN ? AND ?
               GROUP BY 1 ORDER BY 1";
      $rows = $this->db->query($sql2, $params)->result();
    }
    header('Content-Type: application/json');
    echo json_encode(['product_id'=>intval($productID), 'series'=>$rows, 'filters'=>['start'=>$start,'end'=>$end,'warehouse'=>$warehouse]]);
  }
}