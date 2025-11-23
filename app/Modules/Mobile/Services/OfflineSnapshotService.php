<?php

declare(strict_types=1);

namespace Modules\Mobile\Services;

use DateInterval;
use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;
use Modules\Foundation\Services\AuditService;
use Modules\Mobile\Domain\Snapshot;

/**
 * Issues and verifies signed offline snapshots for mobile/PWA clients.
 */
class OfflineSnapshotService
{
    /** @var array<string, string> */
    private array $signingKeys;

    private string $activeKeyId;

    private int $defaultTtlSeconds;

    /**
     * @param array<string, string> $fallbackKeys
     */
    public function __construct(
        string $signingKey,
        string $keyId,
        private readonly AuditService $auditService,
        int $defaultTtlSeconds = 3600,
        array $fallbackKeys = []
    ) {
        if ($defaultTtlSeconds <= 0) {
            throw new InvalidArgumentException('Default TTL must be positive.');
        }

        $this->defaultTtlSeconds = $defaultTtlSeconds;
        $this->activeKeyId = $keyId;
        $this->signingKeys = $fallbackKeys;
        $this->signingKeys[$keyId] = $signingKey;
    }

    /**
     * @param array<string, mixed> $dataset
     * @param array<string, mixed> $context
     */
    public function issueSnapshot(array $dataset, array $context, ?int $ttlSeconds = null, string $version = 'v1'): Snapshot
    {
        $tenantId = trim((string) ($context['tenant_id'] ?? ''));
        if ($tenantId === '') {
            throw new InvalidArgumentException('Tenant ID is required in the context.');
        }

        $ttl = $ttlSeconds ?? $this->defaultTtlSeconds;
        if ($ttl <= 0) {
            throw new InvalidArgumentException('Snapshot TTL must be positive.');
        }

        $issuedAt = new DateTimeImmutable('now', new DateTimeZone('UTC'));
        $expiresAt = $issuedAt->add(new DateInterval(sprintf('PT%dS', $ttl)));
        $snapshotId = $this->buildSnapshotId($tenantId, $issuedAt, (string) ($context['device_id'] ?? ''));

        $checksum = hash('sha256', json_encode($dataset, JSON_THROW_ON_ERROR));
        $signature = $this->sign($snapshotId, $checksum, $expiresAt, $this->activeKeyId);

        $metadata = array_filter(
            [
                'device_id'    => $context['device_id'] ?? null,
                'ttl_seconds'  => $ttl,
                'dataset_keys' => array_keys($dataset),
            ],
            static fn ($value) => $value !== null && $value !== ''
        );

        $snapshot = new Snapshot(
            snapshotId: $snapshotId,
            tenantId: $tenantId,
            issuedAt: $issuedAt,
            expiresAt: $expiresAt,
            payload: $dataset,
            checksum: $checksum,
            signature: $signature,
            keyId: $this->activeKeyId,
            version: $version,
            metadata: $metadata,
        );

        $this->auditService->recordEvent(
            eventKey: sprintf('mobile.snapshot.%s', $snapshotId),
            eventType: 'snapshot_issued',
            context: $context,
            before: null,
            after: $snapshot->toArray(),
            metadata: [
                'tenant_id' => $tenantId,
                'device_id' => $context['device_id'] ?? null,
                'key_id'    => $this->activeKeyId,
                'ttl'       => $ttl,
            ]
        );

        return $snapshot;
    }

    /**
     * @param array<string, mixed> $context
     */
    public function verifySnapshot(Snapshot $snapshot, array $context): bool
    {
        $contextTenantId = trim((string) ($context['tenant_id'] ?? ''));
        if ($contextTenantId === '') {
            $this->recordVerificationFailure($snapshot, $context, 'tenant_missing');

            return false;
        }

        if ($contextTenantId !== $snapshot->getTenantId()) {
            $this->recordVerificationFailure($snapshot, $context, 'tenant_mismatch');

            return false;
        }

        $keyId = $snapshot->getKeyId();
        $key = $this->signingKeys[$keyId] ?? null;

        if ($key === null) {
            $this->recordVerificationFailure($snapshot, $context, 'unknown_key');

            return false;
        }

        $expectedSignature = $this->sign(
            $snapshot->getSnapshotId(),
            $snapshot->getChecksum(),
            $snapshot->getExpiresAt(),
            $keyId,
            $key
        );

        if (!hash_equals($expectedSignature, $snapshot->getSignature())) {
            $this->recordVerificationFailure($snapshot, $context, 'invalid_signature');

            return false;
        }

        if ($snapshot->isExpired()) {
            $this->recordVerificationFailure($snapshot, $context, 'expired');

            return false;
        }

        $this->auditService->recordEvent(
            eventKey: sprintf('mobile.snapshot.%s', $snapshot->getSnapshotId()),
            eventType: 'snapshot_verified',
            context: $context,
            before: null,
            after: [
                'snapshot_id' => $snapshot->getSnapshotId(),
                'tenant_id'   => $snapshot->getTenantId(),
                'metadata'    => $snapshot->getMetadata(),
            ],
            metadata: [
                'tenant_id' => $snapshot->getTenantId(),
                'device_id' => $snapshot->getMetadata()['device_id'] ?? null,
                'key_id'    => $keyId,
            ]
        );

        return true;
    }

    public function rotateSigningKey(string $newKey, string $keyId): void
    {
        $this->signingKeys[$keyId] = $newKey;
        $this->activeKeyId = $keyId;
    }

    public function getActiveKeyId(): string
    {
        return $this->activeKeyId;
    }

    /**
     * @param array<string, mixed> $context
     */
    private function recordVerificationFailure(Snapshot $snapshot, array $context, string $reason): void
    {
        $this->auditService->recordEvent(
            eventKey: sprintf('mobile.snapshot.%s', $snapshot->getSnapshotId()),
            eventType: 'snapshot_verification_failed',
            context: $context,
            before: null,
            after: [
                'snapshot_id' => $snapshot->getSnapshotId(),
                'tenant_id'   => $snapshot->getTenantId(),
                'reason'      => $reason,
            ],
            metadata: [
                'tenant_id' => $snapshot->getTenantId(),
                'device_id' => $snapshot->getMetadata()['device_id'] ?? null,
                'key_id'    => $snapshot->getKeyId(),
                'reason'    => $reason,
            ]
        );
    }

    private function buildSnapshotId(string $tenantId, DateTimeImmutable $issuedAt, string $deviceId): string
    {
        $timestamp = $issuedAt->format('YmdHis');
        $hash = substr(hash('sha256', $tenantId . $timestamp . $deviceId . microtime()), 0, 10);

        return sprintf('%s-%s-%s', strtoupper($tenantId), $timestamp, strtoupper($hash));
    }

    private function sign(
        string $snapshotId,
        string $checksum,
        DateTimeImmutable $expiresAt,
        string $keyId,
        ?string $overrideKey = null
    ): string {
        $key = $overrideKey ?? $this->signingKeys[$keyId] ?? null;
        if ($key === null) {
            throw new InvalidArgumentException(sprintf('Signing key not found for key id %s.', $keyId));
        }

        $data = json_encode([
            'snapshot_id' => $snapshotId,
            'checksum'    => $checksum,
            'expires_at'  => $expiresAt->format(DATE_ATOM),
            'key_id'      => $keyId,
        ], JSON_THROW_ON_ERROR);

        return base64_encode(hash_hmac('sha256', $data, $key, true));
    }
}
