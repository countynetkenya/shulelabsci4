<?php

if (!function_exists('shulelabs_bootstrap_env')) {
    /**
     * @return array<string, string>
     */
    function shulelabs_bootstrap_env(string $basePath): array
    {
        $basePath = rtrim($basePath, DIRECTORY_SEPARATOR);
        $envFile = $basePath . DIRECTORY_SEPARATOR . '.env';

        if (!is_readable($envFile)) {
            return [];
        }

        $variables = [];
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || strpos($line, '#') === 0) {
                continue;
            }

            if (strpos($line, '=') === false) {
                continue;
            }

            list($key, $value) = array_map('trim', explode('=', $line, 2));
            if ($key === '') {
                continue;
            }

            $length = strlen($value);
            if ($length >= 2) {
                $firstChar = $value[0];
                $lastChar = $value[$length - 1];

                $hasMatchingQuotes = ($firstChar === '"' && $lastChar === '"')
                    || ($firstChar === "'" && $lastChar === "'");

                if ($hasMatchingQuotes) {
                    $value = substr($value, 1, -1);
                }
            }

            $value = str_replace('\\n', PHP_EOL, $value);

            $variables[$key] = $value;
            putenv($key . '=' . $value);
            $_ENV[$key] = $value;
            if (!array_key_exists($key, $_SERVER)) {
                $_SERVER[$key] = $value;
            }
        }

        if (!empty($variables['APP_ENV']) && empty($_SERVER['CI_ENV'])) {
            $_SERVER['CI_ENV'] = $variables['APP_ENV'];
        }

        if (!empty($variables['CI_ENV']) && empty($_SERVER['CI_ENV'])) {
            $_SERVER['CI_ENV'] = $variables['CI_ENV'];
        }

        return $variables;
    }
}

if (!function_exists('shulelabs_env')) {
    function shulelabs_env(string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, $_ENV)) {
            return $_ENV[$key];
        }

        $value = getenv($key);
        if ($value !== false) {
            return $value;
        }

        if (array_key_exists($key, $_SERVER)) {
            return $_SERVER[$key];
        }

        return $default;
    }
}
