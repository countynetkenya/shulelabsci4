<?php

namespace Tests\Feature\Foundation;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Modules\Foundation\Services\SettingsService;

class SettingsTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrate = true;

    protected $migrateOnce = false;

    protected $refresh = true;

    protected $namespace = 'App';

    public function testSetAndGet()
    {
        $service = new SettingsService();

        // Test String
        $service->set('general', 'site_name', 'My School');
        $this->assertEquals('My School', $service->get('general', 'site_name'));

        // Test Integer
        $service->set('mail', 'port', 587, 'integer');
        $this->assertSame(587, $service->get('mail', 'port'));

        // Test Boolean
        $service->set('system', 'maintenance_mode', true, 'boolean');
        $this->assertTrue($service->get('system', 'maintenance_mode'));

        // Test JSON
        $config = ['key' => 'value', 'arr' => [1, 2]];
        $service->set('payment', 'config', $config, 'json');
        $this->assertSame($config, $service->get('payment', 'config'));
    }

    public function testDefaultValue()
    {
        $service = new SettingsService();
        $this->assertEquals('Default', $service->get('non_existent', 'key', 'Default'));
    }

    public function testGetGroup()
    {
        $service = new SettingsService();
        $service->set('group1', 'key1', 'val1');
        $service->set('group1', 'key2', 'val2');
        $service->set('group2', 'key3', 'val3');

        $group = $service->getGroup('group1');
        $this->assertCount(2, $group);
        $this->assertEquals('val1', $group['key1']);
        $this->assertEquals('val2', $group['key2']);
    }
}
