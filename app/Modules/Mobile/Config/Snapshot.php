<?php

declare(strict_types=1);

namespace Modules\Mobile\Config;

use CodeIgniter\Config\BaseConfig;

class Snapshot extends BaseConfig
{
    public string $signingKey = 'changeme-offline-signing-key';

    public string $keyId = 'snapshot-key';

    public int $defaultTtlSeconds = 3600;

    /**
     * @var array<string, string>
     */
    public array $fallbackKeys = [];

    public function __construct()
    {
        parent::__construct();

        $this->signingKey = (string) $this->readEnv('mobile.snapshot.signingKey', $this->signingKey);
        $this->keyId = (string) $this->readEnv('mobile.snapshot.keyId', $this->keyId);
        $this->defaultTtlSeconds = (int) $this->readEnv('mobile.snapshot.ttl', $this->defaultTtlSeconds);

        $fallback = $this->readEnv('mobile.snapshot.fallback');
        if (is_string($fallback) && $fallback !== '') {
            $this->fallbackKeys = $this->parseFallbackKeys($fallback);
        }
    }

    private function readEnv(string $key, mixed $default = null): mixed
    {
        if (function_exists('env')) {
            /** @phpstan-ignore-next-line */
            return env($key, $default);
        }

        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
        if ($value === false) {
            return $default;
        }

        return $value;
    }

    /**
     * Parses a comma-separated list of keyId:key pairs into an associative array.
     *
     * The input should be in the format: "keyId1:key1,keyId2:key2"
     * For example: "snapshot-key-1:secret1,snapshot-key-2:secret2"
     *
     * @param string $definition Comma-separated list of keyId:key pairs.
     * @return array<string, string> Associative array of keyId => key.
     */
    private function parseFallbackKeys(string $definition): array
    {
        $keys = [];

        foreach (explode(',', $definition) as $entry) {
            $entry = trim($entry);
            if ($entry === '') {
                continue;
            }

            [$keyId, $key] = array_pad(explode(':', $entry, 2), 2, '');
            $keyId = trim($keyId);
            $key = trim($key);

            if ($keyId === '' || $key === '') {
                continue;
            }

            $keys[$keyId] = $key;
        }

        return $keys;
    }
}
