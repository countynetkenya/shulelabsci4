<?php

declare(strict_types=1);

namespace Tests\Ci4\Learning;

use Modules\Foundation\Services\AuditService;
use Modules\Foundation\Services\IntegrationRegistry;
use Modules\Learning\Services\MoodleClientInterface;
use Modules\Learning\Services\MoodleDispatchRunner;
use RuntimeException;
use Tests\Ci4\Foundation\FoundationDatabaseTestCase;

class MoodleDispatchRunnerTest extends FoundationDatabaseTestCase
{
    private IntegrationRegistry $registry;

    protected function setUp(): void
    {
        parent::setUp();

        $this->registry = new IntegrationRegistry($this->db, new AuditService($this->db));
    }

    public function testRunGradesProcessesQueuedDispatch(): void
    {
        $dispatch = $this->registry->registerDispatch(
            'moodle.push_grades',
            'grades-1',
            [
                'course' => ['id' => 'BIO-1', 'name' => 'Biology'],
                'grades' => [['user_id' => 'student-1', 'grade' => 80]],
            ],
            ['tenant_id' => 'tenant-1']
        );

        $client = $this->createMock(MoodleClientInterface::class);
        $client->expects($this->once())
            ->method('pushGrades')
            ->with($this->arrayHasKey('course'))
            ->willReturn(['status' => 'ok']);

        $runner = new MoodleDispatchRunner($this->registry, $client, 600);
        $summary = $runner->runGrades();

        $this->assertSame(1, $summary['dispatched']);
        $this->assertSame(1, $summary['completed']);
        $row = $this->db->table('integration_dispatches')->where('id', $dispatch['id'])->get()->getFirstRow('array');
        $this->assertSame('completed', $row['status']);
    }

    public function testRunGradesMarksFailedOnException(): void
    {
        $dispatch = $this->registry->registerDispatch(
            'moodle.push_grades',
            'grades-2',
            [
                'course' => ['id' => 'BIO-2', 'name' => 'Biology'],
                'grades' => [['user_id' => 'student-7', 'grade' => 70]],
            ],
            ['tenant_id' => 'tenant-3']
        );

        $client = $this->createMock(MoodleClientInterface::class);
        $client->expects($this->once())
            ->method('pushGrades')
            ->willThrowException(new RuntimeException('API down'));

        $runner = new MoodleDispatchRunner($this->registry, $client, 300);
        $summary = $runner->runGrades();

        $this->assertSame(1, $summary['failed']);
        $this->assertSame(1, count($summary['errors']));

        $row = $this->db->table('integration_dispatches')->where('id', $dispatch['id'])->get()->getFirstRow('array');
        $this->assertSame('failed', $row['status']);
        $this->assertSame('API down', $row['error_message']);
        $this->assertSame(300, (int) $row['retry_after']);
    }

    public function testRunEnrollmentsProcessesQueuedDispatch(): void
    {
        $dispatch = $this->registry->registerDispatch(
            'moodle.sync_enrollments',
            'enroll-1',
            [
                'course'      => ['id' => 'HIST-1', 'name' => 'History'],
                'enrollments' => [['user_id' => 'teacher-1', 'role' => 'teacher']],
            ],
            ['tenant_id' => 'tenant-5']
        );

        $client = $this->createMock(MoodleClientInterface::class);
        $client->expects($this->once())
            ->method('syncEnrollments')
            ->with($this->arrayHasKey('enrollments'))
            ->willReturn(['status' => 'ok']);

        $runner = new MoodleDispatchRunner($this->registry, $client, 600);
        $summary = $runner->runEnrollments();

        $this->assertSame(1, $summary['completed']);
        $row = $this->db->table('integration_dispatches')->where('id', $dispatch['id'])->get()->getFirstRow('array');
        $this->assertSame('completed', $row['status']);
    }
}
