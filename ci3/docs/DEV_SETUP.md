# Developer Setup – Sprint 1 Foundations

## Prerequisites
- PHP 8.2 with extensions required by CodeIgniter and project dependencies (openssl, mbstring, intl, gd, pdo_mysql).
- Composer v2.6+.
- MySQL access for running migrations.

## Install Dependencies
```bash
composer install
```

## Environment Configuration
1. Copy `.env.example` to `.env` and populate secrets:
   - `JWT_SECRET`, `JWT_ISSUER`, `JWT_TTL_SECONDS`, `JWT_LEEWAY_SECONDS`.
   - Toggle API guard via `API_JWT_GUARD_ENABLED` (set `true` to enforce Bearer tokens in dev).
   - Provide integration credentials for Google Drive and M-PESA as available.
2. Re-run the app or clear opcode cache after editing `.env`.

## Database Migrations
```bash
php index.php migrate status
php index.php migrate latest
```
- Use `php index.php migrate version <timestamp>` to roll forward/back to a specific migration.

## OpenAPI Contract
```bash
composer openapi:build
```
- Generated spec is written to `ci4/docs/openapi.yaml`. Commit the artifact after generation.

## JWT Smoke Test
With guard enabled, hit any `/api/...` endpoint providing `Authorization: Bearer <token>` generated via the `Jwt_service` (e.g. from a tinker script). Requests without a token will receive `401` responses.
