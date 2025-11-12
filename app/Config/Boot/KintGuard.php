<?php

declare(strict_types=1);

if (! function_exists('kint_guard_detect_init_file')) {
    /**
     * Attempts to locate the bundled Kint initialization script shipped with CodeIgniter.
     */
    function kint_guard_detect_init_file(): ?string
    {
        $candidates = [
            __DIR__ . '/../../vendor/codeigniter4/framework/system/ThirdParty/Kint/init.php',
            __DIR__ . '/../../../vendor/codeigniter4/framework/system/ThirdParty/Kint/init.php',
            dirname(__DIR__, 4) . '/vendor/codeigniter4/framework/system/ThirdParty/Kint/init.php',
        ];

        foreach ($candidates as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }
}

if (! function_exists('kint_guard_register_fallback')) {
    /**
     * Loads the lightweight fallback helpers that mimic the public Kint API.
     */
    function kint_guard_register_fallback(): void
    {
        static $registered = false;

        if ($registered) {
            return;
        }

        require_once __DIR__ . '/../KintFallback.php';

        if (class_exists('KintFallback')) {
            KintFallback::notifyMissingLibrary();
        }

        $registered = true;
    }
}
