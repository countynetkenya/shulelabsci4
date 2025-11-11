<?php

namespace Modules\Foundation\Services;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\ConnectionInterface;
use CodeIgniter\I18n\Time;
use Config\Database;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use RuntimeException;

/**
 * Issues and validates QR tokens for verifiable documents and assets.
 */
class QrService
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
     * Issues a new QR token for a resource and returns the persisted record along with PNG bytes.
     *
     * @param array<string, mixed> $context
     * @return array{token: string, png: string, url: string}
     */
    public function issueToken(string $resourceType, string $resourceId, array $context, ?int $ttlSeconds = null): array
    {
        $token = bin2hex(random_bytes(16));
        $expiresAt = $ttlSeconds ? Time::now('UTC')->addSeconds($ttlSeconds) : null;

        $record = [
            'resource_type' => $resourceType,
            'resource_id'   => $resourceId,
            'tenant_id'     => $context['tenant_id'] ?? null,
            'token'         => $token,
            'issued_at'     => Time::now('UTC')->toDateTimeString(),
            'expires_at'    => $expiresAt?->toDateTimeString(),
        ];

        $this->db->table('qr_tokens')->insert($record);

        $verificationUrl = ($context['base_url'] ?? 'https://schoolos.shulelabs.com') . '/verify/' . $token;
        $pngData = Builder::create()
            ->writer(new PngWriter())
            ->data($verificationUrl)
            ->size(300)
            ->margin(10)
            ->build()
            ->getString();

        return [
            'token' => $token,
            'png'   => $pngData,
            'url'   => $verificationUrl,
        ];
    }

    /**
     * Validates the provided token and records a scan event.
     *
     * @param array<string, mixed> $context
     *
     * @return array<string, mixed>
     */
    public function verify(string $token, array $context): array
    {
        $row = $this->db->table('qr_tokens')
            ->where('token', $token)
            ->get()
            ->getFirstRow('array');

        if (! $row) {
            throw new RuntimeException('QR token not found.');
        }

        if ($row['expires_at'] !== null && Time::parse($row['expires_at'], 'UTC')->isBefore(Time::now('UTC'))) {
            throw new RuntimeException('QR token expired.');
        }

        $scan = [
            'token_id'    => $row['id'],
            'scanned_at'  => Time::now('UTC')->toDateTimeString(),
            'ip_address'  => $context['ip'] ?? null,
            'user_agent'  => $context['user_agent'] ?? null,
            'metadata'    => json_encode($context['metadata'] ?? [], JSON_THROW_ON_ERROR),
        ];

        $this->db->table('qr_scans')->insert($scan);

        return $row;
    }
}
