<?php

defined('BASEPATH') or exit('No direct script access allowed');

class MY_Log extends CI_Log
{
    protected function _format_line($level, $date, $message)
    {
        $payload = [
            'level' => strtolower($level),
            'timestamp' => $date,
            'channel' => 'application',
        ];

        $httpContext = $this->buildHttpContext();
        if (!empty($httpContext)) {
            $payload['context'] = $httpContext;
        }

        $sanitized = $this->sanitizeMessage($message);
        $payload['message'] = $sanitized;

        $json = json_encode($payload, JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            $fallback = [
                'level' => strtolower($level),
                'timestamp' => $date,
                'channel' => 'application',
                'message' => $this->maskPii((string) $message),
            ];
            $json = json_encode($fallback, JSON_UNESCAPED_SLASHES);
        }

        return $json . PHP_EOL;
    }

    protected function buildHttpContext(): array
    {
        $context = [];

        if (!empty($_SERVER['REQUEST_METHOD'])) {
            $context['http_method'] = $_SERVER['REQUEST_METHOD'];
        }

        if (!empty($_SERVER['REQUEST_URI'])) {
            $context['request_uri'] = $_SERVER['REQUEST_URI'];
        }

        if (!empty($_SERVER['REMOTE_ADDR'])) {
            $context['client_ip'] = $_SERVER['REMOTE_ADDR'];
        }

        if (!empty($_SERVER['HTTP_USER_AGENT'])) {
            $context['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
        }

        return $context;
    }

    protected function sanitizeMessage($message)
    {
        if (is_array($message)) {
            return $this->sanitizeArray($message);
        }

        if (is_object($message)) {
            return $this->sanitizeArray((array) $message);
        }

        if (is_string($message)) {
            $decoded = json_decode($message, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $this->sanitizeArray($decoded);
            }

            return $this->maskPii($message);
        }

        return $message;
    }

    protected function sanitizeArray(array $data): array
    {
        $sanitized = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $sanitized[$key] = $this->sanitizeArray($value);
                continue;
            }

            if (is_object($value)) {
                $sanitized[$key] = $this->sanitizeArray((array) $value);
                continue;
            }

            if (is_string($value)) {
                if ($this->isSensitiveKey($key)) {
                    $sanitized[$key] = $this->maskSensitiveValue($value);
                } else {
                    $sanitized[$key] = $this->maskPii($value);
                }
                continue;
            }

            $sanitized[$key] = $value;
        }

        return $sanitized;
    }

    protected function isSensitiveKey($key): bool
    {
        $key = strtolower((string) $key);
        $sensitive = [
            'email', 'phone', 'mobile', 'msisdn', 'contact',
            'national_id', 'nationalid', 'id_number', 'idnumber',
            'ssn', 'taxpin', 'kra_pin', 'nin', 'passport', 'account',
        ];

        foreach ($sensitive as $candidate) {
            if (strpos($key, $candidate) !== false) {
                return true;
            }
        }

        return false;
    }

    protected function maskSensitiveValue(string $value): string
    {
        $value = trim($value);
        $length = strlen($value);

        if ($length <= 4) {
            return str_repeat('*', $length ?: 4);
        }

        $visible = max(1, (int) floor($length * 0.2));
        return substr($value, 0, $visible) . str_repeat('*', $length - ($visible * 2)) . substr($value, -$visible);
    }

    protected function maskPii(string $value): string
    {
        $value = preg_replace_callback(
            '/([A-Z0-9._%+-]+)@([A-Z0-9.-]+\.[A-Z]{2,})/i',
            static function ($matches) {
                $local = $matches[1];
                if (strlen($local) <= 2) {
                    $local = substr($local, 0, 1) . '*';
                } else {
                    $local = substr($local, 0, 2) . str_repeat('*', max(strlen($matches[1]) - 2, 1));
                }

                return $local . '@' . $matches[2];
            },
            $value
        );

        $value = preg_replace_callback(
            '/(\+?\d[\d\s\-().]{5,}\d)/',
            function ($matches) {
                return $this->maskDigits($matches[1]);
            },
            $value
        );

        $value = preg_replace_callback(
            '/\b(\d{6,})\b/',
            function ($matches) {
                return $this->maskDigits($matches[1]);
            },
            $value
        );

        return $value;
    }

    protected function maskDigits(string $value): string
    {
        $digits = preg_replace('/\\D+/', '', $value);
        $length = strlen($digits);
        if ($length <= 4) {
            return str_repeat('*', $length ?: 4);
        }

        $visible = 2;
        $maskedDigits = substr($digits, 0, $visible) . str_repeat('*', $length - ($visible * 2)) . substr($digits, -$visible);

        $result = '';
        $maskIndex = 0;
        foreach (str_split($value) as $char) {
            if (ctype_digit($char)) {
                $result .= $maskedDigits[$maskIndex++] ?? '*';
            } else {
                $result .= $char;
            }
        }

        return $result;
    }
}
