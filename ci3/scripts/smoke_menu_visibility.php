#!/usr/bin/env php
<?php
$root = dirname(__DIR__);

if (!defined('BASEPATH')) {
    define('BASEPATH', $root . '/mvc/');
}

if (!defined('APPPATH')) {
    define('APPPATH', $root . '/mvc/');
}

if (!defined('ENVIRONMENT')) {
    define('ENVIRONMENT', 'development');
}

require_once $root . '/mvc/config/env.php';
if (function_exists('shulelabs_bootstrap_env')) {
    shulelabs_bootstrap_env($root);
}

require_once APPPATH . 'libraries/SidebarRegistry.php';

$results = [];
$overallPass = true;

$addResult = function (string $label, bool $passed, string $details = '') use (&$results, &$overallPass) {
    $results[] = [
        'label' => $label,
        'status' => $passed ? 'PASS' : 'FAIL',
        'details' => $details,
    ];
    if (!$passed) {
        $overallPass = false;
    }
};

$warn = function (string $label, string $details) use (&$results) {
    $results[] = [
        'label' => $label,
        'status' => 'WARN',
        'details' => $details,
    ];
};

$shulelabsConfig = $root . '/mvc/config/shulelabs.php';
$config = [];
if (is_file($shulelabsConfig)) {
    require $shulelabsConfig;
}

$flags = $config['shulelabs']['feature_flags'] ?? [];
$expectedFlags = [
    'OKR_V1',
    'CFR_V1',
    'UNIFIED_STATEMENT',
    'PAYROLL_V2',
    'PERMISSIONS_V1',
];

foreach ($expectedFlags as $flag) {
    $enabled = array_key_exists($flag, $flags) ? (bool) $flags[$flag] : false;
    $addResult("Feature flag {$flag}", $enabled, $enabled ? 'enabled' : 'disabled');
}

$sidebarItems = SidebarRegistry::itemsForContext('admin_sidebar');
$syncableItems = SidebarRegistry::syncableItems();

$controllerChecks = [];
foreach ($sidebarItems as $item) {
    if (!is_array($item) || empty($item['controller'])) {
        continue;
    }

    $controller = $item['controller'];
    if (isset($controllerChecks[$controller])) {
        continue;
    }

    $path = $root . '/mvc/controllers/' . ltrim(str_replace('\\', '/', $controller), '/') . '.php';
    if (!is_file($path)) {
        $controllerChecks[$controller] = [false, 'file missing'];
        continue;
    }

    $contents = file_get_contents($path) ?: '';
    $className = basename($controller);
    $hasClass = (bool) preg_match('/class\s+' . preg_quote($className, '/') . '\b/', $contents);
    $method = isset($item['method']) && $item['method'] !== '' ? $item['method'] : 'index';
    $pattern = '/function\s+' . preg_quote($method, '/') . '\s*\(/i';
    $hasMethod = (bool) preg_match($pattern, $contents);
    $controllerChecks[$controller] = [
        $hasClass && $hasMethod,
        $hasClass && $hasMethod ? 'class and method found' : 'missing class or method',
    ];
}

foreach ($controllerChecks as $controller => [$passed, $details]) {
    $addResult("Controller {$controller}", $passed, $details);
}

$routesFile = $root . '/mvc/config/routes.php';
if (is_file($routesFile)) {
    $routesSource = file_get_contents($routesFile) ?: '';
    $routeExpectations = [];
    foreach ($sidebarItems as $item) {
        if (!is_array($item)) {
            continue;
        }

        $uri = isset($item['route']) ? ltrim($item['route'], '/') : (isset($item['link']) ? ltrim($item['link'], '/') : '');
        if ($uri === '') {
            continue;
        }

        $controller = isset($item['controller']) ? basename($item['controller']) : null;
        if ($controller === null) {
            continue;
        }

        $method = isset($item['method']) && $item['method'] !== '' ? $item['method'] : 'index';
        $routeExpectations[$uri] = $controller . '/' . $method;
    }
    foreach ($routeExpectations as $uri => $target) {
        $needle = "'{$uri}' => '{$target}'";
        $addResult(
            "Route {$uri}",
            strpos($routesSource, $needle) !== false,
            strpos($routesSource, $needle) !== false ? 'explicit mapping present' : 'mapping not found'
        );
    }
} else {
    $warn('Routes file', 'mvc/config/routes.php is missing');
}

$dbConfigFile = $root . '/mvc/config/database.php';
$db = [];
if (is_file($dbConfigFile)) {
    require $dbConfigFile;
}

$activeGroup = $active_group ?? 'default';
$connection = $db[$activeGroup] ?? [];

$hostname = $connection['hostname'] ?? '';
$username = $connection['username'] ?? '';
$password = $connection['password'] ?? '';
$database = $connection['database'] ?? '';
$port = $connection['port'] ?? null;
$dbprefix = $connection['dbprefix'] ?? '';

if ($hostname && $username && $database) {
    mysqli_report(MYSQLI_REPORT_OFF);
    $mysqli = @mysqli_init();
    if ($mysqli !== false) {
        $portValue = $port !== null ? (int) $port : ini_get('mysqli.default_port');
        $connected = @$mysqli->real_connect($hostname, $username, $password, $database, $portValue ? (int) $portValue : null);
        if ($connected) {
            $table = $dbprefix . 'menu_overrides';
            $links = [];
            foreach ($syncableItems as $item) {
                if (!is_array($item) || empty($item['link'])) {
                    continue;
                }
                $links[] = ltrim($item['link'], '/');
            }
            $links = array_unique(array_filter($links));
            if (empty($links)) {
                $warn('Sidebar config', 'no syncable links defined in sidebar.php');
            } else {
                $escaped = array_map([$mysqli, 'real_escape_string'], $links);
                $values = "'" . implode("','", $escaped) . "'";
                $sql = "SELECT link FROM {$table} WHERE link IN ({$values})";
                $result = $mysqli->query($sql);
                $found = [];
                if ($result) {
                    while ($row = $result->fetch_assoc()) {
                        $found[] = $row['link'];
                    }
                }
                foreach ($links as $link) {
                    $addResult(
                        "menu_overrides: {$link}",
                        in_array($link, $found, true),
                        in_array($link, $found, true) ? 'row present' : 'row missing'
                    );
                }
            }
            $mysqli->close();
        } else {
            $warn('Database connection', 'connection failed: ' . $mysqli->connect_error);
        }
    } else {
        $warn('Database connection', 'mysqli extension unavailable');
    }
} else {
    $warn('Database config', 'hostname/username/database incomplete');
}

foreach ($results as $result) {
    $details = $result['details'] !== '' ? " ({$result['details']})" : '';
    echo sprintf('[%s] %s%s', $result['status'], $result['label'], $details), PHP_EOL;
}

echo $overallPass ? 'OVERALL: PASS' : 'OVERALL: FAIL', PHP_EOL;
exit($overallPass ? 0 : 1);
