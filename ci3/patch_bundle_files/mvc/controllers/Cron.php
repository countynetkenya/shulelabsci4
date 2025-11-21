<?php if(!defined('BASEPATH')) exit('No direct script access allowed');
class Cron extends CI_Controller {
    public function __construct(){ parent::__construct(); if (php_sapi_name() !== 'cli') { show_404(); exit; } $this->load->database(); }
    public function refreshInventoryMonthly($from=null,$to=null){
        $from = $from ?: date('Y-m-01', strtotime('-2 months'));
        $to   = $to   ?: date('Y-m-t');
        echo "Rebuilding inventory_monthly from $from to $to\n";
        $this->db->query("DELETE im FROM inventory_monthly im WHERE im.month BETWEEN DATE_FORMAT(?,'%Y-%m-01') AND DATE_FORMAT(?,'%Y-%m-01')", [$from,$to]);
        $sql = "INSERT INTO inventory_monthly (month, productID, productwarehouseID, purchases, sales, adjustments, nonbillable_issues)
                SELECT DATE_FORMAT(txn_date,'%Y-%m-01'), productID, productwarehouseID,
                       SUM(CASE WHEN source='purchase' THEN qty_in ELSE 0 END),
                       SUM(CASE WHEN source='sale' THEN qty_out ELSE 0 END),
                       SUM(CASE WHEN source='adjustment' THEN qty_in-qty_out ELSE 0 END),
                       SUM(CASE WHEN source='issue_nonbillable' THEN qty_out ELSE 0 END)
                FROM inventory_ledger
                WHERE txn_date BETWEEN ? AND ?
                GROUP BY 1,2,3
                ON DUPLICATE KEY UPDATE purchases=VALUES(purchases), sales=VALUES(sales), adjustments=VALUES(adjustments), nonbillable_issues=VALUES(nonbillable_issues)";
        $this->db->query($sql, [$from,$to]);
        echo "Done.\n";
    }
}