<?php

namespace Modules\Threads\Services;

use InvalidArgumentException;
use Modules\Foundation\Services\AuditService;
use Modules\Threads\Domain\Thread;
use RuntimeException;

class ThreadService
{
    public function __construct(
        private readonly ThreadRepositoryInterface $repository,
        private readonly AuditService $auditService,
        private readonly EventBus $eventBus
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $context
     */
    public function createThread(array $payload, array $context): Thread
    {
        $subject = trim((string) ($payload['subject'] ?? ''));
        $contextType = trim((string) ($payload['context_type'] ?? ''));
        $contextId = trim((string) ($payload['context_id'] ?? ''));
        $isCfr = (bool) ($payload['is_cfr'] ?? false);
        $message = $payload['message'] ?? null;

        if ($subject === '' || $contextType === '' || $contextId === '') {
            throw new InvalidArgumentException('Subject, context_type, and context_id are required.');
        }

        $thread = new Thread($subject, $contextType, $contextId, $isCfr);

        if (is_string($message) && $message !== '') {
            $thread->addMessage($context['actor_id'] ?? 'system', $message);
        }

        $saved = $this->repository->save($thread);

        $this->auditService->recordEvent(
            eventKey: sprintf('threads.thread.%s', $saved->getId()),
            eventType: 'thread_created',
            context: $context,
            before: null,
            after: $saved->toArray()
        );

        $this->eventBus->dispatch('Threads.Created', [
            'thread_id'    => $saved->getId(),
            'context_type' => $saved->getContextType(),
            'context_id'   => $saved->getContextId(),
            'is_cfr'       => $saved->isCfr(),
        ]);

        return $saved;
    }

    /**
     * @param array<string, mixed> $context
     */
    public function postMessage(string $threadId, string $message, array $context): Thread
    {
        $thread = $this->repository->find($threadId);
        if ($thread === null) {
            throw new RuntimeException('Thread not found.');
        }

        $author = $context['actor_id'] ?? 'system';
        if ($message === '') {
            throw new InvalidArgumentException('Message body cannot be empty.');
        }

        $thread->addMessage($author, $message);
        $updated = $this->repository->save($thread);

        $this->auditService->recordEvent(
            eventKey: sprintf('threads.thread.%s', $threadId),
            eventType: 'thread_message_posted',
            context: $context,
            before: null,
            after: ['author' => $author, 'message' => $message]
        );

        $this->eventBus->dispatch('Threads.MessagePosted', [
            'thread_id' => $threadId,
            'author'    => $author,
        ]);

        return $updated;
    }

    /**
     * @param array<string, mixed> $filters
     * @return list<array<string, mixed>>
     */
    public function listThreads(array $filters = []): array
    {
        return array_map(
            static fn (Thread $thread): array => $thread->toArray(),
            $this->repository->search($filters)
        );
    }
}
