<?php

use PHPUnit\Framework\TestCase;

if (!defined('BASEPATH')) {
    define('BASEPATH', __DIR__ . '/../../mvc/');
}
if (!defined('APPPATH')) {
    define('APPPATH', __DIR__ . '/../../mvc/');
}

require_once APPPATH . 'libraries/SidebarRegistry.php';

final class SidebarRegistryTest extends TestCase
{
    protected function setUp(): void
    {
        SidebarRegistry::resetCache();
    }

    public function testAdminSidebarItemsIncludeOkr()
    {
        $items = SidebarRegistry::itemsForContext('admin_sidebar');
        $this->assertArrayHasKey('okr', $items, 'OKR entry should be available for admin sidebar');
        $this->assertSame('okr', $items['okr']['link']);
    }

    public function testFeaturedSuperadminLinksAreOrderedByPriority()
    {
        $featured = SidebarRegistry::featuredSuperadminItems();
        $keys = array_keys($featured);
        $this->assertNotEmpty($keys);
        $priorities = array_map(function ($item) {
            return isset($item['priority']) ? (int) $item['priority'] : null;
        }, $featured);
        $sorted = array_values($priorities);
        sort($sorted);
        $this->assertSame($sorted, array_values($priorities));
        $this->assertContains('superadmin_users', $keys);
    }

    public function testSyncableItemsMirrorSidebarConfig()
    {
        $syncable = SidebarRegistry::syncableItems();
        $this->assertArrayHasKey('payroll', $syncable);
        $this->assertArrayHasKey('superadmin_users', $syncable);
    }

    public function testBuildOverridePayloadDefaultsSkipPermissionToFalse()
    {
        $items = SidebarRegistry::items();
        $this->assertArrayHasKey('payroll', $items);

        $result = SidebarRegistry::buildOverridePayload('payroll', $items['payroll']);
        $this->assertNotNull($result);
        $this->assertArrayHasKey('payload', $result);
        $this->assertArrayHasKey('skip_permission', $result['payload']);
        $this->assertFalse($result['payload']['skip_permission']);
    }

    public function testBuildOverridePayloadRespectsExplicitSkipPermissionFlag()
    {
        $items = SidebarRegistry::items();
        $this->assertArrayHasKey('payroll', $items);

        $custom = $items['payroll'];
        $custom['skip_permission'] = true;

        $result = SidebarRegistry::buildOverridePayload('custom_payroll', $custom);
        $this->assertNotNull($result);
        $this->assertTrue($result['payload']['skip_permission']);
    }
}
