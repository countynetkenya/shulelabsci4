<?php

namespace Tests\Feature\Admissions;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Traits\TenantTestTrait;

class AdmissionsWebTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;
    use TenantTestTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupTenantContext();
    }

    public function testAdminCanViewAdmissionsDashboard()
    {
        $response = $this->withSession($this->getAdminSession())
                         ->get('admissions');

        $response->assertOK();
        $response->assertSee('Admissions Portal');
    }

    public function testApiApply()
    {
        $response = $this->withSession($this->getAdminSession())
                         ->post('api/admissions/apply');

        $response->assertOK();
        $response->assertJSONFragment(['status' => 'success']);
    }
}
