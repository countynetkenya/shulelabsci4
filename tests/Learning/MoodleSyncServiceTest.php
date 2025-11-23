<?php

declare(strict_types=1);

namespace Tests\Ci4\Learning;

use Modules\Foundation\Services\AuditService;
use Modules\Foundation\Services\IntegrationRegistry;
use Modules\Learning\Services\MoodleClientInterface;
use Modules\Learning\Services\MoodleSyncService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class MoodleSyncServiceTest extends TestCase
{
    private MoodleClientInterface&MockObject $client;

    private IntegrationRegistry&MockObject $registry;

    private AuditService&MockObject $auditService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = $this->createMock(MoodleClientInterface::class);
        $this->registry = $this->createMock(IntegrationRegistry::class);
        $this->auditService = $this->createMock(AuditService::class);
    }

    public function testPushGradesRegistersDispatchAndMarksCompleted(): void
    {
        $service = new MoodleSyncService($this->client, $this->registry, $this->auditService);

        $this->registry
            ->expects($this->once())
            ->method('registerDispatch')
            ->with(
                'moodle.push_grades',
                $this->isType('string'),
                $this->callback(function (array $payload): bool {
                    $this->assertSame('BIO-101', $payload['course']['id']);
                    $this->assertCount(2, $payload['grades']);

                    return true;
                }),
                $this->arrayHasKey('tenant_id')
            )
            ->willReturn(['id' => 33]);

        $this->client
            ->expects($this->once())
            ->method('pushGrades')
            ->willReturn(['status' => 'ok']);

        $this->registry
            ->expects($this->once())
            ->method('markCompleted')
            ->with(33, $this->arrayHasKey('tenant_id'), ['status' => 'ok']);

        $this->auditService
            ->expects($this->once())
            ->method('recordEvent')
            ->with(
                'learning.moodle.course.BIO-101',
                'grades_pushed',
                $this->arrayHasKey('tenant_id'),
                null,
                $this->arrayHasKey('response'),
                $this->arrayHasKey('dispatch_id')
            );

        $response = $service->pushGrades(
            course: ['id' => 'BIO-101', 'name' => 'Biology'],
            grades: [
                ['user_id' => 'student-1', 'grade' => 88.5],
                ['user_id' => 'student-2', 'grade' => 74.0],
            ],
            context: ['tenant_id' => 'tenant-1']
        );

        $this->assertSame(['status' => 'ok'], $response);
    }

    public function testPushGradesMarksFailureWhenClientThrows(): void
    {
        $service = new MoodleSyncService($this->client, $this->registry, $this->auditService);

        $this->registry
            ->expects($this->once())
            ->method('registerDispatch')
            ->willReturn(['id' => 44]);

        $this->client
            ->expects($this->once())
            ->method('pushGrades')
            ->willThrowException(new RuntimeException('API unreachable'));

        $this->registry
            ->expects($this->once())
            ->method('markFailed')
            ->with(44, $this->arrayHasKey('tenant_id'), 'API unreachable');

        $this->auditService
            ->expects($this->never())
            ->method('recordEvent');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to push grades to Moodle.');

        $service->pushGrades(
            ['id' => 'CHEM-2', 'name' => 'Chemistry'],
            [
                ['user_id' => 'student-7', 'grade' => 60],
            ],
            ['tenant_id' => 'tenant-77']
        );
    }

    public function testSyncEnrollmentsUsesIntegrationRegistry(): void
    {
        $service = new MoodleSyncService($this->client, $this->registry, $this->auditService);

        $this->registry
            ->expects($this->once())
            ->method('registerDispatch')
            ->with(
                'moodle.sync_enrollments',
                $this->isType('string'),
                $this->callback(function (array $payload): bool {
                    $this->assertCount(1, $payload['enrollments']);
                    $this->assertSame('teacher', $payload['enrollments'][0]['role']);

                    return true;
                }),
                $this->arrayHasKey('tenant_id')
            )
            ->willReturn(['id' => 55]);

        $this->client
            ->expects($this->once())
            ->method('syncEnrollments')
            ->willReturn(['enrolled' => 1]);

        $this->registry
            ->expects($this->once())
            ->method('markCompleted')
            ->with(55, $this->arrayHasKey('tenant_id'), ['enrolled' => 1]);

        $this->auditService
            ->expects($this->once())
            ->method('recordEvent')
            ->with(
                'learning.moodle.course.HIST-12',
                'enrollments_synced',
                $this->arrayHasKey('tenant_id'),
                null,
                $this->arrayHasKey('enrollments'),
                $this->arrayHasKey('dispatch_id')
            );

        $result = $service->syncEnrollments(
            ['id' => 'HIST-12', 'name' => 'History'],
            [['user_id' => 'teacher-9', 'role' => 'teacher']],
            ['tenant_id' => 'tenant-3']
        );

        $this->assertSame(['enrolled' => 1], $result);
    }

    public function testPushGradesUsesStableIdempotencyKey(): void
    {
        $service = new MoodleSyncService($this->client, $this->registry, $this->auditService);

        $capturedKeys = [];

        $this->registry
            ->expects($this->exactly(2))
            ->method('registerDispatch')
            ->willReturnCallback(function (string $event, string $key, array $payload, array $context) use (&$capturedKeys) {
                $this->assertSame('moodle.push_grades', $event);
                $this->assertArrayHasKey('dispatched_at', $payload);
                $capturedKeys[] = $key;

                return ['id' => count($capturedKeys)];
            });

        $this->client
            ->expects($this->exactly(2))
            ->method('pushGrades')
            ->willReturn(['status' => 'ok']);

        $this->registry
            ->expects($this->exactly(2))
            ->method('markCompleted');

        $this->auditService
            ->expects($this->exactly(2))
            ->method('recordEvent');

        $course = ['id' => 'BIO-101', 'name' => 'Biology'];
        $grades = [
            ['user_id' => 'student-1', 'grade' => 88.5],
            ['user_id' => 'student-2', 'grade' => 74.0],
        ];
        $context = ['tenant_id' => 'tenant-1'];

        $service->pushGrades($course, $grades, $context);
        $service->pushGrades($course, $grades, $context);

        $this->assertCount(2, $capturedKeys);
        $this->assertSame($capturedKeys[0], $capturedKeys[1]);
    }
}
