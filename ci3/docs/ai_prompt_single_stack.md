# AI Prompt: Single PHP 8.3 FPM Stack for ShuleLabs

## Context

- The legacy CodeIgniter 3 front controller and bootstrap logic live at the repository root in `index.php`, confirming that CI3 runs from the top-level project directory.
- The CodeIgniter 4 application is invoked through the `/public/v2.php` shim, which simply requires `ci4/public/index.php`, so CI4 can be served by routing `/v2` traffic through that single entry point.
- The existing Docker Compose stack already shares one PHP-FPM service (`app`) between Nginx, the queue worker, and the scheduler while mounting the full repository into `/var/www/html`, and Nginx points at `public/` and forwards PHP requests to that same FPM backend.

## Prompt

> 🔧 **Prompt: Run ShuleLabs CI3 + CI4 on one PHP 8.3 FPM stack (Docker + Nginx)**
>
> **Context & Access**
>
> * You have GitHub access to the ShuleLabs repository (main branch). CI3 lives at the repo root, and CI4 is served through `public/v2.php` → `ci4/public/index.php`.
> * Goal: upgrade the existing Docker setup to PHP 8.3 FPM while continuing to run both frameworks behind a single FPM service and a single Nginx vhost, keeping workers/scheduler on the same image.
>
> **Key Requirements**
>
> 1. **Upgrade the PHP image**
>    * Update `docker/php-fpm/Dockerfile` to use `php:8.3-fpm-bookworm`.
>    * Install the current dependency set (`git`, `unzip`, `libzip-dev`, `libpng-dev`, `libicu-dev`, `libxml2-dev`, `libjpeg62-turbo-dev`, `libfreetype6-dev`, `default-mysql-client`) plus anything PHP 8.3 needs.
>    * Enable extensions: `bcmath`, `gd` (with freetype/jpeg/webp), `intl`, `mysqli`, `pdo_mysql`, `pcntl`, `zip`, and `redis` (via PECL).
>    * Copy Composer from `composer:2`, retain `/var/log/php`, and keep the custom php.ini snippet.
>    * Clean apt caches to keep the image slim.
>
> 2. **Compose & service topology**
>    * Keep a single PHP-FPM service (`app`) that mounts the project into `/var/www/html`.
>    * Ensure `worker` and `scheduler` continue to extend the same service definition; update `depends_on` as needed.
>    * Adjust the build context to leverage the updated Dockerfile and bump any service metadata (labels, image tags) to reflect PHP 8.3.
>    * Do **not** add a second PHP container; all PHP traffic (web, `/v2`, queues, cron) must hit the shared FPM backend.
>
> 3. **Nginx configuration**
>    * Preserve a single server block that serves `root /var/www/html/public`.
>    * Confirm `/` routes through the CI3 front controller.
>    * Add an explicit `location ^~ /v2` block (or equivalent rewrite) that directs requests to `/public/v2.php`, ensuring CI4 traffic works without extra vhosts.
>    * Continue forwarding PHP files to `app:9000`, keeping cache headers and security hardening.
>
> 4. **Environment & configuration**
>    * Extend `.env.example` with any new variables required for Docker (e.g., timezone) without removing existing entries.
>    * Document how CI3 consumes env vars via `mvc/config/env.php` and how CI4 reads from `.env`.
>
> 5. **Docs & DX**
>    * Refresh `docs/Docker.md` with PHP 8.3 prerequisites, `docker compose up` instructions, and how to hit CI3 vs CI4 (e.g., `/` vs `/v2`).
>    * Update the root `Makefile` with helper targets (`docker-up`, `docker-down`, composer install commands) while retaining existing ones.
>    * Add or adjust `.dockerignore` entries if needed to speed builds.
>
> 6. **CI/CD**
>    * Amend `.github/workflows/` to build the new PHP 8.3 image, lint the Nginx config (`nginx -t`), boot the stack, and verify:
>      * `docker compose exec app php -m` contains the required extensions.
>      * `curl -I` against `/` (CI3) and `/v2` (CI4) both return 200s.
>      * Composer validation for CI4 (and CI3 if composer.json exists).
>    * Keep the workflow self-contained and cache-friendly.
>
> 7. **Acceptance checklist**
>    * `docker compose up -d --build` succeeds on a clean machine.
>    * Visiting `http://localhost:8080/` serves CI3; `http://localhost:8080/v2` serves CI4.
>    * Queue worker and scheduler start correctly against the shared PHP image.
>    * GitHub Actions workflow passes.
>    * Documentation reflects the single-container architecture and upgrade path.
>
> **Deliverables**
>
> * Updated `docker/php-fpm/Dockerfile`
> * Updated `docker-compose.yml`, `docker/nginx/default.conf`
> * Refreshed `.env.example`, `Makefile`, `.dockerignore`
> * Updated CI workflow file(s)
> * Updated `docs/Docker.md`
> * PR description covering what changed, how to run it, acceptance results, and any secrets/configs needed.
