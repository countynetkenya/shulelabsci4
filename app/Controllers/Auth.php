<?php

namespace App\Controllers;

use App\Libraries\HashCompat;
use App\Models\LoginLogModel;
use App\Models\SiteModel;
use App\Models\UserModel;
use App\Services\UserMigrationService;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * Auth Controller.
 *
 * Handles user authentication (signin/signout)
 * Uses unprefixed tables (users, roles, user_roles)
 * Supports automatic migration from CI3 tables during signin
 * Maintains CI3-compatible password hashing via HashCompat
 */
class Auth extends BaseController
{
    protected $userModel;

    protected $siteModel;

    protected $loginLogModel;

    protected $hashCompat;

    protected $userMigrationService;

    protected $data = [];

    public function __construct()
    {
        helper(['compatibility', 'cookie', 'form']);
        $this->userModel = new UserModel();
        $this->siteModel = new SiteModel();
        $this->loginLogModel = new LoginLogModel();
        $this->hashCompat = new HashCompat();
        $this->userMigrationService = new UserMigrationService();
    }

    /**
     * Sign in page.
     *
     * @return string|RedirectResponse
     */
    public function signin()
    {
        $method = $this->request->getMethod();
        log_message('debug', 'Auth::signin() - Method: ' . $method . ' - Signin page accessed');
        log_message('debug', 'Auth::signin() - POST data: ' . json_encode($this->request->getPost()));

        // If already logged in, redirect to dashboard
        if (session()->get('loggedin')) {
            log_message('debug', 'Auth::signin() - User already logged in, redirecting to dashboard');
            return redirect()->to('/dashboard');
        }

        // Get site info (handle missing table gracefully)
        try {
            $this->data['siteinfos'] = $this->siteModel->getSite(0);
        } catch (\Exception $e) {
            log_message('warning', 'Auth::signin() - Could not fetch site info: ' . $e->getMessage());
            $this->data['siteinfos'] = (object) [
                'sname' => 'ShuleLabs',
                'photo' => 'default-logo.png',
                'address' => '',
                'phone' => '',
                'email' => '',
            ];
        }
        $this->data['form_validation'] = 'No';

        if (strtolower($this->request->getMethod()) === 'post') {
            log_message('debug', 'Auth::signin() - POST request detected, processing signin');
            return $this->processSignin();
        }

        // Show signin form
        log_message('debug', 'Auth::signin() - Displaying signin form');
        return view('auth/signin', $this->data);
    }

    /**
     * Process signin form submission.
     *
     * @return string|RedirectResponse
     */
    protected function processSignin()
    {
        $validation = \Config\Services::validation();

        // Validation rules
        $rules = [
            'username' => [
                'rules' => 'required|max_length[40]',
                'errors' => [
                    'required' => 'Username is required',
                    'max_length' => 'Username must not exceed 40 characters',
                ],
            ],
            'password' => [
                'rules' => 'required|max_length[40]',
                'errors' => [
                    'required' => 'Password is required',
                    'max_length' => 'Password must not exceed 40 characters',
                ],
            ],
        ];

        if (!$this->validate($rules)) {
            $errors = $this->validator->getErrors();
            log_message('info', 'Auth::processSignin() - Validation failed: ' . json_encode(array_keys($errors)));
            // Build error message string
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error;
            }
            $this->data['form_validation'] = implode(' ', $errorMessages);
            $this->data['siteinfos'] = $this->siteModel->getSite(0);
            return view('auth/signin', $this->data);
        }

        // Handle remember me cookie
        $this->handleRememberMe();

        // Get credentials
        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        log_message('info', 'Auth::processSignin() - Signin attempt for username: ' . $username);

        // Find user by username (any status to provide detailed feedback)
        $user = $this->userModel->findByUsernameAnyStatus($username);

        // If user not found in users, check CI3 tables and migrate
        if (!$user) {
            log_message('info', 'Auth::processSignin() - User not found in users, checking CI3 tables: ' . $username);
            $user = $this->userMigrationService->findAndMigrateUser($username);

            if (!$user) {
                log_message('info', 'Auth::processSignin() - User not found in any table: ' . $username);
                $this->data['form_validation'] = '<i class="fa fa-user-times"></i> <strong>Username not found.</strong> Please check your username and try again.';
                $this->data['error_type'] = 'username';
                $this->data['siteinfos'] = $this->siteModel->getSite(0);
                return view('auth/signin', $this->data);
            }

            log_message('info', 'Auth::processSignin() - User migrated from CI3, proceeding with authentication: ' . $username);
        }

        log_message('debug', 'Auth::processSignin() - User found: ID=' . $user->id . ', active=' . $user->is_active);

        // Check if user is active
        if (!$user->is_active) {
            log_message('warning', 'Auth::processSignin() - Inactive account signin attempt: ' . $username);
            $this->data['form_validation'] = '<i class="fa fa-ban"></i> <strong>Account Deactivated.</strong> Your account has been deactivated. Please contact the administrator.';
            $this->data['error_type'] = 'inactive';
            $this->data['siteinfos'] = $this->siteModel->getSite(0);
            return view('auth/signin', $this->data);
        }

