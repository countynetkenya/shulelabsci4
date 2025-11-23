<?php

namespace Modules\Threads\Services;

use Closure;
use Throwable;

/**
 * Lightweight synchronous event bus for propagating engagement signals.
 */
class EventBus
{
    /**
     * @var array<string, list<callable(array<string, mixed>): void>>
     */
    private array $listeners = [];

    public function subscribe(string $eventName, callable $listener): void
    {
        if ($listener instanceof Closure) {
            $this->listeners[$eventName][] = $listener;
        } else {
            $this->listeners[$eventName][] = Closure::fromCallable($listener);
        }
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function dispatch(string $eventName, array $payload = []): EventDispatchResult
    {
        $successful = [];
        $failures = [];

        foreach ($this->listeners[$eventName] ?? [] as $index => $listener) {
            $identifier = sprintf('%s#%d', $eventName, $index);

            try {
                $listener($payload);
                $successful[] = $identifier;
            } catch (Throwable $exception) {
                $failures[$identifier] = $exception->getMessage();
            }
        }

        return new EventDispatchResult($successful, $failures);
    }
}
