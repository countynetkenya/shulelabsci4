<?php

declare(strict_types=1);

namespace Tests\Ci4\Hr;

use Modules\Foundation\Services\AuditService;
use Modules\Foundation\Services\MakerCheckerService;
use Modules\Hr\Services\KenyaPayrollTemplate;
use Modules\Hr\Services\PayrollService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PayrollServiceTest extends TestCase
{
    private MakerCheckerService&MockObject $makerChecker;

    private AuditService&MockObject $auditService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->makerChecker = $this->createMock(MakerCheckerService::class);
        $this->auditService = $this->createMock(AuditService::class);
    }

    public function testGeneratesKenyanPayslipWithStatutoryBreakdown(): void
    {
        $service = new PayrollService([new KenyaPayrollTemplate()], $this->makerChecker, $this->auditService);

        $this->makerChecker
            ->expects($this->once())
            ->method('submit')
            ->with(
                'payroll.payslip',
                $this->arrayHasKey('statutory'),
                $this->arrayHasKey('tenant_id')
            )
            ->willReturn(101);

        $this->auditService
            ->expects($this->once())
            ->method('recordEvent')
            ->with(
                $this->stringStartsWith('payroll.payslip.'),
                'payslip_generated',
                $this->arrayHasKey('tenant_id'),
                null,
                $this->arrayHasKey('payslip_number'),
                $this->arrayHasKey('template')
            );

        $payload = [
            'employee_id'   => 'EMP-42',
            'employee_name' => 'Faith Wanjiku',
            'base_salary'   => 120000.0,
            'allowances'    => [
                ['name' => 'House Allowance', 'amount' => 20000, 'taxable' => true],
                ['name' => 'Transport Allowance', 'amount' => 8000, 'taxable' => false],
            ],
            'deductions'    => [
                ['name' => 'Pension', 'amount' => 5000, 'pre_tax' => true],
                ['name' => 'Sacco Loan', 'amount' => 7000, 'pre_tax' => false],
            ],
            'period'        => '2025-01',
            'payout_date'   => '2025-01-31',
            'country_code'  => 'KE',
        ];

        $context = [
            'tenant_id' => 'tenant-1',
            'actor_id'  => 'hr-approver',
        ];

        $payslip = $service->generatePayslip($payload, $context);

        $this->assertSame('EMP42-202501', $payslip->getPayslipNumber());
        $this->assertSame(148000.0, $payslip->getGrossPay());
        $this->assertSame(140000.0, $payslip->getTaxablePay());
        $this->assertEqualsWithDelta(95314.65, $payslip->getNetPay(), 0.01);

        $statutory = $payslip->getStatutoryDeductions();
        $this->assertEqualsWithDelta(32235.35, $statutory['paye'], 0.01);
        $this->assertEqualsWithDelta(2160.0, $statutory['nssf'], 0.01);
        $this->assertEqualsWithDelta(2220.0, $statutory['housing_levy'], 0.01);
        $this->assertEqualsWithDelta(4070.0, $statutory['shif'], 0.01);

        $this->assertSame(101, $payslip->getApprovalRequestId());
        $this->assertContains('Tiered NSSF rates in effect (6% capped at KES 36,000).', $payslip->getComplianceDetails()['notes']);
        $this->assertContains('SHIF health contribution charged at 2.75% of gross (capped at KES 5,000).', $payslip->getComplianceDetails()['notes']);
    }

    public function testLegacyPeriodAppliesFlatNssfAndNoShif(): void
    {
        $service = new PayrollService([new KenyaPayrollTemplate()], $this->makerChecker, $this->auditService);

        $this->makerChecker
            ->expects($this->once())
            ->method('submit')
            ->willReturn(5);

        $this->auditService
            ->expects($this->once())
            ->method('recordEvent');

        $payload = [
            'employee_id'   => 'EMP-99',
            'employee_name' => 'James Otieno',
            'base_salary'   => 50000,
            'allowances'    => [
                ['name' => 'Overtime', 'amount' => 5000, 'taxable' => true],
            ],
            'deductions'    => [],
            'period'        => '2023-11',
            'payout_date'   => '2023-11-30',
            'country_code'  => 'KE',
        ];

        $context = ['tenant_id' => 'tenant-2'];

        $payslip = $service->generatePayslip($payload, $context);

        $statutory = $payslip->getStatutoryDeductions();
        $this->assertSame(200.0, $statutory['nssf']);
        $this->assertSame(0.0, $statutory['shif']);
        $this->assertContains('Legacy flat NSSF contribution of KES 200 applied.', $payslip->getComplianceDetails()['notes']);
        $this->assertContains('SHIF not yet in effect for this period.', $payslip->getComplianceDetails()['notes']);
    }
}
