<?php defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Sidebar menu overrides
|--------------------------------------------------------------------------
| Adjust the structure of the admin sidebar without modifying controllers
| or views.  Add custom nodes to the `$config['menu_custom_nodes']` array
| and relocate existing entries via `$config['menu_relocations']`.  After
| updating this file, clear the cached `dbMenus` value from the user session
| so the new structure can be rebuilt on the next request.
|
| Each custom node accepts:
| - menuName: language key suffix used by `topbar_menu_lang` (e.g. `main_exam`)
| - link: controller/method path or `#` for headings
| - icon: Font Awesome class
| - priority: lower numbers float to the top within a branch
| - status: whether the node is enabled
| - parent: menuName/link identifier for the desired parent node (or `null`)
| - create_if_missing: bool|array to optionally create the parent placeholder
| - skip_permission (optional): bypasses permission checks in the renderer
|
| Relocations follow the same conventions but operate on existing nodes.
*/

$config['menu_custom_nodes'] = [
    [
        'menuName' => 'menuoverrides',
        'link' => 'menuoverrides/index',
        'icon' => 'fa-sitemap',
        'priority' => 65,
        'status' => 1,
        'parent' => 'main_administrator',
        'create_if_missing' => false,
    ],
    [
        'menuName' => 'main_examreport',
        'link' => '#',
        'icon' => 'fa-file-text-o',
        'priority' => 20,
        'status' => 1,
        'parent' => 'main_exam',
        'create_if_missing' => false,
    ],
    [
        'menuName' => 'examtranscriptreport',
        'link' => 'examtranscriptreport/index',
        'icon' => 'fa-file-text-o',
        'priority' => 10,
        'status' => 1,
        'parent' => 'main_examreport',
        'create_if_missing' => true,
    ],
    [
        'menuName' => 'inventory_transfers_incoming',
        'link' => 'inventory/transfers_incoming',
        'icon' => 'fa-truck',
        'priority' => 30,
        'status' => 1,
        'parent' => 'main_inventory',
        'create_if_missing' => false,
        'skip_permission' => true,
    ],
    [
        'menuName' => 'inventory_transfers_outgoing',
        'link' => 'inventory/transfers_outgoing',
        'icon' => 'fa-exchange',
        'priority' => 40,
        'status' => 1,
        'parent' => 'main_inventory',
        'create_if_missing' => false,
        'skip_permission' => true,
    ],
];

$config['menu_relocations'] = [
    [
        'menuName' => 'examreport',
        'parent' => 'main_examreport',
        'priority' => 20,
        'create_if_missing' => true,
    ],
    [
        'menuName' => 'progresscardreport',
        'parent' => 'main_examreport',
        'priority' => 30,
        'create_if_missing' => true,
    ],
    [
        'menuName' => 'terminalreport',
        'parent' => 'main_examreport',
        'priority' => 40,
        'create_if_missing' => true,
    ],
    [
        'menuName' => 'marksheetreport',
        'parent' => 'main_examreport',
        'priority' => 50,
        'create_if_missing' => true,
    ],
];
