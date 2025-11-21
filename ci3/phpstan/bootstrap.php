<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../stubs/CodeIgniter.php';

$projectRoot = dirname(__DIR__);

if (!defined('FCPATH')) {
    define('FCPATH', $projectRoot . DIRECTORY_SEPARATOR);
}

if (!defined('BASEPATH')) {
    define('BASEPATH', $projectRoot . DIRECTORY_SEPARATOR . 'main' . DIRECTORY_SEPARATOR);
}

if (!defined('APPPATH')) {
    define('APPPATH', $projectRoot . DIRECTORY_SEPARATOR . 'mvc' . DIRECTORY_SEPARATOR);
}

if (!defined('ROOTPATH')) {
    define('ROOTPATH', $projectRoot . DIRECTORY_SEPARATOR);
}

if (!defined('WRITEPATH')) {
    define('WRITEPATH', $projectRoot . DIRECTORY_SEPARATOR . 'ci4' . DIRECTORY_SEPARATOR . 'writable' . DIRECTORY_SEPARATOR);
}

if (!defined('ENVIRONMENT')) {
    $env = $_SERVER['CI_ENV'] ?? $_ENV['CI_ENV'] ?? getenv('CI_ENV');
    define('ENVIRONMENT', is_string($env) && $env !== '' ? $env : 'development');
}

if (!defined('CI_VERSION')) {
    define('CI_VERSION', '3.1-stub');
}

if (!function_exists('get_instance')) {
    function get_instance(): CI_Controller
    {
        static $instance;
        if ($instance === null) {
            $instance = new CI_Controller();
        }

        return $instance;
    }
}

if (!function_exists('base_url')) {
    function base_url(string $uri = ''): string
    {
        $base = rtrim($_SERVER['APP_URL'] ?? 'http://localhost', '/');

        return $uri === '' ? $base . '/' : $base . '/' . ltrim($uri, '/');
    }
}

if (!function_exists('redirect')) {
    function redirect(string $uri = '', string $method = 'auto', ?int $code = null): void
    {
        // Intentionally empty stub for static analysis.
    }
}

if (!function_exists('config_item')) {
    /**
     * @param string $key
     * @return mixed
     */
    function config_item($key)
    {
        return null;
    }
}

if (!function_exists('load_class')) {
    function load_class(string $class, string $directory = 'libraries', string $prefix = 'CI_')
    {
        $qualified = $prefix . $class;
        if (class_exists($qualified)) {
            return new $qualified();
        }

        return new \stdClass();
    }
}

if (!function_exists('is_cli')) {
    function is_cli(): bool
    {
        return PHP_SAPI === 'cli';
    }
}

if (!function_exists('show_error')) {
    function show_error(string $message = '', int $status_code = 500, string $heading = 'An Error Was Encountered'): void
    {
        // Stubbed for analysis.
    }
}

if (!function_exists('log_message')) {
    function log_message(string $level, string $message): void
    {
        // Stubbed for analysis.
    }
}

if (!function_exists('validation_errors')) {
    function validation_errors(): string
    {
        return '';
    }
}

if (!function_exists('site_url')) {
    function site_url(string $uri = ''): string
    {
        return base_url($uri);
    }
}

if (!function_exists('set_status_header')) {
    function set_status_header(int $code = 200, string $text = ''): void
    {
        // Stubbed for analysis.
    }
}

if (!function_exists('esc')) {
    /**
     * @param mixed $data
     * @param string|null $context
     * @param string|null $encoding
     * @return mixed
     */
    function esc($data, ?string $context = null, ?string $encoding = null)
    {
        return $data;
    }
}

if (!function_exists('clean_path')) {
    function clean_path(string $path): string
    {
        return $path;
    }
}

if (!function_exists('service')) {
    /**
     * @return mixed
     */
    function service(string $name)
    {
        return new \stdClass();
    }
}

if (!function_exists('lang')) {
    function lang(string $line, array $args = [], ?string $locale = null): string
    {
        return $line;
    }
}
