<?php

declare(strict_types=1);

namespace Tests\Ci4\Foundation;

use CodeIgniter\I18n\Time;
use Modules\Foundation\Services\AuditService;

class AuditServiceTest extends FoundationDatabaseTestCase
{
    public function testRecordEventPersistsHashChain(): void
    {
        $service = new AuditService($this->db);

        $firstId = $service->recordEvent(
            eventKey: 'finance.invoice.1001',
            eventType: 'created',
            context: ['school_id' => 1, 'actor_id' => 'user-1'],
            before: null,
            after: ['invoice' => '1001'],
            metadata: ['ip' => '10.0.0.1']
        );

        $secondId = $service->recordEvent(
            eventKey: 'finance.invoice.1001',
            eventType: 'updated',
            context: ['school_id' => 1, 'actor_id' => 'user-2'],
            before: ['status' => 'draft'],
            after: ['status' => 'approved'],
            metadata: ['ip' => '10.0.0.2']
        );

        $this->assertNotSame($firstId, $secondId);

        $events = $this->db->table('audit_events')->orderBy('id', 'ASC')->get()->getResultArray();
        $this->assertCount(2, $events);
        $this->assertSame($events[0]['hash_value'], $events[1]['previous_hash']);

        $this->assertTrue($service->verifyIntegrity());
    }

    public function testVerifyIntegrityDetectsTampering(): void
    {
        $service = new AuditService($this->db);

        $eventId = $service->recordEvent(
            eventKey: 'inventory.transfer.1',
            eventType: 'initiated',
            context: ['school_id' => 7],
            before: null,
            after: ['transfer_id' => '1']
        );

        $this->db->table('audit_events')->where('id', $eventId)->update(['hash_value' => 'tampered']);

        $this->assertFalse($service->verifyIntegrity());
    }

    public function testSealDayPersistsSealRecord(): void
    {
        $service = new AuditService($this->db);
        $service->recordEvent(
            eventKey: 'threads.message.1',
            eventType: 'posted',
            context: ['tenant_id' => 'tenant-55'],
            before: null,
            after: ['message' => 'hello world']
        );

        $service->sealDay();

        $sealDate = Time::today('UTC')->toDateString();
        $seal = $this->db->table('audit_seals')->where('seal_date', $sealDate)->get()->getFirstRow('array');

        $this->assertNotNull($seal);
        $this->assertNotEmpty($seal['hash_value']);
    }
}
