<?php

declare(strict_types=1);

/**
 * PHPStan Bootstrap for CodeIgniter 4
 *
 * Provides stub definitions for CodeIgniter 4 helper functions
 * that PHPStan cannot auto-discover from the framework.
 */

// Load CI4 framework if available
if (!class_exists('CodeIgniter\Config\BaseConfig')) {
    if (file_exists(__DIR__ . '/../../vendor/autoload.php')) {
        require_once __DIR__ . '/../../vendor/autoload.php';
    }
}

if (!function_exists('session')) {
    /**
     * Returns the shared session instance.
     *
     * @return \CodeIgniter\Session\Session
     */
    function session(?string $val = null)
    {
        // Stub for PHPStan - returns Session instance
        /** @var \CodeIgniter\Session\Session */
        return \Config\Services::session();
    }
}

if (!function_exists('redirect')) {
    /**
     * Convenience method that works with the current global $request and
     * $router instances to redirect using named/reverse-routed routes
     * to determine the URL to go to.
     *
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    function redirect(?string $route = null)
    {
        // Stub for PHPStan - returns RedirectResponse
        /** @var \CodeIgniter\HTTP\RedirectResponse */
        return \Config\Services::redirectresponse();
    }
}

if (!function_exists('config')) {
    /**
     * More simple way of getting config instances.
     *
     * @template T of \CodeIgniter\Config\BaseConfig
     * @param class-string<T> $name
     * @return T
     */
    function config(string $name, bool $getShared = true)
    {
        // Stub for PHPStan
        return \Config\Factories::config($name, ['getShared' => $getShared]);
    }
}

if (!function_exists('env')) {
    /**
     * Retrieve environment variable value.
     *
     * @return bool|string|null
     */
    function env(string $key, $default = null)
    {
        // Stub for PHPStan
        return $_ENV[$key] ?? $default;
    }
}

if (!function_exists('current_url')) {
    /**
     * Returns the current URL.
     *
     * @return string
     */
    function current_url(bool $returnObject = false)
    {
        // Stub for PHPStan - always returns string in this context
        return 'http://localhost/';
    }
}

if (!function_exists('base_url')) {
    /**
     * Returns the base URL.
     *
     * @return string
     */
    function base_url($relativePath = '', ?string $scheme = null): string
    {
        return 'http://localhost/';
    }
}

if (!function_exists('esc')) {
    /**
     * Escapes data for output.
     *
     * @param array<array-key, mixed>|string $data
     * @return array<array-key, mixed>|string
     */
    function esc($data, string $context = 'html', ?string $encoding = null)
    {
        return $data;
    }
}

if (!function_exists('service')) {
    /**
     * Allows cleaner access to the Services Config file.
     *
     * @return mixed
     */
    function service(string $name, ...$params)
    {
        return \Config\Services::$name(...$params);
    }
}

if (!function_exists('model')) {
    /**
     * More simple way of getting model instances.
     *
     * @template T
     * @param class-string<T> $name
     * @return T
     */
    function model(string $name, bool $getShared = true, ?object &$conn = null)
    {
        // Stub for PHPStan
        return \Config\Factories::models($name, ['getShared' => $getShared], $conn);
    }
}

if (!function_exists('helper')) {
    /**
     * Load a helper file.
     *
     * @param array<int, string>|string $filenames
     */
    function helper($filenames): void
    {
        // Stub for PHPStan - no-op
    }
}

if (!function_exists('view')) {
    /**
     * Render a view.
     *
     * @param array<string, mixed> $data
     * @param array<string, mixed> $options
     */
    function view(string $name, array $data = [], array $options = []): string
    {
        return '';
    }
}

if (!function_exists('csrf_field')) {
    /**
     * Generate CSRF field for forms.
     */
    function csrf_field(?string $id = null): string
    {
        return '<input type="hidden" name="csrf_test_name" value="csrf_token" />';
    }
}

if (!function_exists('csrf_token')) {
    /**
     * Get CSRF token name.
     */
    function csrf_token(): string
    {
        return 'csrf_test_name';
    }
}

if (!function_exists('csrf_hash')) {
    /**
     * Get CSRF hash value.
     */
    function csrf_hash(): string
    {
        return 'csrf_token_value';
    }
}

if (!function_exists('form_open')) {
    /**
     * Open a form with CSRF protection.
     *
     * @param array<string, mixed>|string $attributes
     * @param array<string, mixed> $hidden
     */
    function form_open(string $action = '', $attributes = [], array $hidden = []): string
    {
        return '<form>';
    }
}

if (!function_exists('form_close')) {
    /**
     * Close a form.
     */
    function form_close(string $extra = ''): string
    {
        return '</form>';
    }
}

if (!function_exists('old')) {
    /**
     * Retrieve old input data.
     *
     * @return array<array-key, mixed>|string|null
     */
    function old(string $key, $default = null, string $escape = 'html')
    {
        return $default;
    }
}

if (!function_exists('log_message')) {
    /**
     * Log a message.
     */
    function log_message(string $level, string $message, array $context = []): void
    {
        // Stub for PHPStan - no-op
    }
}
