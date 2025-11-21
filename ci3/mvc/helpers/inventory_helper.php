<?php

defined('BASEPATH') or exit('No direct script access allowed');

if (!function_exists('get_current_stock')) {
    function get_current_stock(int $productID, int $warehouseID): int
    {
        /** @var CI_Controller $CI */
        $CI = get_instance();
        $CI->load->database();

        $sql = 'SELECT SUM(qty_in - qty_out) AS current_stock
                FROM inventory_ledger
                WHERE productID = ? AND productwarehouseID = ?';
        $query = $CI->db->query($sql, [$productID, $warehouseID]);
        $result = $query->row();

        if ($result && isset($result->current_stock)) {
            return (int) $result->current_stock;
        }

        return 0;
    }
}

if (!function_exists('has_sufficient_stock')) {
    function has_sufficient_stock(int $productID, int $warehouseID, int $quantity_to_decrease): bool
    {
        $currentStock = get_current_stock($productID, $warehouseID);

        return $quantity_to_decrease <= $currentStock;
    }
}
