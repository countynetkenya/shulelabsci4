<?php

namespace Tests\Feature\Library;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Traits\TenantTestTrait;

/**
 * LibraryCrudTest - Feature tests for Library Book CRUD operations.
 *
 * Tests all CRUD endpoints for the Library module:
 * - GET /library (index)
 * - GET /library/create (create form)
 * - POST /library/store (create action)
 * - GET /library/edit/{id} (edit form)
 * - POST /library/update/{id} (update action)
 * - GET /library/delete/{id} (delete action)
 */
class LibraryCrudTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;
    use TenantTestTrait;

    protected $migrate = true;

    protected $migrateOnce = false;

    protected $refresh = true;

    protected $namespace = 'App';

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupTenantContext();
    }

    /**
     * Test: Index page displays books.
     */
    public function testIndexDisplaysBooks()
    {
        // Seed a test book
        $this->db->table('library_books')->insert([
            'school_id'        => $this->schoolId,
            'title'            => 'Test Book Title',
            'author'           => 'Test Author',
            'isbn'             => '978-1-234567-89-0',
            'category'         => 'Fiction',
            'total_copies'     => 3,
            'available_copies' => 2,
            'created_at'       => date('Y-m-d H:i:s'),
            'updated_at'       => date('Y-m-d H:i:s'),
        ]);

        $result = $this->withSession($this->getAdminSession())
                       ->get('library');

        $result->assertOK();
        $result->assertSee('Test Book Title');
        $result->assertSee('Test Author');
        $result->assertSee('978-1-234567-89-0');
    }

    /**
     * Test: Index page shows empty state when no books.
     */
    public function testIndexShowsEmptyState()
    {
        $result = $this->withSession($this->getAdminSession())
                       ->get('library');

        $result->assertOK();
        $result->assertSee('No books found');
    }

    /**
     * Test: Create page displays form.
     */
    public function testCreatePageDisplaysForm()
    {
        $result = $this->withSession($this->getAdminSession())
                       ->get('library/create');

        $result->assertOK();
        $result->assertSee('Add Library Book');
        $result->assertSee('Title');
        $result->assertSee('Author');
    }

    /**
     * Test: Store creates a new book.
     */
    public function testStoreCreatesBook()
    {
        $result = $this->withSession($this->getAdminSession())
                       ->post('library/store', [
                           'title'        => 'New Library Book',
                           'author'       => 'New Author',
                           'isbn'         => '978-0-123456-78-9',
                           'category'     => 'Science',
                           'total_copies' => 5,
                           csrf_token()   => csrf_hash(),
                       ]);

        $result->assertRedirectTo('/library');

        $this->seeInDatabase('library_books', [
            'title'     => 'New Library Book',
            'author'    => 'New Author',
            'school_id' => $this->schoolId,
        ]);
    }

    /**
     * Test: Store validation fails with missing required fields.
     */
    public function testStoreValidationFailsWithMissingFields()
    {
        $result = $this->withSession($this->getAdminSession())
                       ->post('library/store', [
                           'title'      => '', // Empty title
                           'author'     => '', // Empty author
                           csrf_token() => csrf_hash(),
                       ]);

        // Should redirect back with errors
        $result->assertRedirect();
    }

    /**
     * Test: Edit page displays book data.
     */
    public function testEditPageDisplaysBookData()
    {
        // Seed a test book
        $this->db->table('library_books')->insert([
            'school_id'        => $this->schoolId,
            'title'            => 'Book To Edit',
            'author'           => 'Author To Edit',
            'isbn'             => '978-9-876543-21-0',
            'category'         => 'History',
            'total_copies'     => 2,
            'available_copies' => 2,
            'created_at'       => date('Y-m-d H:i:s'),
            'updated_at'       => date('Y-m-d H:i:s'),
        ]);
        $bookId = $this->db->insertID();

        $result = $this->withSession($this->getAdminSession())
                       ->get("library/edit/{$bookId}");

        $result->assertOK();
        $result->assertSee('Edit Library Book');
        $result->assertSee('Book To Edit');
        $result->assertSee('Author To Edit');
    }

    /**
     * Test: Edit page returns 404 for non-existent book.
     */
    public function testEditPageRedirectsForNonExistentBook()
    {
        $result = $this->withSession($this->getAdminSession())
                       ->get('library/edit/99999');

        // Should redirect with error
        $result->assertRedirectTo('/library');
    }

    /**
     * Test: Update modifies existing book.
     */
    public function testUpdateModifiesBook()
    {
        // Seed a test book
        $this->db->table('library_books')->insert([
            'school_id'        => $this->schoolId,
            'title'            => 'Original Title',
            'author'           => 'Original Author',
            'isbn'             => '978-1-111111-11-1',
            'category'         => 'Fiction',
            'total_copies'     => 3,
            'available_copies' => 3,
            'created_at'       => date('Y-m-d H:i:s'),
            'updated_at'       => date('Y-m-d H:i:s'),
        ]);
        $bookId = $this->db->insertID();

        $result = $this->withSession($this->getAdminSession())
                       ->post("library/update/{$bookId}", [
                           'title'           => 'Updated Title',
                           'author'          => 'Updated Author',
                           'isbn'            => '978-2-222222-22-2',
                           'category'        => 'Non-Fiction',
                           'total_copies'    => 5,
                           'available_copies' => 4,
                           csrf_token()      => csrf_hash(),
                       ]);

        $result->assertRedirectTo('/library');

        $this->seeInDatabase('library_books', [
            'id'     => $bookId,
            'title'  => 'Updated Title',
            'author' => 'Updated Author',
        ]);
    }

    /**
     * Test: Delete removes a book.
     */
    public function testDeleteRemovesBook()
    {
        // Seed a test book
        $this->db->table('library_books')->insert([
            'school_id'        => $this->schoolId,
            'title'            => 'Book To Delete',
            'author'           => 'Author Delete',
            'isbn'             => '978-3-333333-33-3',
            'category'         => 'Drama',
            'total_copies'     => 1,
            'available_copies' => 1,
            'created_at'       => date('Y-m-d H:i:s'),
            'updated_at'       => date('Y-m-d H:i:s'),
        ]);
        $bookId = $this->db->insertID();

        // Verify book exists
        $this->seeInDatabase('library_books', ['id' => $bookId]);

        $result = $this->withSession($this->getAdminSession())
                       ->get("library/delete/{$bookId}");

        $result->assertRedirectTo('/library');

        // Verify book is deleted
        $this->dontSeeInDatabase('library_books', ['id' => $bookId]);
    }

    /**
     * Test: Delete non-existent book redirects with error.
     */
    public function testDeleteNonExistentBookRedirects()
    {
        $result = $this->withSession($this->getAdminSession())
                       ->get('library/delete/99999');

        $result->assertRedirectTo('/library');
    }

    /**
     * Test: Tenant scoping - cannot access other school's books.
     */
    public function testCannotAccessOtherSchoolBooks()
    {
        // Create a book for a different school
        $this->db->table('library_books')->insert([
            'school_id'        => 99999, // Different school
            'title'            => 'Other School Book',
            'author'           => 'Other Author',
            'isbn'             => '978-9-999999-99-9',
            'category'         => 'Other',
            'total_copies'     => 1,
            'available_copies' => 1,
            'created_at'       => date('Y-m-d H:i:s'),
            'updated_at'       => date('Y-m-d H:i:s'),
        ]);
        $otherBookId = $this->db->insertID();

        // Try to edit it with our session (different school)
        $result = $this->withSession($this->getAdminSession())
                       ->get("library/edit/{$otherBookId}");

        // Should redirect because book not found for this school
        $result->assertRedirectTo('/library');
    }

    /**
     * Test: Search functionality filters books.
     */
    public function testSearchFiltersBooks()
    {
        // Seed multiple books
        $this->db->table('library_books')->insertBatch([
            [
                'school_id'        => $this->schoolId,
                'title'            => 'Harry Potter',
                'author'           => 'J.K. Rowling',
                'isbn'             => '978-HP-001',
                'category'         => 'Fantasy',
                'total_copies'     => 3,
                'available_copies' => 2,
                'created_at'       => date('Y-m-d H:i:s'),
                'updated_at'       => date('Y-m-d H:i:s'),
            ],
            [
                'school_id'        => $this->schoolId,
                'title'            => 'Lord of the Rings',
                'author'           => 'J.R.R. Tolkien',
                'isbn'             => '978-LOTR-001',
                'category'         => 'Fantasy',
                'total_copies'     => 2,
                'available_copies' => 2,
                'created_at'       => date('Y-m-d H:i:s'),
                'updated_at'       => date('Y-m-d H:i:s'),
            ],
        ]);

        $result = $this->withSession($this->getAdminSession())
                       ->get('library?search=Harry');

        $result->assertOK();
        $result->assertSee('Harry Potter');
        $result->assertDontSee('Lord of the Rings');
    }

    /**
     * Test: Category filter works.
     */
    public function testCategoryFilterWorks()
    {
        // Seed books with different categories
        $this->db->table('library_books')->insertBatch([
            [
                'school_id'        => $this->schoolId,
                'title'            => 'Science Book',
                'author'           => 'Scientist',
                'isbn'             => '978-SCI-001',
                'category'         => 'Science',
                'total_copies'     => 1,
                'available_copies' => 1,
                'created_at'       => date('Y-m-d H:i:s'),
                'updated_at'       => date('Y-m-d H:i:s'),
            ],
            [
                'school_id'        => $this->schoolId,
                'title'            => 'Math Book',
                'author'           => 'Mathematician',
                'isbn'             => '978-MATH-001',
                'category'         => 'Mathematics',
                'total_copies'     => 1,
                'available_copies' => 1,
                'created_at'       => date('Y-m-d H:i:s'),
                'updated_at'       => date('Y-m-d H:i:s'),
            ],
        ]);

        $result = $this->withSession($this->getAdminSession())
                       ->get('library?category=Science');

        $result->assertOK();
        $result->assertSee('Science Book');
        $result->assertDontSee('Math Book');
    }
}
