<?php

namespace App\Services\Payroll;

class PayrollCalculator
{
    /**
     * @param iterable<float|int|string> $allowances
     * @param array{pre_tax?: iterable<float|int|string>, post_tax?: iterable<float|int|string>} $deductions
     * @return array{
     *     gross: float,
     *     taxable: float,
     *     tax: float,
     *     net: float,
     *     deductions: array{pre_tax: float, post_tax: float, taxes: float, total: float},
     *     effective_rate: float
     * }
     */
    public function summarize(
        float $baseSalary,
        iterable $allowances = [],
        array $deductions = [],
        float $taxRate = 0.0
    ): array {
        $allowances = is_array($allowances) ? $allowances : iterator_to_array($allowances, false);
        $preTaxItems = $deductions['pre_tax'] ?? [];
        $preTaxItems = is_array($preTaxItems) ? $preTaxItems : iterator_to_array($preTaxItems, false);
        $postTaxItems = $deductions['post_tax'] ?? [];
        $postTaxItems = is_array($postTaxItems) ? $postTaxItems : iterator_to_array($postTaxItems, false);

        $gross = $baseSalary + array_sum(array_map('floatval', $allowances));
        $preTax = array_sum(array_map('floatval', $preTaxItems));
        $postTax = array_sum(array_map('floatval', $postTaxItems));

        $taxable = max($gross - $preTax, 0.0);
        $tax = round($taxable * $taxRate, 2);
        $totalDeductions = $preTax + $postTax + $tax;
        $net = round($gross - $preTax - $tax - $postTax, 2);

        $effectiveRate = $gross > 0 ? round(($totalDeductions / $gross) * 100, 2) : 0.0;

        return [
            'gross' => round($gross, 2),
            'taxable' => round($taxable, 2),
            'tax' => $tax,
            'net' => $net,
            'deductions' => [
                'pre_tax' => round($preTax, 2),
                'post_tax' => round($postTax, 2),
                'taxes' => $tax,
                'total' => round($totalDeductions, 2),
            ],
            'effective_rate' => $effectiveRate,
        ];
    }
}
