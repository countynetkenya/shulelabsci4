<?php defined('BASEPATH') OR exit('No direct script access allowed');

class SidebarRegistry
{
    /** @var array|null */
    protected static $config;

    /**
     * @return array
     */
    public static function config()
    {
        if (static::$config === null) {
            $path = APPPATH . 'config/sidebar.php';
            if (is_file($path)) {
                $loaded = include $path;
                static::$config = is_array($loaded) ? $loaded : [];
            } else {
                static::$config = [];
            }
        }

        return static::$config;
    }

    /**
     * @return array
     */
    public static function items()
    {
        $config = static::config();
        $items = isset($config['items']) && is_array($config['items']) ? $config['items'] : [];

        return $items;
    }

    /**
     * @param string $context
     * @return array
     */
    public static function itemsForContext($context)
    {
        $context = strtolower($context);
        $items = [];
        foreach (static::items() as $key => $item) {
            if (!is_array($item)) {
                continue;
            }

            $contexts = isset($item['contexts']) && is_array($item['contexts']) ? $item['contexts'] : [];
            $contexts = array_map('strtolower', $contexts);
            if (!in_array($context, $contexts, true)) {
                continue;
            }

            $items[$key] = $item;
        }

        return $items;
    }

    /**
     * @return array
     */
    public static function syncableItems()
    {
        $items = [];
        foreach (static::items() as $key => $item) {
            if (!is_array($item)) {
                continue;
            }

            if (empty($item['sync_managed'])) {
                continue;
            }

            $items[$key] = $item;
        }

        return $items;
    }

    /**
     * @return array
     */
    public static function featuredSuperadminItems()
    {
        $items = [];
        foreach (static::itemsForContext('superadmin_dashboard') as $key => $item) {
            if (!is_array($item)) {
                continue;
            }

            if (empty($item['superadmin_featured'])) {
                continue;
            }

            $items[$key] = $item;
        }

        uasort($items, function ($a, $b) {
            $aPriority = isset($a['priority']) ? (int) $a['priority'] : 0;
            $bPriority = isset($b['priority']) ? (int) $b['priority'] : 0;

            return $aPriority <=> $bPriority;
        });

        return $items;
    }

    /**
     * Build a standardized menu_override payload from a sidebar config entry.
     *
     * @param string $key
     * @param array $item
     * @return array|null Returns array with link, payload, and notes or null when link missing.
     */
    public static function buildOverridePayload($key, array $item)
    {
        $link = isset($item['link']) ? ltrim($item['link'], '/') : '';
        if ($link === '') {
            return null;
        }

        $notes = [];
        foreach (['feature_flag', 'controller', 'method', 'permission', 'permission_key'] as $metaKey) {
            if (isset($item[$metaKey]) && $item[$metaKey] !== '') {
                $notes[$metaKey] = $item[$metaKey];
            }
        }

        $notes['managed_by'] = 'sidebar_config';
        $notes['config_key'] = $key;

        $menuName = isset($item['menuName']) ? $item['menuName'] : (isset($item['menu_name']) ? $item['menu_name'] : $link);

        $payload = [
            'override_type' => isset($item['override_type']) ? $item['override_type'] : 'custom',
            'menuName' => $menuName,
            'parent' => isset($item['parent']) ? $item['parent'] : null,
            'link' => $link,
            'icon' => isset($item['icon']) ? $item['icon'] : null,
            'priority' => isset($item['priority']) ? (int) $item['priority'] : 0,
            'status' => 1,
            'skip_permission' => isset($item['skip_permission']) ? (bool) $item['skip_permission'] : false,
            'create_if_missing' => isset($item['create_if_missing']) ? $item['create_if_missing'] : null,
            'notes' => !empty($notes) ? json_encode($notes) : null,
        ];

        return [
            'link' => $link,
            'payload' => $payload,
            'notes' => $notes,
        ];
    }

    /**
     * Reset cached configuration (useful for tests).
     */
    public static function resetCache()
    {
        static::$config = null;
    }
}
