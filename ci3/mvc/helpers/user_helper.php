<?php defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('is_super_admin')) {
    /**
     * Determine whether the provided session context belongs to a super admin.
     *
     * @param array|object|null $sessionContext Optional session array/object. Falls back to CI session.
     * @return bool
     */
    function is_super_admin($sessionContext = null)
    {
        $usertypeID = 0;
        $loginUserID = 0;

        if ($sessionContext === null) {
            if (function_exists('get_instance')) {
                $CI = get_instance();
                if (is_object($CI) && isset($CI->session) && method_exists($CI->session, 'userdata')) {
                    $usertypeID = (int) $CI->session->userdata('usertypeID');
                    $loginUserID = (int) $CI->session->userdata('loginuserID');
                }
            }
        } elseif (is_array($sessionContext)) {
            $usertypeID = isset($sessionContext['usertypeID']) ? (int) $sessionContext['usertypeID'] : 0;
            $loginUserID = isset($sessionContext['loginuserID']) ? (int) $sessionContext['loginuserID'] : 0;
        } elseif (is_object($sessionContext)) {
            if (method_exists($sessionContext, 'userdata')) {
                $usertypeID = (int) $sessionContext->userdata('usertypeID');
                $loginUserID = (int) $sessionContext->userdata('loginuserID');
            } else {
                $usertypeID = isset($sessionContext->usertypeID) ? (int) $sessionContext->usertypeID : 0;
                $loginUserID = isset($sessionContext->loginuserID) ? (int) $sessionContext->loginuserID : 0;
            }
        }

        return $usertypeID === 0 || ($usertypeID === 1 && $loginUserID === 1);
    }
}
