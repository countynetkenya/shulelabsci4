<?php

namespace Modules\Gamification\Services;

use InvalidArgumentException;
use Modules\Foundation\Services\AuditService;

class GamificationService
{
    public function __construct(
        private readonly LeaderboardRepositoryInterface $leaderboard,
        private readonly AuditService $auditService
    ) {
    }

    /**
     * @param array<string, mixed> $event
     * @param array<string, mixed> $context
     *
     * @return array<string, mixed>
     */
    public function handleRecognitionAwarded(array $event, array $context = []): array
    {
        $recipientId = (string) ($event['recipient_id'] ?? '');
        $points = (int) ($event['points'] ?? 0);

        if ($recipientId === '' || $points <= 0) {
            throw new InvalidArgumentException('Recognition events require a recipient_id and positive points.');
        }

        $metadata = [
            'thread_id' => $event['thread_id'] ?? null,
            'badge_set' => $event['badge_set'] ?? 'default',
            'house_id'  => $event['house_id'] ?? null,
        ];

        $newTotal = $this->leaderboard->incrementPoints($recipientId, $points, $metadata);
        $badges = $this->evaluateBadgeUnlocks($newTotal);

        foreach ($badges as $badge) {
            $this->leaderboard->awardBadge($recipientId, $badge, $metadata);
        }

        $this->auditService->recordEvent(
            eventKey: sprintf('gamification.recognition.%s', $recipientId),
            eventType: 'recognition_awarded',
            context: $context,
            before: null,
            after: [
                'points_awarded' => $points,
                'new_total'      => $newTotal,
                'badges'         => $badges,
            ]
        );

        return [
            'recipientId'   => $recipientId,
            'pointsAwarded' => $points,
            'newTotal'      => $newTotal,
            'badgesIssued'  => $badges,
        ];
    }

    /**
     * @return list<string>
     */
    private function evaluateBadgeUnlocks(int $newTotal): array
    {
        $badges = [];

        if ($newTotal >= 50) {
            $badges[] = 'starter';
        }

        if ($newTotal >= 100) {
            $badges[] = 'centurion';
        }

        if ($newTotal >= 250) {
            $badges[] = 'champion';
        }

        return array_values(array_unique($badges));
    }
}
