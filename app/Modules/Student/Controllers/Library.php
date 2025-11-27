<?php

namespace App\Modules\Student\Controllers;

use App\Controllers\BaseController;

/**
 * Library Controller for Student Module.
 *
 * Manages library book browsing and borrowing for students
 */
class Library extends BaseController
{
    protected $userID;

    protected $schoolID;

    public function __construct()
    {
        $this->userID = session()->get('userID');
        $this->schoolID = session()->get('schoolID');
    }

    /**
     * Display library books.
     */
    public function index()
    {
        if (!$this->userID) {
            return redirect()->to('/auth/signin')->with('error', 'Please login first.');
        }

        $db = \Config\Database::connect();

        // Get available books
        $books = $db->table('library_books')
            ->select('library_books.*, 
                     (library_books.total_copies - 
                      (SELECT COUNT(*) FROM library_borrowings 
                       WHERE library_borrowings.book_id = library_books.id 
                       AND library_borrowings.return_date IS NULL)) as available_copies')
            ->orderBy('library_books.title', 'ASC')
            ->get()
            ->getResultArray();

        $data = [
            'title' => 'Library',
            'books' => $books,
            'myBorrowings' => $this->getMyBorrowings(),
        ];

        return view('modules/student/library/index', $data);
    }

    /**
     * Borrow a book.
     */
    public function borrow($bookID)
    {
        if (!$this->userID) {
            return redirect()->to('/auth/signin')->with('error', 'Please login first.');
        }

        $db = \Config\Database::connect();

        // Check if book is available
        $book = $db->table('library_books')->where('id', $bookID)->get()->getRow();

        if (!$book) {
            return redirect()->to('/student/library')->with('error', 'Book not found.');
        }

        // Check current borrowings
        $currentBorrowings = $db->table('library_borrowings')
            ->where('book_id', $bookID)
            ->where('return_date IS NULL')
            ->countAllResults();

        if ($currentBorrowings >= $book->total_copies) {
            return redirect()->to('/student/library')->with('error', 'Book is not available. All copies are borrowed.');
        }

        // Check if student already borrowed this book
        $alreadyBorrowed = $db->table('library_borrowings')
            ->where('book_id', $bookID)
            ->where('student_id', $this->userID)
            ->where('return_date IS NULL')
            ->countAllResults();

        if ($alreadyBorrowed > 0) {
            return redirect()->to('/student/library')->with('error', 'You have already borrowed this book.');
        }

        // Create borrowing record
        $borrowingData = [
            'book_id' => $bookID,
            'student_id' => $this->userID,
            'borrow_date' => date('Y-m-d'),
            'due_date' => date('Y-m-d', strtotime('+14 days')), // 2 weeks borrowing period
            'created_at' => date('Y-m-d H:i:s'),
        ];

        if ($db->table('library_borrowings')->insert($borrowingData)) {
            return redirect()->to('/student/library')->with('success', 'Book borrowed successfully. Please return by ' . date('M d, Y', strtotime('+14 days')));
        }

        return redirect()->to('/student/library')->with('error', 'Failed to borrow book.');
    }

    /**
     * Return a book.
     */
    public function return($borrowingID)
    {
        if (!$this->userID) {
            return redirect()->to('/auth/signin')->with('error', 'Please login first.');
        }

        $db = \Config\Database::connect();

        // Verify borrowing belongs to this student
        $borrowing = $db->table('library_borrowings')
            ->where('id', $borrowingID)
            ->where('student_id', $this->userID)
            ->where('return_date IS NULL')
            ->get()
            ->getRow();

        if (!$borrowing) {
            return redirect()->to('/student/library')->with('error', 'Borrowing record not found.');
        }

        // Mark as returned
        $updateData = [
            'return_date' => date('Y-m-d'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($db->table('library_borrowings')->where('id', $borrowingID)->update($updateData)) {
            return redirect()->to('/student/library')->with('success', 'Book returned successfully.');
        }

        return redirect()->to('/student/library')->with('error', 'Failed to return book.');
    }

    /**
     * View book details.
     */
    public function view($bookID)
    {
        if (!$this->userID) {
            return redirect()->to('/auth/signin')->with('error', 'Please login first.');
        }

        $db = \Config\Database::connect();

        $book = $db->table('library_books')
            ->where('id', $bookID)
            ->get()
            ->getRow();

        if (!$book) {
            return redirect()->to('/student/library')->with('error', 'Book not found.');
        }

        // Get current borrowings
        $currentBorrowings = $db->table('library_borrowings')
            ->where('book_id', $bookID)
            ->where('return_date IS NULL')
            ->countAllResults();

        $data = [
            'title' => $book->title,
            'book' => $book,
            'availableCopies' => $book->total_copies - $currentBorrowings,
        ];

        return view('modules/student/library/view', $data);
    }

    /**
     * Get books borrowed by this student.
     */
    private function getMyBorrowings(): array
    {
        $db = \Config\Database::connect();

        return $db->table('library_borrowings')
            ->select('library_borrowings.*, library_books.title, library_books.author, library_books.isbn')
            ->join('library_books', 'library_borrowings.book_id = library_books.id')
            ->where('library_borrowings.student_id', $this->userID)
            ->where('library_borrowings.return_date IS NULL')
            ->orderBy('library_borrowings.due_date', 'ASC')
            ->get()
            ->getResultArray() ?? [];
    }
}
