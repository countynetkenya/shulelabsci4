<?php

namespace App\Modules\Security\Services;

use App\Modules\Security\Models\TwoFactorAuthModel;

/**
 * TwoFactorService - Handles 2FA setup and verification.
 */
class TwoFactorService
{
    private TwoFactorAuthModel $twoFactorModel;

    private string $encryptionKey;

    public function __construct(?TwoFactorAuthModel $model = null)
    {
        $this->twoFactorModel = $model ?? new TwoFactorAuthModel();
        $this->encryptionKey = getenv('encryption.key') ?: 'default_key_change_me';
    }

    /**
     * Setup 2FA for a user (generates secret).
     *
     * @return array{secret: string, qr_url: string, backup_codes: array}
     */
    public function setup(int $userId, string $email, string $issuer = 'ShuleLabs'): array
    {
        // Generate TOTP secret (Base32 encoded, 160 bits)
        $secret = $this->generateSecret();
        $backupCodes = $this->generateBackupCodes();

        // Store encrypted secret
        $existingConfig = $this->twoFactorModel->getForUser($userId);
        $data = [
            'user_id' => $userId,
            'method' => 'totp',
            'secret_encrypted' => $this->encrypt($secret),
            'backup_codes' => json_encode(array_map(fn ($code) => password_hash($code, PASSWORD_DEFAULT), $backupCodes)),
            'is_enabled' => 0,
        ];

        if ($existingConfig) {
            $this->twoFactorModel->update($existingConfig['id'], $data);
        } else {
            $this->twoFactorModel->insert($data);
        }

        // Generate QR code URL (otpauth://totp/...)
        $qrUrl = $this->generateQrUrl($secret, $email, $issuer);

        return [
            'secret' => $secret,
            'qr_url' => $qrUrl,
            'backup_codes' => $backupCodes,
        ];
    }

    /**
     * Verify a TOTP code.
     */
    public function verify(int $userId, string $code): bool
    {
        $config = $this->twoFactorModel->getForUser($userId);
        if (!$config || !$config['secret_encrypted']) {
            return false;
        }

        $secret = $this->decrypt($config['secret_encrypted']);

        // Check current and adjacent time windows (allows for clock skew)
        $currentTime = floor(time() / 30);
        for ($i = -1; $i <= 1; $i++) {
            $expectedCode = $this->generateTotp($secret, $currentTime + $i);
            if (hash_equals($expectedCode, $code)) {
                $this->twoFactorModel->updateLastUsed($userId);
                return true;
            }
        }

        return false;
    }

    /**
     * Verify and enable 2FA (after initial setup).
     */
    public function verifyAndEnable(int $userId, string $code): bool
    {
        if ($this->verify($userId, $code)) {
            return $this->twoFactorModel->enable($userId);
        }
        return false;
    }

    /**
     * Verify a backup code.
     */
    public function verifyBackupCode(int $userId, string $code): bool
    {
        $config = $this->twoFactorModel->getForUser($userId);
        if (!$config || empty($config['backup_codes'])) {
            return false;
        }

        $backupCodes = $config['backup_codes'];
        foreach ($backupCodes as $index => $hashedCode) {
            if (password_verify($code, $hashedCode)) {
                // Remove used backup code
                unset($backupCodes[$index]);
                $this->twoFactorModel->update($config['id'], [
                    'backup_codes' => json_encode(array_values($backupCodes)),
                ]);
                $this->twoFactorModel->updateLastUsed($userId);
                return true;
            }
        }

        return false;
    }

    /**
     * Disable 2FA for a user.
     */
    public function disable(int $userId): bool
    {
        return $this->twoFactorModel->disable($userId);
    }

    /**
     * Check if 2FA is enabled for a user.
     */
    public function isEnabled(int $userId): bool
    {
        return $this->twoFactorModel->isEnabled($userId);
    }

    /**
     * Generate a random TOTP secret.
     */
    private function generateSecret(int $length = 32): string
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secret = '';
        for ($i = 0; $i < $length; $i++) {
            $secret .= $chars[random_int(0, 31)];
        }
        return $secret;
    }

    /**
     * Generate backup codes.
     *
     * @return array<string>
     */
    private function generateBackupCodes(int $count = 10): array
    {
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $codes[] = strtoupper(bin2hex(random_bytes(4)));
        }
        return $codes;
    }

    /**
     * Generate QR code URL for authenticator apps.
     */
    private function generateQrUrl(string $secret, string $email, string $issuer): string
    {
        $label = urlencode("{$issuer}:{$email}");
        $params = http_build_query([
            'secret' => $secret,
            'issuer' => $issuer,
            'algorithm' => 'SHA1',
            'digits' => 6,
            'period' => 30,
        ]);
        return "otpauth://totp/{$label}?{$params}";
    }

    /**
     * Generate a TOTP code.
     */
    private function generateTotp(string $secret, int $counter): string
    {
        // Decode Base32 secret
        $key = $this->base32Decode($secret);

        // Pack counter as 64-bit big-endian
        $binary = pack('N*', 0) . pack('N*', $counter);

        // HMAC-SHA1
        $hash = hash_hmac('sha1', $binary, $key, true);

        // Dynamic truncation
        $offset = ord($hash[19]) & 0x0f;
        $code = (
            ((ord($hash[$offset]) & 0x7f) << 24) |
            ((ord($hash[$offset + 1]) & 0xff) << 16) |
            ((ord($hash[$offset + 2]) & 0xff) << 8) |
            (ord($hash[$offset + 3]) & 0xff)
        ) % 1000000;

        return str_pad((string) $code, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Decode Base32 string.
     */
    private function base32Decode(string $data): string
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $data = strtoupper($data);
        $buffer = 0;
        $bits = 0;
        $output = '';

        for ($i = 0; $i < strlen($data); $i++) {
            $char = $data[$i];
            if ($char === '=') {
                break;
            }
            $val = strpos($chars, $char);
            if ($val === false) {
                continue;
            }
            $buffer = ($buffer << 5) | $val;
            $bits += 5;
            if ($bits >= 8) {
                $bits -= 8;
                $output .= chr(($buffer >> $bits) & 0xff);
            }
        }

        return $output;
    }

    /**
     * Encrypt a value.
     */
    private function encrypt(string $value): string
    {
        $key = hash('sha256', $this->encryptionKey, true);
        $iv = random_bytes(16);
        $encrypted = openssl_encrypt($value, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
        return base64_encode($iv . $encrypted);
    }

    /**
     * Decrypt a value.
     */
    private function decrypt(string $value): string
    {
        $key = hash('sha256', $this->encryptionKey, true);
        $data = base64_decode($value);
        $iv = substr($data, 0, 16);
        $encrypted = substr($data, 16);
        return openssl_decrypt($encrypted, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
    }
}
