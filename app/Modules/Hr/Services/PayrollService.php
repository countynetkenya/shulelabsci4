<?php

declare(strict_types=1);

namespace Modules\Hr\Services;

use InvalidArgumentException;
use Modules\Foundation\Services\AuditService;
use Modules\Foundation\Services\MakerCheckerService;
use Modules\Hr\Domain\Payslip;

/**
 * Coordinates payroll generation and approval workflows.
 */
class PayrollService
{
    /** @var array<string, PayrollTemplateInterface> */
    private array $templates = [];

    /**
     * @param iterable<PayrollTemplateInterface> $templates
     */
    public function __construct(
        iterable $templates,
        private readonly MakerCheckerService $makerChecker,
        private readonly AuditService $auditService,
    ) {
        foreach ($templates as $template) {
            $this->registerTemplate($template);
        }
    }

    public function registerTemplate(PayrollTemplateInterface $template): void
    {
        $this->templates[strtoupper($template->getKey())] = $template;
    }

    public function hasTemplate(string $key): bool
    {
        return array_key_exists(strtoupper($key), $this->templates);
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $context
     */
    public function generatePayslip(array $payload, array $context): Payslip
    {
        $employeeId = trim((string) ($payload['employee_id'] ?? ''));
        $employeeName = trim((string) ($payload['employee_name'] ?? ''));
        $period = trim((string) ($payload['period'] ?? ''));
        $payoutDate = trim((string) ($payload['payout_date'] ?? ''));
        $countryCode = strtoupper((string) ($payload['country_code'] ?? 'KE'));

        if ($employeeId === '') {
            throw new InvalidArgumentException('Employee ID is required.');
        }

        if ($employeeName === '') {
            throw new InvalidArgumentException('Employee name is required.');
        }

        if ($period === '') {
            throw new InvalidArgumentException('Payroll period is required.');
        }

        if ($payoutDate === '') {
            throw new InvalidArgumentException('Payout date is required.');
        }

        $template = $this->templates[$countryCode] ?? null;
        if ($template === null) {
            throw new InvalidArgumentException(sprintf('No payroll template registered for country %s.', $countryCode));
        }

        $calculation = $template->calculate($payload);

        $payslipNumber = $payload['payslip_number'] ?? $this->generatePayslipNumber($employeeId, $period);

        $payslip = new Payslip(
            payslipNumber: $payslipNumber,
            employeeId: $employeeId,
            employeeName: $employeeName,
            countryCode: $countryCode,
            period: $period,
            payoutDate: $payoutDate,
            baseSalary: (float) ($payload['base_salary'] ?? 0.0),
            allowances: $calculation['allowances'],
            deductions: $calculation['deductions'],
            grossPay: $calculation['gross_pay'],
            taxablePay: $calculation['taxable_pay'],
            netPay: $calculation['net_pay'],
            statutoryDeductions: $calculation['statutory'],
            compliance: $calculation['compliance'],
        );

        $approvalId = $this->makerChecker->submit(
            actionKey: 'payroll.payslip',
            payload: [
                'payslip_number' => $payslip->getPayslipNumber(),
                'employee_id'    => $employeeId,
                'employee_name'  => $employeeName,
                'country_code'   => $countryCode,
                'period'         => $period,
                'gross_pay'      => $payslip->getGrossPay(),
                'net_pay'        => $payslip->getNetPay(),
                'statutory'      => $payslip->getStatutoryDeductions(),
            ],
            context: $context,
        );

        $payslip->setApprovalRequestId($approvalId);

        $metadata = $payslip->getComplianceDetails();
        $metadata['template'] = $countryCode;
        $metadata['request_origin'] = $context['request_origin'] ?? null;

        $this->auditService->recordEvent(
            eventKey: sprintf('payroll.payslip.%s', $payslip->getPayslipNumber()),
            eventType: 'payslip_generated',
            context: $context,
            before: null,
            after: $payslip->toArray(),
            metadata: $metadata,
        );

        return $payslip;
    }

    private function generatePayslipNumber(string $employeeId, string $period): string
    {
        $sanitisedEmployee = strtoupper(preg_replace('/[^A-Z0-9]/', '', $employeeId) ?? $employeeId);
        $sanitisedPeriod = preg_replace('/[^0-9]/', '', $period) ?? $period;

        return sprintf('%s-%s', $sanitisedEmployee, $sanitisedPeriod);
    }
}
