<?php

namespace App\Modules\Library\Services;

use App\Modules\Library\Models\LibraryBookModel;
use Modules\Foundation\Services\AuditService;
use CodeIgniter\HTTP\RequestInterface;

/**
 * LibraryService - Business logic for library book management
 * 
 * All queries are tenant-scoped by school_id.
 * Integrates with AuditService for logging critical actions.
 */
class LibraryService
{
    protected LibraryBookModel $model;
    protected ?AuditService $auditService = null;

    public function __construct(?AuditService $auditService = null)
    {
        $this->model = new LibraryBookModel();
        
        // Try to inject AuditService
        try {
            $this->auditService = $auditService ?? new AuditService();
        } catch (\Throwable $e) {
            // AuditService not available, continue without it
            log_message('debug', 'AuditService not available: ' . $e->getMessage());
        }
    }

    /**
     * Get all books for a school
     */
    public function getAll(int $schoolId, array $filters = []): array
    {
        return $this->model->getBooksBySchool($schoolId, $filters);
    }

    /**
     * Get a single book by ID (scoped to school)
     */
    public function getById(int $id, int $schoolId): ?array
    {
        $book = $this->model
            ->where('school_id', $schoolId)
            ->where('id', $id)
            ->first();
        
        return $book ?: null;
    }

    /**
     * Create a new book
     */
    public function create(array $data): int|false
    {
        // Set defaults for copies if not provided
        if (!isset($data['total_copies'])) {
            $data['total_copies'] = 1;
        }
        if (!isset($data['available_copies'])) {
            $data['available_copies'] = $data['total_copies'];
        }

        $result = $this->model->insert($data);

        if ($result && $this->auditService) {
            try {
                $this->auditService->recordEvent(
                    'library.book.created',
                    'create',
                    [
                        'school_id' => $data['school_id'] ?? null,
                        'actor_id'  => session()->get('user_id'),
                    ],
                    null,
                    $data,
                    $this->getRequestMetadata()
                );
            } catch (\Throwable $e) {
                log_message('warning', 'Audit log failed: ' . $e->getMessage());
            }
        }

        return $result;
    }

    /**
     * Update an existing book
     */
    public function update(int $id, array $data, int $schoolId): bool
    {
        // Get before state for audit
        $before = $this->getById($id, $schoolId);
        
        if (!$before) {
            return false;
        }

        $result = $this->model
            ->where('school_id', $schoolId)
            ->update($id, $data);

        if ($result && $this->auditService) {
            try {
                $this->auditService->recordEvent(
                    'library.book.updated',
                    'update',
                    [
                        'school_id' => $schoolId,
                        'actor_id'  => session()->get('user_id'),
                    ],
                    $before,
                    array_merge($before, $data),
                    $this->getRequestMetadata()
                );
            } catch (\Throwable $e) {
                log_message('warning', 'Audit log failed: ' . $e->getMessage());
            }
        }

        return $result;
    }

    /**
     * Delete a book
     */
    public function delete(int $id, int $schoolId): bool
    {
        // Get before state for audit
        $before = $this->getById($id, $schoolId);
        
        if (!$before) {
            return false;
        }

        $result = $this->model
            ->where('school_id', $schoolId)
            ->delete($id);

        if ($result && $this->auditService) {
            try {
                $this->auditService->recordEvent(
                    'library.book.deleted',
                    'delete',
                    [
                        'school_id' => $schoolId,
                        'actor_id'  => session()->get('user_id'),
                    ],
                    $before,
                    null,
                    $this->getRequestMetadata()
                );
            } catch (\Throwable $e) {
                log_message('warning', 'Audit log failed: ' . $e->getMessage());
            }
        }

        return $result;
    }

    /**
     * Get all categories for a school
     */
    public function getCategories(int $schoolId): array
    {
        return $this->model->getCategories($schoolId);
    }

    /**
     * Search books by title, author, or ISBN
     */
    public function search(int $schoolId, string $query): array
    {
        return $this->model->getBooksBySchool($schoolId, ['search' => $query]);
    }

    /**
     * Get available books only
     */
    public function getAvailable(int $schoolId): array
    {
        return $this->model->getBooksBySchool($schoolId, ['available_only' => true]);
    }

    /**
     * Borrow a book (decrease available copies)
     */
    public function borrowBook(int $bookId, int $schoolId): bool
    {
        $book = $this->getById($bookId, $schoolId);
        
        if (!$book || $book['available_copies'] <= 0) {
            return false;
        }

        return $this->model->updateAvailableCopies($bookId, -1);
    }

    /**
     * Return a book (increase available copies)
     */
    public function returnBook(int $bookId, int $schoolId): bool
    {
        $book = $this->getById($bookId, $schoolId);
        
        if (!$book || $book['available_copies'] >= $book['total_copies']) {
            return false;
        }

        return $this->model->updateAvailableCopies($bookId, 1);
    }

    /**
     * Get request metadata for audit logging
     */
    protected function getRequestMetadata(): array
    {
        $request = service('request');
        
        return [
            'ip'          => $request->getIPAddress(),
            'user_agent'  => $request->getUserAgent()->getAgentString(),
            'request_uri' => current_url(),
        ];
    }
}
