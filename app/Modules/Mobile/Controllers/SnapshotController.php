<?php

declare(strict_types=1);

namespace Modules\Mobile\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;
use Config\Services;
use DateTimeImmutable;
use Exception;
use InvalidArgumentException;
use Modules\Foundation\Controllers\InteractsWithIncomingRequest;
use Modules\Mobile\Domain\Snapshot;
use Modules\Mobile\Services\OfflineSnapshotService;
use Modules\Mobile\Services\SnapshotTelemetryService;

class SnapshotController extends ResourceController
{
    protected $format = 'json';

    private OfflineSnapshotService $snapshots;
    private SnapshotTelemetryService $telemetry;

    use InteractsWithIncomingRequest;

    public function __construct(
        ?OfflineSnapshotService $snapshots = null,
        ?SnapshotTelemetryService $telemetry = null
    ) {
        $this->snapshots = $snapshots ?? Services::offlineSnapshots();
        $this->telemetry = $telemetry ?? Services::snapshotTelemetry();
    }

    public function issue(): ResponseInterface
    {
        $payload = $this->incomingRequest()->getJSON(true) ?? [];
        $dataset = $payload['dataset'] ?? [];
        if (! is_array($dataset)) {
            return $this->failValidationErrors(['dataset payload must be an object.']);
        }

        $ttl     = isset($payload['ttl_seconds']) ? (int) $payload['ttl_seconds'] : null;
        $version = isset($payload['version']) ? (string) $payload['version'] : 'v1';

        try {
            $snapshot = $this->snapshots->issueSnapshot($dataset, $this->buildContext($payload), $ttl, $version);
        } catch (InvalidArgumentException $exception) {
            return $this->failValidationErrors([$exception->getMessage()]);
        }

        return $this->respondCreated($snapshot->toArray());
    }

    public function verify(): ResponseInterface
    {
        $payload = $this->incomingRequest()->getJSON(true) ?? [];
        if (! is_array($payload)) {
            return $this->failValidationErrors(['Snapshot payload must be an object.']);
        }

        try {
            $snapshot = $this->reconstituteSnapshot($payload);
        } catch (InvalidArgumentException $exception) {
            return $this->failValidationErrors([$exception->getMessage()]);
        }

        $context = $this->buildContext($payload);

        $verified = $this->snapshots->verifySnapshot($snapshot, $context);

        return $this->respond(['verified' => $verified]);
    }

    public function telemetry(): ResponseInterface
    {
        $hours = $this->incomingRequest()->getGet('hours');
        $window = $hours !== null ? (int) $hours : 24;

        try {
            $report = $this->telemetry->getTelemetry($window);
        } catch (InvalidArgumentException $exception) {
            return $this->failValidationErrors([$exception->getMessage()]);
        }

        return $this->respond($report);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function reconstituteSnapshot(array $payload): Snapshot
    {
        foreach (['snapshot_id', 'tenant_id', 'issued_at', 'expires_at', 'payload', 'checksum', 'signature', 'key_id', 'version'] as $field) {
            if (! array_key_exists($field, $payload)) {
                throw new InvalidArgumentException(sprintf('Snapshot payload missing required field: %s.', $field));
            }
        }

        if (! is_array($payload['payload'])) {
            throw new InvalidArgumentException('Snapshot payload must contain an object payload.');
        }

        $metadata = isset($payload['metadata']) && is_array($payload['metadata']) ? $payload['metadata'] : [];

        try {
            $issuedAt  = new DateTimeImmutable((string) $payload['issued_at']);
            $expiresAt = new DateTimeImmutable((string) $payload['expires_at']);
        } catch (Exception $exception) {
            throw new InvalidArgumentException('Snapshot payload contains invalid timestamps.', 0, $exception);
        }

        return new Snapshot(
            snapshotId: (string) $payload['snapshot_id'],
            tenantId: (string) $payload['tenant_id'],
            issuedAt: $issuedAt,
            expiresAt: $expiresAt,
            payload: $payload['payload'],
            checksum: (string) $payload['checksum'],
            signature: (string) $payload['signature'],
            keyId: (string) $payload['key_id'],
            version: (string) $payload['version'],
            metadata: $metadata,
        );
    }

    /**
     * @param array<string, mixed>|null $payload
     * @return array<string, mixed>
     */
    private function buildContext(?array $payload = null): array
    {
        $payload ??= [];
        $request = $this->incomingRequest();

        return [
            'tenant_id'      => $request->getHeaderLine('X-Tenant-ID') ?: ($payload['tenant_id'] ?? null),
            'actor_id'       => $request->getHeaderLine('X-Actor-ID') ?: null,
            'device_id'      => $request->getHeaderLine('X-Device-ID') ?: ($payload['device_id'] ?? null),
            'request_origin' => $request->getIPAddress(),
        ];
    }

}
