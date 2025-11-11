<?php

namespace Modules\Foundation\Services;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\ConnectionInterface;
use CodeIgniter\I18n\Time;
use Config\Database;
use RuntimeException;

/**
 * Provides append-only audit logging with hash-chained integrity and daily sealing.
 */
class AuditService
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
     * Records an append-only audit event and returns the persisted event ID.
     *
     * @param array<string, mixed> $context
     * @param array<string, mixed>|null $before
     * @param array<string, mixed>|null $after
     * @param array<string, mixed> $metadata
     */
    public function recordEvent(
        string $eventKey,
        string $eventType,
        array $context,
        ?array $before,
        ?array $after,
        array $metadata = []
    ): int {
        $now = Time::now('UTC');
        $payload = [
            'event_key'     => $eventKey,
            'event_type'    => $eventType,
            'tenant_id'     => $context['tenant_id'] ?? null,
            'actor_id'      => $context['actor_id'] ?? null,
            'ip_address'    => $metadata['ip'] ?? null,
            'user_agent'    => $metadata['user_agent'] ?? null,
            'request_uri'   => $metadata['request_uri'] ?? null,
            'before_state'  => $before === null ? null : json_encode($before, JSON_THROW_ON_ERROR),
            'after_state'   => $after === null ? null : json_encode($after, JSON_THROW_ON_ERROR),
            'metadata_json' => empty($metadata) ? null : json_encode($metadata, JSON_THROW_ON_ERROR),
            'created_at'    => $now->toDateTimeString(),
        ];

        $this->db->transStart();

        $previousHash = $this->getLatestHash();
        $payload['previous_hash'] = $previousHash;
        $payload['hash_value']    = $this->calculateHash($previousHash, $payload);

        $this->db->table('audit_events')->insert($payload);
        $eventId = (int) $this->db->insertID();

        $this->db->transComplete();

        if (! $this->db->transStatus()) {
            throw new RuntimeException('Failed to record audit event.');
        }

        return $eventId;
    }

    /**
     * Seals all audit entries for the provided date by storing a deterministic hash.
     */
    public function sealDay(?Time $day = null): void
    {
        $day ??= Time::today('UTC');
        $start = $day->setTime(0, 0, 0);
        $end   = $day->setTime(23, 59, 59);

        $builder = $this->db->table('audit_events');
        $builder->select('hash_value');
        $builder->where('created_at >=', $start->toDateTimeString());
        $builder->where('created_at <=', $end->toDateTimeString());
        $builder->orderBy('id', 'ASC');

        $hashStream = '';
        foreach ($builder->get()->getResultArray() as $row) {
            $hashStream .= $row['hash_value'];
        }

        $sealHash = hash('sha256', $hashStream);

        $this->db->table('audit_seals')->replace([
            'seal_date'   => $start->toDateString(),
            'hash_value'  => $sealHash,
            'sealed_at'   => Time::now('UTC')->toDateTimeString(),
        ]);
    }

    /**
     * Recomputes the hash chain and returns true when the stored values are intact.
     */
    public function verifyIntegrity(): bool
    {
        $builder = $this->db->table('audit_events');
        $builder->orderBy('id', 'ASC');

        $previousHash = null;
        foreach ($builder->get()->getResultArray() as $row) {
            $expected = $this->calculateHash($row['previous_hash'], $row);
            if (! hash_equals($expected, $row['hash_value'])) {
                return false;
            }

            if ($previousHash !== null && ! hash_equals($previousHash, (string) $row['previous_hash'])) {
                return false;
            }

            $previousHash = $row['hash_value'];
        }

        return true;
    }

    private function getLatestHash(): ?string
    {
        $builder = $this->db->table('audit_events');
        $builder->select('hash_value');
        $builder->orderBy('id', 'DESC');

        $row = $builder->get(1)->getFirstRow('array');

        return $row ? (string) $row['hash_value'] : null;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function calculateHash(?string $previousHash, array $payload): string
    {
        $data = array_merge($payload, ['previous_hash' => $previousHash]);
        unset($data['hash_value'], $data['id']);

        array_walk_recursive($data, static function (&$value): void {
            if (is_int($value) || is_float($value)) {
                $value = (string) $value;
            } elseif (is_bool($value)) {
                $value = $value ? 'true' : 'false';
            }
        });

        ksort($data);

        return hash('sha256', json_encode($data, JSON_THROW_ON_ERROR));
    }
}
