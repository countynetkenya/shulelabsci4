<?php

namespace App\Controllers;

use App\Models\GradeModel;
use App\Models\InvoiceModel;
use App\Models\RoleModel;
use App\Models\SchoolClassModel;
use App\Models\SchoolModel;
use App\Models\StudentEnrollmentModel;
use App\Models\UserModel;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * Admin Controller.
 *
 * Admin panel for system administration
 */
class Admin extends BaseController
{
    protected $data = [];

    protected UserModel $userModel;

    protected RoleModel $roleModel;

    protected SchoolModel $schoolModel;

    public function __construct()
    {
        helper(['compatibility', 'form']);
        $this->userModel = new UserModel();
        $this->roleModel = new RoleModel();
        $this->schoolModel = new SchoolModel();
    }

    /**
     * Admin panel index/dashboard.
     */
    public function index(): string
    {
        $session = session();

        // Get admin info
        $this->data['user'] = [
            'name' => $session->get('name'),
            'email' => $session->get('email'),
            'usertypeID' => $session->get('usertypeID'),
            'photo' => $session->get('photo'),
        ];

        // Dashboard statistics
        $this->data['stats'] = [
            'total_users' => $this->userModel->where('is_active', 1)->countAllResults(),
            'total_schools' => $this->schoolModel->countAllResults(),
            'active_students' => $this->getUserCountByRole('student'),
            'active_teachers' => $this->getUserCountByRole('teacher'),
        ];

        return view('admin/index', $this->data);
    }

    /**
     * User management - List all users.
     */
    public function users(): string
    {
        $this->data['users'] = $this->userModel->getUsersWithRoles();
        $this->data['roles'] = $this->roleModel->findAll();
        $this->data['user'] = $this->getUserData();

        return view('admin/users', $this->data);
    }

