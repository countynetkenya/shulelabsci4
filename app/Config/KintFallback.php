<?php

declare(strict_types=1);

if (! class_exists('KintFallback')) {
    /**
     * Minimal replacements for Kint helper functions when the package is unavailable.
     */
    final class KintFallback
    {
        private static bool $notified = false;

        public static function notifyMissingLibrary(): void
        {
            if (self::$notified) {
                return;
            }

            self::$notified = true;
            self::write('Kint debugger was not found; using fallback debug helpers.');
        }

        /**
         * @return mixed
         */
        public static function dump(...$vars)
        {
            self::notifyMissingLibrary();

            $result = count($vars) === 1 ? $vars[0] : $vars;

            foreach ($vars as $index => $var) {
                $output = print_r($var, true);
                self::write(sprintf('[dump:%d]%s%s', $index + 1, PHP_EOL, $output));
            }

            return $result;
        }

        public static function dumpToString(mixed $var): string
        {
            self::notifyMissingLibrary();

            return print_r($var, true);
        }

        public static function trace(): array
        {
            self::notifyMissingLibrary();

            $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
            self::write('[trace]' . PHP_EOL . print_r($trace, true));

            return $trace;
        }

        private static function write(string $message): void
        {
            if (PHP_SAPI === 'cli' && defined('STDERR')) {
                fwrite(STDERR, $message . PHP_EOL);

                return;
            }

            error_log($message);
        }
    }
}

if (! function_exists('d')) {
    function d(...$vars)
    {
        return KintFallback::dump(...$vars);
    }
}

if (! function_exists('dd')) {
    function dd(...$vars): never
    {
        KintFallback::dump(...$vars);
        exit(1);
    }
}

if (! function_exists('s')) {
    function s(...$vars): array
    {
        return array_map(static fn ($var): string => KintFallback::dumpToString($var), $vars);
    }
}

if (! function_exists('sd')) {
    function sd(...$vars): never
    {
        KintFallback::dump(...$vars);
        exit(1);
    }
}

if (! function_exists('trace')) {
    function trace(): array
    {
        return KintFallback::trace();
    }
}

if (! class_exists('Kint')) {
    final class Kint
    {
        public static function dump(...$vars)
        {
            return KintFallback::dump(...$vars);
        }

        public static function trace(): array
        {
            return KintFallback::trace();
        }
    }
}
