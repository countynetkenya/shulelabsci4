<?php

namespace Tests\Hr;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Test\DatabaseTestTrait;
use Tests\Support\Traits\TenantTestTrait;
use Modules\Hr\Models\EmployeeModel;

class EmployeeWebTest extends CIUnitTestCase
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
        
        // Robust CSRF Disable
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
                       ->call('get', '/hr/employees');
        
        $result->assertOK();
        $result->assertSee('Employees');
        $result->assertSee('Add Employee');
    }

    public function testCreatePage()
    {
        $result = $this->withSession($this->getAdminSession())
                       ->call('get', '/hr/employees/create');
        
        $result->assertOK();
    }
}
