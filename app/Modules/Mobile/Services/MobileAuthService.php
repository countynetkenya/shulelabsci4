<?php

namespace App\Modules\Mobile\Services;

use CodeIgniter\Database\ConnectionInterface;
use Config\Database;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * MobileAuthService - Handles mobile JWT authentication and device management.
 */
class MobileAuthService
{
    private $db;
    private string $jwtKey;
    private int $accessTokenTTL = 3600; // 1 hour
    private int $refreshTokenTTL = 2592000; // 30 days

    public function __construct(?ConnectionInterface $connection = null)
    {
        $this->db = $connection ?? Database::connect();
        $this->jwtKey = getenv('JWT_SECRET') ?: 'default-secret-change-in-production';
    }

    /**
     * Register a mobile device.
     */
    public function registerDevice(int $userId, string $deviceId, string $deviceType, ?string $deviceName = null, ?string $osVersion = null, ?string $appVersion = null): int
    {
        $existing = $this->db->table('mobile_devices')
            ->where('user_id', $userId)
            ->where('device_id', $deviceId)
            ->get()
            ->getRowArray();

        if ($existing) {
            $this->db->table('mobile_devices')
                ->where('id', $existing['id'])
                ->update([
                    'device_name' => $deviceName,
                    'os_version' => $osVersion,
                    'app_version' => $appVersion,
                    'is_active' => 1,
                    'last_active_at' => date('Y-m-d H:i:s'),
                ]);
            return (int) $existing['id'];
        }

        $this->db->table('mobile_devices')->insert([
            'user_id' => $userId,
            'device_id' => $deviceId,
            'device_name' => $deviceName,
            'device_type' => $deviceType,
            'os_version' => $osVersion,
            'app_version' => $appVersion,
            'is_active' => 1,
            'last_active_at' => date('Y-m-d H:i:s'),
        ]);

        return (int) $this->db->insertID();
    }

    /**
     * Generate access and refresh tokens.
     */
    public function generateTokens(int $userId, int $deviceId): array
    {
        $now = time();

        // Access token
        $accessPayload = [
            'iss' => base_url(),
            'aud' => base_url(),
            'iat' => $now,
            'exp' => $now + $this->accessTokenTTL,
            'sub' => $userId,
            'device_id' => $deviceId,
            'type' => 'access',
        ];
        $accessToken = JWT::encode($accessPayload, $this->jwtKey, 'HS256');

        // Refresh token
        $refreshPayload = [
            'iss' => base_url(),
            'aud' => base_url(),
            'iat' => $now,
            'exp' => $now + $this->refreshTokenTTL,
            'sub' => $userId,
            'device_id' => $deviceId,
            'type' => 'refresh',
        ];
        $refreshToken = JWT::encode($refreshPayload, $this->jwtKey, 'HS256');

        // Store refresh token hash
        $this->db->table('api_tokens')->insert([
            'user_id' => $userId,
            'device_id' => $deviceId,
            'token_hash' => hash('sha256', $refreshToken),
            'expires_at' => date('Y-m-d H:i:s', $now + $this->refreshTokenTTL),
        ]);

        return [
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'expires_in' => $this->accessTokenTTL,
            'token_type' => 'Bearer',
        ];
    }

    /**
     * Validate access token.
     */
    public function validateAccessToken(string $token): ?array
    {
        try {
            $decoded = JWT::decode($token, new Key($this->jwtKey, 'HS256'));

            if ($decoded->type !== 'access') {
                return null;
            }

            return [
                'user_id' => $decoded->sub,
                'device_id' => $decoded->device_id,
            ];
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Refresh access token using refresh token.
     */
    public function refreshAccessToken(string $refreshToken): ?array
    {
        try {
            $decoded = JWT::decode($refreshToken, new Key($this->jwtKey, 'HS256'));

            if ($decoded->type !== 'refresh') {
                return null;
            }

            // Verify refresh token is not revoked
            $tokenHash = hash('sha256', $refreshToken);
            $storedToken = $this->db->table('api_tokens')
                ->where('token_hash', $tokenHash)
                ->where('revoked_at IS NULL')
                ->where('expires_at >', date('Y-m-d H:i:s'))
                ->get()
                ->getRowArray();

            if (!$storedToken) {
                return null;
            }

            // Generate new tokens
            return $this->generateTokens($decoded->sub, $decoded->device_id);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Revoke refresh token.
     */
    public function revokeToken(string $refreshToken): bool
    {
        $tokenHash = hash('sha256', $refreshToken);

        return $this->db->table('api_tokens')
            ->where('token_hash', $tokenHash)
            ->update(['revoked_at' => date('Y-m-d H:i:s')]);
    }

    /**
     * Revoke all tokens for a user.
     */
    public function revokeAllUserTokens(int $userId): int
    {
        return $this->db->table('api_tokens')
            ->where('user_id', $userId)
            ->where('revoked_at IS NULL')
            ->update(['revoked_at' => date('Y-m-d H:i:s')]);
    }

    /**
     * Register push token.
     */
    public function registerPushToken(int $deviceId, string $token, string $platform): bool
    {
        // Remove old tokens for this device
        $this->db->table('push_tokens')
            ->where('device_id', $deviceId)
            ->delete();

        return $this->db->table('push_tokens')->insert([
            'device_id' => $deviceId,
            'token' => $token,
            'platform' => $platform,
            'is_valid' => 1,
        ]);
    }

    /**
     * Get user's active devices.
     */
    public function getUserDevices(int $userId): array
    {
        return $this->db->table('mobile_devices')
            ->where('user_id', $userId)
            ->where('is_active', 1)
            ->orderBy('last_active_at', 'DESC')
            ->get()
            ->getResultArray();
    }

    /**
     * Deactivate a device.
     */
    public function deactivateDevice(int $deviceId, int $userId): bool
    {
        // Revoke all tokens for this device
        $this->db->table('api_tokens')
            ->where('device_id', $deviceId)
            ->where('revoked_at IS NULL')
            ->update(['revoked_at' => date('Y-m-d H:i:s')]);

        return $this->db->table('mobile_devices')
            ->where('id', $deviceId)
            ->where('user_id', $userId)
            ->update(['is_active' => 0]);
    }
}
