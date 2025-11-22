<?php

// PREVENT .env LOADING FOR CI3
// CI4 handles all environment configuration
// CI3 is deprecated and should not read from .env

if (!function_exists('shulelabs_bootstrap_env')) {
    /**
     * @return array<string, string>
     * @deprecated CI3 environment loading is disabled. Use CI4 instead.
     */
    function shulelabs_bootstrap_env(string $basePath): array
    {
        trigger_error(
            'CI3 environment configuration attempted. '
            . 'This system has migrated to CodeIgniter 4. '
            . 'CI3 code paths should not be executed.',
            E_USER_ERROR
        );
        return [];
    }
}

if (!function_exists('shulelabs_env')) {
    /**
     * @deprecated CI3 environment loading is disabled. Use CI4 instead.
     */
    function shulelabs_env(string $key, mixed $default = null): mixed
    {
        trigger_error(
            'CI3 environment configuration attempted. '
            . 'This system has migrated to CodeIgniter 4. '
            . 'CI3 code paths should not be executed.',
            E_USER_ERROR
        );
        return $default;
    }
}
