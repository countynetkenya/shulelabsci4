<?php

declare(strict_types=1);

namespace Tests\Ci4\Gamification;

use Modules\Foundation\Services\AuditService;
use Modules\Gamification\Services\GamificationService;
use Modules\Gamification\Services\LeaderboardRepositoryInterface;
use PHPUnit\Framework\TestCase;

class GamificationServiceTest extends TestCase
{
    public function testHandleRecognitionAwardedIncrementsPointsAndAwardsBadges(): void
    {
        $repository = $this->createMock(LeaderboardRepositoryInterface::class);
        $audit      = $this->createMock(AuditService::class);

        $service = new GamificationService($repository, $audit);

        $repository
            ->expects($this->once())
            ->method('incrementPoints')
            ->with('student-1', 60, $this->arrayHasKey('house_id'))
            ->willReturn(120);

        $awards = [];
        $repository
            ->expects($this->exactly(2))
            ->method('awardBadge')
            ->willReturnCallback(function (string $recipientId, string $badgeKey, array $metadata) use (&$awards): void {
                $awards[] = [$recipientId, $badgeKey, $metadata];
            });

        $audit
            ->expects($this->once())
            ->method('recordEvent')
            ->with(
                'gamification.recognition.student-1',
                'recognition_awarded',
                $this->arrayHasKey('actor_id'),
                null,
                $this->arrayHasKey('badges')
            );

        $result = $service->handleRecognitionAwarded([
            'recipient_id' => 'student-1',
            'points'       => 60,
            'house_id'     => 'blue',
        ], [
            'actor_id' => 'coach-7',
        ]);

        $this->assertSame(120, $result['newTotal']);
        $this->assertSame(['starter', 'centurion'], $result['badgesIssued']);
        $this->assertSame(
            [
                ['student-1', 'starter', ['thread_id' => null, 'badge_set' => 'default', 'house_id' => 'blue']],
                ['student-1', 'centurion', ['thread_id' => null, 'badge_set' => 'default', 'house_id' => 'blue']],
            ],
            $awards
        );
    }
}
