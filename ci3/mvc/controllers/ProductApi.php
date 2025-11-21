<?php if(!defined('BASEPATH')) exit('No direct script access allowed');
class ProductApi extends CI_Controller {
  public function __construct(){ parent::__construct(); $this->load->database(); }
  public function movement_series($productID){
    $start = $this->input->get('start') ?: date('Y-m-01', strtotime('-5 months'));
    $end   = $this->input->get('end')   ?: date('Y-m-t');
    $warehouse = $this->input->get('warehouse') ?: null;
    $granularity = $this->input->get('granularity');

    $normalized = $this->normaliseDateRange($start, $end);
    $start = $normalized['start'];
    $end   = $normalized['end'];

    $granularity = $this->resolveGranularity($granularity, $start, $end);

    $series = [];

    if($granularity === 'month'){
      $series = $this->fetchMonthlySeries($productID, $start, $end, $warehouse);
    }

    if(empty($series)){
      $series = $this->fetchLedgerSeries($productID, $start, $end, $warehouse, $granularity);
    }

    header('Content-Type: application/json');
    echo json_encode([
      'product_id' => intval($productID),
      'series'     => $series,
      'granularity'=> $granularity,
      'filters'    => ['start' => $start, 'end' => $end, 'warehouse' => $warehouse]
    ]);
  }

  private function normaliseDateRange($start, $end){
    try {
      $startDate = new DateTime($start);
    } catch(Exception $e){
      $startDate = new DateTime(date('Y-m-01', strtotime('-5 months')));
    }
    try {
      $endDate = new DateTime($end);
    } catch(Exception $e){
      $endDate = new DateTime(date('Y-m-t'));
    }
    if($startDate > $endDate){
      $tmp = $startDate; $startDate = $endDate; $endDate = $tmp;
    }
    return [
      'start' => $startDate->format('Y-m-d'),
      'end'   => $endDate->format('Y-m-d')
    ];
  }

  private function resolveGranularity($granularity, $start, $end){
    $allowed = ['day','week','month','year'];
    if(!$granularity || !in_array($granularity, $allowed, true)){
      try {
        $startDate = new DateTime($start);
        $endDate = new DateTime($end);
        $diffDays = (int)$startDate->diff($endDate)->format('%a');
      } catch(Exception $e){
        $diffDays = 0;
      }
      if($diffDays <= 31){
        $granularity = 'day';
      } elseif($diffDays <= 180){
        $granularity = 'week';
      } elseif($diffDays <= 730){
        $granularity = 'month';
      } else {
        $granularity = 'year';
      }
    }
    return $granularity;
  }

  private function fetchMonthlySeries($productID, $start, $end, $warehouse){
    $params = [$productID, $start, $end];
    $warehouseClause = '';
    if($warehouse){
      $warehouseClause = ' AND productwarehouseID=? ';
      $params = [$productID, $warehouse, $start, $end];
    }
    $sql = "SELECT month,
                   SUM(purchases) AS purchases,
                   SUM(sales) AS sales,
                   SUM(adjustments) AS adjustments,
                   SUM(nonbillable_issues) AS nonbillable
            FROM inventory_monthly
            WHERE productID=? " . $warehouseClause . "
              AND month BETWEEN DATE_FORMAT(?,'%Y-%m-01') AND DATE_FORMAT(?,'%Y-%m-01')
            GROUP BY month
            ORDER BY month";
    $rows = $this->db->query($sql, $params)->result();
    $series = [];
    foreach($rows as $row){
      $stockIn = (float)$row->purchases;
      $stockOut = (float)$row->sales + (float)$row->nonbillable;
      $adjustments = (float)$row->adjustments;
      if($adjustments >= 0){
        $stockIn += $adjustments;
      } else {
        $stockOut += abs($adjustments);
      }
      $series[] = [
        'bucket_label' => $row->month,
        'stock_in'     => $stockIn,
        'stock_out'    => $stockOut
      ];
    }
    return $series;
  }

  private function fetchLedgerSeries($productID, $start, $end, $warehouse, $granularity){
    $buckets = [
      'day' => ['expr' => "DATE(txn_date)", 'formatter' => function($bucket){ return $bucket; }],
      'week' => ['expr' => "YEARWEEK(txn_date)", 'formatter' => function($bucket){
        if(!$bucket) return '';
        $year = substr($bucket, 0, 4);
        $week = substr($bucket, 4);
        if(!is_numeric($year) || !is_numeric($week)){
          return $bucket;
        }
        $week = (int)$week;
        $year = (int)$year;
        try {
          $dt = new DateTime();
          $dt->setISODate($year, max(1, $week));
          return $dt->format('Y') . '-W' . str_pad((string)max(1, $week), 2, '0', STR_PAD_LEFT);
        } catch(Exception $e){
          return $year . '-W' . str_pad((string)$week, 2, '0', STR_PAD_LEFT);
        }
      }],
      'month' => ['expr' => "DATE_FORMAT(txn_date,'%Y-%m-01')", 'formatter' => function($bucket){ return $bucket; }],
      'year' => ['expr' => "YEAR(txn_date)", 'formatter' => function($bucket){ return (string)$bucket; }],
    ];

    if(!isset($buckets[$granularity])){
      $granularity = 'month';
    }

    $bucketExpr = $buckets[$granularity]['expr'];
    $params = [$productID, $start, $end];
    $warehouseClause = '';
    if($warehouse){
      $warehouseClause = ' AND productwarehouseID=? ';
      $params = [$productID, $warehouse, $start, $end];
    }

    $sql = "SELECT " . $bucketExpr . " AS bucket,
                   SUM(qty_in) AS total_in,
                   SUM(qty_out) AS total_out
            FROM inventory_ledger
            WHERE productID=? " . $warehouseClause . " AND txn_date BETWEEN ? AND ?
            GROUP BY bucket
            ORDER BY bucket";
    $query = $this->db->query($sql, $params);
    $formatter = $buckets[$granularity]['formatter'];
    $series = [];
    foreach($query->result() as $row){
      $series[] = [
        'bucket_label' => $formatter($row->bucket),
        'stock_in'     => (float)$row->total_in,
        'stock_out'    => (float)$row->total_out
      ];
    }
    return $series;
  }
}