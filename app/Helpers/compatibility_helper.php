<?php

/**
 * CI3 Compatibility Helper
 *
 * Provides helper functions to maintain compatibility with CI3 code patterns
 * while running in the CI4 environment.
 */

if (!function_exists('customCompute')) {
    /**
     * Custom compute function - counts array/object elements or checks if value exists
     * Compatible with CI3's customCompute helper
     *
     * @param mixed $data
     * @return int
     */
    function customCompute($data): int
    {
        if (is_array($data) || is_object($data)) {
            return count((array)$data);
        }

        return $data ? 1 : 0;
    }
}

if (!function_exists('namesorting')) {
    /**
     * Name sorting/truncation function
     * Truncates a name to specified length
     *
     * @param string $name
     * @param int $length
     * @return string
     */
    function namesorting(string $name, int $length = 25): string
    {
        if (strlen($name) > $length) {
            return substr($name, 0, $length) . '...';
        }

        return $name;
    }
}

if (!function_exists('config_item')) {
    /**
     * Get config item - CI3 compatibility
     *
     * @param string $item
     * @return string|bool|null
     */
    function config_item(string $item)
    {
        $config = config(\Config\App::class);

        // Map common CI3 config items to CI4 equivalents
        $mapping = [
            'base_url' => $config->baseURL ?? base_url(),
            'encryption_key' => env('encryption.key', env('ENCRYPTION_KEY', '')),
            'demo' => env('DEMO_MODE', false),
        ];

        return $mapping[$item] ?? null;
    }
}

if (!function_exists('set_value')) {
    /**
     * Set form value for repopulating forms
     *
     * @param string $field
     * @param string $default
     * @return string
     */
    function set_value(string $field, string $default = ''): string
    {
        $request = \Config\Services::request();
        $value = $request->getPost($field);

        return $value !== null ? esc($value) : esc($default);
    }
}

if (!function_exists('form_error')) {
    /**
     * Get form validation error for a field
     *
     * @param string $field
     * @param string $prefix
     * @param string $suffix
     * @return string
     */
    function form_error(string $field, string $prefix = '', string $suffix = ''): string
    {
        $validation = \Config\Services::validation();

        if ($validation->hasError($field)) {
            return $prefix . $validation->getError($field) . $suffix;
        }

        return '';
    }
}

if (!function_exists('doctype')) {
    /**
     * Return HTML5 doctype
     *
     * @param string $type
     * @return string
     */
    function doctype(string $type = 'html5'): string
    {
        return '<!DOCTYPE html>';
    }
}

if (!function_exists('validation_errors')) {
    /**
     * Get all validation errors as a string
     *
     * @param string $prefix
     * @param string $suffix
     * @return string
     */
    function validation_errors(string $prefix = '', string $suffix = ''): string
    {
        $validation = \Config\Services::validation();
        $errors = $validation->getErrors();

        if (empty($errors)) {
            return '';
        }

        $output = '';
        foreach ($errors as $error) {
            $output .= $prefix . $error . $suffix;
        }

        return $output;
    }
}
