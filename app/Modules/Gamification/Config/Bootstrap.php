<?php

namespace Modules\Gamification\Config;

use Modules\Gamification\Listeners\RecognitionAwardedListener;
use Modules\Gamification\Services\GamificationService;
use Modules\Threads\Services\EventBus;

class Bootstrap
{
    public static function register(EventBus $eventBus, GamificationService $service): void
    {
        $eventBus->subscribe('Recognition.Awarded', new RecognitionAwardedListener($service));
    }
}
