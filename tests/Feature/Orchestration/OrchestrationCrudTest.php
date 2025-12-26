<?php

namespace Tests\Feature\Orchestration;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Traits\TenantTestTrait;

/**
 * OrchestrationCrudTest - Feature tests for Orchestration Workflow CRUD operations
 */
class OrchestrationCrudTest extends CIUnitTestCase
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

    public function testIndexDisplaysWorkflows()
    {
        $this->db->table('workflows')->insert([
            'school_id' => $this->schoolId,
            'workflow_id' => 'test_wf_001',
            'name' => 'Test Workflow',
            'description' => 'Test Description',
            'status' => 'pending',
            'current_step' => 0,
            'total_steps' => 3,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $result = $this->withSession($this->getAdminSession())->get('orchestration');
        $result->assertOK();
        $result->assertSee('Test Workflow');
    }

    public function testCreatePageDisplaysForm()
    {
        $result = $this->withSession($this->getAdminSession())->get('orchestration/create');
        $result->assertOK();
        $result->assertSee('Workflow Name');
    }

    public function testStoreCreatesWorkflow()
    {
        $data = [
            'name' => 'New Test Workflow',
            'description' => 'Workflow description',
            'steps' => "Step 1\nStep 2\nStep 3",
        ];

        $result = $this->withSession($this->getAdminSession())->post('orchestration/store', $data);
        $result->assertRedirectTo('/orchestration');
        
        $workflow = $this->db->table('workflows')->where('school_id', $this->schoolId)->where('name', 'New Test Workflow')->get()->getRowArray();
        $this->assertNotNull($workflow);
        $this->assertEquals(3, $workflow['total_steps']);
    }

    public function testUpdateModifiesWorkflow()
    {
        $workflowId = $this->db->table('workflows')->insert([
            'school_id' => $this->schoolId,
            'workflow_id' => 'update_wf_001',
            'name' => 'Original Name',
            'description' => 'Original',
            'status' => 'pending',
            'current_step' => 0,
            'total_steps' => 2,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $data = ['name' => 'Updated Name', 'description' => 'Updated description', 'status' => 'running'];
        $result = $this->withSession($this->getAdminSession())->post('orchestration/update/' . $workflowId, $data);
        $result->assertRedirectTo('/orchestration');
        
        $workflow = $this->db->table('workflows')->where('id', $workflowId)->get()->getRowArray();
        $this->assertEquals('Updated Name', $workflow['name']);
        $this->assertEquals('running', $workflow['status']);
    }

    public function testDeleteRemovesWorkflow()
    {
        $workflowId = $this->db->table('workflows')->insert([
            'school_id' => $this->schoolId,
            'workflow_id' => 'delete_wf_001',
            'name' => 'Delete Test',
            'description' => 'Test workflow for deletion',
            'status' => 'failed',
            'current_step' => 1,
            'total_steps' => 3,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $result = $this->withSession($this->getAdminSession())->get('orchestration/delete/' . $workflowId);
        $result->assertRedirectTo('/orchestration');
        
        $workflow = $this->db->table('workflows')->where('id', $workflowId)->get()->getRowArray();
        $this->assertNull($workflow);
    }
}
