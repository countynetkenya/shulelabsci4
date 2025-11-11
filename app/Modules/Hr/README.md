# HR Module

The HR module introduces compliant payroll generation for the CI4 stack. The
initial release focuses on the Kenyan template with effective-dated statutory
rates and a sandbox-friendly API that surfaces detailed payslip breakdowns.

## Capabilities

- Generate payslips with base salary, taxable/non-taxable allowances, and
  pre/post-tax deductions.
- Apply Kenyan PAYE bands, personal relief, NSSF tiering, housing levy, and the
  SHIF health contribution with effective dates.
- Enforce maker-checker approvals for every payslip before disbursement.
- Emit immutable audit logs that capture the compliance breakdown for each run.

## Key Services

- `Services\PayrollService` – orchestrates payslip generation, selects the
  correct payroll template, records audit events, and triggers approvals.
- `Services\KenyaPayrollTemplate` – encodes Kenyan statutory rates and
  calculates earnings, deductions, and compliance metadata for a pay period.
- `Domain\Payslip` – immutable value object representing the payslip that is
  returned to API consumers and stored for audit/ledger purposes.

Future templates can be registered with `PayrollService` to extend coverage to
additional countries without altering calling code.
