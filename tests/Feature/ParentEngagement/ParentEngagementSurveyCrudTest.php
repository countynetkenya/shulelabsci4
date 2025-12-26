<?php

namespace Tests\Feature\ParentEngagement;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Traits\TenantTestTrait;

/**
 * ParentEngagementSurveyCrudTest - Tests CRUD operations for Parent Engagement module.
 */
class ParentEngagementSurveyCrudTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;
    use TenantTestTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupTenantContext();
    }

    public function testIndexPageLoadsSuccessfully(): void
    {
        $result = $this->withSession($this->getAdminSession())
            ->get('parent-engagement');

        $result->assertStatus(200);
        $result->assertSee('Parent Engagement');
    }

    public function testCreatePageLoadsSuccessfully(): void
    {
        $result = $this->withSession($this->getAdminSession())
            ->get('parent-engagement/create');

        $result->assertStatus(200);
        $result->assertSee('Create Survey');
    }

    public function testCanCreateSurvey(): void
    {
        $data = [
            'title'           => 'Test Survey',
            'survey_type'     => 'feedback',
            'target_audience' => 'all_parents',
            'status'          => 'draft',
        ];

        $result = $this->withSession($this->getAdminSession())
            ->post('parent-engagement', $data);

        $result->assertRedirectTo('/parent-engagement');

        $this->seeInDatabase('surveys', [
            'title'     => 'Test Survey',
            'school_id' => $this->schoolId,
        ]);
    }
}
