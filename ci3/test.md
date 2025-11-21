# Shulelabs – Soft Delete / Status-Based Deactivation Testing

## Objective
Verify that accounting and core document records (payments, invoices, credit memos) cannot be hard-deleted and instead use status changes (`ACTIVE`, `CANCELED`, `VOIDED`).

---

## 1. Database Verification
- [ ] Run SQL to confirm `status` column exists:

```sql
SHOW COLUMNS FROM payment LIKE 'status';
SHOW COLUMNS FROM make_payment LIKE 'status';
SHOW COLUMNS FROM globalpayment LIKE 'status';
SHOW COLUMNS FROM invoice LIKE 'status';
SHOW COLUMNS FROM maininvoice LIKE 'status';
SHOW COLUMNS FROM creditmemo LIKE 'status';
SHOW COLUMNS FROM maincreditmemo LIKE 'status';
