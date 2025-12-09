<?php

namespace Tests\Feature\ApprovalWorkflows;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Traits\TenantTestTrait;

/**
 * ApprovalCrudTest - Feature tests for Approval Workflows CRUD operations
 */
class ApprovalCrudTest extends CIUnitTestCase
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

    public function testIndexDisplaysApprovalRequests()
    {
        // Seed workflow
        $this->db->table('approval_workflows')->insert([
            'school_id'   => $this->schoolId,
            'name'        => 'Test Workflow',
            'code'        => 'TEST',
            'entity_type' => 'test_entity',
            'is_active'   => 1,
            'created_by'  => $this->userId,
            'created_at'  => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);
        $workflowId = $this->db->insertID();

        // Seed approval request
        $this->db->table('approval_requests')->insert([
            'school_id'    => $this->schoolId,
            'workflow_id'  => $workflowId,
            'entity_type'  => 'test_entity',
            'entity_id'    => 1,
            'request_data' => '{}',
            'status'       => 'pending',
            'priority'     => 'normal',
            'requested_by' => $this->userId,
            'requested_at' => date('Y-m-d H:i:s'),
            'created_at'   => date('Y-m-d H:i:s'),
        ]);

        $result = $this->withSession($this->getAdminSession())
                       ->get('approvals');

        $result->assertOK();
        $result->assertSee('Approval Workflows');
        $result->assertSee('test_entity');
    }

    public function testIndexShowsEmptyState()
    {
        $result = $this->withSession($this->getAdminSession())
                       ->get('approvals');

        $result->assertOK();
        $result->assertSee('No approval requests found');
    }

    public function testCreatePageDisplaysForm()
    {
        $result = $this->withSession($this->getAdminSession())
                       ->get('approvals/create');

        $result->assertOK();
        $result->assertSee('New Approval Request');
        $result->assertSee('Entity Type');
    }

    public function testStoreCreatesApprovalRequest()
    {
        $result = $this->withSession($this->getAdminSession())
                       ->post('approvals/store', [
                           'workflow_id' => 1,
                           'entity_type' => 'purchase_request',
                           'entity_id'   => 100,
                           'priority'    => 'high',
                           csrf_token()  => csrf_hash(),
                       ]);

        $result->assertRedirectTo('/approvals');
        
        $this->seeInDatabase('approval_requests', [
            'entity_type' => 'purchase_request',
            'entity_id'   => 100,
            'priority'    => 'high',
            'school_id'   => $this->schoolId,
        ]);
    }

    public function testStoreValidationFailsWithMissingFields()
    {
        $result = $this->withSession($this->getAdminSession())
                       ->post('approvals/store', [
                           'workflow_id' => '',
                           'entity_type' => '',
                           csrf_token()  => csrf_hash(),
                       ]);

        $result->assertRedirect();
    }

    public function testEditPageDisplaysRequestData()
    {
        // Seed workflow
        $this->db->table('approval_workflows')->insert([
            'school_id'   => $this->schoolId,
            'name'        => 'Edit Test Workflow',
            'code'        => 'EDIT_TEST',
            'entity_type' => 'edit_test',
            'is_active'   => 1,
            'created_by'  => $this->userId,
            'created_at'  => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);
        $workflowId = $this->db->insertID();

        // Seed approval request
        $this->db->table('approval_requests')->insert([
            'school_id'    => $this->schoolId,
            'workflow_id'  => $workflowId,
            'entity_type'  => 'edit_test',
            'entity_id'    => 200,
            'request_data' => '{}',
            'status'       => 'pending',
            'priority'     => 'normal',
            'requested_by' => $this->userId,
            'requested_at' => date('Y-m-d H:i:s'),
            'created_at'   => date('Y-m-d H:i:s'),
        ]);
        $requestId = $this->db->insertID();

        $result = $this->withSession($this->getAdminSession())
                       ->get("approvals/edit/{$requestId}");

        $result->assertOK();
        $result->assertSee('Edit Approval Request');
        $result->assertSee('edit_test');
    }

    public function testEditPageRedirectsForNonExistent()
    {
        $result = $this->withSession($this->getAdminSession())
                       ->get('approvals/edit/99999');

        $result->assertRedirectTo('/approvals');
    }

    public function testUpdateModifiesRequest()
    {
        // Seed workflow
        $this->db->table('approval_workflows')->insert([
            'school_id'   => $this->schoolId,
            'name'        => 'Update Test Workflow',
            'code'        => 'UPDATE_TEST',
            'entity_type' => 'update_test',
            'is_active'   => 1,
            'created_by'  => $this->userId,
            'created_at'  => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);
        $workflowId = $this->db->insertID();

        // Seed approval request
        $this->db->table('approval_requests')->insert([
            'school_id'    => $this->schoolId,
            'workflow_id'  => $workflowId,
            'entity_type'  => 'original_type',
            'entity_id'    => 300,
            'request_data' => '{}',
            'status'       => 'pending',
            'priority'     => 'normal',
            'requested_by' => $this->userId,
            'requested_at' => date('Y-m-d H:i:s'),
            'created_at'   => date('Y-m-d H:i:s'),
        ]);
        $requestId = $this->db->insertID();

        $result = $this->withSession($this->getAdminSession())
                       ->post("approvals/update/{$requestId}", [
                           'workflow_id' => $workflowId,
                           'entity_type' => 'updated_type',
                           'entity_id'   => 300,
                           'status'      => 'approved',
                           'priority'    => 'high',
                           csrf_token()  => csrf_hash(),
                       ]);

        $result->assertRedirectTo('/approvals');
        
        $this->seeInDatabase('approval_requests', [
            'id'          => $requestId,
            'entity_type' => 'updated_type',
            'status'      => 'approved',
            'priority'    => 'high',
        ]);
    }

    public function testDeleteRemovesRequest()
    {
        // Seed workflow
        $this->db->table('approval_workflows')->insert([
            'school_id'   => $this->schoolId,
            'name'        => 'Delete Test Workflow',
            'code'        => 'DELETE_TEST',
            'entity_type' => 'delete_test',
            'is_active'   => 1,
            'created_by'  => $this->userId,
            'created_at'  => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);
        $workflowId = $this->db->insertID();

        // Seed approval request
        $this->db->table('approval_requests')->insert([
            'school_id'    => $this->schoolId,
            'workflow_id'  => $workflowId,
            'entity_type'  => 'delete_test',
            'entity_id'    => 400,
            'request_data' => '{}',
            'status'       => 'pending',
            'priority'     => 'normal',
            'requested_by' => $this->userId,
            'requested_at' => date('Y-m-d H:i:s'),
            'created_at'   => date('Y-m-d H:i:s'),
        ]);
        $requestId = $this->db->insertID();

        $this->seeInDatabase('approval_requests', ['id' => $requestId]);

        $result = $this->withSession($this->getAdminSession())
                       ->get("approvals/delete/{$requestId}");

        $result->assertRedirectTo('/approvals');
        
        $this->dontSeeInDatabase('approval_requests', ['id' => $requestId]);
    }

    public function testDeleteNonExistentRedirects()
    {
        $result = $this->withSession($this->getAdminSession())
                       ->get('approvals/delete/99999');

        $result->assertRedirectTo('/approvals');
    }

    public function testCannotAccessOtherSchoolRequests()
    {
        // Create request for different school
        $this->db->table('approval_workflows')->insert([
            'school_id'   => 99999,
            'name'        => 'Other School Workflow',
            'code'        => 'OTHER',
            'entity_type' => 'other',
            'is_active'   => 1,
            'created_by'  => 999,
            'created_at'  => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);
        $otherWorkflowId = $this->db->insertID();

        $this->db->table('approval_requests')->insert([
            'school_id'    => 99999,
            'workflow_id'  => $otherWorkflowId,
            'entity_type'  => 'other_school',
            'entity_id'    => 999,
            'request_data' => '{}',
            'status'       => 'pending',
            'priority'     => 'normal',
            'requested_by' => 999,
            'requested_at' => date('Y-m-d H:i:s'),
            'created_at'   => date('Y-m-d H:i:s'),
        ]);
        $otherRequestId = $this->db->insertID();

        $result = $this->withSession($this->getAdminSession())
                       ->get("approvals/edit/{$otherRequestId}");

        $result->assertRedirectTo('/approvals');
    }
}
