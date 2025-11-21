<?php

declare(strict_types=1);

namespace Modules\Foundation\Config;

/**
 * Registrar for Foundation Module Auto-Discovery
 * 
 * This class enables CodeIgniter 4's auto-discovery mechanism to find
 * migrations and other resources in the Foundation module.
 */
class Registrar
{
    /**
     * Returns the migration configuration for this module
     * 
     * @return array
     */
    public static function Migrations(): array
    {
        return [
            'enabled' => true,
        ];
    }
}
