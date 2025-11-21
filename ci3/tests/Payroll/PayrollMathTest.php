<?php

declare(strict_types=1);

use App\Services\Payroll\PayrollCalculator;
use PHPUnit\Framework\TestCase;

final class PayrollMathTest extends TestCase
{
    public function testSummarizeCalculatesNetPay(): void
    {
        $calculator = new PayrollCalculator();
        $result = $calculator->summarize(
            75000.00,
            [2500.00, 1200.00],
            [
                'pre_tax' => [5000.00],
                'post_tax' => [1500.00, 750.00],
            ],
            0.30
        );

        $this->assertSame(78700.0, $result['gross']);
        $this->assertSame(73700.0, $result['taxable']);
        $this->assertSame(29360.0, $result['deductions']['total']);
        $this->assertSame(49340.0, $result['net']);
        $this->assertSame(37.31, $result['effective_rate']);
    }

    public function testSummarizeHandlesZeroGrossGracefully(): void
    {
        $calculator = new PayrollCalculator();
        $result = $calculator->summarize(0.0, [], [], 0.15);

        $this->assertSame(0.0, $result['gross']);
        $this->assertSame(0.0, $result['deductions']['total']);
        $this->assertSame(0.0, $result['net']);
        $this->assertSame(0.0, $result['effective_rate']);
    }
}
