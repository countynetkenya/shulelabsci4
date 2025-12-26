<?php

namespace Tests\Feature\Governance;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * @group Governance
 */
class GovernanceCrudTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $namespace = 'App\Modules\Governance';
    protected $migrate = true;
    protected $migrateOnce = false;
    protected $refresh = true;
    protected $seed = '';

    protected function setUp(): void
    {
        parent::setUp();
        
        // Mock session for authenticated admin user
        $_SESSION['school_id'] = 1;
        $_SESSION['schoolID'] = 1;
        $_SESSION['user_id'] = 1;
        $_SESSION['loginuserID'] = 1;
        $_SESSION['usertypeID'] = 1;
        $_SESSION['is_admin'] = true;
    }

    public function testGovernanceIndexPageLoads()
    {
        $result = $this->withSession($_SESSION)
            ->get('governance');

        $result->assertStatus(200);
        $result->assertSee('Governance');
    }

    public function testGovernanceCreatePageLoads()
    {
        $result = $this->withSession($_SESSION)
            ->get('governance/create');

        $result->assertStatus(200);
        $result->assertSee('Create Policy');
    }

    public function testCanCreatePolicy()
    {
        $data = [
            'policy_number' => 'POL-TEST-001',
            'title' => 'Test Policy',
            'category' => 'Academic',
            'content' => 'This is a test policy content',
            'summary' => 'Test summary',
            'version' => '1.0',
            'status' => 'draft',
        ];

        $result = $this->withSession($_SESSION)
            ->post('governance/store', $data);

        $result->assertRedirectTo('/governance');
        
        // Verify data was inserted
        $this->seeInDatabase('policies', [
            'policy_number' => 'POL-TEST-001',
            'title' => 'Test Policy',
            'school_id' => 1,
        ]);
    }

    public function testGovernanceEditPageLoads()
    {
        $policyId = $this->insertTestPolicy();

        $result = $this->withSession($_SESSION)
            ->get("governance/edit/{$policyId}");

        $result->assertStatus(200);
        $result->assertSee('Edit Policy');
    }

    public function testCanUpdatePolicy()
    {
        $policyId = $this->insertTestPolicy();

        $data = [
            'policy_number' => 'POL-TEST-002',
            'title' => 'Updated Policy',
            'category' => 'Financial',
            'content' => 'Updated policy content',
            'summary' => 'Updated summary',
            'version' => '2.0',
            'status' => 'approved',
        ];

        $result = $this->withSession($_SESSION)
            ->post("governance/update/{$policyId}", $data);

        $result->assertRedirectTo('/governance');
        
        $this->seeInDatabase('policies', [
            'id' => $policyId,
            'title' => 'Updated Policy',
        ]);
    }

    public function testCanDeletePolicy()
    {
        $policyId = $this->insertTestPolicy();

        $result = $this->withSession($_SESSION)
            ->get("governance/delete/{$policyId}");

        $result->assertRedirectTo('/governance');
        
        $this->dontSeeInDatabase('policies', [
            'id' => $policyId,
        ]);
    }

    protected function insertTestPolicy(): int
    {
        $db = \Config\Database::connect();
        $data = [
            'school_id' => 1,
            'policy_number' => 'POL-TEST-001',
            'title' => 'Test Policy',
            'category' => 'Academic',
            'content' => 'Test policy content',
            'summary' => 'Test summary',
            'version' => '1.0',
            'status' => 'draft',
            'created_by' => 1,
            'created_at' => date('Y-m-d H:i:s'),
        ];
        
        $db->table('policies')->insert($data);
        return (int) $db->insertID();
    }
}
