<?php

use PHPUnit\Framework\TestCase;

if (!defined('BASEPATH')) {
    define('BASEPATH', __DIR__ . '/../../mvc/');
}
if (!defined('APPPATH')) {
    define('APPPATH', __DIR__ . '/../../mvc/');
}

if (!function_exists('get_instance')) {
    function &get_instance()
    {
        return $GLOBALS['menu_authorizer_ci_instance'];
    }
}

require_once APPPATH . 'helpers/action_helper.php';
require_once APPPATH . 'helpers/user_helper.php';
require_once APPPATH . 'libraries/MenuAuthorizer.php';

final class MenuAuthorizerTest extends TestCase
{
    protected function setUp(): void
    {
        $this->setInstance([
            'usertypeID' => 2,
            'loginuserID' => 42,
            'master_permission_set' => [
                'okr_view' => 'yes',
            ],
        ]);
    }

    protected function tearDown(): void
    {
        $GLOBALS['menu_authorizer_ci_instance'] = null;
    }

    private function setInstance(array $sessionData): void
    {
        $session = new class($sessionData) {
            private $data;

            public function __construct(array $data)
            {
                $this->data = $data;
            }

            public function userdata($key)
            {
                return $this->data[$key] ?? null;
            }
        };

        $loader = new class {
            public function helper($name)
            {
                // no-op for tests
            }
        };

        $GLOBALS['menu_authorizer_ci_instance'] = (object) [
            'session' => $session,
            'load' => $loader,
        ];
    }

    public function testFilterKeepsItemWhenPermissionGrantedWithUnderscoreKey(): void
    {
        $authorizer = new MenuAuthorizer();
        $items = [
            'okr' => [
                'link' => 'okr',
                'permission' => 'okr_view',
            ],
        ];

        $filtered = $authorizer->filter($items);

        $this->assertArrayHasKey('okr', $filtered);
    }

    public function testSuperAdminBypassesPermissionChecks(): void
    {
        $this->setInstance([
            'usertypeID' => 1,
            'loginuserID' => 1,
            'master_permission_set' => [],
        ]);

        $authorizer = new MenuAuthorizer();
        $items = [
            'payroll' => [
                'link' => 'payroll',
                'permission' => 'payroll_view',
            ],
        ];

        $filtered = $authorizer->filter($items);

        $this->assertArrayHasKey('payroll', $filtered);
    }
}
