<?php

namespace Tests\Feature\Governance;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Traits\TenantTestTrait;

/**
 * GovernanceWebTest - Feature tests for Governance CRUD
 */
class GovernanceWebTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;
    use TenantTestTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupTenantContext();
    }

    public function testAdminCanViewGovernanceIndex()
    {
        $response = $this->withSession($this->getAdminSession())
                         ->get('governance');

        $response->assertOK();
        $response->assertSee('Governance & Policies');
        $response->assertSee('fa-gavel');
    }

    public function testAdminCanViewCreateForm()
    {
        $response = $this->withSession($this->getAdminSession())
                         ->get('governance/create');

        $response->assertOK();
        $response->assertSee('Create Policy');
        $response->assertSee('title');
        $response->assertSee('content');
    }

    public function testAdminCanCreatePolicy()
    {
        $data = [
            'title' => 'Test Policy',
            'category' => 'Academic',
            'content' => 'This is a test policy content.',
            'summary' => 'Test summary',
            'version' => '1.0',
            'status' => 'draft',
        ];

        $response = $this->withSession($this->getAdminSession())
                         ->post('governance/store', $data);

        $response->assertRedirectTo('/governance');
        $response->assertSessionHas('message');
    }

    public function testCreatePolicyValidatesRequiredFields()
    {
        $data = [
            'title' => 'Test',
            // Missing required category and content
        ];

        $response = $this->withSession($this->getAdminSession())
                         ->post('governance/store', $data);

        $response->assertRedirect();
        $response->assertSessionHas('errors');
    }

    public function testAdminCanViewEditForm()
    {
        // Create a policy first
        $db = \Config\Database::connect();
        $db->table('policies')->insert([
            'school_id' => 1,
            'policy_number' => 'TEST-POL-001',
            'title' => 'Edit Test Policy',
            'category' => 'Academic',
            'content' => 'Test content',
            'summary' => 'Test summary',
            'version' => '1.0',
            'status' => 'draft',
            'created_by' => 1,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $policyId = $db->insertID();

        $response = $this->withSession($this->getAdminSession())
                         ->get("governance/edit/{$policyId}");

        $response->assertOK();
        $response->assertSee('Edit Policy');
        $response->assertSee('Edit Test Policy');
    }

    public function testAdminCanUpdatePolicy()
    {
        // Create a policy first
        $db = \Config\Database::connect();
        $db->table('policies')->insert([
            'school_id' => 1,
            'policy_number' => 'TEST-POL-002',
            'title' => 'Update Test Policy',
            'category' => 'Academic',
            'content' => 'Old content',
            'summary' => 'Old summary',
            'version' => '1.0',
            'status' => 'draft',
            'created_by' => 1,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $policyId = $db->insertID();

        $data = [
            'title' => 'Updated Policy Title',
            'category' => 'Financial',
            'content' => 'Updated content',
            'summary' => 'Updated summary',
            'version' => '2.0',
            'status' => 'approved',
        ];

        $response = $this->withSession($this->getAdminSession())
                         ->post("governance/update/{$policyId}", $data);

        $response->assertRedirectTo('/governance');
        $response->assertSessionHas('message');
    }

    public function testAdminCanDeletePolicy()
    {
        // Create a policy first
        $db = \Config\Database::connect();
        $db->table('policies')->insert([
            'school_id' => 1,
            'policy_number' => 'TEST-POL-003',
            'title' => 'Delete Test Policy',
            'category' => 'Academic',
            'content' => 'To be deleted',
            'version' => '1.0',
            'status' => 'draft',
            'created_by' => 1,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $policyId = $db->insertID();

        $response = $this->withSession($this->getAdminSession())
                         ->get("governance/delete/{$policyId}");

        $response->assertRedirectTo('/governance');
        $response->assertSessionHas('message');
    }
}
