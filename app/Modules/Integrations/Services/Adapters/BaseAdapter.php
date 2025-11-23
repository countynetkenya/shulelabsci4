<?php

namespace Modules\Integrations\Services\Adapters;

use Modules\Integrations\Services\Interfaces\IntegrationAdapterInterface;

/**
 * Base class for integration adapters.
 * Provides common functionality for all adapters.
 */
abstract class BaseAdapter implements IntegrationAdapterInterface
{
    /**
     * @var array<string, mixed>
     */
    protected array $config;

    /**
     * @param array<string, mixed> $config
     */
    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function validateConfig(array $config): bool
    {
        $required = $this->getRequiredConfigKeys();

        foreach ($required as $key) {
            if (!isset($config[$key]) || empty($config[$key])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get the list of required configuration keys.
     *
     * @return array<string>
     */
    abstract protected function getRequiredConfigKeys(): array;

    /**
     * Get a configuration value.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    protected function getConfig(string $key, $default = null)
    {
        return $this->config[$key] ?? $default;
    }

    /**
     * Log a message (can be overridden by child classes).
     *
     * @param string $level
     * @param string $message
     * @param array<string, mixed> $context
     */
    protected function log(string $level, string $message, array $context = []): void
    {
        log_message($level, sprintf('[%s] %s', $this->getName(), $message), $context);
    }
}
