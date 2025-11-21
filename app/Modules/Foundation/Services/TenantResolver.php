<?php

namespace Modules\Foundation\Services;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\ConnectionInterface;
use CodeIgniter\HTTP\IncomingRequest;
use Config\Database;
use JsonException;
use RuntimeException;

/**
 * Resolves the active tenant hierarchy (organisation -> school -> warehouse).
 */
class TenantResolver
{
    /**
     * @phpstan-var BaseConnection<object, object>
     */
    private BaseConnection $db;

    /**
     * @phpstan-param ConnectionInterface<object, object>|null $connection
     */
    public function __construct(?ConnectionInterface $connection = null)
    {
        $this->db = $connection instanceof BaseConnection ? $connection : Database::connect();
    }

    /**
     * Resolves tenant identifiers using headers, JWT claims, or query parameters.
     *
     * @return array{organisation_id?: string|int, school_id?: string|int, warehouse_id?: string|int}
     */
    public function fromRequest(IncomingRequest $request): array
    {
        $headers = $request->getHeaderLine('X-Tenant-Context');
        if ($headers !== '') {
            try {
                $parts = json_decode($headers, true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException) {
                $parts = null;
            }

            if (is_array($parts)) {
                return $this->fromIdentifiers($parts);
            }
        }

        $organisation = $request->getHeaderLine('X-Organisation-ID') ?: $request->getGet('organisation_id');
        $school       = $request->getHeaderLine('X-School-ID') ?: $request->getGet('school_id');
        $warehouse    = $request->getHeaderLine('X-Warehouse-ID') ?: $request->getGet('warehouse_id');

        return $this->fromIdentifiers([
            'organisation_id' => $organisation ?: null,
            'school_id'       => $school ?: null,
            'warehouse_id'    => $warehouse ?: null,
        ]);
    }

    /**
     * Resolves tenant metadata from the provided identifiers.
     *
     * @param array{organisation_id?: string|int|null, school_id?: string|int|null, warehouse_id?: string|int|null} $ids
     *
     * @return array{
     *     tenant_id: int|string|null,
     *     organisation?: array<string, mixed>,
     *     school?: array<string, mixed>,
     *     warehouse?: array<string, mixed>
     * }
     */
    public function fromIdentifiers(array $ids): array
    {
        $organisationId = $ids['organisation_id'] ?? null;
        $schoolId       = $ids['school_id'] ?? null;
        $warehouseId    = $ids['warehouse_id'] ?? null;

        $context = [];
        if ($organisationId !== null) {
            $context['organisation'] = $this->fetchTenant('organisation', $organisationId);
        }

        if ($schoolId !== null) {
            $context['school'] = $this->fetchTenant('school', $schoolId);
        }

        if ($warehouseId !== null) {
            $context['warehouse'] = $this->fetchTenant('warehouse', $warehouseId);
        }

        if ($context === []) {
            throw new RuntimeException('Unable to resolve tenant context.');
        }

        $context['tenant_id'] = $schoolId ?? $organisationId ?? $warehouseId;

        return $context;
    }

    /**
     * @return array<string, mixed>
     */
    private function fetchTenant(string $type, int|string $id): array
    {
        $row = $this->db->table('tenant_catalog')
            ->where('tenant_type', $type)
            ->where('id', $id)
            ->get()
            ->getFirstRow('array');

        if (! $row) {
            throw new RuntimeException(sprintf('Unknown tenant %s::%s', $type, $id));
        }

        return $row;
    }
}