        // Hash password using CI3 compatible method
        $hashedPassword = $this->hashCompat->hash($password);

        // Verify password - support both bcrypt (new) and SHA512 (CI3 compat)
        $passwordValid = false;

        if (password_verify($password, $user->password_hash)) {
            // Bcrypt hash (new CI4 users)
            $passwordValid = true;
            log_message('debug', 'Auth::processSignin() - Password verified using bcrypt');
        } elseif ($user->password_hash === $hashedPassword) {
            // SHA512 hash (CI3 migrated users)
            $passwordValid = true;
            log_message('debug', 'Auth::processSignin() - Password verified using SHA512 (CI3 compat)');
        }

        if (!$passwordValid) {
            log_message('info', 'Auth::processSignin() - Password mismatch for user: ' . $username);
            $this->data['form_validation'] = '<i class="fa fa-key"></i> <strong>Incorrect Password.</strong> The password you entered is incorrect. Please try again or contact support.';
            $this->data['error_type'] = 'password';
            $this->data['siteinfos'] = $this->siteModel->getSite(0);
            return view('auth/signin', $this->data);
        }

        log_message('info', 'Auth::processSignin() - Authentication successful for user: ' . $username);

        // Add legacy compatibility fields
        $user->userID = $user->id;
        $user->name = $user->full_name;
        $user->active = $user->is_active;
        $user->user_table = $user->ci3_user_table ?? 'users';

        // Get primary role to set usertypeID
        $role = $this->userModel->getUserPrimaryRole($user->id);
        if ($role) {
            $user->usertypeID = $role->ci3_usertype_id;
            log_message('debug', 'Auth::processSignin() - User role: ' . $role->role_name . ' (usertypeID=' . $role->ci3_usertype_id . ')');
        } else {
            log_message('error', 'Auth::processSignin() - No role found for user: ' . $username);
            $this->data['form_validation'] = '<i class="fa fa-exclamation-triangle"></i> <strong>Account Configuration Error.</strong> Your user account is not properly configured. Please contact the administrator.';
            $this->data['error_type'] = 'config';
            $this->data['siteinfos'] = $this->siteModel->getSite(0);
            return view('auth/signin', $this->data);
        }

        // Create login log
        log_message('debug', 'Auth::processSignin() - Creating login log for user: ' . $username);
        $this->createLoginLog($user);

        // Set session data
        log_message('debug', 'Auth::processSignin() - Setting user session for user: ' . $username);
        $this->setUserSession($user);

