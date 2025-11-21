<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
if (!function_exists('inventory_can_commit')){
  function inventory_can_commit($CI, $productID, $productwarehouseID, $deltaQty){
    $row = $CI->db->query("SELECT onhand FROM inventory_onhand WHERE productID=? AND productwarehouseID=?", [$productID,$productwarehouseID])->row();
    $onhand = $row ? floatval($row->onhand) : 0.0;
    return ($onhand + floatval($deltaQty)) >= 0;
  }
}