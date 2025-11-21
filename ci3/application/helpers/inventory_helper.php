<?php

defined('BASEPATH') or exit('No direct script access allowed');

if (!function_exists('inventory_can_commit')) {
    /**
     * Determine whether inventory adjustments can proceed without dropping below zero.
     */
    function inventory_can_commit(object $ci, int $productID, int $productwarehouseID, float $deltaQty): bool
    {
        static $allowNegativeCache = [];

        $row = $ci->db->query(
            'SELECT onhand FROM inventory_onhand WHERE productID=? AND productwarehouseID=?',
            [$productID, $productwarehouseID]
        )->row();

        $onhand = $row ? (float) $row->onhand : 0.0;

        $schoolID = 0;
        if (isset($ci->session) && method_exists($ci->session, 'userdata')) {
            $schoolID = (int) $ci->session->userdata('schoolID');
        }

        if (!array_key_exists($schoolID, $allowNegativeCache)) {
            $allowNegative = false;
            $settingValue = null;

            if (isset($ci->siteinfos) && isset($ci->siteinfos->inventory_allow_negative_stock)) {
                $settingValue = $ci->siteinfos->inventory_allow_negative_stock;
            }

            if ($settingValue === null) {
                if (!isset($ci->setting_m)) {
                    $ci->load->model('setting_m');
                }

                $settings = $ci->setting_m->get_setting($schoolID);
                if ($settings && isset($settings->inventory_allow_negative_stock)) {
                    $settingValue = $settings->inventory_allow_negative_stock;
                }
            }

            $allowNegative = in_array(
                strtolower((string) $settingValue),
                ['1', 'yes', 'true', 'enable', 'enabled', 'allow'],
                true
            );

            $allowNegativeCache[$schoolID] = $allowNegative;
        } else {
            $allowNegative = $allowNegativeCache[$schoolID];
        }

        if ($allowNegative) {
            return true;
        }

        return ($onhand + (float) $deltaQty) >= 0;
    }
}
