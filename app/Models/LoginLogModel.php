<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * LoginLog Model
 *
 * Handles login logging and tracking
 * Compatible with CI3 database schema
 */
class LoginLogModel extends Model
{
    protected $table = 'loginlog';
    protected $primaryKey = 'loginlogID';
    protected $returnType = 'object';
    protected $allowedFields = ['userID', 'usertypeID', 'ip', 'browser', 'login', 'logout'];
    protected $useTimestamps = false;

    /**
     * Create login log entry
     *
     * @param array<string, mixed> $data
     * @return int|bool
     */
    public function createLoginLog(array $data)
    {
        return $this->insert($data);
    }

    /**
     * Update logout time
     *
     * @param int $loginlogID
     * @param int $logoutTime
     * @return bool
     */
    public function updateLogout(int $loginlogID, int $logoutTime): bool
    {
        return $this->update($loginlogID, ['logout' => $logoutTime]);
    }

    /**
     * Get single login log entry
     *
     * @param array<string, mixed> $where
     * @return object|null
     */
    public function getSingleLoginLog(array $where): ?object
    {
        return $this->where($where)->first();
    }
}
