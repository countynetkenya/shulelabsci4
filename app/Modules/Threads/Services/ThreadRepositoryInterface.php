<?php

namespace Modules\Threads\Services;

use Modules\Threads\Domain\Thread;

interface ThreadRepositoryInterface
{
    public function save(Thread $thread): Thread;

    /**
     * @param array<string, mixed> $filters
     * @return list<Thread>
     */
    public function search(array $filters = []): array;

    public function find(string $threadId): ?Thread;
}
