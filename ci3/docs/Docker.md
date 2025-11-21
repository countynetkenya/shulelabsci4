# Docker development stack (PHP 8.3 FPM)

This project ships a single Docker Compose stack that runs both the legacy CodeIgniter 3
application and the CodeIgniter 4 runtime behind one PHP 8.3 FPM service.

## Prerequisites

- Docker Engine 24+
- Docker Compose Plugin 2.20+
- At least 4 GB of memory available to containers

## Quick start

```bash
cp .env.example .env
make docker-up
```

After the containers start, install Composer dependencies:

```bash
make docker-composer-ci3
make docker-composer-ci4
```

Both commands execute inside the shared PHP FPM container (`app`). The CI3 command
graciously succeeds even if the legacy runtime does not ship a `composer.json`.

## Accessing the applications

- CodeIgniter 3: http://localhost:8080/
- CodeIgniter 4: http://localhost:8080/v2

The `/public/v2.php` shim continues to bootstrap the CI4 front controller inside the
same container topology.

To stop or rebuild the stack:

```bash
make docker-down
make docker-rebuild
```

## Environment configuration

The Docker stack honours values from `.env`. CI3 loads them through
`mvc/config/env.php`, while CI4 reads from `ci4/.env` (mirrored from `.env`).
A new `TZ` variable controls the default timezone for all PHP processes. Override it in
`.env` if your environment is outside `Africa/Nairobi`.

Database and Redis credentials in `.env.example` align with the Compose services so the
applications boot without additional configuration.

## Troubleshooting

- `make docker-logs` tails the web container logs.
- `make docker-bash` opens an interactive shell in the PHP FPM container.
- Run `docker compose ps` to check service status if a container exits unexpectedly.
