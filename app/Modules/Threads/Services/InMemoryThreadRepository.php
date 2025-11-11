<?php

namespace Modules\Threads\Services;

use Modules\Threads\Domain\Thread;

class InMemoryThreadRepository implements ThreadRepositoryInterface
{
    /**
     * @var array<string, Thread>
     */
    private array $threads = [];

    public function save(Thread $thread): Thread
    {
        $this->threads[$thread->getId()] = $thread;

        return $thread;
    }

    public function search(array $filters = []): array
    {
        $result = $this->threads;

        if (isset($filters['context_type'])) {
            $result = array_filter($result, static fn (Thread $thread): bool => $thread->getContextType() === $filters['context_type']);
        }

        if (isset($filters['context_id'])) {
            $result = array_filter($result, static fn (Thread $thread): bool => $thread->getContextId() === $filters['context_id']);
        }

        if (array_key_exists('is_cfr', $filters)) {
            $isCfr = (bool) $filters['is_cfr'];
            $result = array_filter($result, static fn (Thread $thread): bool => $thread->isCfr() === $isCfr);
        }

        return array_values($result);
    }

    public function find(string $threadId): ?Thread
    {
        return $this->threads[$threadId] ?? null;
    }
}
