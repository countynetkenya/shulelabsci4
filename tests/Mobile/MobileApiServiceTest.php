<?php

namespace Tests\Mobile;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use App\Services\MobileApiService;

/**
 * @internal
 */
final class MobileApiServiceTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $refresh = false;
    protected MobileApiService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new MobileApiService();
    }

    public function testFormatResponse(): void
    {
        $response = $this->service->formatResponse(true, ['key' => 'value'], 'Success', 200);

        $this->assertIsArray($response);
        $this->assertTrue($response['success']);
        $this->assertEquals('Success', $response['message']);
        $this->assertEquals(['key' => 'value'], $response['data']);
        $this->assertEquals(200, $response['status_code']);
        $this->assertArrayHasKey('timestamp', $response);
    }

    public function testFormatPagination(): void
    {
        $pagination = $this->service->formatPagination(100, 2, 20);

        $this->assertIsArray($pagination);
        $this->assertEquals(100, $pagination['total']);
        $this->assertEquals(2, $pagination['page']);
        $this->assertEquals(20, $pagination['per_page']);
        $this->assertEquals(5, $pagination['total_pages']);
        $this->assertTrue($pagination['has_more']);
    }

    public function testGetDashboard(): void
    {
        $response = $this->service->getDashboard(6);

        $this->assertIsArray($response);
        $this->assertTrue($response['success']);
        $this->assertArrayHasKey('data', $response);
        $this->assertArrayHasKey('statistics', $response['data']);
        $this->assertArrayHasKey('quick_actions', $response['data']);
    }

    public function testGetStudentProfile(): void
    {
        $response = $this->service->getStudentProfile(50, 6);

        $this->assertIsArray($response);
        $this->assertArrayHasKey('success', $response);
        
        if ($response['success']) {
            $this->assertArrayHasKey('data', $response);
            $this->assertArrayHasKey('student_id', $response['data']);
        }
    }

    public function testGetClassStudents(): void
    {
        $response = $this->service->getClassStudents(1, 6, 1, 10);

        $this->assertIsArray($response);
        $this->assertTrue($response['success']);
        $this->assertArrayHasKey('data', $response);
        $this->assertArrayHasKey('students', $response['data']);
        $this->assertArrayHasKey('pagination', $response['data']);
    }

    public function testGetInvoices(): void
    {
        // Create test invoice first
        $financeService = new \App\Services\FinanceService();
        $financeService->createInvoice(50, 6, [['name' => 'Fee', 'amount' => 10000]], '2025-12-31');

        $response = $this->service->getInvoices(6, null, 1, 10);

        $this->assertIsArray($response);
        $this->assertTrue($response['success']);
        $this->assertArrayHasKey('invoices', $response['data']);
        $this->assertArrayHasKey('pagination', $response['data']);
    }

    public function testGetInvoicesWithStatus(): void
    {
        $response = $this->service->getInvoices(6, 'pending', 1, 10);

        $this->assertIsArray($response);
        $this->assertTrue($response['success']);
        $this->assertArrayHasKey('invoices', $response['data']);
    }

    public function testGetLibraryBooks(): void
    {
        // Add test book
        $libraryService = new \App\Services\LibraryService();
        $libraryService->addBook(6, 'Mobile Test Book', 'ISBN-MOB-001', 'Author Mobile', 'Fiction', 5);

        $response = $this->service->getLibraryBooks(6, null, 1, 10);

        $this->assertIsArray($response);
        $this->assertTrue($response['success']);
        $this->assertArrayHasKey('books', $response['data']);
        $this->assertArrayHasKey('pagination', $response['data']);
    }

    public function testGetCourses(): void
    {
        // Create test course
        $learningService = new \App\Services\LearningService();
        $learningService->createCourse(6, 1, 'Mobile Test Course', 'MOB101', 25);

        $response = $this->service->getCourses(6);

        $this->assertIsArray($response);
        $this->assertTrue($response['success']);
        $this->assertArrayHasKey('courses', $response['data']);
    }

    public function testGetStudentGrades(): void
    {
        // Create course, assignment, and grade
        $learningService = new \App\Services\LearningService();
        $courseResult = $learningService->createCourse(6, 1, 'Grade Test Course', 'GRADE101', 25);
        $assignmentResult = $learningService->createAssignment($courseResult['course_id'], 6, 'Test Assignment', 'Description', '2025-12-31', 100);
        $learningService->submitGrade($assignmentResult['assignment_id'], 50, 6, 85, 'Good work');

        $response = $this->service->getStudentGrades(50, $courseResult['course_id']);

        $this->assertIsArray($response);
        $this->assertTrue($response['success']);
        $this->assertArrayHasKey('grades', $response['data']);
        $this->assertArrayHasKey('course_average', $response['data']);
    }

    public function testPaginationHasMore(): void
    {
        $pagination = $this->service->formatPagination(50, 2, 20);
        $this->assertTrue($pagination['has_more']);

        $paginationLast = $this->service->formatPagination(50, 3, 20);
        $this->assertFalse($paginationLast['has_more']);
    }

    public function testMobileInvoiceDataSimplified(): void
    {
        $financeService = new \App\Services\FinanceService();
        $financeService->createInvoice(51, 6, [['name' => 'Tuition', 'amount' => 15000]], '2025-12-31');

        $response = $this->service->getInvoices(6);

        $this->assertTrue($response['success']);
        $invoices = $response['data']['invoices'];
        
        if (!empty($invoices)) {
            $invoice = $invoices[0];
            $this->assertArrayHasKey('id', $invoice);
            $this->assertArrayHasKey('balance', $invoice);
            $this->assertArrayHasKey('status', $invoice);
            $this->assertArrayNotHasKey('items', $invoice); // Simplified for mobile
        }
    }
}
