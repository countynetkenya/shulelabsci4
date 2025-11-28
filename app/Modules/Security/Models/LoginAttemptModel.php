<?php

namespace App\Modules\Security\Models;

use CodeIgniter\Model;

/**
 * LoginAttemptModel - Tracks login attempts for security.
 */
class LoginAttemptModel extends Model
{
    protected $table = 'login_attempts';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;

    protected $allowedFields = [
        'identifier', 'ip_address', 'user_agent', 'attempt_type',
        'was_successful', 'failure_reason',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = '';

    protected $casts = [
        'id' => 'int',
        'was_successful' => 'bool',
    ];

    /**
     * Record a login attempt.
     */
    public function recordAttempt(string $identifier, string $ipAddress, bool $successful, ?string $failureReason = null, string $type = 'login', ?string $userAgent = null): int
    {
        $this->insert([
            'identifier' => $identifier,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'attempt_type' => $type,
            'was_successful' => $successful ? 1 : 0,
            'failure_reason' => $failureReason,
        ]);
        return (int) $this->getInsertID();
    }

    /**
     * Count recent failed attempts for an identifier.
     */
    public function countRecentFailed(string $identifier, int $minutes = 30): int
    {
        $since = date('Y-m-d H:i:s', strtotime("-{$minutes} minutes"));
        return $this->where('identifier', $identifier)
            ->where('was_successful', 0)
            ->where('created_at >=', $since)
            ->countAllResults();
    }

    /**
     * Count recent failed attempts from an IP.
     */
    public function countRecentFailedByIp(string $ipAddress, int $minutes = 30): int
    {
        $since = date('Y-m-d H:i:s', strtotime("-{$minutes} minutes"));
        return $this->where('ip_address', $ipAddress)
            ->where('was_successful', 0)
            ->where('created_at >=', $since)
            ->countAllResults();
    }

    /**
     * Check if an identifier is locked out.
     */
    public function isLockedOut(string $identifier, int $maxAttempts = 5, int $lockoutMinutes = 30): bool
    {
        return $this->countRecentFailed($identifier, $lockoutMinutes) >= $maxAttempts;
    }

    /**
     * Get lockout remaining time in seconds.
     */
    public function getLockoutRemaining(string $identifier, int $maxAttempts = 5, int $lockoutMinutes = 30): int
    {
        if (!$this->isLockedOut($identifier, $maxAttempts, $lockoutMinutes)) {
            return 0;
        }

        $lastAttempt = $this->where('identifier', $identifier)
            ->where('was_successful', 0)
            ->orderBy('created_at', 'DESC')
            ->first();

        if (!$lastAttempt) {
            return 0;
        }

        $lockoutEnd = strtotime($lastAttempt['created_at']) + ($lockoutMinutes * 60);
        return max(0, $lockoutEnd - time());
    }
}
