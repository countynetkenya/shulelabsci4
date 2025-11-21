<?php defined('BASEPATH') OR exit('No direct script access allowed');

$sidebarConfigPath = __DIR__ . '/sidebar.php';
$config['admin_sidebar_pages'] = [];

if (is_file($sidebarConfigPath)) {
    $sidebarConfig = include $sidebarConfigPath;
    $items = isset($sidebarConfig['items']) && is_array($sidebarConfig['items']) ? $sidebarConfig['items'] : [];

    foreach ($items as $key => $item) {
        if (!is_array($item)) {
            continue;
        }

        $contexts = isset($item['contexts']) && is_array($item['contexts']) ? $item['contexts'] : [];
        if (!in_array('admin_sidebar', $contexts, true)) {
            continue;
        }

        $link = isset($item['link']) ? ltrim($item['link'], '/') : '';
        if ($link === '') {
            continue;
        }

        $config['admin_sidebar_pages'][$key] = [
            'name' => isset($item['name']) ? $item['name'] : $key,
            'menuName' => isset($item['menu_name']) ? $item['menu_name'] : $key,
            'menu_label_key' => isset($item['menu_label_key']) ? $item['menu_label_key'] : null,
            'route' => $link,
            'link' => $link,
            'controller' => isset($item['controller']) ? $item['controller'] : null,
            'method' => isset($item['method']) ? $item['method'] : 'index',
            'parent' => isset($item['parent']) ? $item['parent'] : null,
            'icon' => isset($item['icon']) ? $item['icon'] : null,
            'feature_flag' => isset($item['feature_flag']) ? $item['feature_flag'] : null,
            'permission_key' => isset($item['permission']) ? $item['permission'] : null,
            'priority' => isset($item['priority']) ? (int) $item['priority'] : 0,
            'create_if_missing' => isset($item['create_if_missing']) ? $item['create_if_missing'] : null,
            'notes_label' => isset($item['notes_label']) ? $item['notes_label'] : sprintf('%s admin sidebar link', isset($item['name']) ? $item['name'] : $key),
            'legacy_links' => isset($item['legacy_links']) ? $item['legacy_links'] : [],
            'sync_managed' => !empty($item['sync_managed']),
        ];
    }
}

return $config['admin_sidebar_pages'];
