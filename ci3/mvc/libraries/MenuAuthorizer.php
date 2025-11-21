<?php defined('BASEPATH') OR exit('No direct script access allowed');

class MenuAuthorizer
{
    /** @var CI_Controller */
    protected $CI;

    public function __construct()
    {
        $this->CI = get_instance();

        if (!function_exists('is_super_admin')) {
            $helperPath = APPPATH . 'helpers/user_helper.php';
            if (is_file($helperPath)) {
                require_once $helperPath;
            }
        }

        if (isset($this->CI->load) && is_object($this->CI->load) && method_exists($this->CI->load, 'helper')) {
            $this->CI->load->helper('user');
        }
    }

    /**
     * Filter menu items against feature flags, permissions, and optional super admin requirement.
     *
     * @param array $items
     * @return array
     */
    public function filter(array $items)
    {
        $filtered = [];
        foreach ($items as $key => $item) {
            if (!is_array($item)) {
                continue;
            }

            if (!$this->featureFlagAllows($item)) {
                continue;
            }

            if (!$this->permissionAllows($item)) {
                continue;
            }

            if (!$this->superadminAllows($item)) {
                continue;
            }

            $filtered[$key] = $item;
        }

        return $filtered;
    }

    /**
     * @param array $item
     * @return bool
     */
    protected function featureFlagAllows(array $item)
    {
        if (empty($item['feature_flag'])) {
            return true;
        }

        if (!function_exists('feature_flag_enabled')) {
            return true;
        }

        return feature_flag_enabled($item['feature_flag']);
    }

    /**
     * @param array $item
     * @return bool
     */
    protected function permissionAllows(array $item)
    {
        if (!empty($item['skip_permission'])) {
            return true;
        }

        if ($this->isSuperAdmin()) {
            return true;
        }

        $permissionKey = isset($item['permission']) ? $item['permission'] : (isset($item['permission_key']) ? $item['permission_key'] : null);
        if ($permissionKey === null || $permissionKey === '' || !function_exists('permissionChecker')) {
            return true;
        }

        $candidates = [$permissionKey];

        if (strpos($permissionKey, '.') !== false) {
            $candidates[] = str_replace('.', '_', $permissionKey);
        }

        if (strpos($permissionKey, '_') !== false) {
            $candidates[] = str_replace('_', '.', $permissionKey);
        }

        $candidates = array_unique(array_filter($candidates, function ($candidate) {
            return $candidate !== '' && $candidate !== null;
        }));

        foreach ($candidates as $candidate) {
            if (permissionChecker($candidate)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array $item
     * @return bool
     */
    protected function superadminAllows(array $item)
    {
        if (empty($item['superadmin_only'])) {
            return true;
        }

        return $this->isSuperAdmin();
    }

    /**
     * @return bool
     */
    protected function isSuperAdmin()
    {
        if (function_exists('is_super_admin')) {
            return is_super_admin($this->CI->session);
        }

        $usertypeID = (int) $this->CI->session->userdata('usertypeID');
        $loginUserID = (int) $this->CI->session->userdata('loginuserID');

        return $usertypeID === 0 || ($usertypeID === 1 && $loginUserID === 1);
    }
}
