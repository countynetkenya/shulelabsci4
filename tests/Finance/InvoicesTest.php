<?php

namespace Tests\Finance;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Test\DatabaseTestTrait;
use Tests\Support\Traits\TenantTestTrait;

class InvoicesTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use DatabaseTestTrait;
    use TenantTestTrait;

    protected $migrate = true;
    protected $migrateOnce = false;
    protected $refresh = true;
    protected $namespace = 'App';

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupTenantContext();
        
        // Robust CSRF Disable (Fixing TenantTestTrait limitation)
        $config = config('Filters');
        $newBefore = [];
        foreach ($config->globals['before'] as $key => $value) {
            if ($value !== 'csrf' && $key !== 'csrf') {
                if (is_array($value)) {
                     $newBefore[$key] = $value;
                } else {
                    $newBefore[] = $value;
                }
            }
        }
        $config->globals['before'] = $newBefore;
        \CodeIgniter\Config\Factories::injectMock('filters', 'Filters', $config);
    }

    public function testIndex()
    {
        $result = $this->withSession($this->getAdminSession())
                       ->call('get', '/finance/invoices');
        
        if (!$result->isOK()) {
             // Debug info if needed
        }
        
        $result->assertOK();
        $result->assertSee('Invoices');
    }

    public function testCreate()
    {
        $result = $this->withSession($this->getAdminSession())
                       ->call('get', '/finance/invoices/create');
        $result->assertOK();
        $result->assertSee('Create New Invoice');
    }

    public function testStore()
    {
        // Create a student (user)
        $this->db->table('users')->insert([
            'username' => 'student1',
            'email' => 'student1@example.com',
            'password_hash' => 'hash',
            'full_name' => 'Student One',
            'is_active' => 1
        ]);
        $studentId = $this->db->insertID();

        $result = $this->withSession($this->getAdminSession())
                       ->call('post', '/finance/invoices', [
            'student_id' => $studentId,
            'amount' => 1000,
            'due_date' => date('Y-m-d', strtotime('+30 days')),
            'description' => 'Tuition Fee'
        ]);

        $result->assertRedirectTo('/finance/invoices');
        $this->seeInDatabase('finance_invoices', ['student_id' => $studentId, 'amount' => 1000]);
    }
}
