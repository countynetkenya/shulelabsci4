<?php

namespace Tests\Feature\Gamification;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * @group Gamification
 */
class GamificationCrudTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $namespace = 'App\Modules\Gamification';

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

    public function testGamificationIndexPageLoads()
    {
        $result = $this->withSession($_SESSION)
            ->get('gamification');

        $result->assertStatus(200);
        $result->assertSee('Gamification Dashboard');
    }

    public function testGamificationCreatePageLoads()
    {
        $result = $this->withSession($_SESSION)
            ->get('gamification/create');

        $result->assertStatus(200);
        $result->assertSee('Create Badge');
    }

    public function testCanCreateBadge()
    {
        $data = [
            'name' => 'Test Badge',
            'code' => 'TEST_BADGE',
            'description' => 'Test badge description',
            'category' => 'academic',
            'tier' => 'bronze',
            'points_reward' => 50,
            'is_secret' => 0,
        ];

        $result = $this->withSession($_SESSION)
            ->post('gamification/store', $data);

        $result->assertRedirectTo('/gamification');

        // Verify data was inserted
        $this->seeInDatabase('badges', [
            'name' => 'Test Badge',
            'code' => 'TEST_BADGE',
            'school_id' => 1,
        ]);
    }

    public function testGamificationEditPageLoads()
    {
        $badgeId = $this->insertTestBadge();

        $result = $this->withSession($_SESSION)
            ->get("gamification/edit/{$badgeId}");

        $result->assertStatus(200);
        $result->assertSee('Edit Badge');
    }

    public function testCanUpdateBadge()
    {
        $badgeId = $this->insertTestBadge();

        $data = [
            'name' => 'Updated Badge',
            'code' => 'UPDATED_BADGE',
            'description' => 'Updated description',
            'category' => 'sports',
            'tier' => 'gold',
            'points_reward' => 100,
            'is_secret' => 1,
            'is_active' => 1,
        ];

        $result = $this->withSession($_SESSION)
            ->post("gamification/update/{$badgeId}", $data);

        $result->assertRedirectTo('/gamification');

        $this->seeInDatabase('badges', [
            'id' => $badgeId,
            'name' => 'Updated Badge',
        ]);
    }

    public function testCanDeleteBadge()
    {
        $badgeId = $this->insertTestBadge();

        $result = $this->withSession($_SESSION)
            ->get("gamification/delete/{$badgeId}");

        $result->assertRedirectTo('/gamification');

        $this->dontSeeInDatabase('badges', [
            'id' => $badgeId,
        ]);
    }

    public function testCannotDeleteGlobalBadge()
    {
        // Insert a global badge (school_id = null)
        $db = \Config\Database::connect();
        $data = [
            'school_id' => null,
            'name' => 'Global Badge',
            'code' => 'GLOBAL_BADGE',
            'category' => 'special',
            'tier' => 'platinum',
            'points_reward' => 500,
            'is_secret' => 0,
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s'),
        ];
        $db->table('badges')->insert($data);
        $badgeId = (int) $db->insertID();

        $result = $this->withSession($_SESSION)
            ->get("gamification/delete/{$badgeId}");

        $result->assertRedirectTo('/gamification');

        // Should still exist
        $this->seeInDatabase('badges', [
            'id' => $badgeId,
            'name' => 'Global Badge',
        ]);
    }

    protected function insertTestBadge(): int
    {
        $db = \Config\Database::connect();
        $data = [
            'school_id' => 1,
            'name' => 'Test Badge',
            'code' => 'TEST_BADGE',
            'category' => 'academic',
            'tier' => 'bronze',
            'points_reward' => 50,
            'is_secret' => 0,
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $db->table('badges')->insert($data);
        return (int) $db->insertID();
    }
}
