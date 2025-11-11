<?php

namespace Modules\Inventory\Services;

use Modules\Inventory\Domain\Transfer;

/**
 * Simple in-memory repository useful for bootstrapping tests and CLI demos.
 */
class InMemoryTransferRepository implements TransferRepositoryInterface
{
    /**
     * @var array<string, Transfer>
     */
    private array $store = [];

    public function store(Transfer $transfer): Transfer
    {
        $this->store[$transfer->getId()] = $transfer;

        return $transfer;
    }

    public function find(string $transferId): ?Transfer
    {
        return $this->store[$transferId] ?? null;
    }

    public function update(Transfer $transfer, array $metadata = []): Transfer
    {
        $this->store[$transfer->getId()] = $transfer;

        return $transfer;
    }
}
