<?php

namespace Modules\Gamification\Listeners;

use Modules\Gamification\Services\GamificationService;

class RecognitionAwardedListener
{
    public function __construct(private readonly GamificationService $service)
    {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function __invoke(array $payload): void
    {
        $context = [
            'tenant_id' => $payload['tenant_id'] ?? null,
            'actor_id'  => $payload['actor_id'] ?? null,
        ];

        $this->service->handleRecognitionAwarded($payload, $context);
    }
}
