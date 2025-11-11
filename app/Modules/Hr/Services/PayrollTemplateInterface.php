<?php

declare(strict_types=1);

namespace Modules\Hr\Services;

/**
 * Contract for payroll templates per country/region.
 */
interface PayrollTemplateInterface
{
    public function getKey(): string;

    /**
     * Calculates payroll totals for a pay run.
     *
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     */
    public function calculate(array $payload): array;
}
