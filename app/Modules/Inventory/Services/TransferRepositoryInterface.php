<?php

namespace Modules\Inventory\Services;

use Modules\Inventory\Domain\Transfer;

/**
 * Persistence contract for inventory transfer requests.
 */
interface TransferRepositoryInterface
{
    public function store(Transfer $transfer): Transfer;

    public function find(string $transferId): ?Transfer;

    /**
     * @param array<string, mixed> $metadata
     */
    public function update(Transfer $transfer, array $metadata = []): Transfer;
}
