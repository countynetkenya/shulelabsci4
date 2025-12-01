<?php

declare(strict_types=1);

namespace Tests\Ci4\Hr;

use Modules\Foundation\Services\AuditService;
use Modules\Foundation\Services\MakerCheckerService;
use Modules\Hr\Services\KenyaPayrollTemplate;
use Modules\Hr\Services\PayrollApprovalService;
use Modules\Hr\Services\PayrollService;
use Tests\Ci4\Foundation\FoundationDatabaseTestCase;

class PayrollApprovalServiceTest extends FoundationDatabaseTestCase
{
    private AuditService $auditService;

    private MakerCheckerService $makerChecker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->auditService = new AuditService($this->db);
        $this->makerChecker = new MakerCheckerService($this->db, $this->auditService);
    }

    public function testListPendingAndApprove(): void
    {
        $payroll = new PayrollService([
            new KenyaPayrollTemplate(),
        ], $this->makerChecker, $this->auditService);

        $payroll->generatePayslip($this->samplePayload('EMP-1', 'Alice A.'), $this->sampleContext(1, 'maker-1'));

        $service = new PayrollApprovalService($this->db, $this->makerChecker, $this->auditService);
        $pending = $service->listPending(1);
        $this->assertCount(1, $pending);

        $approvalId = $pending[0]['id'];
        $approved = $service->approve($approvalId, ['actor_id' => 'checker-7', 'school_id' => 1]);

        $this->assertSame('approved', $approved['status']);
        $this->assertSame('checker-7', $approved['checker_id']);
        $this->assertNotNull($approved['processed_at']);
    }

    public function testRejectRequiresReason(): void
    {
        $service = new PayrollApprovalService($this->db, $this->makerChecker, $this->auditService);

        $this->expectException(\InvalidArgumentException::class);
        $service->reject(99, '', ['actor_id' => 'checker']);
    }

    public function testSummariseAggregatesStatuses(): void
    {
        $payroll = new PayrollService([
            new KenyaPayrollTemplate(),
        ], $this->makerChecker, $this->auditService);

        $payroll->generatePayslip($this->samplePayload('EMP-1', 'Alice A.'), $this->sampleContext(2, 'maker-1'));
        $payroll->generatePayslip($this->samplePayload('EMP-2', 'Ben B.'), $this->sampleContext(2, 'maker-2'));

        $service = new PayrollApprovalService($this->db, $this->makerChecker, $this->auditService);
        $pending = $service->listPending(2);
        $this->assertCount(2, $pending);

        $service->approve($pending[0]['id'], ['actor_id' => 'checker-1', 'school_id' => 2]);
        $service->reject($pending[1]['id'], 'Missing supporting documents', ['actor_id' => 'checker-2', 'school_id' => 2]);

        $summary = $service->summarise(2);
        $this->assertSame(0, $summary['counts']['pending']);
        $this->assertSame(1, $summary['counts']['approved']);
        $this->assertSame(1, $summary['counts']['rejected']);
    }

    /**
     * @return array<string, mixed>
     */
    private function samplePayload(string $employeeId, string $name): array
    {
        return [
            'employee_id'   => $employeeId,
            'employee_name' => $name,
            'period'        => '2025-01',
            'payout_date'   => '2025-01-31',
            'country_code'  => 'KE',
            'base_salary'   => 120000,
            'allowances'    => [
                ['name' => 'Housing', 'amount' => 20000, 'taxable' => true],
            ],
            'deductions'    => [
                ['name' => 'Sacco', 'amount' => 5000, 'pre_tax' => false],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function sampleContext(int $schoolId, string $actorId): array
    {
        return [
            'school_id'      => $schoolId,
            'actor_id'       => $actorId,
            'request_origin' => 'cli',
        ];
    }
}
