# Gamification Module

The Gamification module tracks points, badges, and leaderboards driven by
recognition events emitted from Threads and CFR activity.

## Capabilities

- Listen to `Recognition.Awarded` events and increment recipient points.
- Unlock milestone badges at configured thresholds.
- Persist audit trails for every point or badge update via the shared
  `AuditService`.

## Key Services

- `Services\GamificationService` – core orchestrator that responds to
  recognition events.
- `Services\LeaderboardRepositoryInterface` – contract for persisting point
  totals and badge unlocks.
- `Listeners\RecognitionAwardedListener` – glue listener attaching the service
  to the Threads event bus.

## Usage

Register the listener with the event bus at boot time:

```php
use Modules\Gamification\Config\Bootstrap as GamificationBootstrap;
use Modules\Gamification\Services\GamificationService;
use Modules\Threads\Services\EventBus;

$eventBus = new EventBus();
$service  = new GamificationService($repository, $auditService);
GamificationBootstrap::register($eventBus, $service);
```

Use a concrete `LeaderboardRepositoryInterface` implementation to store points
and badges in your preferred database.
