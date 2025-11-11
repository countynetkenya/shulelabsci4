<?php

declare(strict_types=1);

namespace Tests\Ci4\Threads;

use Modules\Threads\Services\EventBus;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class EventBusTest extends TestCase
{
    public function testDispatchInvokesAllSubscribersAndCapturesFailures(): void
    {
        $bus = new EventBus();

        $calls = [];
        $bus->subscribe('Threads.Created', static function (array $payload) use (&$calls): void {
            $calls[] = $payload['thread_id'];
        });

        $bus->subscribe('Threads.Created', static function (): void {
            throw new RuntimeException('listener failed');
        });

        $result = $bus->dispatch('Threads.Created', ['thread_id' => 'thread-1']);

        $this->assertSame(['thread-1'], $calls);
        $this->assertFalse($result->isSuccessful());
        $this->assertSame(['Threads.Created#1' => 'listener failed'], $result->failedListeners());
        $this->assertSame(['Threads.Created#0'], $result->successfulListeners());
    }
}
