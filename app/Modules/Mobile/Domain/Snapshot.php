<?php

declare(strict_types=1);

namespace Modules\Mobile\Domain;

use DateTimeImmutable;

/**
 * Immutable representation of an offline snapshot package.
 */
class Snapshot
{
    /**
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        private readonly string $snapshotId,
        private readonly string $tenantId,
        private readonly DateTimeImmutable $issuedAt,
        private readonly DateTimeImmutable $expiresAt,
        private readonly array $payload,
        private readonly string $checksum,
        private readonly string $signature,
        private readonly string $keyId,
        private readonly string $version,
        private readonly array $metadata = [],
    ) {
    }

    public function getSnapshotId(): string
    {
        return $this->snapshotId;
    }

    public function getTenantId(): string
    {
        return $this->tenantId;
    }

    public function getIssuedAt(): DateTimeImmutable
    {
        return $this->issuedAt;
    }

    public function getExpiresAt(): DateTimeImmutable
    {
        return $this->expiresAt;
    }

    /**
     * @return array<string, mixed>
     */
    public function getPayload(): array
    {
        return $this->payload;
    }

    public function getChecksum(): string
    {
        return $this->checksum;
    }

    public function getSignature(): string
    {
        return $this->signature;
    }

    public function getKeyId(): string
    {
        return $this->keyId;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * @return array<string, mixed>
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function isExpired(?DateTimeImmutable $reference = null): bool
    {
        $reference ??= new DateTimeImmutable('now', $this->expiresAt->getTimezone());

        return $reference >= $this->expiresAt;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'snapshot_id' => $this->snapshotId,
            'tenant_id'   => $this->tenantId,
            'issued_at'   => $this->issuedAt->format(DATE_ATOM),
            'expires_at'  => $this->expiresAt->format(DATE_ATOM),
            'payload'     => $this->payload,
            'checksum'    => $this->checksum,
            'signature'   => $this->signature,
            'key_id'      => $this->keyId,
            'version'     => $this->version,
            'metadata'    => $this->metadata,
        ];
    }
}
