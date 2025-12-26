<?php

namespace Tests\Feature\Reports;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Traits\TenantTestTrait;

/**
 * ReportsCrudTest - Tests CRUD operations for Reports module
 *
 * @internal
 */
class ReportsCrudTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;
    use TenantTestTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupTenantContext();
        
        // Run migrations for Reports module
        $this->migrateDatabase();
    }

    public function testIndexPageLoadsSuccessfully(): void
    {
        $result = $this->withSession($this->getAdminSession())
            ->get('reports');

        $result->assertStatus(200);
        $result->assertSee('Reports');
    }

    public function testCreatePageLoadsSuccessfully(): void
    {
        $result = $this->withSession($this->getAdminSession())
            ->get('reports/create');

        $result->assertStatus(200);
        $result->assertSee('Create New Report');
    }

    public function testCanCreateNewReport(): void
    {
        $data = [
            'name'        => 'Test Report',
            'description' => 'Test Description',
            'template'    => 'student_performance',
            'format'      => 'pdf',
            'status'      => 'draft',
        ];

        $result = $this->withSession($this->getAdminSession())
            ->post('reports', $data);

        $result->assertRedirectTo('/reports');
        
        $this->seeInDatabase('reports', [
            'name'      => 'Test Report',
            'school_id' => $this->schoolId,
        ]);
    }

    public function testValidationFailsWithInvalidData(): void
    {
        $data = [
            'name'     => '', // Empty name should fail
            'template' => '',
            'format'   => 'invalid_format',
        ];

        $result = $this->withSession($this->getAdminSession())
            ->post('reports', $data);

        $result->assertRedirect();
        
        $this->dontSeeInDatabase('reports', [
            'name' => '',
        ]);
    }

    public function testCanUpdateReport(): void
    {
        // Create a report first
        $reportId = $this->db->table('reports')->insert([
            'school_id'   => $this->schoolId,
            'name'        => 'Original Report',
            'template'    => 'attendance_summary',
            'format'      => 'pdf',
            'status'      => 'draft',
            'created_by'  => $this->userId,
            'created_at'  => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);

        $data = [
            'name'        => 'Updated Report',
            'template'    => 'attendance_summary',
            'format'      => 'excel',
            'status'      => 'active',
        ];

        $result = $this->withSession($this->getAdminSession())
            ->post("reports/{$reportId}", $data);

        $result->assertRedirectTo('/reports');
        
        $this->seeInDatabase('reports', [
            'id'     => $reportId,
            'name'   => 'Updated Report',
            'format' => 'excel',
        ]);
    }

    public function testCanDeleteReport(): void
    {
        // Create a report first
        $reportId = $this->db->table('reports')->insert([
            'school_id'   => $this->schoolId,
            'name'        => 'Report to Delete',
            'template'    => 'financial_overview',
            'format'      => 'pdf',
            'status'      => 'draft',
            'created_by'  => $this->userId,
            'created_at'  => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);

        $result = $this->withSession($this->getAdminSession())
            ->get("reports/{$reportId}/delete");

        $result->assertRedirectTo('/reports');
        
        $this->dontSeeInDatabase('reports', [
            'id' => $reportId,
        ]);
    }

    public function testEditPageLoadsWithCorrectData(): void
    {
        // Create a report first
        $reportId = $this->db->table('reports')->insert([
            'school_id'   => $this->schoolId,
            'name'        => 'Test Report',
            'template'    => 'inventory_status',
            'format'      => 'csv',
            'status'      => 'active',
            'created_by'  => $this->userId,
            'created_at'  => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);

        $result = $this->withSession($this->getAdminSession())
            ->get("reports/{$reportId}/edit");

        $result->assertStatus(200);
        $result->assertSee('Edit Report');
        $result->assertSee('Test Report');
    }

    protected function migrateDatabase(): void
    {
        // Create reports table if it doesn't exist
        if (!$this->db->tableExists('reports')) {
            $this->db->query("
                CREATE TABLE IF NOT EXISTS reports (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    school_id INTEGER NOT NULL,
                    name VARCHAR(255) NOT NULL,
                    description TEXT,
                    template VARCHAR(100) NOT NULL,
                    parameters TEXT,
                    format VARCHAR(20) DEFAULT 'pdf',
                    schedule VARCHAR(100),
                    is_scheduled INTEGER DEFAULT 0,
                    last_generated_at DATETIME,
                    file_path VARCHAR(500),
                    status VARCHAR(20) DEFAULT 'draft',
                    created_by INTEGER NOT NULL,
                    created_at DATETIME,
                    updated_at DATETIME
                )
            ");
        }
    }
}
