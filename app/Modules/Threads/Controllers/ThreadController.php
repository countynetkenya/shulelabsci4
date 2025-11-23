<?php

namespace Modules\Threads\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;
use Modules\Foundation\Services\AuditService;
use Modules\Threads\Services\EventBus;
use Modules\Threads\Services\InMemoryThreadRepository;
use Modules\Threads\Services\ThreadRepositoryInterface;
use Modules\Threads\Services\ThreadService;
use Throwable;

class ThreadController extends ResourceController
{
    private ThreadService $threadService;

    public function __construct(?ThreadService $threadService = null)
    {
        $this->threadService = $threadService ?? $this->buildDefaultService();
    }

    public function index(): ResponseInterface
    {
        $filters = array_filter([
            'context_type' => $this->request->getGet('context_type'),
            'context_id'   => $this->request->getGet('context_id'),
            'is_cfr'       => $this->request->getGet('is_cfr'),
        ], static fn ($value) => $value !== null && $value !== '');

        $threads = $this->threadService->listThreads($filters);

        return $this->respond($threads);
    }

    public function create(): ResponseInterface
    {
        $payload = $this->request->getJSON(true) ?? [];
        $context = $this->buildContext();

        $thread = $this->threadService->createThread($payload, $context);

        return $this->respondCreated($thread->toArray());
    }

    public function postMessage(string $threadId): ResponseInterface
    {
        $payload = $this->request->getJSON(true) ?? [];
        $context = $this->buildContext();
        $message = (string) ($payload['message'] ?? '');

        $thread = $this->threadService->postMessage($threadId, $message, $context);

        return $this->respond($thread->toArray());
    }

    /**
     * @return array<string, mixed>
     */
    private function buildContext(): array
    {
        return [
            'tenant_id' => $this->request->getHeaderLine('X-Tenant-ID') ?: null,
            'actor_id'  => $this->request->getHeaderLine('X-Actor-ID') ?: null,
        ];
    }

    private function buildDefaultService(): ThreadService
    {
        try {
            $repository = service('threadsRepository');
        } catch (Throwable) {
            $repository = null;
        }
        if (!$repository instanceof ThreadRepositoryInterface) {
            $repository = new InMemoryThreadRepository();
        }

        try {
            $eventBus = service('threadsEventBus');
        } catch (Throwable) {
            $eventBus = null;
        }
        if (!$eventBus instanceof EventBus) {
            $eventBus = new EventBus();
        }

        return new ThreadService($repository, new AuditService(), $eventBus);
    }
}
