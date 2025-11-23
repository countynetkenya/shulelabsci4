<?php

namespace App\Libraries;

/**
 * Hash Compatibility Library.
 *
 * Provides password hashing compatible with CI3's hash method
 * Uses SHA-512 with encryption key salt
 */
class HashCompat
{
    /**
     * Hash a password using CI3 compatible method.
     *
     * @param string $password
     * @return string
     */
    public function hash(string $password): string
    {
        // Try to get encryption key from multiple sources
        $encryptionKey = env('encryption.key', env('ENCRYPTION_KEY', ''));

        if (empty($encryptionKey)) {
            // Try to get from CI4 Encryption config
            $config = config('Encryption');
            $encryptionKey = $config->key ?? '';
        }

        if (empty($encryptionKey)) {
            // Fall back to CI3 encryption key for compatibility during migration
            $encryptionKey = '8bc8ae426d4354c8df0488e2d7f1a9de';
        }

        if (empty($encryptionKey)) {
            throw new \RuntimeException('Encryption key not set. Please set ENCRYPTION_KEY in .env file.');
        }

        return hash('sha512', $password . $encryptionKey);
    }

    /**
     * Verify a password against a hash.
     *
     * @param string $password
     * @param string $hash
     * @return bool
     */
    public function verify(string $password, string $hash): bool
    {
        return $this->hash($password) === $hash;
    }
}
