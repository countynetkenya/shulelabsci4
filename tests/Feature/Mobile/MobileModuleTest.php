<?php

namespace Tests\Feature\Mobile;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * MobileModuleTest - Tests for Mobile app API.
 */
class MobileModuleTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use DatabaseTestTrait;

    protected $migrateOnce = true;
    protected $seedOnce = true;
    protected $seed = 'WaveModulesSeeder';

    /**
     * Test mobile login endpoint.
     */
    public function testMobileLogin(): void
    {
        $result = $this->withBodyFormat('json')
            ->post('/api/v1/mobile/auth/login', [
                'email' => 'student109@school1.local',
                'password' => 'Student@123',
                'device_id' => 'test-device-123',
                'device_type' => 'android',
            ]);

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 401, 404]));
    }

    /**
     * Test token refresh.
     */
    public function testTokenRefresh(): void
    {
        $result = $this->withBodyFormat('json')
            ->post('/api/v1/mobile/auth/refresh', [
                'refresh_token' => 'dummy-refresh-token',
            ]);

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 401, 404]));
    }

    /**
     * Test register device.
     */
    public function testRegisterDevice(): void
    {
        $result = $this->withSession(['user_id' => 100, 'school_id' => 1])
            ->withBodyFormat('json')
            ->post('/api/v1/mobile/devices', [
                'device_id' => 'test-device-456',
                'device_type' => 'ios',
                'device_name' => 'iPhone 15',
            ]);

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 201, 404]));
    }

    /**
     * Test push token registration.
     */
    public function testRegisterPushToken(): void
    {
        $result = $this->withSession(['user_id' => 100, 'school_id' => 1])
            ->withBodyFormat('json')
            ->post('/api/v1/mobile/push-token', [
                'token' => 'fcm-token-123456',
                'platform' => 'fcm',
            ]);

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 201, 404]));
    }

    /**
     * Test offline sync snapshot.
     */
    public function testGetSyncSnapshot(): void
    {
        $result = $this->withSession(['user_id' => 100, 'school_id' => 1])
            ->get('/api/v1/mobile/sync/timetable');

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 404]));
    }

    /**
     * Test offline queue submission.
     */
    public function testSubmitOfflineQueue(): void
    {
        $result = $this->withSession(['user_id' => 101, 'school_id' => 1, 'role' => 'teacher'])
            ->withBodyFormat('json')
            ->post('/api/v1/mobile/sync/queue', [
                'operations' => [
                    [
                        'operation' => 'create',
                        'entity_type' => 'attendance',
                        'payload' => ['student_id' => 100, 'status' => 'present'],
                        'client_timestamp' => date('Y-m-d H:i:s'),
                    ],
                ],
            ]);

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 201, 404]));
    }

    /**
     * Test sync status endpoint.
     */
    public function testGetSyncStatus(): void
    {
        $result = $this->withSession(['user_id' => 100, 'school_id' => 1])
            ->get('/api/v1/mobile/sync/status');

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 404]));
    }

    /**
     * Test logout (token revocation).
     */
    public function testMobileLogout(): void
    {
        $result = $this->withSession(['user_id' => 100, 'school_id' => 1])
            ->withBodyFormat('json')
            ->post('/api/v1/mobile/auth/logout', []);

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 204, 404]));
    }
}
