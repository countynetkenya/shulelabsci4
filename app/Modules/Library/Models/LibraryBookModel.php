<?php

namespace App\Modules\Library\Models;

use CodeIgniter\Model;

/**
 * LibraryBookModel - Manages the library_books table.
 *
 * Columns (from migration):
 * - id, school_id, title, isbn, author, category, total_copies, available_copies, created_at, updated_at
 */
class LibraryBookModel extends Model
{
    protected $table = 'library_books';

    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType = 'array';

    protected $useSoftDeletes = false;

    protected $protectFields = true;

    protected $allowedFields = [
        'school_id',
        'title',
        'isbn',
        'author',
        'category',
        'total_copies',
        'available_copies',
    ];

    protected $useTimestamps = true;

    protected $dateFormat = 'datetime';

    protected $createdField = 'created_at';

    protected $updatedField = 'updated_at';

    // Validation rules
    protected $validationRules = [
        'school_id'    => 'required|integer',
        'title'        => 'required|min_length[2]|max_length[255]',
        'author'       => 'required|max_length[255]',
        'isbn'         => 'permit_empty|max_length[50]',
        'category'     => 'permit_empty|max_length[100]',
        'total_copies' => 'permit_empty|integer|greater_than_equal_to[0]',
        'available_copies' => 'permit_empty|integer|greater_than_equal_to[0]',
    ];

    protected $validationMessages = [
        'title' => [
            'required' => 'Book title is required.',
            'min_length' => 'Book title must be at least 2 characters.',
        ],
        'author' => [
            'required' => 'Author name is required.',
        ],
    ];

    /**
     * Get books by school with optional filters.
     */
    public function getBooksBySchool(int $schoolId, array $filters = []): array
    {
        $builder = $this->where('school_id', $schoolId);

        if (!empty($filters['category'])) {
            $builder->where('category', $filters['category']);
        }

        if (!empty($filters['search'])) {
            $builder->groupStart()
                ->like('title', $filters['search'])
                ->orLike('author', $filters['search'])
                ->orLike('isbn', $filters['search'])
                ->groupEnd();
        }

        if (isset($filters['available_only']) && $filters['available_only']) {
            $builder->where('available_copies >', 0);
        }

        return $builder->orderBy('title', 'ASC')->findAll();
    }

    /**
     * Get book categories for a school.
     */
    public function getCategories(int $schoolId): array
    {
        return $this->select('DISTINCT category as category', false)
            ->where('school_id', $schoolId)
            ->where('category IS NOT NULL')
            ->where('category !=', '')
            ->findAll();
    }

    /**
     * Update available copies count.
     */
    public function updateAvailableCopies(int $bookId, int $change): bool
    {
        $book = $this->find($bookId);
        if (!$book) {
            return false;
        }

        $newCount = max(0, $book['available_copies'] + $change);
        return $this->update($bookId, ['available_copies' => $newCount]);
    }
}
