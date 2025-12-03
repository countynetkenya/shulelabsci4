<?php

namespace Modules\Scheduler\Services;

use RuntimeException;

/**
 * Parses and validates cron expressions and calculates next run times.
 *
 * Supports standard 5-field cron expressions: minute hour day-of-month month day-of-week
 */
class CronExpressionParser
{
    private const MINUTE_MIN = 0;
    private const MINUTE_MAX = 59;
    private const HOUR_MIN = 0;
    private const HOUR_MAX = 23;
    private const DAY_MIN = 1;
    private const DAY_MAX = 31;
    private const MONTH_MIN = 1;
    private const MONTH_MAX = 12;
    private const DOW_MIN = 0;
    private const DOW_MAX = 6;

    private const MONTH_NAMES = [
        'jan' => 1, 'feb' => 2, 'mar' => 3, 'apr' => 4,
        'may' => 5, 'jun' => 6, 'jul' => 7, 'aug' => 8,
        'sep' => 9, 'oct' => 10, 'nov' => 11, 'dec' => 12,
    ];

    private const DOW_NAMES = [
        'sun' => 0, 'mon' => 1, 'tue' => 2, 'wed' => 3,
        'thu' => 4, 'fri' => 5, 'sat' => 6,
    ];

    /**
     * Validate a cron expression.
     */
    public function isValid(string $expression): bool
    {
        try {
            $this->parse($expression);
            return true;
        } catch (RuntimeException) {
            return false;
        }
    }

    /**
     * Parse a cron expression into its components.
     *
     * @return array{minute: array<int>, hour: array<int>, day: array<int>, month: array<int>, dow: array<int>}
     */
    public function parse(string $expression): array
    {
        $parts = preg_split('/\s+/', trim($expression));
        if ($parts === false || count($parts) !== 5) {
            throw new RuntimeException('Cron expression must have exactly 5 fields');
        }

        return [
            'minute' => $this->parseField($parts[0], self::MINUTE_MIN, self::MINUTE_MAX),
            'hour' => $this->parseField($parts[1], self::HOUR_MIN, self::HOUR_MAX),
            'day' => $this->parseField($parts[2], self::DAY_MIN, self::DAY_MAX, true),
            'month' => $this->parseField($parts[3], self::MONTH_MIN, self::MONTH_MAX, false, self::MONTH_NAMES),
            'dow' => $this->parseField($parts[4], self::DOW_MIN, self::DOW_MAX, true, self::DOW_NAMES),
        ];
    }

    /**
     * Calculate the next run time from a given base time.
     */
    public function getNextRunTime(string $expression, ?string $from = null, string $timezone = 'Africa/Nairobi'): string
    {
        $parsed = $this->parse($expression);
        $tz = new \DateTimeZone($timezone);
        $dt = $from ? new \DateTime($from, $tz) : new \DateTime('now', $tz);

        // Start from next minute
        $dt->modify('+1 minute');
        $dt->setTime((int) $dt->format('H'), (int) $dt->format('i'), 0);

        // Search for next valid time (limit to prevent infinite loop)
        for ($i = 0; $i < 527040; $i++) { // ~1 year in minutes
            $minute = (int) $dt->format('i');
            $hour = (int) $dt->format('G');
            $day = (int) $dt->format('j');
            $month = (int) $dt->format('n');
            $dow = (int) $dt->format('w');

            if (
                in_array($minute, $parsed['minute'], true) &&
                in_array($hour, $parsed['hour'], true) &&
                in_array($month, $parsed['month'], true) &&
                $this->matchesDayConstraint($day, $dow, $parsed['day'], $parsed['dow'])
            ) {
                return $dt->format('Y-m-d H:i:s');
            }

            $dt->modify('+1 minute');
        }

        throw new RuntimeException('Could not calculate next run time within one year');
    }

