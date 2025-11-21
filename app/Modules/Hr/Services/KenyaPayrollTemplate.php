<?php

declare(strict_types=1);

namespace Modules\Hr\Services;

use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;

/**
 * Kenyan payroll template with effective-dated statutory calculations.
 */
class KenyaPayrollTemplate implements PayrollTemplateInterface
{
    private const PERSONAL_RELIEF = 2400.0;
    private const HOUSING_LEVY_RATE = 0.015;
    private const SHIF_RATE = 0.0275;
    private const SHIF_CAP = 5000.0;
    private const NSSF_RATE = 0.06;
    private const NSSF_TIER_CAP = 36000.0;

    /** @var list<array{upper: float|null, rate: float}> */
    private const TAX_BANDS = [
        ['upper' => 24000.0, 'rate' => 0.10],
        ['upper' => 32333.0, 'rate' => 0.25],
        ['upper' => null,    'rate' => 0.30],
    ];

    public function getKey(): string
    {
        return 'KE';
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function calculate(array $payload): array
    {
        $baseSalary = $this->normaliseAmount($payload['base_salary'] ?? null, 'Base salary');
        $allowances = $this->normaliseAllowances($payload['allowances'] ?? []);
        $deductions = $this->normaliseDeductions($payload['deductions'] ?? []);
        $payoutDate = $this->parseDate($payload['payout_date'] ?? null);

        $taxableAllowancesTotal   = 0.0;
        $nonTaxableAllowancesTotal = 0.0;

        foreach ($allowances as $allowance) {
            if ($allowance['taxable']) {
                $taxableAllowancesTotal += $allowance['amount'];
            } else {
                $nonTaxableAllowancesTotal += $allowance['amount'];
            }
        }

        $grossPay     = $baseSalary + $taxableAllowancesTotal + $nonTaxableAllowancesTotal;
        $taxableGross = $baseSalary + $taxableAllowancesTotal;

        $preTaxContributions  = 0.0;
        $postTaxDeductions    = 0.0;

        foreach ($deductions as $deduction) {
            if ($deduction['pre_tax']) {
                $preTaxContributions += $deduction['amount'];
            } else {
                $postTaxDeductions += $deduction['amount'];
            }
        }

        $nssf         = $this->calculateNssf($taxableGross, $payoutDate);
        $housingLevy  = $this->calculateHousingLevy($grossPay, $payoutDate);
        $shif         = $this->calculateShif($grossPay, $payoutDate);

        $taxableIncome = max(0.0, $taxableGross - $nssf - $preTaxContributions);
        $payeBeforeRelief = $this->calculatePaye($taxableIncome);
        $paye            = max(0.0, $payeBeforeRelief - self::PERSONAL_RELIEF);

        $totalStatutory = $paye + $nssf + $housingLevy + $shif;
        $netPay = $grossPay - $totalStatutory - $preTaxContributions - $postTaxDeductions;

        $statutory = [
            'paye'          => $this->roundCurrency($paye),
            'nssf'          => $this->roundCurrency($nssf),
            'housing_levy'  => $this->roundCurrency($housingLevy),
            'shif'          => $this->roundCurrency($shif),
            'total'         => $this->roundCurrency($totalStatutory),
        ];

        $effectiveTaxRate = $taxableIncome <= 0.0 ? 0.0 : ($paye / $taxableIncome) * 100;

        $compliance = [
            'personal_relief'            => self::PERSONAL_RELIEF,
            'taxable_income'            => $this->roundCurrency($taxableIncome),
            'taxable_gross'             => $this->roundCurrency($taxableGross),
            'effective_tax_rate_percent' => $this->roundCurrency($effectiveTaxRate),
            'pre_tax_deductions_total'  => $this->roundCurrency($preTaxContributions),
            'post_tax_deductions_total' => $this->roundCurrency($postTaxDeductions),
            'bands'                     => self::TAX_BANDS,
            'notes'                     => $this->notesForPeriod($payoutDate),
        ];

        return [
            'allowances'                 => $allowances,
            'deductions'                 => $deductions,
            'gross_pay'                  => $this->roundCurrency($grossPay),
            'taxable_pay'                => $this->roundCurrency($taxableGross),
            'net_pay'                    => $this->roundCurrency($netPay),
            'statutory'                  => $statutory,
            'compliance'                 => $compliance,
        ];
    }

    private function roundCurrency(float $value): float
    {
        return round($value, 2);
    }

    private function parseDate(null|string $raw): DateTimeImmutable
    {
        $raw ??= 'now';
        try {
            $date = new DateTimeImmutable($raw, new DateTimeZone('UTC'));
        } catch (\Exception $exception) {
            throw new InvalidArgumentException('Invalid payout date provided.', 0, $exception);
        }

        return $date;
    }

    private function normaliseAmount(mixed $value, string $label): float
    {
        if (! is_numeric($value)) {
            throw new InvalidArgumentException(sprintf('%s must be numeric.', $label));
        }

        $amount = (float) $value;
        if ($amount < 0) {
            throw new InvalidArgumentException(sprintf('%s cannot be negative.', $label));
        }

        return $amount;
    }

    /**
     * @param array<int|string, mixed> $allowances
     * @return list<array{name: string, amount: float, taxable: bool}>
     */
    private function normaliseAllowances(array $allowances): array
    {
        $normalised = [];
        foreach ($allowances as $allowance) {
            if (! is_array($allowance)) {
                throw new InvalidArgumentException('Each allowance must be an array.');
            }

            $name     = trim((string) ($allowance['name'] ?? ''));
            $amount   = $this->normaliseAmount($allowance['amount'] ?? null, 'Allowance amount');
            $taxable  = array_key_exists('taxable', $allowance) ? (bool) $allowance['taxable'] : true;

            if ($name === '') {
                throw new InvalidArgumentException('Allowance name is required.');
            }

            $normalised[] = [
                'name'    => $name,
                'amount'  => $this->roundCurrency($amount),
                'taxable' => $taxable,
            ];
        }

        return $normalised;
    }

    /**
     * @param array<int|string, mixed> $deductions
     * @return list<array{name: string, amount: float, pre_tax: bool}>
     */
    private function normaliseDeductions(array $deductions): array
    {
        $normalised = [];
        foreach ($deductions as $deduction) {
            if (! is_array($deduction)) {
                throw new InvalidArgumentException('Each deduction must be an array.');
            }

            $name    = trim((string) ($deduction['name'] ?? ''));
            $amount  = $this->normaliseAmount($deduction['amount'] ?? null, 'Deduction amount');
            $preTax  = array_key_exists('pre_tax', $deduction) ? (bool) $deduction['pre_tax'] : false;

            if ($name === '') {
                throw new InvalidArgumentException('Deduction name is required.');
            }

            $normalised[] = [
                'name'    => $name,
                'amount'  => $this->roundCurrency($amount),
                'pre_tax' => $preTax,
            ];
        }

        return $normalised;
    }

    private function calculateNssf(float $taxableGross, DateTimeImmutable $payoutDate): float
    {
        $modernisationStart = new DateTimeImmutable('2024-02-01', new DateTimeZone('UTC'));
        if ($payoutDate < $modernisationStart) {
            // Pre-2024 rates were flat at KES 200 per employee.
            return 200.0;
        }

        $chargeable = min($taxableGross, self::NSSF_TIER_CAP);
        return $chargeable * self::NSSF_RATE;
    }

    private function calculateHousingLevy(float $grossPay, DateTimeImmutable $payoutDate): float
    {
        $levyStart = new DateTimeImmutable('2023-07-01', new DateTimeZone('UTC'));
        if ($payoutDate < $levyStart) {
            return 0.0;
        }

        return $grossPay * self::HOUSING_LEVY_RATE;
    }

    private function calculateShif(float $grossPay, DateTimeImmutable $payoutDate): float
    {
        $shifStart = new DateTimeImmutable('2024-07-01', new DateTimeZone('UTC'));
        if ($payoutDate < $shifStart) {
            return 0.0;
        }

        return min($grossPay * self::SHIF_RATE, self::SHIF_CAP);
    }

    private function calculatePaye(float $taxableIncome): float
    {
        $remaining = $taxableIncome;
        $tax       = 0.0;
        $lowerBandEdge = 0.0;

        foreach (self::TAX_BANDS as $band) {
            $upper = $band['upper'];
            $rate  = $band['rate'];

            if ($upper === null) {
                $tax += $remaining * $rate;
                break;
            }

            $bandWidth = max(0.0, $upper - $lowerBandEdge);
            if ($remaining <= 0.0) {
                break;
            }

            $charge = min($remaining, $bandWidth);
            $tax   += $charge * $rate;
            $remaining -= $charge;
            $lowerBandEdge = $upper;
        }

        return $tax;
    }

    /**
     * @return list<string>
     */
    private function notesForPeriod(DateTimeImmutable $payoutDate): array
    {
        $notes = [];

        $levyStart = new DateTimeImmutable('2023-07-01', new DateTimeZone('UTC'));
        if ($payoutDate >= $levyStart) {
            $notes[] = 'Housing levy applied at 1.5% of gross pay.';
        }

        $modernisationStart = new DateTimeImmutable('2024-02-01', new DateTimeZone('UTC'));
        if ($payoutDate >= $modernisationStart) {
            $notes[] = 'Tiered NSSF rates in effect (6% capped at KES 36,000).';
        } else {
            $notes[] = 'Legacy flat NSSF contribution of KES 200 applied.';
        }

        $shifStart = new DateTimeImmutable('2024-07-01', new DateTimeZone('UTC'));
        if ($payoutDate >= $shifStart) {
            $notes[] = 'SHIF health contribution charged at 2.75% of gross (capped at KES 5,000).';
        } else {
            $notes[] = 'SHIF not yet in effect for this period.';
        }

        return $notes;
    }
}
