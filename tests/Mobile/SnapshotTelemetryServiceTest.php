<?php

declare(strict_types=1);

namespace Tests\Ci4\Mobile;

use Modules\Foundation\Services\AuditService;
use Modules\Mobile\Services\OfflineSnapshotService;
use Modules\Mobile\Services\SnapshotTelemetryService;
use Tests\Ci4\Foundation\FoundationDatabaseTestCase;

class SnapshotTelemetryServiceTest extends FoundationDatabaseTestCase
{
    public function testTelemetryAggregatesIssuedVerifiedAndFailures(): void
    {
        $audit     = new AuditService($this->db);
        $snapshots = new OfflineSnapshotService('secret-key', 'key-1', $audit, 3600);

        $context = ['tenant_id' => 'tenant-1', 'device_id' => 'device-1'];
        $snapshot = $snapshots->issueSnapshot(['students' => 50], $context);
        $snapshots->verifySnapshot($snapshot, $context);
        $snapshots->verifySnapshot($snapshot, ['tenant_id' => 'tenant-2']);

        $service   = new SnapshotTelemetryService($this->db);
        $telemetry = $service->getTelemetry(12);

        $this->assertSame(1, $telemetry['totals']['issued']);
        $this->assertSame(1, $telemetry['totals']['verified']);
        $this->assertSame(1, $telemetry['totals']['failed']);
        $this->assertNotEmpty($telemetry['tenants']);

        $tenant = $telemetry['tenants'][0];
        $this->assertSame('tenant-1', $tenant['tenant_id']);
        $this->assertSame(1, $tenant['issued']);
        $this->assertSame(1, $tenant['verified']);

        $this->assertNotEmpty($telemetry['recent_failures']);
        $this->assertSame('tenant-1', $telemetry['recent_failures'][0]['tenant_id']);
    }
}
