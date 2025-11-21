<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('shulelabs_bool_env')) {
    function shulelabs_bool_env($key, $default = false)
    {
        $value = shulelabs_env($key, null);
        if ($value === null) {
            return $default;
        }

        if (is_bool($value)) {
            return $value;
        }

        $filtered = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        return $filtered === null ? $default : $filtered;
    }
}

$jwtSecret = shulelabs_env('JWT_SECRET', 'please-change-me');
$jwtLeeway = (int) shulelabs_env('JWT_LEEWAY_SECONDS', 0);
$config['shulelabs'] = [
    'feature_flags' => [
        'UNIFIED_STATEMENT' => shulelabs_bool_env('FLAG_UNIFIED_STATEMENT', true),
        'AUTO_BILLING_TRANSPORT' => shulelabs_bool_env('FLAG_AUTO_BILLING_TRANSPORT', false),
        'AUTO_BILLING_HOSTEL' => shulelabs_bool_env('FLAG_AUTO_BILLING_HOSTEL', false),
        'PAYROLL_V2' => shulelabs_bool_env('FLAG_PAYROLL_V2', false),
        'PERMISSIONS_V1' => shulelabs_bool_env('FLAG_PERMISSIONS_V1', false),
        'OKR_V1' => shulelabs_bool_env('FLAG_OKR_V1', false),
        'CFR_V1' => shulelabs_bool_env('FLAG_CFR_V1', false),
    ],
    'jwt' => [
        'secret' => $jwtSecret,
        'issuer' => shulelabs_env('JWT_ISSUER', 'shulelabs.local'),
        'ttl' => (int) shulelabs_env('JWT_TTL_SECONDS', 3600),
        'algorithm' => 'HS256',
        'leeway' => $jwtLeeway,
    ],
    'security' => [
        'api_jwt_guard' => [
            'enabled' => shulelabs_bool_env('API_JWT_GUARD_ENABLED', false),
        ],
    ],
    'external_services' => [
        'google_drive' => [
            'service_account_json' => shulelabs_env('GDRIVE_SERVICE_ACCOUNT_JSON', ''),
            'root_folder_id' => shulelabs_env('GDRIVE_ROOT_FOLDER_ID', ''),
        ],
        'mpesa' => [
            'consumer_key' => shulelabs_env('MPESA_CONSUMER_KEY', ''),
            'consumer_secret' => shulelabs_env('MPESA_CONSUMER_SECRET', ''),
            'passkey' => shulelabs_env('MPESA_PASSKEY', ''),
            'shortcode' => shulelabs_env('MPESA_SHORTCODE', ''),
        ],
    ],
];

$config['feature_flags'] = $config['shulelabs']['feature_flags'];
$config['jwt_secret_key'] = $jwtSecret;
$config['jwt_algorithm'] = $config['shulelabs']['jwt']['algorithm'];
$config['jwt_issuer'] = $config['shulelabs']['jwt']['issuer'];
$config['jwt_ttl'] = $config['shulelabs']['jwt']['ttl'];
$config['jwt_leeway'] = $config['shulelabs']['jwt']['leeway'];
