<?php

namespace App\Modules\POS\Controllers\Web;

use App\Controllers\BaseController;
use App\Modules\POS\Services\PosService;

/**
 * PosController - Handles CRUD operations for POS products
 * 
 * All data is tenant-scoped by school_id from session.
 */
class PosController extends BaseController
{
    protected PosService $service;

    public function __construct()
    {
        $this->service = new PosService();
    }

    /**
     * Check if user has permission to access POS module
     */
    protected function checkAccess(): bool
    {
        // Allow admins
        $usertypeID = session()->get('usertypeID');
        $isAdmin = in_array($usertypeID, [0, 1, '0', '1']);
        
        if ($isAdmin) {
            return true;
        }

        // Check POS-specific permission if auth service is available
        try {
            $auth = service('auth');
            if ($auth && method_exists($auth, 'can')) {
                return $auth->can('pos.view');
            }
        } catch (\Throwable $e) {
            // Auth service not available
        }

        // Default: require admin for now
        return $isAdmin;
    }

    /**
     * Get current school ID from session
     */
    protected function getSchoolId(): int
    {
        return (int) (session()->get('school_id') ?? session()->get('schoolID') ?? 1);
    }

    /**
     * List all products
     */
    public function index()
    {
        if (!$this->checkAccess()) {
            return redirect()->to('/login')->with('error', 'Access denied. Please log in.');
        }

        $schoolId = $this->getSchoolId();
        
        // Get filter parameters
        $filters = [
            'search'   => $this->request->getGet('search'),
            'category' => $this->request->getGet('category'),
        ];

        $data = [
            'products'   => $this->service->getAll($schoolId, array_filter($filters)),
            'categories' => $this->service->getCategories($schoolId),
            'filters'    => $filters,
        ];

        return view('App\Modules\POS\Views\index', $data);
    }

    /**
     * Show create form
     */
    public function create()
    {
        if (!$this->checkAccess()) {
            return redirect()->to('/login')->with('error', 'Access denied. Please log in.');
        }

        $schoolId = $this->getSchoolId();
        $data = [
            'categories' => $this->service->getCategories($schoolId),
        ];

        return view('App\Modules\POS\Views\create', $data);
    }

    /**
     * Store a new product
     */
    public function store()
    {
        if (!$this->checkAccess()) {
            return redirect()->to('/login')->with('error', 'Access denied. Please log in.');
        }

        $schoolId = $this->getSchoolId();
        
        // Validation rules
        $rules = [
            'name'        => 'required|min_length[2]|max_length[255]',
            'price'       => 'required|decimal|greater_than[0]',
            'stock'       => 'permit_empty|integer|greater_than_equal_to[0]',
            'sku'         => 'permit_empty|max_length[100]',
            'category'    => 'permit_empty|max_length[100]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'school_id'   => $schoolId,
            'name'        => $this->request->getPost('name'),
            'description' => $this->request->getPost('description') ?: null,
            'price'       => $this->request->getPost('price'),
            'stock'       => (int) ($this->request->getPost('stock') ?: 0),
            'sku'         => $this->request->getPost('sku') ?: null,
            'barcode'     => $this->request->getPost('barcode') ?: null,
            'category'    => $this->request->getPost('category') ?: null,
            'is_active'   => 1,
        ];

        $result = $this->service->create($data);

        if ($result) {
            return redirect()->to('/pos')->with('message', 'Product added successfully!');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to add product. Please try again.');
    }

    /**
     * Show edit form
     */
    public function edit(int $id)
    {
        if (!$this->checkAccess()) {
            return redirect()->to('/login')->with('error', 'Access denied. Please log in.');
        }

        $schoolId = $this->getSchoolId();
        $product = $this->service->getById($id, $schoolId);
        
        if (!$product) {
            return redirect()->to('/pos')->with('error', 'Product not found.');
        }

        $data = [
            'product'    => $product,
            'categories' => $this->service->getCategories($schoolId),
        ];

        return view('App\Modules\POS\Views\edit', $data);
    }

    /**
     * Update an existing product
     */
    public function update(int $id)
    {
        if (!$this->checkAccess()) {
            return redirect()->to('/login')->with('error', 'Access denied. Please log in.');
        }

        $schoolId = $this->getSchoolId();

        // Verify product exists
        $existingProduct = $this->service->getById($id, $schoolId);
        if (!$existingProduct) {
            return redirect()->to('/pos')->with('error', 'Product not found.');
        }

        // Validation rules
        $rules = [
            'name'        => 'required|min_length[2]|max_length[255]',
            'price'       => 'required|decimal|greater_than[0]',
            'stock'       => 'permit_empty|integer|greater_than_equal_to[0]',
            'sku'         => 'permit_empty|max_length[100]',
            'category'    => 'permit_empty|max_length[100]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'name'        => $this->request->getPost('name'),
            'description' => $this->request->getPost('description') ?: null,
            'price'       => $this->request->getPost('price'),
            'stock'       => (int) ($this->request->getPost('stock') ?? $existingProduct['stock']),
            'sku'         => $this->request->getPost('sku') ?: null,
            'barcode'     => $this->request->getPost('barcode') ?: null,
            'category'    => $this->request->getPost('category') ?: null,
        ];

        $result = $this->service->update($id, $data, $schoolId);

        if ($result) {
            return redirect()->to('/pos')->with('message', 'Product updated successfully!');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to update product. Please try again.');
    }

    /**
     * Delete a product
     */
    public function delete(int $id)
    {
        if (!$this->checkAccess()) {
            return redirect()->to('/login')->with('error', 'Access denied. Please log in.');
        }

        $schoolId = $this->getSchoolId();
        
        // Verify product exists
        $product = $this->service->getById($id, $schoolId);
        if (!$product) {
            return redirect()->to('/pos')->with('error', 'Product not found.');
        }

        $result = $this->service->delete($id, $schoolId);

        if ($result) {
            return redirect()->to('/pos')->with('message', 'Product deleted successfully!');
        }

        return redirect()->to('/pos')->with('error', 'Failed to delete product. Please try again.');
    }
}
