<?php

declare(strict_types=1);

namespace Tests\Ci4\Foundation;

use Modules\Foundation\Services\AuditService;
use Modules\Foundation\Services\IntegrationRegistry;
use RuntimeException;

class IntegrationRegistryTest extends FoundationDatabaseTestCase
{
    public function testRegisterDispatchPersistsAndEnforcesIdempotency(): void
    {
        $service = new IntegrationRegistry($this->db, new AuditService($this->db));

        $first = $service->registerDispatch(
            channel: 'quickbooks',
            idempotencyKey: 'sync-1',
            payload: ['invoice' => 'INV-1'],
            context: ['school_id' => 10]
        );

        $this->assertSame('queued', $first['status']);

        $second = $service->registerDispatch(
            channel: 'quickbooks',
            idempotencyKey: 'sync-1',
            payload: ['invoice' => 'INV-1'],
            context: ['school_id' => 10]
        );

        $this->assertSame($first['id'], $second['id']);
        $this->assertSame(1, $this->db->table('integration_dispatches')->countAllResults());
    }

    public function testMarkCompletedUpdatesStatusAndAudit(): void
    {
        $service = new IntegrationRegistry($this->db, new AuditService($this->db));

        $dispatch = $service->registerDispatch('mpesa', 'push-1', ['amount' => '100.00'], ['school_id' => 11]);

        $service->markCompleted($dispatch['id'], ['school_id' => 11], ['status' => 'success']);

        $row = $this->db->table('integration_dispatches')->where('id', $dispatch['id'])->get()->getFirstRow('array');
        $this->assertSame('completed', $row['status']);
        $this->assertNotNull($row['completed_at']);

        $audit = $this->db->table('audit_events')
            ->where('event_type', 'integration_dispatch_completed')
            ->where('event_key', sprintf('integration:%s:%s', $row['channel'], $dispatch['id']))
            ->get()
            ->getFirstRow('array');

        $this->assertNotNull($audit);
    }

    public function testMarkFailedUpdatesStatusAndAudit(): void
    {
        $service = new IntegrationRegistry($this->db, new AuditService($this->db));
        $dispatch = $service->registerDispatch('mpesa', 'push-2', ['amount' => '200.00'], ['school_id' => 12]);

        $service->markFailed($dispatch['id'], ['school_id' => 12], 'Timeout talking to MPESA', 60);

        $row = $this->db->table('integration_dispatches')->where('id', $dispatch['id'])->get()->getFirstRow('array');
        $this->assertSame('failed', $row['status']);
        $this->assertSame('Timeout talking to MPESA', $row['error_message']);
        $this->assertSame(60, (int) $row['retry_after']);

        $audit = $this->db->table('audit_events')
            ->where('event_type', 'integration_dispatch_failed')
            ->where('event_key', sprintf('integration:%s:%s', $row['channel'], $dispatch['id']))
            ->get()
            ->getFirstRow('array');

        $this->assertNotNull($audit);
    }

    public function testMarkCompletedThrowsWhenDispatchMissing(): void
    {
        $service = new IntegrationRegistry($this->db, new AuditService($this->db));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Integration dispatch not found.');

        $service->markCompleted(404, ['school_id' => 404], ['status' => 'missing']);
    }

    public function testClaimPendingDispatchesReturnsProcessingRows(): void
    {
        $service = new IntegrationRegistry($this->db, new AuditService($this->db));

        $queued = $service->registerDispatch('moodle.push_grades', 'grades-claim', ['payload' => 1], ['school_id' => 50]);
        $service->registerDispatch('moodle.push_grades', 'grades-wait', ['payload' => 2], ['school_id' => 50]);
        $failed = $service->registerDispatch('moodle.push_grades', 'grades-failed', ['payload' => 3], ['school_id' => 50]);
        $service->markFailed($failed['id'], ['school_id' => 50], 'temporary', 3600);

        $claimed = $service->claimPendingDispatches('moodle.push_grades', 1);

        $this->assertCount(1, $claimed);
        $this->assertSame($queued['id'], $claimed[0]['id']);

        $row = $this->db->table('integration_dispatches')->where('id', $queued['id'])->get()->getFirstRow('array');
        $this->assertSame('processing', $row['status']);

        $waitingRow = $this->db->table('integration_dispatches')->where('id', $failed['id'])->get()->getFirstRow('array');
        $this->assertSame('failed', $waitingRow['status']);
    }
}
