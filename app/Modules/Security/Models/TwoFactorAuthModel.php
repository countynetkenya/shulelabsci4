<?php

namespace App\Modules\Security\Models;

use CodeIgniter\Model;

/**
 * TwoFactorAuthModel - Manages 2FA configuration.
 */
class TwoFactorAuthModel extends Model
{
    protected $table = 'two_factor_auth';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;

    protected $allowedFields = [
        'user_id', 'method', 'secret_encrypted', 'backup_codes',
        'is_enabled', 'verified_at', 'last_used_at',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = '';

    protected $casts = [
        'id' => 'int',
        'user_id' => 'int',
        'is_enabled' => 'bool',
        'backup_codes' => 'json-array',
    ];

    /**
     * Get 2FA config for a user.
     */
    public function getForUser(int $userId): ?array
    {
        return $this->where('user_id', $userId)->first();
    }

    /**
     * Check if 2FA is enabled for a user.
     */
    public function isEnabled(int $userId): bool
    {
        $config = $this->getForUser($userId);
        return $config !== null && $config['is_enabled'];
    }

    /**
     * Enable 2FA for a user.
     */
    public function enable(int $userId): bool
    {
        return $this->where('user_id', $userId)
            ->set(['is_enabled' => 1, 'verified_at' => date('Y-m-d H:i:s')])
            ->update();
    }

    /**
     * Disable 2FA for a user.
     */
    public function disable(int $userId): bool
    {
        return $this->where('user_id', $userId)->delete();
    }

    /**
     * Update last used timestamp.
     */
    public function updateLastUsed(int $userId): bool
    {
        return $this->where('user_id', $userId)
            ->set(['last_used_at' => date('Y-m-d H:i:s')])
            ->update();
    }
}
