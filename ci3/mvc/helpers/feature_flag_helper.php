<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

if (!function_exists('feature_flag_enabled')) {
    function feature_flag_enabled($flag)
    {
        $flag = strtoupper($flag);
        $config = config_item('shulelabs');
        if (!is_array($config) || !isset($config['feature_flags'])) {
            return false;
        }

        if (isset($config['feature_flags'][$flag])) {
            return (bool) $config['feature_flags'][$flag];
        }

        // Allow flag names without section prefix when accessing directly.
        foreach ($config['feature_flags'] as $key => $value) {
            if (strtoupper($key) === $flag) {
                return (bool) $value;
            }
        }

        return false;
    }
}

if (!function_exists('require_feature_flag')) {
    function require_feature_flag($flag)
    {
        if (!feature_flag_enabled($flag)) {
            show_404();
            exit;
        }
    }
}
