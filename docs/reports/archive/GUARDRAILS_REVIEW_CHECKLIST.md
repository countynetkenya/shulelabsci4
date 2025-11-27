# Guardrails Review Checklist

**Version**: 1.0.0  
**Last Updated**: 2025-11-22  

## Overview

This checklist validates all code changes against ShuleLabs CI4 standards for multi-tenant safety, observability, security, and documentation alignment.

## Multi-Tenant Safety

### Model Implementation
- [ ] Tenant-scoped models extend `TenantAwareModel`
- [ ] `setTenantId()` called before all queries
- [ ] Tenant ownership verified before returning data
- [ ] No raw SQL queries bypassing tenant scoping

### Testing
- [ ] Tests verify queries without tenant ID throw exception
- [ ] Tests verify cross-tenant data access is blocked
- [ ] Tests verify tenant ID auto-injection on insert

## Observability

### Logging
- [ ] Structured JSON logging with tenant_id, user_id, request_id
- [ ] Appropriate log levels used
- [ ] No sensitive data in logs

### Metrics & Health
- [ ] API request count and duration tracked
- [ ] Error rates monitored
- [ ] Health checks updated for new dependencies

## Security

### Authentication & Authorization
- [ ] Protected routes require `auth` filter
- [ ] Sensitive operations require `authorize` filter
- [ ] Permissions checked before actions

### Input/Output Safety
- [ ] All inputs validated
- [ ] CSRF tokens in forms
- [ ] User output escaped with `esc()`
- [ ] Audit logging for sensitive actions

## Documentation

- [ ] Code has PHPDoc comments
- [ ] README updated if needed
- [ ] DATABASE.md updated for schema changes
- [ ] SECURITY.md updated for security changes

## PR Template

```markdown
## Guardrails Checklist
- [ ] Multi-tenant safety verified
- [ ] Observability implemented
- [ ] Security requirements met
- [ ] Documentation updated
- [ ] Tests added
```

---
**Maintained By**: ShuleLabs Platform Team
