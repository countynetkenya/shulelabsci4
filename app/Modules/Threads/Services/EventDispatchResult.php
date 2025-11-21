<?php

namespace Modules\Threads\Services;

/**
 * Immutable view of dispatch outcomes for each subscriber.
 */
class EventDispatchResult
{
    /**
     * @param list<string> $successful
     * @param array<string, string> $failures
     */
    public function __construct(
        private readonly array $successful,
        private readonly array $failures
    ) {
    }

    /**
     * @return list<string>
     */
    public function successfulListeners(): array
    {
        return $this->successful;
    }

    /**
     * @return array<string, string>
     */
    public function failedListeners(): array
    {
        return $this->failures;
    }

    public function isSuccessful(): bool
    {
        return $this->failures === [];
    }
}
