<?php

namespace Modules\Inventory\Services;

use InvalidArgumentException;
use Modules\Foundation\Services\AuditService;
use Modules\Foundation\Services\MakerCheckerService;
use Modules\Foundation\Services\QrService;
use Modules\Inventory\Domain\Transfer;
use RuntimeException;

/**
 * Coordinates QR-enabled warehouse transfer workflows with maker-checker approvals.
 */
class TransferService
{
    public function __construct(
        private readonly TransferRepositoryInterface $repository,
        private readonly QrService $qrService,
        private readonly AuditService $auditService,
        private readonly MakerCheckerService $makerChecker
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $context
     */
    public function initiateTransfer(array $payload, array $context): Transfer
    {
        $source = (string) ($payload['source_warehouse_id'] ?? '');
        $target = (string) ($payload['target_warehouse_id'] ?? '');
        $items = $payload['items'] ?? null;

        if ($source === '' || $target === '') {
            throw new InvalidArgumentException('Source and target warehouse IDs are required.');
        }

        if ($source === $target) {
            throw new InvalidArgumentException('Source and target warehouses must be different.');
        }

        if (!is_array($items) || $items === []) {
            throw new InvalidArgumentException('At least one transfer item is required.');
        }

        $normalisedItems = $this->normaliseItems($items);
        $transfer = new Transfer($source, $target, $normalisedItems);

        $approvalId = $this->makerChecker->submit(
            actionKey: 'inventory.transfer',
            payload: [
                'transfer_id' => $transfer->getId(),
                'source'      => $source,
                'target'      => $target,
                'items'       => $normalisedItems,
            ],
            context: $context
        );

        $transfer->setApprovalRequestId($approvalId);

        $qr = $this->qrService->issueToken(
            resourceType: 'inventory_transfer',
            resourceId: $transfer->getId(),
            context: [
                'tenant_id' => $context['tenant_id'] ?? null,
                'base_url'  => $context['base_url'] ?? null,
            ]
        );

        $transfer->setQrDetails($qr['token'], $qr['url']);

        $stored = $this->repository->store($transfer);

        $this->auditService->recordEvent(
            eventKey: sprintf('inventory.transfer.%s', $transfer->getId()),
            eventType: 'transfer_initiated',
            context: $context,
            before: null,
            after: $stored->toArray(),
            metadata: [
                'qr_url'         => $qr['url'],
                'maker_request'  => $approvalId,
                'request_origin' => $context['request_origin'] ?? null,
            ]
        );

        return $stored;
    }

    /**
     * @param array<string, mixed> $context
     * @param array<string, mixed> $metadata
     */
    public function completeTransfer(string $transferId, string $decision, array $context, array $metadata = []): Transfer
    {
        $transfer = $this->repository->find($transferId);

        if ($transfer === null) {
            throw new RuntimeException('Transfer request not found.');
        }

        if ($transfer->getCompletedAt() !== null) {
            throw new RuntimeException('Transfer has already been completed and cannot be processed again.');
        }

        if ($transfer->getStatus() !== Transfer::STATUS_PENDING) {
            throw new RuntimeException(sprintf(
                'Transfer must be pending to be processed. Current status: %s.',
                $transfer->getStatus()
            ));
        }

        $decision = strtolower($decision);
        if (!in_array($decision, [Transfer::STATUS_ACCEPTED, Transfer::STATUS_REJECTED], true)) {
            throw new InvalidArgumentException('Decision must be accepted or rejected.');
        }

        $before = $transfer->toArray();

        if ($decision === Transfer::STATUS_ACCEPTED) {
            $transfer->markAccepted();
        } else {
            $transfer->markRejected();
        }

        $updated = $this->repository->update($transfer, $metadata);

        $approvalRequestId = $transfer->getApprovalRequestId();
        if ($approvalRequestId !== null) {
            if ($decision === Transfer::STATUS_ACCEPTED) {
                $this->makerChecker->approve($approvalRequestId, $context);
            } else {
                $reason = (string) ($metadata['rejection_reason'] ?? 'Rejected at receiving warehouse');
                $this->makerChecker->reject($approvalRequestId, $context, $reason);
            }
        }

        $this->auditService->recordEvent(
            eventKey: sprintf('inventory.transfer.%s', $transfer->getId()),
            eventType: 'transfer_completed',
            context: $context,
            before: $before,
            after: array_merge($updated->toArray(), ['decision' => $decision, 'metadata' => $metadata]),
            metadata: [
                'decision' => $decision,
            ]
        );

        return $updated;
    }

    /**
     * @param array<int|string, mixed> $items
     * @return array<int, array{sku: non-empty-string, quantity: int<1, max>}>
     */
    private function normaliseItems(array $items): array
    {
        $normalised = [];

        foreach ($items as $item) {
            if (!is_array($item)) {
                throw new InvalidArgumentException('Transfer items must be arrays.');
            }

            $sku = (string) ($item['sku'] ?? '');
            $quantity = $item['quantity'] ?? null;

            if ($sku === '') {
                throw new InvalidArgumentException('Each transfer item requires a SKU.');
            }

            if (!is_int($quantity) || $quantity <= 0) {
                throw new InvalidArgumentException('Transfer item quantity must be a positive integer.');
            }

            $normalised[] = [
                'sku'      => $sku,
                'quantity' => $quantity,
            ];
        }

        return $normalised;
    }
}
