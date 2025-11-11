<?php

declare(strict_types=1);

namespace Modules\Hr\Domain;

/**
 * Immutable value object representing a generated payslip.
 */
class Payslip
{
    /**
     * @param array<int, array{name: string, amount: float, taxable: bool}> $allowances
     * @param array<int, array{name: string, amount: float, pre_tax: bool}> $deductions
     * @param array<string, float> $statutoryDeductions
     * @param array<string, mixed> $compliance
     */
    public function __construct(
        private readonly string $payslipNumber,
        private readonly string $employeeId,
        private readonly string $employeeName,
        private readonly string $countryCode,
        private readonly string $period,
        private readonly string $payoutDate,
        private readonly float $baseSalary,
        private readonly array $allowances,
        private readonly array $deductions,
        private readonly float $grossPay,
        private readonly float $taxablePay,
        private readonly float $netPay,
        private readonly array $statutoryDeductions,
        private readonly array $compliance,
        private ?int $approvalRequestId = null,
    ) {
    }

    public function getPayslipNumber(): string
    {
        return $this->payslipNumber;
    }

    public function getEmployeeId(): string
    {
        return $this->employeeId;
    }

    public function getEmployeeName(): string
    {
        return $this->employeeName;
    }

    public function getCountryCode(): string
    {
        return $this->countryCode;
    }

    public function getPeriod(): string
    {
        return $this->period;
    }

    public function getPayoutDate(): string
    {
        return $this->payoutDate;
    }

    public function getBaseSalary(): float
    {
        return $this->baseSalary;
    }

    /**
     * @return array<int, array{name: string, amount: float, taxable: bool}>
     */
    public function getAllowances(): array
    {
        return $this->allowances;
    }

    /**
     * @return array<int, array{name: string, amount: float, pre_tax: bool}>
     */
    public function getDeductions(): array
    {
        return $this->deductions;
    }

    public function getGrossPay(): float
    {
        return $this->grossPay;
    }

    public function getTaxablePay(): float
    {
        return $this->taxablePay;
    }

    public function getNetPay(): float
    {
        return $this->netPay;
    }

    /**
     * @return array<string, float>
     */
    public function getStatutoryDeductions(): array
    {
        return $this->statutoryDeductions;
    }

    /**
     * @return array<string, mixed>
     */
    public function getComplianceDetails(): array
    {
        return $this->compliance;
    }

    public function getApprovalRequestId(): ?int
    {
        return $this->approvalRequestId;
    }

    public function setApprovalRequestId(int $approvalRequestId): void
    {
        $this->approvalRequestId = $approvalRequestId;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'payslip_number'       => $this->payslipNumber,
            'employee_id'          => $this->employeeId,
            'employee_name'        => $this->employeeName,
            'country_code'         => $this->countryCode,
            'period'               => $this->period,
            'payout_date'          => $this->payoutDate,
            'base_salary'          => $this->baseSalary,
            'allowances'           => $this->allowances,
            'deductions'           => $this->deductions,
            'gross_pay'            => $this->grossPay,
            'taxable_pay'          => $this->taxablePay,
            'net_pay'              => $this->netPay,
            'statutory_deductions' => $this->statutoryDeductions,
            'compliance'           => $this->compliance,
            'approval_request_id'  => $this->approvalRequestId,
        ];
    }
}
