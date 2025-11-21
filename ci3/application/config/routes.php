<?php

$route['default_controller'] = 'welcome';
$route['404_override'] = '';
$route['translate_uri_dashes'] = false;

// Inventory pages & APIs
$route['inventory/transfers_incoming'] = 'InventoryPages/incoming';
$route['inventory/transfers_outgoing'] = 'InventoryPages/outgoing';
$route['inventory/transfer/(:num)/accept']['POST'] = 'InventoryTransfer/accept/$1';
$route['inventory/transfer/(:num)/reject']['POST'] = 'InventoryTransfer/reject/$1';
$route['productapi/movement_series/(:num)'] = 'ProductApi/movement_series/$1';
