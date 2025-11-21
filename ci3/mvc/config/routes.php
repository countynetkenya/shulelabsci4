<?php
    defined('BASEPATH') OR exit('No direct script access allowed');

    spl_autoload_register(function($className) {
        if ( strpos($className, 'CI_') !== 0 ) {
            $file = APPPATH . 'libraries/' . $className . '.php';
            if ( file_exists($file) && is_file($file) ) {
                @include_once( $file );
            }
        }
    });

    $route['version']            = 'app/version';
    $route['default_controller'] = 'frontend/index';
    $route['healthz']           = 'healthz/index';
    $route['productapi/movement_series/(:num)'] = 'ProductApi/movement_series/$1';

    if (!function_exists('feature_flag_enabled')) {
        $featureFlagHelper = APPPATH . 'helpers/feature_flag_helper.php';
        if (is_file($featureFlagHelper)) {
            include_once $featureFlagHelper;
        }
    }

    if (!function_exists('admin_sidebar_normalize_controller_path')) {
        function admin_sidebar_normalize_controller_path(string $controller): string
        {
            $normalized = str_replace('\\', '/', trim($controller));
            return ltrim($normalized, '/');
        }
    }

    if (!function_exists('admin_sidebar_method_exists')) {
        function admin_sidebar_method_exists(string $controllerFile, string $method): bool
        {
            if (!is_file($controllerFile)) {
                return false;
            }

            static $cache = [];
            $cacheKey = $controllerFile . '::' . strtolower($method);
            if (array_key_exists($cacheKey, $cache)) {
                return $cache[$cacheKey];
            }

            $contents = file_get_contents($controllerFile);
            if ($contents === false) {
                $cache[$cacheKey] = false;
                return false;
            }

            $pattern = '/function\s+' . preg_quote($method, '/') . '\s*\(/i';
            $cache[$cacheKey] = (bool) preg_match($pattern, $contents);

            return $cache[$cacheKey];
        }
    }

    $adminSidebarPages = [];
    $adminSidebarConfigPath = APPPATH . 'config/admin_sidebar_pages.php';
    if (is_file($adminSidebarConfigPath)) {
        $loadedPages = include $adminSidebarConfigPath;
        if (is_array($loadedPages)) {
            $adminSidebarPages = $loadedPages;
        }
    }

    foreach ($adminSidebarPages as $pageKey => $pageConfig) {
        if (!is_array($pageConfig)) {
            continue;
        }

        $routeKey = isset($pageConfig['route']) ? trim($pageConfig['route']) : '';
        if ($routeKey === '' || isset($route[$routeKey])) {
            continue;
        }

        $flag = isset($pageConfig['feature_flag']) ? $pageConfig['feature_flag'] : null;
        if ($flag && function_exists('feature_flag_enabled') && !feature_flag_enabled($flag)) {
            if (function_exists('log_message')) {
                log_message('debug', sprintf('Skipping admin route %s because feature flag %s is disabled.', $routeKey, $flag));
            }
            continue;
        }

        $controller = isset($pageConfig['controller']) ? $pageConfig['controller'] : '';
        if ($controller === '') {
            continue;
        }

        $normalizedController = admin_sidebar_normalize_controller_path($controller);
        $controllerFile = APPPATH . 'controllers/' . $normalizedController . '.php';

        if (!is_file($controllerFile)) {
            if (function_exists('log_message')) {
                log_message('debug', sprintf('Skipping admin route %s because controller %s is missing.', $routeKey, $normalizedController));
            }
            continue;
        }

        $method = isset($pageConfig['method']) && $pageConfig['method'] !== '' ? $pageConfig['method'] : 'index';
        if (!admin_sidebar_method_exists($controllerFile, $method)) {
            if (function_exists('log_message')) {
                log_message('debug', sprintf('Skipping admin route %s because method %s::%s is unavailable.', $routeKey, $normalizedController, $method));
            }
            continue;
        }

        $route[$routeKey] = $normalizedController . '/' . $method;
    }

    $explicitAdminRoutes = [
        'admin/index' => 'Superadmindashboard/index',
        'cfr' => 'Cfr/index',
        'finance/statement' => 'FinanceStatement/index',
        'okr' => 'Okr/index',
        'payroll' => 'Payroll/index',
        'superadmin/dashboard' => 'Superadmindashboard/index',
        'superadmin/users' => 'Superadminusers/index',
    ];

    foreach ($explicitAdminRoutes as $uri => $target) {
        if (!isset($route[$uri])) {
            $route[$uri] = $target;
        }
    }

    $financeStatementRoutes = [
        'finance_statement/api' => 'FinanceStatement/api',
        'finance_statement/export_csv' => 'FinanceStatement/export_csv',
        'finance_statement/export_pdf' => 'FinanceStatement/export_pdf',
        'finance_statement/sectioncall' => 'FinanceStatement/sectioncall',
        'finance_statement/studentcall' => 'FinanceStatement/studentcall',
        'finance_statement/parentcall' => 'FinanceStatement/parentcall',
        'finance_statement/termcall' => 'FinanceStatement/termcall',
        'finance_statement/datescall' => 'FinanceStatement/datescall',
    ];

    $CFG =& load_class('Config', 'core');
    if (config_item('shulelabs') === NULL) {
        $CFG->load('shulelabs');
    }

    $shulelabsConfig = config_item('shulelabs');
    $hasUnifiedFlagConfig = false;
    if (is_array($shulelabsConfig)) {
        $featureFlags = isset($shulelabsConfig['feature_flags']) ? $shulelabsConfig['feature_flags'] : null;
        if (is_array($featureFlags) && array_key_exists('UNIFIED_STATEMENT', $featureFlags)) {
            $hasUnifiedFlagConfig = true;
        }
    }

    $isUnifiedStatementEnabled = true;
    if ($hasUnifiedFlagConfig && function_exists('feature_flag_enabled')) {
        $isUnifiedStatementEnabled = feature_flag_enabled('UNIFIED_STATEMENT');
    } elseif (function_exists('shulelabs_bool_env')) {
        $isUnifiedStatementEnabled = shulelabs_bool_env('FLAG_UNIFIED_STATEMENT', true);
    }

    foreach ($financeStatementRoutes as $uri => $target) {
        if (isset($route[$uri])) {
            continue;
        }

        if (!$isUnifiedStatementEnabled) {
            if (function_exists('log_message')) {
                log_message('debug', sprintf('Skipping admin route %s because feature flag %s is disabled.', $uri, 'UNIFIED_STATEMENT'));
            }
            continue;
        }

        $route[$uri] = $target;
    }

    if (!isset($route['superadmin/users/(:any)'])) {
        $route['superadmin/users/(:any)'] = 'Superadminusers/$1';
    }

    if (!isset($route['admin/(:any)'])) {
        $route['admin/(:any)'] = 'Superadminusers/$1';
    }