    /**
     * Create new user.
     */
    public function createUser()
    {
        if ($this->request->getMethod() !== 'post') {
            return redirect()->to('/admin/users');
        }

        $validation = \Config\Services::validation();
        $validation->setRules([
            'username' => 'required|min_length[3]|is_unique[users.username]',
            'email' => 'required|valid_email|is_unique[users.email]',
            'full_name' => 'required|min_length[3]',
            'password' => 'required|min_length[8]',
            'role_id' => 'required|integer',
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $data = [
            'username' => $this->request->getPost('username'),
            'email' => $this->request->getPost('email'),
            'full_name' => $this->request->getPost('full_name'),
            'password_hash' => password_hash($this->request->getPost('password'), PASSWORD_BCRYPT),
            'schoolID' => $this->request->getPost('schoolID'),
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $userId = $this->userModel->insert($data);

        if ($userId) {
            // Assign role
            $roleId = $this->request->getPost('role_id');
            db_connect()->table('user_roles')->insert([
                'user_id' => $userId,
                'role_id' => $roleId,
            ]);

            return redirect()->to('/admin/users')->with('success', 'User created successfully');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to create user');
    }

    /**
     * Update user.
     */
    public function updateUser(int $id)
    {
        if ($this->request->getMethod() !== 'post') {
            return redirect()->to('/admin/users');
        }

        $user = $this->userModel->find($id);
        if (!$user) {
            return redirect()->to('/admin/users')->with('error', 'User not found');
        }

        $data = [
            'full_name' => $this->request->getPost('full_name'),
            'email' => $this->request->getPost('email'),
            'is_active' => $this->request->getPost('is_active', FILTER_VALIDATE_INT) ?? 1,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        // Update password if provided
        $password = $this->request->getPost('password');
        if (!empty($password)) {
            $data['password_hash'] = password_hash($password, PASSWORD_BCRYPT);
        }

        if ($this->userModel->update($id, $data)) {
            return redirect()->to('/admin/users')->with('success', 'User updated successfully');
        }

        return redirect()->back()->with('error', 'Failed to update user');
    }

    /**
     * Delete user.
     */
    public function deleteUser(int $id): RedirectResponse
    {
        $user = $this->userModel->find($id);
        if (!$user) {
            return redirect()->to('/admin/users')->with('error', 'User not found');
        }

        // Soft delete - set is_active to 0
        if ($this->userModel->update($id, ['is_active' => 0, 'updated_at' => date('Y-m-d H:i:s')])) {
            return redirect()->to('/admin/users')->with('success', 'User deactivated successfully');
        }

        return redirect()->to('/admin/users')->with('error', 'Failed to deactivate user');
    }

    /**
     * School management.
     */
    public function schools(): string
    {
        $this->data['schools'] = $this->schoolModel->findAll();
        $this->data['user'] = $this->getUserData();

        return view('admin/schools', $this->data);
    }

    /**
     * Update school settings.
     */
    public function updateSchool()
    {
        if ($this->request->getMethod() !== 'post') {
            return redirect()->to('/admin/schools');
        }

        $id = $this->request->getPost('school_id');
        $school = $this->schoolModel->find($id);

        if (!$school) {
            return redirect()->to('/admin/schools')->with('error', 'School not found');
        }

        $data = [
            'school_name' => $this->request->getPost('school_name'),
            'school_code' => $this->request->getPost('school_code'),
            'address' => $this->request->getPost('address'),
            'phone' => $this->request->getPost('phone'),
            'email' => $this->request->getPost('email'),
        ];

        if ($this->schoolModel->update($id, $data)) {
            return redirect()->to('/admin/schools')->with('success', 'School updated successfully');
        }

        return redirect()->back()->with('error', 'Failed to update school');
    }

    /**
     * System settings.
     */
    public function settings(): string
    {
        $settingModel = db_connect()->table('setting');
        $this->data['settings'] = $settingModel->get()->getResultArray();
        $this->data['user'] = $this->getUserData();

        return view('admin/settings', $this->data);
    }

    /**
     * Update system settings.
     */
    public function updateSettings()
    {
        if ($this->request->getMethod() !== 'post') {
            return redirect()->to('/admin/settings');
        }

        $settingModel = db_connect()->table('setting');
        $settings = $this->request->getPost('settings');

        foreach ($settings as $key => $value) {
            $settingModel->where('field_key', $key)->update(['field_value' => $value]);
        }

        return redirect()->to('/admin/settings')->with('success', 'Settings updated successfully');
    }

    /**
     * Reports - Attendance, grades, enrollment.
     */
    public function reports(): string
    {
        $this->data['user'] = $this->getUserData();

        // Get summary data for reports
        $enrollmentModel = new StudentEnrollmentModel();
        $gradeModel = new GradeModel();

        $this->data['report_data'] = [
            'total_enrollments' => $enrollmentModel->countAllResults(),
            'recent_grades' => $gradeModel->orderBy('created_at', 'DESC')->limit(10)->find(),
            'enrollment_by_class' => $this->getEnrollmentByClass(),
        ];

        return view('admin/reports', $this->data);
    }

    /**
     * Finance - View invoices and payments.
     */
    public function finance(): string
    {
        $this->data['user'] = $this->getUserData();

        $invoiceModel = new InvoiceModel();
        $this->data['invoices'] = $invoiceModel->orderBy('created_at', 'DESC')->limit(50)->find();
        $this->data['finance_summary'] = [
            'total_invoices' => $invoiceModel->countAllResults(),
            'total_amount' => $invoiceModel->selectSum('total_amount')->first()['total_amount'] ?? 0,
            'paid_amount' => $invoiceModel->where('status', 'paid')->selectSum('total_amount')->first()['total_amount'] ?? 0,
        ];

        return view('admin/finance', $this->data);
    }

    // Helper methods

    private function getUserData(): array
    {
        $session = session();
        return [
            'name' => $session->get('name'),
            'email' => $session->get('email'),
            'usertypeID' => $session->get('usertypeID'),
            'photo' => $session->get('photo'),
        ];
    }

    private function getUserCountByRole(string $roleSlug): int
    {
        $role = $this->roleModel->where('role_slug', $roleSlug)->first();
        if (!$role) {
            return 0;
        }

        return db_connect()->table('user_roles')
            ->where('role_id', $role['id'])
            ->countAllResults();
    }

    private function getEnrollmentByClass(): array
    {
        $classModel = new SchoolClassModel();
        $enrollmentModel = new StudentEnrollmentModel();

        $classes = $classModel->findAll();
        $result = [];

        foreach ($classes as $class) {
            $result[] = [
                'class_name' => $class['class_name'] ?? 'Unknown',
                'student_count' => $enrollmentModel->where('class_id', $class['classesID'] ?? $class['id'])->countAllResults(),
            ];
        }

        return $result;
    }
}
