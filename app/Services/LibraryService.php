<?php

namespace App\Services;

use App\Models\LibraryBookModel;
use App\Models\LibraryBorrowingModel;

/**
 * LibraryService - Library catalog and borrowing management.
 */
class LibraryService
{
    protected LibraryBookModel $bookModel;

    protected LibraryBorrowingModel $borrowingModel;

    public function __construct()
    {
        $this->bookModel = model(LibraryBookModel::class);
        $this->borrowingModel = model(LibraryBorrowingModel::class);
    }

    /**
     * Get all books in school library.
     */
    public function getSchoolBooks(int $schoolId, ?string $category = null): array
    {
        $builder = $this->bookModel->forSchool($schoolId);

        if ($category) {
            $builder->where('category', $category);
        }

        return $builder->findAll();
    }

    /**
     * Add book to library.
     */
    public function addBook(int $schoolId, string $title, string $isbn, string $author, string $category, int $quantity = 1): array
    {
        $data = [
            'school_id' => $schoolId,
            'title' => $title,
            'isbn' => $isbn,
            'author' => $author,
            'category' => $category,
            'total_copies' => $quantity,
            'available_copies' => $quantity,
        ];

        $bookId = $this->bookModel->insert($data);

        if (!$bookId) {
            return ['success' => false, 'message' => 'Failed to add book'];
        }

        return ['success' => true, 'book_id' => $bookId];
    }

    /**
     * Borrow book.
     */
    public function borrowBook(int $bookId, int $studentId, int $schoolId, string $dueDate): array
    {
        $book = $this->bookModel->find($bookId);

        if (!$book) {
            return ['success' => false, 'message' => 'Book not found'];
        }

        if ($book['available_copies'] <= 0) {
            return ['success' => false, 'message' => 'No copies available'];
        }

        // Create borrowing record
        $borrowingData = [
            'school_id' => $schoolId,
            'book_id' => $bookId,
            'student_id' => $studentId,
            'borrowed_date' => date('Y-m-d H:i:s'),
            'due_date' => $dueDate,
            'status' => 'borrowed',
        ];

        $borrowingId = $this->borrowingModel->insert($borrowingData);

        if (!$borrowingId) {
            return ['success' => false, 'message' => 'Failed to create borrowing record'];
        }

        // Decrease available copies
        $this->bookModel->update($bookId, [
            'available_copies' => $book['available_copies'] - 1,
        ]);

        return ['success' => true, 'borrowing_id' => $borrowingId];
    }

    /**
     * Return book.
     */
    public function returnBook(int $borrowingId): array
    {
        $borrowing = $this->borrowingModel->find($borrowingId);

        if (!$borrowing) {
            return ['success' => false, 'message' => 'Borrowing record not found'];
        }

        if ($borrowing['status'] === 'returned') {
            return ['success' => false, 'message' => 'Book already returned'];
        }

        // Update borrowing record
        $this->borrowingModel->update($borrowingId, [
            'status' => 'returned',
            'returned_date' => date('Y-m-d H:i:s'),
        ]);

        // Increase available copies
        $book = $this->bookModel->find($borrowing['book_id']);
        $this->bookModel->update($borrowing['book_id'], [
            'available_copies' => $book['available_copies'] + 1,
        ]);

        return ['success' => true];
    }

    /**
     * Get student's borrowed books.
     */
    public function getStudentBorrowings(int $studentId, int $schoolId, ?string $status = 'borrowed'): array
    {
        $builder = $this->borrowingModel
            ->forSchool($schoolId)
            ->where('student_id', $studentId);

        if ($status) {
            $builder->where('status', $status);
        }

        return $builder->findAll();
    }

    /**
     * Get overdue books.
     */
    public function getOverdueBooks(int $schoolId): array
    {
        return $this->borrowingModel
            ->forSchool($schoolId)
            ->where('status', 'borrowed')
            ->where('due_date <', date('Y-m-d'))
            ->findAll();
    }

    /**
     * Get library statistics.
     */
    public function getLibraryStats(int $schoolId): array
    {
        $allBooks = $this->bookModel->forSchool($schoolId)->findAll();
        $borrowedBooks = $this->borrowingModel->forSchool($schoolId)->where('status', 'borrowed')->findAll();
        $overdueBooks = $this->getOverdueBooks($schoolId);

        $totalBooks = 0;
        $availableBooks = 0;

        foreach ($allBooks as $book) {
            $totalBooks += $book['total_copies'];
            $availableBooks += $book['available_copies'];
        }

        return [
            'total_titles' => count($allBooks),
            'total_copies' => $totalBooks,
            'available_copies' => $availableBooks,
            'borrowed_count' => count($borrowedBooks),
            'overdue_count' => count($overdueBooks),
        ];
    }

    /**
     * Search books.
     */
    public function searchBooks(int $schoolId, string $query): array
    {
        return $this->bookModel
            ->forSchool($schoolId)
            ->groupStart()
                ->like('title', $query)
                ->orLike('author', $query)
                ->orLike('isbn', $query)
            ->groupEnd()
            ->findAll();
    }
}
