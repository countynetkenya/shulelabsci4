<?php

declare(strict_types=1);

namespace Tests\Ci4\Threads;

use Modules\Foundation\Services\AuditService;
use Modules\Threads\Domain\Thread;
use Modules\Threads\Services\EventBus;
use Modules\Threads\Services\ThreadRepositoryInterface;
use Modules\Threads\Services\ThreadService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ThreadServiceTest extends TestCase
{
    private ThreadRepositoryInterface&MockObject $repository;

    private AuditService&MockObject $audit;

    private EventBus&MockObject $eventBus;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->createMock(ThreadRepositoryInterface::class);
        $this->audit = $this->createMock(AuditService::class);
        $this->eventBus = $this->createMock(EventBus::class);
    }

    public function testCreateThreadPersistsDispatchesAndAudits(): void
    {
        $service = new ThreadService($this->repository, $this->audit, $this->eventBus);

        $this->repository
            ->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(Thread::class))
            ->willReturnCallback(static fn (Thread $thread): Thread => $thread);

        $this->audit
            ->expects($this->once())
            ->method('recordEvent')
            ->with(
                $this->stringStartsWith('threads.thread.'),
                'thread_created',
                $this->arrayHasKey('actor_id'),
                null,
                $this->isType('array')
            );

        $this->eventBus
            ->expects($this->once())
            ->method('dispatch')
            ->with('Threads.Created', $this->arrayHasKey('thread_id'));

        $thread = $service->createThread([
            'subject'      => 'Celebrate win',
            'context_type' => 'recognition',
            'context_id'   => 'rec-1',
            'message'      => 'Great job team!',
        ], [
            'actor_id' => 'user-1',
        ]);

        $this->assertSame('recognition', $thread->getContextType());
        $this->assertCount(1, $thread->getMessages());
    }

    public function testPostMessageAppendsMessageAndDispatchesEvent(): void
    {
        $service = new ThreadService($this->repository, $this->audit, $this->eventBus);

        $existing = new Thread('Existing', 'okr', 'okr-1', true, 'thread-55');
        $this->repository
            ->expects($this->once())
            ->method('find')
            ->with('thread-55')
            ->willReturn($existing);

        $this->repository
            ->expects($this->once())
            ->method('save')
            ->with($existing)
            ->willReturn($existing);

        $this->audit
            ->expects($this->once())
            ->method('recordEvent')
            ->with(
                'threads.thread.thread-55',
                'thread_message_posted',
                $this->arrayHasKey('actor_id'),
                null,
                $this->arrayHasKey('message')
            );

        $this->eventBus
            ->expects($this->once())
            ->method('dispatch')
            ->with('Threads.MessagePosted', $this->arrayHasKey('thread_id'));

        $result = $service->postMessage('thread-55', 'We hit the milestone!', [
            'actor_id' => 'coach-7',
        ]);

        $this->assertCount(1, $result->getMessages());
        $this->assertSame('coach-7', $result->getMessages()[0]['author']);
    }
}
