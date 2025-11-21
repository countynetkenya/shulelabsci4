<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('shulelabs_env')) {
    require_once __DIR__ . '/env.php';
}

if (function_exists('shulelabs_bootstrap_env')) {
    $projectRoot = dirname(dirname(__DIR__));
    shulelabs_bootstrap_env($projectRoot);
}

$active_group = 'default';
$active_record = TRUE;

$db = [
    'default' => [
        'dsn'          => '',
        'hostname'     => '',
        'username'     => '',
        'password'     => '',
        'database'     => '',
        'dbdriver'     => 'mysqli',
        'dbprefix'     => '',
        'pconnect'     => FALSE,
        'db_debug'     => (defined('ENVIRONMENT') && ENVIRONMENT !== 'production'),
        'cache_on'     => FALSE,
        'cachedir'     => '',
        'char_set'     => 'utf8mb4',
        'dbcollat'     => 'utf8mb4_unicode_ci',
        'swap_pre'     => '',
        'encrypt'      => FALSE,
        'compress'     => FALSE,
        'autoinit'     => FALSE,
        'stricton'     => FALSE,
        'failover'     => [],
        'save_queries' => TRUE,
    ],
];

$searchPaths = [];
$envDefined = defined('ENVIRONMENT') && ENVIRONMENT !== '';
$environmentPath = null;

if ($envDefined) {
    $environmentPath = __DIR__ . DIRECTORY_SEPARATOR . ENVIRONMENT . DIRECTORY_SEPARATOR . 'database.php';
    $searchPaths[] = $environmentPath;
}

$shouldFallback = !$envDefined;
if (!$shouldFallback && ENVIRONMENT === 'development') {
    $shouldFallback = !is_file($environmentPath);
}

if ($shouldFallback) {
    $fallbackEnvironments = ['testing', 'production'];
    foreach ($fallbackEnvironments as $fallback) {
        $fallbackPath = __DIR__ . DIRECTORY_SEPARATOR . $fallback . DIRECTORY_SEPARATOR . 'database.php';
        if ($fallbackPath === $environmentPath) {
            continue;
        }

        $searchPaths[] = $fallbackPath;
    }
}

$configSource = null;
foreach ($searchPaths as $path) {
    if (is_file($path)) {
        require $path;
        $configSource = $path;
        break;
    }
}

$resolveEnv = function (array $keys, $default = null) {
    foreach ($keys as $key) {
        $value = shulelabs_env($key, null);
        if ($value !== null) {
            return $value;
        }
    }

    return $default;
};

$resolveBool = function (array $keys, $default = false) use ($resolveEnv) {
    $value = $resolveEnv($keys, null);
    if ($value === null) {
        return $default;
    }

    if (is_bool($value)) {
        return $value;
    }

    return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $default;
};

$envOverrides = [
    'dsn'      => $resolveEnv(['DB_DSN', 'DATABASE_DSN'], ''),
    'hostname' => $resolveEnv(['DB_HOST', 'DB_HOSTNAME', 'DATABASE_HOST']),
    'username' => $resolveEnv(['DB_USERNAME', 'DB_USER', 'DATABASE_USERNAME']),
    'password' => $resolveEnv(['DB_PASSWORD', 'DATABASE_PASSWORD']),
    'database' => $resolveEnv(['DB_NAME', 'DB_DATABASE', 'DATABASE_NAME'], 'shulelabs_staging'),
    'dbdriver' => $resolveEnv(['DB_DRIVER', 'DATABASE_DRIVER'], $db['default']['dbdriver']),
    'port'     => $resolveEnv(['DB_PORT', 'DATABASE_PORT'], null),
];

foreach ($envOverrides as $key => $value) {
    if ($value === null) {
        continue;
    }

    if ($key === 'port') {
        if ($value !== '') {
            $db['default'][$key] = (int) $value;
        }
        continue;
    }

    $db['default'][$key] = $value;
}

$allowEmptyPassword = $resolveBool(['DB_ALLOW_EMPTY_PASSWORD', 'DATABASE_ALLOW_EMPTY_PASSWORD'], false);

$requiredKeys = ['hostname', 'username', 'database'];
$missing = [];
foreach ($requiredKeys as $key) {
    $value = isset($db['default'][$key]) ? trim((string) $db['default'][$key]) : '';
    if ($value === '') {
        $missing[] = $key;
    }
}

$passwordValue = isset($db['default']['password']) ? $db['default']['password'] : null;
if (($passwordValue === null || $passwordValue === '') && !$allowEmptyPassword) {
    $missing[] = 'password';
}

if (!empty($missing)) {
    $hintSources = $configSource ? [$configSource] : $searchPaths;
    $hintText = empty($hintSources)
        ? 'mvc/config/{environment}/database.php'
        : implode(', ', $hintSources);

    trigger_error(
        sprintf(
            'Database configuration is incomplete (missing: %s). Set DB_HOST, DB_USERNAME, DB_PASSWORD, DB_DATABASE in your environment or update %s. '
            . 'Set DB_ALLOW_EMPTY_PASSWORD=1 to bypass the password requirement for local sandboxes.',
            implode(', ', array_unique($missing)),
            $hintText
        ),
        E_USER_ERROR
    );
}

