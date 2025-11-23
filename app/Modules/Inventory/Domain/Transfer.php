<?php

namespace Modules\Inventory\Domain;

use DateTimeImmutable;
use Ramsey\Uuid\Uuid;

/**
 * Represents a warehouse transfer request that must be verified via QR scanning.
 */
class Transfer
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_REJECTED = 'rejected';

    private string $id;

    private string $sourceWarehouseId;

    private string $targetWarehouseId;

    /**
     * @var list<array{sku: string, quantity: int}>
     */
    private array $items;

    private string $status;

    private DateTimeImmutable $requestedAt;

    private ?DateTimeImmutable $completedAt = null;

    private ?string $qrToken = null;

    private ?string $qrUrl = null;

    private ?int $approvalRequestId = null;

    /**
     * @param list<array{sku: string, quantity: int}> $items
     */
    public function __construct(
        string $sourceWarehouseId,
        string $targetWarehouseId,
        array $items,
        ?string $id = null,
        ?DateTimeImmutable $requestedAt = null
    ) {
        $this->id = $id ?? Uuid::uuid4()->toString();
        $this->sourceWarehouseId = $sourceWarehouseId;
        $this->targetWarehouseId = $targetWarehouseId;
        $this->items = $items;
        $this->status = self::STATUS_PENDING;
        $this->requestedAt = $requestedAt ?? new DateTimeImmutable('now', new \DateTimeZone('UTC'));
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getSourceWarehouseId(): string
    {
        return $this->sourceWarehouseId;
    }

    public function getTargetWarehouseId(): string
    {
        return $this->targetWarehouseId;
    }

    /**
     * @return list<array{sku: string, quantity: int}>
     */
    public function getItems(): array
    {
        return $this->items;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function markAccepted(): void
    {
        $this->status = self::STATUS_ACCEPTED;
        $this->completedAt = new DateTimeImmutable('now', new \DateTimeZone('UTC'));
    }

    public function markRejected(): void
    {
        $this->status = self::STATUS_REJECTED;
        $this->completedAt = new DateTimeImmutable('now', new \DateTimeZone('UTC'));
    }

    public function getRequestedAt(): DateTimeImmutable
    {
        return $this->requestedAt;
    }

    public function getCompletedAt(): ?DateTimeImmutable
    {
        return $this->completedAt;
    }

    public function setQrDetails(string $token, string $url): void
    {
        $this->qrToken = $token;
        $this->qrUrl = $url;
    }

    public function getQrToken(): ?string
    {
        return $this->qrToken;
    }

    public function getQrUrl(): ?string
    {
        return $this->qrUrl;
    }

    public function setApprovalRequestId(int $requestId): void
    {
        $this->approvalRequestId = $requestId;
    }

    public function getApprovalRequestId(): ?int
    {
        return $this->approvalRequestId;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id'                  => $this->id,
            'sourceWarehouseId'   => $this->sourceWarehouseId,
            'targetWarehouseId'   => $this->targetWarehouseId,
            'items'               => $this->items,
            'status'              => $this->status,
            'requestedAt'         => $this->requestedAt->format(DATE_ATOM),
            'completedAt'         => $this->completedAt?->format(DATE_ATOM),
            'qrToken'             => $this->qrToken,
            'qrUrl'               => $this->qrUrl,
            'approvalRequestId'   => $this->approvalRequestId,
        ];
    }
}
