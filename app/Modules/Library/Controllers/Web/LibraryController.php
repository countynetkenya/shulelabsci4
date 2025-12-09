<?php

namespace App\Modules\Library\Controllers\Web;

use App\Controllers\BaseController;
use App\Modules\Library\Services\LibraryService;

/**
 * LibraryController - Handles CRUD operations for library books
 * 
 * All data is tenant-scoped by school_id from session.
 */
class LibraryController extends BaseController
{
    protected LibraryService $service;

    public function __construct()
    {
        $this->service = new LibraryService();
    }

    /**
     * Check if user has permission to access library module
     */
    protected function checkAccess(): bool
    {
        // Allow admins and librarians
        $usertypeID = session()->get('usertypeID');
        $isAdmin = in_array($usertypeID, [0, 1, '0', '1']);
        
        if ($isAdmin) {
            return true;
        }

        // Check library-specific permission if auth service is available
        try {
            $auth = service('auth');
            if ($auth && method_exists($auth, 'can')) {
                return $auth->can('library.view');
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
     * List all books
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
            'books'      => $this->service->getAll($schoolId, array_filter($filters)),
            'categories' => $this->service->getCategories($schoolId),
            'filters'    => $filters,
        ];

        return view('App\Modules\Library\Views\index', $data);
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

        return view('App\Modules\Library\Views\create', $data);
    }

    /**
     * Store a new book
     */
    public function store()
    {
        if (!$this->checkAccess()) {
            return redirect()->to('/login')->with('error', 'Access denied. Please log in.');
        }

        $schoolId = $this->getSchoolId();
        
        // Validation rules
        $rules = [
            'title'           => 'required|min_length[2]|max_length[255]',
            'author'          => 'required|max_length[255]',
            'isbn'            => 'permit_empty|max_length[50]',
            'category'        => 'permit_empty|max_length[100]',
            'total_copies'    => 'permit_empty|integer|greater_than_equal_to[1]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $totalCopies = (int) ($this->request->getPost('total_copies') ?: 1);
        
        $data = [
            'school_id'        => $schoolId,
            'title'            => $this->request->getPost('title'),
            'author'           => $this->request->getPost('author'),
            'isbn'             => $this->request->getPost('isbn') ?: null,
            'category'         => $this->request->getPost('category') ?: null,
            'total_copies'     => $totalCopies,
            'available_copies' => $totalCopies, // New books are all available
        ];

        $result = $this->service->create($data);

        if ($result) {
            return redirect()->to('/library')->with('message', 'Book added successfully!');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to add book. Please try again.');
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
        $book = $this->service->getById($id, $schoolId);
        
        if (!$book) {
            return redirect()->to('/library')->with('error', 'Book not found.');
        }

        $data = [
            'book'       => $book,
            'categories' => $this->service->getCategories($schoolId),
        ];

        return view('App\Modules\Library\Views\edit', $data);
    }

    /**
     * Update an existing book
     */
    public function update(int $id)
    {
        if (!$this->checkAccess()) {
            return redirect()->to('/login')->with('error', 'Access denied. Please log in.');
        }

        $schoolId = $this->getSchoolId();

        // Verify book exists
        $existingBook = $this->service->getById($id, $schoolId);
        if (!$existingBook) {
            return redirect()->to('/library')->with('error', 'Book not found.');
        }

        // Validation rules
        $rules = [
            'title'           => 'required|min_length[2]|max_length[255]',
            'author'          => 'required|max_length[255]',
            'isbn'            => 'permit_empty|max_length[50]',
            'category'        => 'permit_empty|max_length[100]',
            'total_copies'    => 'permit_empty|integer|greater_than_equal_to[1]',
            'available_copies' => 'permit_empty|integer|greater_than_equal_to[0]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $totalCopies = (int) ($this->request->getPost('total_copies') ?: $existingBook['total_copies']);
        $availableCopies = (int) ($this->request->getPost('available_copies') ?? $existingBook['available_copies']);
        
        // Ensure available copies doesn't exceed total
        $availableCopies = min($availableCopies, $totalCopies);

        $data = [
            'title'            => $this->request->getPost('title'),
            'author'           => $this->request->getPost('author'),
            'isbn'             => $this->request->getPost('isbn') ?: null,
            'category'         => $this->request->getPost('category') ?: null,
            'total_copies'     => $totalCopies,
            'available_copies' => $availableCopies,
        ];

        $result = $this->service->update($id, $data, $schoolId);

        if ($result) {
            return redirect()->to('/library')->with('message', 'Book updated successfully!');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to update book. Please try again.');
    }

    /**
     * Delete a book
     */
    public function delete(int $id)
    {
        if (!$this->checkAccess()) {
            return redirect()->to('/login')->with('error', 'Access denied. Please log in.');
        }

        $schoolId = $this->getSchoolId();
        
        // Verify book exists
        $book = $this->service->getById($id, $schoolId);
        if (!$book) {
            return redirect()->to('/library')->with('error', 'Book not found.');
        }

        $result = $this->service->delete($id, $schoolId);

        if ($result) {
            return redirect()->to('/library')->with('message', 'Book deleted successfully!');
        }

        return redirect()->to('/library')->with('error', 'Failed to delete book. Please try again.');
    }
}
