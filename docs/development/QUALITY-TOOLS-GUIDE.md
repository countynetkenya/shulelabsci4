# Code Quality Tools - Quick Start Guide

This guide helps you use the code quality tools configured for ShuleLabs CI4.

## Installation

First, install the quality tool dependencies:

```bash
composer install
```

This will install:
- **PHP-CS-Fixer** - Code style fixer
- **PHPStan** - Static analysis tool
- **PHPMD** - PHP Mess Detector

## Running Quality Checks

### 1. Check Code Style (PSR-12)

**Check without making changes:**
```bash
composer cs:check
```

**Automatically fix style issues:**
```bash
composer cs:fix
```

### 2. Run Static Analysis

**Analyze code for type errors:**
```bash
composer phpstan
```

This runs PHPStan at level 5, catching:
- Type mismatches
- Undefined methods/properties
- Incorrect parameter types
- Missing return types

### 3. Check for Code Smells

**Detect complexity and design issues:**
```bash
composer phpmd
```

This checks for:
- Long methods/classes
- Too many parameters
- Cyclomatic complexity
- Unused code
- Naming issues

### 4. Run All Quality Checks

**One command to check everything:**
```bash
composer quality:check
```

This runs:
1. Code style check
2. Static analysis
3. All tests

**Auto-fix and test:**
```bash
composer quality:fix
```

This runs:
1. Auto-fix code style
2. Run tests

## Understanding Output

### PHP-CS-Fixer

```bash
# If issues found, you'll see:
1) app/Modules/Finance/Services/InvoiceService.php
   - Line 45: Expected 1 blank line after opening tag
   - Line 87: Expected single space around concatenation operator
```

**Fix:** Run `composer cs:fix` to auto-correct.

### PHPStan

```bash
# If errors found, you'll see:
------ -------------------------------------------------
 Line   app/Modules/Finance/Services/InvoiceService.php
------ -------------------------------------------------
 42     Parameter $amount of method processPayment()
        expects float, string given.
------ -------------------------------------------------
```

**Fix:** Update the type hint or fix the calling code.

### PHPMD

```bash
# If issues found, you'll see:
app/Modules/Finance/Services/InvoiceService.php:42
    The method processPayment() has a Cyclomatic Complexity of 12.
```

**Fix:** Refactor complex methods into smaller ones.

## Configuration Files

Each tool has a configuration file you can customize:

- **`.php-cs-fixer.php`** - Code style rules
- **`phpstan.neon`** - Static analysis settings
- **`phpmd.xml`** - Mess detector rules

## IDE Integration

### PhpStorm

1. Go to **Settings → PHP → Quality Tools**
2. Configure paths to each tool in `vendor/bin/`
3. Enable inspections for each tool

### VS Code

Install extensions:
- **PHP CS Fixer** by junstyle
- **PHPStan** by SanderRonde
- **PHPMD** by ecodes

## Pre-Commit Checks

Add this to `.git/hooks/pre-commit`:

```bash
#!/bin/sh

echo "Running quality checks..."

composer cs:check
if [ $? -ne 0 ]; then
    echo "❌ Code style issues found. Run 'composer cs:fix'"
    exit 1
fi

composer phpstan
if [ $? -ne 0 ]; then
    echo "❌ Static analysis failed. Fix errors above."
    exit 1
fi

echo "✅ All quality checks passed!"
exit 0
```

Make it executable:
```bash
chmod +x .git/hooks/pre-commit
```

## CI/CD Integration

Add to your GitHub Actions workflow:

```yaml
- name: Check code quality
  run: composer quality:check
```

## Troubleshooting

### "Command not found" errors

**Problem:** `vendor/bin/php-cs-fixer: not found`

**Solution:** Run `composer install` first.

### Out of memory errors

**Problem:** PHPStan runs out of memory

**Solution:** Increase memory limit:
```bash
php -d memory_limit=1G vendor/bin/phpstan analyse
```

### Too many errors

**Problem:** Hundreds of errors when first running tools

**Solution:** 
1. Start with `composer cs:fix` to auto-fix style
2. Address PHPStan errors gradually
3. PHPMD issues are warnings - fix priority ones first

## Best Practices

1. **Run checks before committing:**
   ```bash
   composer quality:check
   ```

2. **Fix style automatically:**
   ```bash
   composer cs:fix
   ```

3. **Check new code immediately:**
   Don't accumulate quality debt - check as you write.

4. **Don't ignore warnings:**
   Each PHPMD warning indicates potential technical debt.

5. **Use strict types:**
   Add `declare(strict_types=1)` to new files.

## Getting Help

- **Documentation:** See `docs/development/CODE-STANDARDS.md`
- **Quality Report:** See `QUALITY_CHECK_REPORT.md`
- **PHP-CS-Fixer:** https://github.com/PHP-CS-Fixer/PHP-CS-Fixer
- **PHPStan:** https://phpstan.org/
- **PHPMD:** https://phpmd.org/

## Quick Reference

| Command | Purpose | When to Use |
|---------|---------|-------------|
| `composer cs:check` | Check code style | Before committing |
| `composer cs:fix` | Fix code style | After writing code |
| `composer phpstan` | Static analysis | Before pushing |
| `composer phpmd` | Find code smells | During refactoring |
| `composer quality:check` | Check everything | Before PR |
| `composer quality:fix` | Fix + test | After changes |
| `composer test` | Run tests only | Frequent |

---

**Last Updated**: 2025-11-22  
**Need Help?** Open an issue or ask the team!
