<?php

declare(strict_types=1);

namespace Tests\Ci4\Foundation;

use Modules\Foundation\Services\AuditService;
use Modules\Foundation\Services\SoftDeleteManager;
use RuntimeException;

class SoftDeleteManagerTest extends FoundationDatabaseTestCase
{
    public function testSoftDeleteMarksRecordAndAudits(): void
    {
        $this->db->table('example_records')->insert([
            'name'       => 'Temporary Row',
            'deleted_at' => null,
            'deleted_by' => null,
            'delete_reason' => null,
            'updated_at' => null,
        ]);

        $manager = new SoftDeleteManager($this->db, new AuditService($this->db));

        $manager->softDelete('example_records', 1, ['school_id' => 5, 'actor_id' => 'user-77'], 'cleanup');

        $row = $this->db->table('example_records')->where('id', 1)->get()->getFirstRow('array');
        $this->assertNotNull($row['deleted_at']);
        $this->assertSame('user-77', $row['deleted_by']);
        $this->assertSame('cleanup', $row['delete_reason']);

        $audit = $this->db->table('audit_events')
            ->where('event_type', 'soft_delete')
            ->where('event_key', 'example_records:1')
            ->get()
            ->getFirstRow('array');

        $this->assertNotNull($audit);
    }

    public function testSoftDeleteThrowsWhenRecordMissing(): void
    {
        $manager = new SoftDeleteManager($this->db, new AuditService($this->db));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Record example_records::99 not found for soft delete.');

        $manager->softDelete('example_records', 99, ['actor_id' => 'user-1'], 'not found');
    }
}
