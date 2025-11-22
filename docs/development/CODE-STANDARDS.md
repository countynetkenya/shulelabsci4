# 📏 Code Standards

**Last Updated**: 2024-11-22  
**Status**: Active

## Overview

This document defines the code quality standards and best practices for the ShuleLabs CI4 project. All contributors must follow these standards to ensure maintainable, secure, and high-quality code.

## Table of Contents

- [PHP Standards](#php-standards)
- [Code Style](#code-style)
- [Type Safety](#type-safety)
- [Security Standards](#security-standards)
- [Documentation](#documentation)
- [Testing Standards](#testing-standards)
- [Quality Tools](#quality-tools)
- [Code Review Process](#code-review-process)

---

## PHP Standards

### Language Version
- **Required**: PHP 8.3+
- **Features**: Use modern PHP features (enums, attributes, match expressions)
- **Strict Types**: Encourage `declare(strict_types=1)` in new files

### PSR Standards
- **PSR-4**: Autoloading (all files MUST follow)
- **PSR-12**: Extended coding style (enforced via PHP-CS-Fixer)
- **PSR-3**: Logger interface (for logging)
- **PSR-7**: HTTP message interfaces (via CodeIgniter)

### File Structure
```php
<?php

declare(strict_types=1);  // Recommended for new files

namespace Modules\ModuleName\Services;

use CodeIgniter\Database\BaseConnection;
use RuntimeException;

/**
 * Service description.
 */
class ServiceName
{
    // Class implementation
}
```

---

## Code Style

### Automated Formatting
Use PHP-CS-Fixer to automatically format code:

```bash
composer cs:fix
```

### Key Style Rules
- **Indentation**: 4 spaces (no tabs)
- **Line Length**: Soft limit of 120 characters
- **Braces**: Opening brace on same line for classes/methods
- **Arrays**: Short syntax `[]` instead of `array()`
- **Strings**: Single quotes unless interpolation needed
- **Imports**: Alphabetically sorted, one per line

### Example
```php
<?php

declare(strict_types=1);

namespace Modules\Finance\Services;

use CodeIgniter\Database\BaseConnection;
use Modules\Foundation\Services\AuditService;
use RuntimeException;

class InvoiceService
{
    public function __construct(
        private readonly InvoiceRepositoryInterface $repository,
        private readonly LedgerService $ledger,
        private readonly AuditService $audit
    ) {
    }

    public function issueInvoice(array $payload, array $context): Invoice
    {
        $invoice = Invoice::fromArray($payload);
        
        // Business logic here
        
        return $invoice;
    }
}
```

---

## Type Safety

### Type Declarations
- **Always** use type hints for parameters and return types
- Use union types when appropriate: `string|null`
- Use array shapes in PHPDoc: `@param array{id: int, name: string} $data`

### Examples
```php
// ✅ Good
public function processPayment(string $invoiceNumber, float $amount): Payment
{
    // ...
}

// ❌ Bad
public function processPayment($invoiceNumber, $amount)
{
    // ...
}
```

### Nullable Types
```php
// ✅ Explicit nullable
public function findUser(?int $userId): ?User
{
    return $userId ? $this->repository->find($userId) : null;
}
```

### Collections
```php
/**
 * @param array<int, Student> $students
 * @return array<string, mixed>
 */
public function summarizeGrades(array $students): array
{
    // ...
}
```

---

## Security Standards

### Input Validation
- **NEVER** trust user input
- Use CodeIgniter's validation library
- Validate at controller level before passing to services

```php
// ✅ Good
$rules = [
    'email'    => 'required|valid_email',
    'password' => 'required|min_length[8]',
];

if (!$this->validate($rules)) {
    return $this->fail($this->validator->getErrors());
}
```

### Database Security
- **ALWAYS** use Query Builder or Models (NEVER raw SQL)
- Use parameter binding for dynamic values
- Avoid `escape()` - use Query Builder instead

```php
// ✅ Good
$builder->where('user_id', $userId)->get();

// ❌ Bad
$db->query("SELECT * FROM users WHERE id = " . $userId);
```

### Authentication & Authorization
- Use session-based authentication (CodeIgniter sessions)
- Check permissions at controller level
- Never trust client-provided user IDs

### Cryptography
- Use `password_hash()` and `password_verify()` for passwords
- Use SHA-256 or stronger for hashing (NOT MD5 or SHA-1)
- Use `random_bytes()` for tokens/secrets

```php
// ✅ Good
$hash = hash('sha256', $data);
$token = bin2hex(random_bytes(32));

// ❌ Bad
$hash = md5($data);  // Too weak!
```

### Configuration
- **NEVER** hardcode credentials in code
- Use `.env` file for environment-specific config
- Keep `.env` out of version control

```php
// ✅ Good
$apiKey = getenv('PAYMENT_API_KEY');

// ❌ Bad
$apiKey = 'sk_live_abc123xyz';
```

---

## Documentation

### PHPDoc Requirements
All public methods MUST have PHPDoc with:
- Description
- `@param` for each parameter with type
- `@return` with return type
- `@throws` for exceptions

```php
/**
 * Generate a payslip for an employee.
 *
 * @param array<string, mixed> $payload Employee and period data
 * @param array<string, mixed> $context Tenant and actor information
 * @return Payslip The generated payslip
 * @throws InvalidArgumentException If required fields are missing
 */
public function generatePayslip(array $payload, array $context): Payslip
{
    // ...
}
```

### Code Comments
- Explain **WHY**, not **WHAT**
- Avoid obvious comments
- Keep comments up-to-date with code

```php
// ✅ Good
// Calculate tax using Kenya PAYE brackets as of 2024
$tax = $this->calculatePaye($grossPay);

// ❌ Bad
// Set tax to result of calculatePaye
$tax = $this->calculatePaye($grossPay);
```

### TODO Comments
- Include ticket/issue number
- Add expected completion date if known
- Format: `// TODO #123: Description`

```php
// TODO #456: Implement WhatsApp notifications after API credentials received
```

---

## Testing Standards

### Test Coverage
- Aim for **>80%** code coverage
- All public methods should have tests
- Critical paths require multiple test cases

### Test Structure
```php
namespace Tests\Ci4\Finance;

use CodeIgniter\Test\CIUnitTestCase;
use Modules\Finance\Services\InvoiceService;

class InvoiceServiceTest extends CIUnitTestCase
{
    public function testIssueInvoiceCreatesValidInvoice(): void
    {
        // Arrange
        $service = new InvoiceService(/* dependencies */);
        $payload = ['amount' => 1000, 'student_id' => 123];
        
        // Act
        $invoice = $service->issueInvoice($payload, []);
        
        // Assert
        $this->assertInstanceOf(Invoice::class, $invoice);
        $this->assertEquals(1000, $invoice->getAmount());
    }
}
```

### Test Naming
- Use descriptive names: `test[Method][Scenario][ExpectedResult]`
- Examples:
  - `testIssueInvoiceThrowsExceptionWhenAmountIsNegative`
  - `testVerifySnapshotReturnsFalseWhenExpired`

---

## Quality Tools

### PHP-CS-Fixer
Enforces PSR-12 coding standards.

```bash
# Check code style
composer cs:check

# Auto-fix code style
composer cs:fix
```

### PHPStan
Static analysis to catch type errors.

```bash
# Run static analysis (level 5)
composer phpstan
```

### PHPMD
Detects code smells and complexity issues.

```bash
# Run mess detector
composer phpmd
```

### Comprehensive Quality Check
```bash
# Run all quality checks
composer quality:check

# Fix what can be auto-fixed, then test
composer quality:fix
```

---

## Code Review Process

### Before Submitting PR
1. Run `composer quality:check` locally
2. Ensure all tests pass
3. Update documentation if needed
4. Write clear commit messages

### Commit Messages
Format:
```
<type>: <subject>

<body>

<footer>
```

Types:
- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation only
- `refactor`: Code change that neither fixes nor adds feature
- `test`: Adding tests
- `chore`: Maintenance tasks

Example:
```
feat: Add invoice settlement with audit logging

Implements the settle invoice functionality that records
payments against invoices and updates the ledger accordingly.
All operations are audited for compliance.

Closes #123
```

### Review Checklist
- [ ] Code follows PSR-12 standards
- [ ] All public methods have PHPDoc
- [ ] Type hints used consistently
- [ ] No security vulnerabilities
- [ ] Tests added/updated
- [ ] No debug statements (var_dump, dd, etc.)
- [ ] No TODO comments (or linked to issues)
- [ ] Database queries use Query Builder
- [ ] Input validation present
- [ ] Error handling appropriate

---

## References

- [PSR-12: Extended Coding Style](https://www.php-fig.org/psr/psr-12/)
- [PHPDoc Standard](https://docs.phpdoc.org/3.0/guide/references/phpdoc/)
- [CodeIgniter 4 User Guide](https://codeigniter.com/user_guide/)
- [OWASP PHP Security Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/PHP_Configuration_Cheat_Sheet.html)
- [System Overview](../01-SYSTEM-OVERVIEW.md)
- [Architecture](../ARCHITECTURE.md)

---

**Version**: 2.0.0  
**Last Reviewed**: 2024-11-22
