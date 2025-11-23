<?php

namespace Tests\Library;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use App\Services\LibraryService;

/**
 * @internal
 */
final class LibraryServiceTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $refresh = false;
    protected LibraryService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new LibraryService();
    }

    public function testAddBook(): void
    {
        $result = $this->service->addBook(6, 'The Great Gatsby', '978-0-7432-7356-5', 'F. Scott Fitzgerald', 'Fiction', 3);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('book_id', $result);

        // Verify book
        $book = model('App\Models\LibraryBookModel')->find($result['book_id']);
        $this->assertEquals('The Great Gatsby', $book['title']);
        $this->assertEquals(3, $book['total_copies']);
        $this->assertEquals(3, $book['available_copies']);
    }

    public function testGetSchoolBooks(): void
    {
        // Add books first
        $this->service->addBook(6, 'Book 1', 'ISBN1', 'Author 1', 'Science', 2);
        $this->service->addBook(6, 'Book 2', 'ISBN2', 'Author 2', 'Math', 1);

        $books = $this->service->getSchoolBooks(6);

        $this->assertIsArray($books);
        $this->assertGreaterThan(0, count($books));
    }

    public function testGetSchoolBooksByCategory(): void
    {
        $this->service->addBook(6, 'Physics Book', 'ISBN3', 'Author 3', 'Science', 1);
        $this->service->addBook(6, 'Math Book', 'ISBN4', 'Author 4', 'Math', 1);

        $scienceBooks = $this->service->getSchoolBooks(6, 'Science');

        $this->assertIsArray($scienceBooks);
        foreach ($scienceBooks as $book) {
            $this->assertEquals('Science', $book['category']);
        }
    }

    public function testBorrowBook(): void
    {
        // Add book first
        $bookResult = $this->service->addBook(6, '1984', 'ISBN5', 'George Orwell', 'Fiction', 2);
        $bookId = $bookResult['book_id'];

        // Borrow book
        $result = $this->service->borrowBook($bookId, 50, 6, '2025-12-31');

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('borrowing_id', $result);

        // Verify book availability decreased
        $book = model('App\Models\LibraryBookModel')->find($bookId);
        $this->assertEquals(1, $book['available_copies']);
    }

    public function testCannotBorrowWhenNoCopiesAvailable(): void
    {
        // Add book with 1 copy
        $bookResult = $this->service->addBook(6, 'Limited Book', 'ISBN6', 'Author 5', 'Fiction', 1);
        $bookId = $bookResult['book_id'];

        // Borrow the only copy
        $this->service->borrowBook($bookId, 50, 6, '2025-12-31');

        // Try to borrow again
        $result = $this->service->borrowBook($bookId, 51, 6, '2025-12-31');

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('No copies available', $result['message']);
    }

    public function testReturnBook(): void
    {
        // Add and borrow book
        $bookResult = $this->service->addBook(6, 'Harry Potter', 'ISBN7', 'J.K. Rowling', 'Fantasy', 2);
        $bookId = $bookResult['book_id'];
        $borrowResult = $this->service->borrowBook($bookId, 52, 6, '2025-12-31');
        $borrowingId = $borrowResult['borrowing_id'];

        // Return book
        $result = $this->service->returnBook($borrowingId);

        $this->assertTrue($result['success']);

        // Verify book availability increased
        $book = model('App\Models\LibraryBookModel')->find($bookId);
        $this->assertEquals(2, $book['available_copies']);

        // Verify borrowing status
        $borrowing = model('App\Models\LibraryBorrowingModel')->find($borrowingId);
        $this->assertEquals('returned', $borrowing['status']);
    }

    public function testCannotReturnBookTwice(): void
    {
        // Add, borrow, and return book
        $bookResult = $this->service->addBook(6, 'Book X', 'ISBN8', 'Author X', 'Fiction', 1);
        $borrowResult = $this->service->borrowBook($bookResult['book_id'], 53, 6, '2025-12-31');
        $this->service->returnBook($borrowResult['borrowing_id']);

        // Try to return again
        $result = $this->service->returnBook($borrowResult['borrowing_id']);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('already returned', $result['message']);
    }

    public function testGetStudentBorrowings(): void
    {
        // Add books and borrow
        $book1 = $this->service->addBook(6, 'Book A', 'ISBN9', 'Author A', 'Fiction', 1);
        $book2 = $this->service->addBook(6, 'Book B', 'ISBN10', 'Author B', 'Science', 1);

        $this->service->borrowBook($book1['book_id'], 54, 6, '2025-12-31');
        $this->service->borrowBook($book2['book_id'], 54, 6, '2025-12-31');

        $borrowings = $this->service->getStudentBorrowings(54, 6);

        $this->assertIsArray($borrowings);
        $this->assertCount(2, $borrowings);
    }

    public function testGetOverdueBooks(): void
    {
        // Add book and create overdue borrowing
        $bookResult = $this->service->addBook(6, 'Overdue Book', 'ISBN11', 'Author Y', 'Fiction', 1);
        $this->service->borrowBook($bookResult['book_id'], 55, 6, '2025-01-01'); // Past due date

        $overdue = $this->service->getOverdueBooks(6);

        $this->assertIsArray($overdue);
        $this->assertGreaterThan(0, count($overdue));
    }

    public function testGetLibraryStats(): void
    {
        // Add multiple books
        $this->service->addBook(6, 'Stats Book 1', 'ISBN12', 'Author S1', 'Fiction', 3);
        $this->service->addBook(6, 'Stats Book 2', 'ISBN13', 'Author S2', 'Science', 2);

        $stats = $this->service->getLibraryStats(6);

        $this->assertIsArray($stats);
        $this->assertArrayHasKey('total_titles', $stats);
        $this->assertArrayHasKey('total_copies', $stats);
        $this->assertArrayHasKey('available_copies', $stats);
        $this->assertArrayHasKey('borrowed_count', $stats);
        $this->assertGreaterThan(0, $stats['total_titles']);
    }

    public function testSearchBooks(): void
    {
        // Add books with searchable content
        $this->service->addBook(6, 'JavaScript Programming', 'ISBN14', 'John Doe', 'Programming', 1);
        $this->service->addBook(6, 'Python Basics', 'ISBN15', 'Jane Smith', 'Programming', 1);

        $results = $this->service->searchBooks(6, 'JavaScript');

        $this->assertIsArray($results);
        $this->assertGreaterThan(0, count($results));
        
        foreach ($results as $book) {
            $this->assertStringContainsStringIgnoringCase('JavaScript', $book['title']);
        }
    }
}