        // Redirect based on user type and school count
        log_message('debug', 'Auth::processSignin() - Redirecting user after signin: ' . $username);
        return $this->redirectAfterSignin($user);
    }

    /**
     * Handle remember me functionality.
     */
    protected function handleRememberMe(): void
    {
        if ($this->request->getPost('remember')) {
            set_cookie('remember_username', $this->request->getPost('username'), 86400 * 30);
            set_cookie('remember_password', $this->request->getPost('password'), 86400 * 30);
        } else {
            delete_cookie('remember_username');
            delete_cookie('remember_password');
        }
    }

    /**
     * Create login log entry.
     *
     * @param object $user
     */
    protected function createLoginLog(object $user): void
    {
        $loginData = [
            'userID' => $user->userID,
            'usertypeID' => $user->usertypeID,
            'ip' => $this->request->getIPAddress(),
            'browser' => $this->request->getUserAgent()->getBrowser(),
            'login' => time(),
            'logout' => null,
        ];

        $this->loginLogModel->createLoginLog($loginData);
    }

    /**
     * Set user session data.
     *
     * @param object $user
     */
    protected function setUserSession(object $user): void
    {
        // Get role information for usertype
        $role = $this->userModel->getUserPrimaryRole($user->id);

        // Parse schoolID to get list of available schools
        $schoolIDs = !empty($user->schoolID) ? explode(',', $user->schoolID) : [];
        $availableSchoolIDs = array_filter($schoolIDs); // Remove empty values

        $sessionData = [
            'loginuserID' => $user->userID,
            'name' => $user->name,
            'email' => $user->email,
            'usertypeID' => $user->usertypeID,
            'usertype' => $role ? $role->role_name : 'Unknown',
            'username' => $user->username,
            'photo' => $user->photo ?? '',
            'schoolID' => $user->schoolID, // Keep original comma-separated string for compatibility
            'schools' => $user->schoolID, // CI3 compatibility
            'available_school_ids' => $availableSchoolIDs, // Array of available school IDs for multi-school staff
            'loggedin' => true,
            'varifyvaliduser' => true, // CI3 compatibility
            'user_table' => $user->user_table,
        ];

        log_message('debug', 'Auth::setUserSession() - Setting session data: ' . json_encode([
            'loginuserID' => $sessionData['loginuserID'],
            'usertypeID' => $sessionData['usertypeID'],
            'usertype' => $sessionData['usertype'],
            'schools' => $sessionData['schools'],
            'available_school_ids' => $sessionData['available_school_ids'],
        ]));

        session()->set($sessionData);

        log_message('debug', 'Auth::setUserSession() - Session data set successfully');
    }

    /**
     * Redirect user after successful signin.
     *
     * @param object $user
     * @return RedirectResponse
     */
    protected function redirectAfterSignin(object $user): RedirectResponse
    {
        $schoolIDs = !empty($user->schoolID) ? explode(',', $user->schoolID) : [];
        $usertypeID = (int) $user->usertypeID;
        $loginuserID = (int) $user->userID;

        log_message('debug', 'Auth::redirectAfterSignin() - Determining redirect for user: usertypeID=' . $usertypeID . ', loginuserID=' . $loginuserID . ', schools=' . $user->schoolID);

        // Check if user is super admin (usertypeID 0 or role_slug 'super_admin')
        $isSuperAdmin = $usertypeID === 0 || ($usertypeID === 1 && $loginuserID === 1);

        if (!$isSuperAdmin) {
            // Also check role_slug for super_admin
            $isSuperAdmin = $this->userModel->hasRole($user->id, 'super_admin');
        }

        if ($isSuperAdmin) {
            // Set school to 0 for super admin
            session()->set('schoolID', 0);
            return $this->redirectWithDebug('/admin', 'Super admin detected (userID=' . $loginuserID . ')');
        }

        // Admin, accountant, librarian, receptionist (usertypeID 1, 5-8)
        if ($usertypeID === 1 || ($usertypeID >= 5 && $usertypeID <= 8)) {
            $schoolCount = count(array_filter($schoolIDs));

            log_message('debug', 'Auth::redirectAfterSignin() - Admin/staff user with ' . $schoolCount . ' school(s)');

            if ($schoolCount > 1) {
                // Multiple schools - show selection page
                return $this->redirectWithDebug('/school/select', 'Multiple schools detected for staff');
            } elseif ($schoolCount === 0) {
                // No schools - redirect to dashboard (they may need to add schools)
                return $this->redirectWithDebug('/dashboard', 'Admin/staff has no schools assigned');
            } else {
                // Single school - set it and go to dashboard
                $this->setSchoolSession((int) $schoolIDs[0]);
                return $this->redirectWithDebug('/dashboard', 'Admin/staff with single school (' . $schoolIDs[0] . ')');
            }
        }

        // Teachers, students, parents - set first school and redirect to dashboard
        if (!empty($schoolIDs) && !empty($schoolIDs[0])) {
            $this->setSchoolSession((int) $schoolIDs[0]);
        }

        return $this->redirectWithDebug('/dashboard', !empty($schoolIDs[0]) ? 'Regular user with school ' . $schoolIDs[0] : 'Regular user with no school assigned');
    }

    /**
     * Set an informational flash message and log where the user is headed after signin.
     */
    protected function redirectWithDebug(string $path, string $reason): RedirectResponse
    {
        $message = sprintf('Login successful. Redirecting to %s (%s).', $path, $reason);
        log_message('info', 'Auth::redirectWithDebug() - ' . $message);

        session()->setFlashdata('signin_debug', $message);

        return redirect()->to($path);
    }

    /**
     * Set school-specific session data.
     *
     * @param int $schoolID
     */
    protected function setSchoolSession(int $schoolID): void
    {
        log_message('debug', 'Auth::setSchoolSession() - Setting school session for schoolID: ' . $schoolID);

        $siteInfo = $this->siteModel->getSite($schoolID);

        if ($siteInfo) {
            // Only set the active school-specific variables
            // Do NOT overwrite available_school_ids which was set during login
            session()->set([
                'schoolID' => $schoolID, // Active school ID
                'defaultschoolyearID' => $siteInfo->school_year ?? null,
                'lang' => $siteInfo->language ?? 'english',
            ]);
            log_message('debug', 'Auth::setSchoolSession() - School session set successfully: schoolID=' . $schoolID);
        } else {
            log_message('error', 'Auth::setSchoolSession() - Site info not found for schoolID: ' . $schoolID);
        }
    }

    /**
     * Sign out.
     *
     * @return RedirectResponse
     */
    public function signout(): RedirectResponse
    {
        $session = session();

        // Update logout time in login log
        $loginLogData = [
            'userID' => $session->get('loginuserID'),
            'usertypeID' => $session->get('usertypeID'),
            'ip' => $this->request->getIPAddress(),
            'browser' => $this->request->getUserAgent()->getBrowser(),
            'logout' => null,
        ];

        $loginLog = $this->loginLogModel->getSingleLoginLog($loginLogData);

        if ($loginLog) {
            $this->loginLogModel->updateLogout($loginLog->loginlogID, time());
        }

        // Destroy session
        $session->destroy();

        // Redirect to signin
        return redirect()->to('/auth/signin');
    }
}