    /**
     * Get a human-readable description of the cron expression.
     */
    public function describe(string $expression): string
    {
        $parsed = $this->parse($expression);

        $parts = [];

        // Minutes
        if ($parsed['minute'] === range(0, 59)) {
            $parts[] = 'Every minute';
        } elseif (count($parsed['minute']) === 1) {
            $parts[] = sprintf('At minute %d', $parsed['minute'][0]);
        } else {
            $parts[] = sprintf('At minutes %s', implode(', ', $parsed['minute']));
        }

        // Hours
        if ($parsed['hour'] !== range(0, 23)) {
            if (count($parsed['hour']) === 1) {
                $parts[] = sprintf('of hour %d', $parsed['hour'][0]);
            } else {
                $parts[] = sprintf('of hours %s', implode(', ', $parsed['hour']));
            }
        }

        // Days
        if ($parsed['day'] !== range(1, 31)) {
            $parts[] = sprintf('on day %s', implode(', ', $parsed['day']));
        }

        // Months
        if ($parsed['month'] !== range(1, 12)) {
            $months = array_map(fn ($m) => date('M', mktime(0, 0, 0, $m, 1)), $parsed['month']);
            $parts[] = sprintf('in %s', implode(', ', $months));
        }

        // Day of week
        if ($parsed['dow'] !== range(0, 6)) {
            $days = array_map(fn ($d) => ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'][$d], $parsed['dow']);
            $parts[] = sprintf('on %s', implode(', ', $days));
        }

        return implode(' ', $parts);
    }

    /**
     * Parse a single cron field.
     *
     * @param array<string, int> $aliases
     * @return array<int>
     */
    private function parseField(string $field, int $min, int $max, bool $allowQuestionMark = false, array $aliases = []): array
    {
        // Handle question mark (any value)
        if ($allowQuestionMark && $field === '?') {
            return range($min, $max);
        }

        // Handle asterisk (all values)
        if ($field === '*') {
            return range($min, $max);
        }

        $values = [];

        // Handle comma-separated values
        $parts = explode(',', $field);
        foreach ($parts as $part) {
            // Handle step values (*/5 or 1-10/2)
            if (str_contains($part, '/')) {
                [$range, $step] = explode('/', $part);
                $step = (int) $step;

                if ($range === '*') {
                    $rangeValues = range($min, $max);
                } else {
                    $rangeValues = $this->parseRange($range, $min, $max, $aliases);
                }

                foreach ($rangeValues as $i => $val) {
                    if ($i % $step === 0) {
                        $values[] = $val;
                    }
                }
            }
            // Handle range (1-5)
            elseif (str_contains($part, '-')) {
                $values = array_merge($values, $this->parseRange($part, $min, $max, $aliases));
            }
            // Single value
            else {
                $val = $this->parseValue($part, $min, $max, $aliases);
                $values[] = $val;
            }
        }

        $values = array_unique($values);
        sort($values);

        return $values;
    }

    /**
     * Parse a range expression (e.g., "1-5").
     *
     * @param array<string, int> $aliases
     * @return array<int>
     */
    private function parseRange(string $range, int $min, int $max, array $aliases): array
    {
        [$start, $end] = explode('-', $range);
        $startVal = $this->parseValue($start, $min, $max, $aliases);
        $endVal = $this->parseValue($end, $min, $max, $aliases);

        return range($startVal, $endVal);
    }

    /**
     * Parse a single value (number or alias).
     *
     * @param array<string, int> $aliases
     */
    private function parseValue(string $value, int $min, int $max, array $aliases): int
    {
        $lower = strtolower($value);
        if (isset($aliases[$lower])) {
            return $aliases[$lower];
        }

        $val = (int) $value;
        if ($val < $min || $val > $max) {
            throw new RuntimeException(sprintf('Value %d out of range [%d-%d]', $val, $min, $max));
        }

        return $val;
    }

    /**
     * Check if day/dow constraints match.
     *
     * @param array<int> $allowedDays
     * @param array<int> $allowedDow
     */
    private function matchesDayConstraint(int $day, int $dow, array $allowedDays, array $allowedDow): bool
    {
        $dayMatch = in_array($day, $allowedDays, true);
        $dowMatch = in_array($dow, $allowedDow, true);

        // If both are restricted, use OR logic (traditional cron behavior)
        $dayRestricted = $allowedDays !== range(1, 31);
        $dowRestricted = $allowedDow !== range(0, 6);

        if ($dayRestricted && $dowRestricted) {
            return $dayMatch || $dowMatch;
        }

        return $dayMatch && $dowMatch;
    }
}
