<?php

declare(strict_types=1);

namespace Tests\Ci4\Foundation;

use Modules\Foundation\Services\AuditService;
use Modules\Foundation\Services\MakerCheckerService;
use RuntimeException;

class MakerCheckerServiceTest extends FoundationDatabaseTestCase
{
    public function testSubmitCreatesPendingRequestAndAudit(): void
    {
        $service = new MakerCheckerService($this->db, new AuditService($this->db));

        $requestId = $service->submit('finance.payment.release', ['payment_id' => 'PAY-1'], [
            'school_id' => 21,
            'actor_id'  => 'maker-1',
        ]);

        $this->assertGreaterThan(0, $requestId);

        $row = $this->db->table('maker_checker_requests')->where('id', $requestId)->get()->getFirstRow('array');
        $this->assertSame('pending', $row['status']);
        $this->assertSame('maker-1', $row['maker_id']);

        $audit = $this->db->table('audit_events')
            ->where('event_type', 'maker_request_submitted')
            ->where('event_key', sprintf('maker_checker:%s', $requestId))
            ->get()
            ->getFirstRow('array');

        $this->assertNotNull($audit);
    }

    public function testApproveUpdatesStatusAndAudit(): void
    {
        $service = new MakerCheckerService($this->db, new AuditService($this->db));
        $requestId = $service->submit('inventory.transfer.approve', ['transfer_id' => 'TR-1'], ['school_id' => 30]);

        $service->approve($requestId, ['actor_id' => 'checker-1']);

        $row = $this->db->table('maker_checker_requests')->where('id', $requestId)->get()->getFirstRow('array');
        $this->assertSame('approved', $row['status']);
        $this->assertSame('checker-1', $row['checker_id']);

        $audit = $this->db->table('audit_events')
            ->where('event_type', 'maker_request_approved')
            ->where('event_key', sprintf('maker_checker:%s', $requestId))
            ->get()
            ->getFirstRow('array');

        $this->assertNotNull($audit);
    }

    public function testRejectUpdatesStatusAndAudit(): void
    {
        $service = new MakerCheckerService($this->db, new AuditService($this->db));
        $requestId = $service->submit('finance.refund.release', ['refund_id' => 'RF-1'], ['school_id' => 31]);

        $service->reject($requestId, ['actor_id' => 'checker-2'], 'Amount mismatch');

        $row = $this->db->table('maker_checker_requests')->where('id', $requestId)->get()->getFirstRow('array');
        $this->assertSame('rejected', $row['status']);
        $this->assertSame('checker-2', $row['checker_id']);
        $this->assertSame('Amount mismatch', $row['rejection_reason']);

        $audit = $this->db->table('audit_events')
            ->where('event_type', 'maker_request_rejected')
            ->where('event_key', sprintf('maker_checker:%s', $requestId))
            ->get()
            ->getFirstRow('array');

        $this->assertNotNull($audit);
    }

    public function testApproveThrowsWhenRequestMissing(): void
    {
        $service = new MakerCheckerService($this->db, new AuditService($this->db));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Maker checker request not found.');

        $service->approve(999, ['actor_id' => 'checker']);
    }
}
