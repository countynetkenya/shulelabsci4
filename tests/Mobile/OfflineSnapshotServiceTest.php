<?php

declare(strict_types=1);

namespace Tests\Ci4\Mobile;

use Modules\Foundation\Services\AuditService;
use Modules\Mobile\Domain\Snapshot;
use Modules\Mobile\Services\OfflineSnapshotService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class OfflineSnapshotServiceTest extends TestCase
{
    private AuditService&MockObject $auditService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->auditService = $this->createMock(AuditService::class);
    }

    public function testIssueAndVerifySnapshot(): void
    {
        $service = new OfflineSnapshotService('secret-key', 'key-1', $this->auditService, 600);

        $events = [];
        $this->auditService
            ->expects($this->exactly(2))
            ->method('recordEvent')
            ->willReturnCallback(function (...$args) use (&$events) {
                $events[] = $args;

                return 0;
            });

        $snapshot = $service->issueSnapshot(
            dataset: [
                'timetable' => ['Monday' => ['Maths', 'Science']],
                'announcements' => ['Exam week starts next Monday'],
            ],
            context: ['tenant_id' => 'tenant-1', 'device_id' => 'device-7']
        );

        $this->assertFalse($snapshot->isExpired());
        $this->assertTrue($service->verifySnapshot($snapshot, ['tenant_id' => 'tenant-1']));
        $this->assertSame('key-1', $service->getActiveKeyId());

        $this->assertCount(2, $events);
        $this->assertSame('snapshot_issued', $events[0][1]);
        $this->assertSame('snapshot_verified', $events[1][1]);
    }

    public function testVerificationFailsWhenSignatureIsTampered(): void
    {
        $service = new OfflineSnapshotService('secret-key', 'key-1', $this->auditService, 600);

        $events = [];
        $this->auditService
            ->expects($this->exactly(2))
            ->method('recordEvent')
            ->willReturnCallback(function (...$args) use (&$events) {
                $events[] = $args;

                return 0;
            });

        $original = $service->issueSnapshot(['grades' => []], ['tenant_id' => 'tenant-2']);

        $tampered = new Snapshot(
            snapshotId: $original->getSnapshotId(),
            tenantId: $original->getTenantId(),
            issuedAt: $original->getIssuedAt(),
            expiresAt: $original->getExpiresAt(),
            payload: $original->getPayload(),
            checksum: $original->getChecksum(),
            signature: base64_encode('tampered'),
            keyId: $original->getKeyId(),
            version: $original->getVersion(),
            metadata: $original->getMetadata()
        );

        $this->assertFalse($service->verifySnapshot($tampered, ['tenant_id' => 'tenant-2']));
        $this->assertCount(2, $events);
        $this->assertSame('snapshot_issued', $events[0][1]);
        $this->assertSame('snapshot_verification_failed', $events[1][1]);
    }

    public function testRotateSigningKeyRetainsPreviousKeysForVerification(): void
    {
        $service = new OfflineSnapshotService('initial-key', 'key-1', $this->auditService, 300);

        $events = [];
        $this->auditService
            ->expects($this->exactly(4))
            ->method('recordEvent')
            ->willReturnCallback(function (...$args) use (&$events) {
                $events[] = $args;

                return 0;
            });

        $firstSnapshot = $service->issueSnapshot(['data' => ['a' => 1]], ['tenant_id' => 'tenant-3']);
        $service->rotateSigningKey('rotated-key', 'key-2');
        $secondSnapshot = $service->issueSnapshot(['data' => ['b' => 2]], ['tenant_id' => 'tenant-3']);

        $this->assertTrue($service->verifySnapshot($firstSnapshot, ['tenant_id' => 'tenant-3']));
        $this->assertTrue($service->verifySnapshot($secondSnapshot, ['tenant_id' => 'tenant-3']));
        $this->assertSame('key-2', $service->getActiveKeyId());

        $this->assertCount(4, $events);
        $this->assertSame('snapshot_issued', $events[0][1]);
        $this->assertSame('snapshot_issued', $events[1][1]);
        $this->assertSame('snapshot_verified', $events[2][1]);
        $this->assertSame('snapshot_verified', $events[3][1]);
    }

    public function testActiveKeyOverridesFallbackKeys(): void
    {
        $this->auditService->method('recordEvent');

        $service = new OfflineSnapshotService(
            signingKey: 'active-key',
            keyId: 'key-1',
            auditService: $this->auditService,
            defaultTtlSeconds: 600,
            fallbackKeys: ['key-1' => 'stale-key']
        );

        $snapshot = $service->issueSnapshot(['dataset' => ['value' => 1]], ['tenant_id' => 'tenant-9']);

        $this->assertSame('key-1', $snapshot->getKeyId());

        $expectedPayload = json_encode([
            'snapshot_id' => $snapshot->getSnapshotId(),
            'checksum'    => $snapshot->getChecksum(),
            'expires_at'  => $snapshot->getExpiresAt()->format(DATE_ATOM),
            'key_id'      => $snapshot->getKeyId(),
        ], JSON_THROW_ON_ERROR);

        $expectedSignature = base64_encode(hash_hmac('sha256', $expectedPayload, 'active-key', true));

        $this->assertSame($expectedSignature, $snapshot->getSignature());
    }

    public function testMetadataRetainsNumericDatasetKeys(): void
    {
        $this->auditService->method('recordEvent');

        $service = new OfflineSnapshotService('secret-key', 'key-1', $this->auditService, 300);

        $snapshot = $service->issueSnapshot([
            0 => ['lesson' => 'Chemistry'],
            1 => ['lesson' => 'Biology'],
        ], ['tenant_id' => 'tenant-10']);

        $metadata = $snapshot->getMetadata();

        $this->assertArrayHasKey('dataset_keys', $metadata);
        $this->assertSame([0, 1], $metadata['dataset_keys']);
    }

    public function testVerificationFailsWhenTenantIdIsMissing(): void
    {
        $service = new OfflineSnapshotService('secret-key', 'key-1', $this->auditService, 600);

        $events = [];
        $this->auditService
            ->expects($this->exactly(2))
            ->method('recordEvent')
            ->willReturnCallback(function (...$args) use (&$events) {
                $events[] = $args;

                return 0;
            });

        $snapshot = $service->issueSnapshot(['grades' => []], ['tenant_id' => 'tenant-2']);

        $this->assertFalse($service->verifySnapshot($snapshot, []));
        $this->assertCount(2, $events);
        $this->assertSame('snapshot_verification_failed', $events[1][1]);
        $this->assertSame('tenant_missing', $events[1][4]['reason']);
    }

    public function testVerificationFailsWhenTenantIdDoesNotMatchSnapshot(): void
    {
        $service = new OfflineSnapshotService('secret-key', 'key-1', $this->auditService, 600);

        $events = [];
        $this->auditService
            ->expects($this->exactly(2))
            ->method('recordEvent')
            ->willReturnCallback(function (...$args) use (&$events) {
                $events[] = $args;

                return 0;
            });

        $snapshot = $service->issueSnapshot(['grades' => []], ['tenant_id' => 'tenant-2']);

        $this->assertFalse($service->verifySnapshot($snapshot, ['tenant_id' => 'tenant-3']));
        $this->assertCount(2, $events);
        $this->assertSame('snapshot_verification_failed', $events[1][1]);
        $this->assertSame('tenant_mismatch', $events[1][4]['reason']);
    }
}
