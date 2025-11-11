<?php

namespace Modules\Gamification\Services;

interface LeaderboardRepositoryInterface
{
    /**
     * @param array<string, mixed> $metadata
     */
    public function incrementPoints(string $recipientId, int $points, array $metadata = []): int;

    /**
     * @param array<string, mixed> $metadata
     */
    public function awardBadge(string $recipientId, string $badgeKey, array $metadata = []): void;
}
