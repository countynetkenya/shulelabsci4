<?php

namespace App\Modules\Security\Services;

use CodeIgniter\Database\ConnectionInterface;
use Config\Database;

/**
 * RateLimiterService - Handles rate limiting for API and authentication.
 */
class RateLimiterService
{
    private $db;

    public function __construct(?ConnectionInterface $connection = null)
    {
        $this->db = $connection ?? Database::connect();
    }

    /**
     * Check if a request should be rate limited.
     *
     * @return array{allowed: bool, remaining: int, retry_after: int}
     */
    public function check(string $key, int $maxAttempts, int $decaySeconds): array
    {
        $this->cleanupExpired();

        $record = $this->db->table('rate_limits')
            ->where('key', $key)
            ->where('expires_at >', date('Y-m-d H:i:s'))
            ->get()
            ->getRowArray();

        if (!$record) {
            return [
                'allowed' => true,
                'remaining' => $maxAttempts - 1,
                'retry_after' => 0,
            ];
        }

        $remaining = max(0, $maxAttempts - $record['hits']);
        $retryAfter = $remaining === 0 ? strtotime($record['expires_at']) - time() : 0;

        return [
            'allowed' => $remaining > 0,
            'remaining' => $remaining,
            'retry_after' => $retryAfter,
        ];
    }

    /**
     * Record a hit against the rate limit.
     */
    public function hit(string $key, int $decaySeconds): int
    {
        $expiresAt = date('Y-m-d H:i:s', time() + $decaySeconds);

        $record = $this->db->table('rate_limits')
            ->where('key', $key)
            ->where('expires_at >', date('Y-m-d H:i:s'))
            ->get()
            ->getRowArray();

        if ($record) {
            $this->db->table('rate_limits')
                ->where('id', $record['id'])
                ->set('hits', 'hits + 1', false)
                ->update();
            return $record['hits'] + 1;
        }

        $this->db->table('rate_limits')->insert([
            'key' => $key,
            'hits' => 1,
            'expires_at' => $expiresAt,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return 1;
    }

    /**
     * Clear rate limits for a key.
     */
    public function clear(string $key): bool
    {
        return $this->db->table('rate_limits')
            ->where('key', $key)
            ->delete();
    }

    /**
     * Get remaining attempts for a key.
     */
    public function remaining(string $key, int $maxAttempts): int
    {
        $record = $this->db->table('rate_limits')
            ->where('key', $key)
            ->where('expires_at >', date('Y-m-d H:i:s'))
            ->get()
            ->getRowArray();

        if (!$record) {
            return $maxAttempts;
        }

        return max(0, $maxAttempts - $record['hits']);
    }

    /**
     * Create a rate limit key for login attempts.
     */
    public static function loginKey(string $identifier): string
    {
        return "login:{$identifier}";
    }

    /**
     * Create a rate limit key for API requests.
     */
    public static function apiKey(string $userId, string $endpoint = ''): string
    {
        return "api:{$userId}:{$endpoint}";
    }

    /**
     * Create a rate limit key for IP-based limiting.
     */
    public static function ipKey(string $ipAddress, string $context = ''): string
    {
        return "ip:{$ipAddress}:{$context}";
    }

    /**
     * Cleanup expired rate limit records.
     */
    private function cleanupExpired(): void
    {
        // Only cleanup 1% of the time to reduce overhead
        if (random_int(1, 100) === 1) {
            $this->db->table('rate_limits')
                ->where('expires_at <', date('Y-m-d H:i:s'))
                ->delete();
        }
    }
}
