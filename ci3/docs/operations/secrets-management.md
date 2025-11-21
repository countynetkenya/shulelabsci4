# Secrets Management – Docker & GitHub Actions Toolchain

Phase 0 closes with the new containerised deployment flow and GitHub Actions pipelines. Secrets are no longer copied across hosts manually – they are stored centrally and injected at runtime so builds remain reproducible.

## GitHub Secrets

| Secret | Scope | Purpose |
| --- | --- | --- |
| `PRODUCTION_ENV_FILE` | Repository | Multi-line `.env` payload used for tagged releases. The deploy workflow materialises this file as `.env.production` before running migrations or packaging artefacts. |
| `REGISTRY_USERNAME` | Repository | Service account or bot user allowed to push images to GitHub Container Registry. |
| `REGISTRY_TOKEN` | Repository | Personal access token or PAT scoped with `write:packages` used alongside `REGISTRY_USERNAME` for Docker authentication. |
| `OPERATIONS_ALERT_WEBHOOK` | Repository | Slack/Teams/webhook endpoint that receives scheduler failure alerts when Phase 0 observability detects an `error` status or a threshold breach. |

### Hardening guidance

1. **Create a dedicated bot account.** Issue a PAT with `write:packages` and `read:packages` only. Avoid re-using human PATs.
2. **Store multi-line secrets verbatim.** When adding `PRODUCTION_ENV_FILE`, paste the full `.env` contents into the GitHub Secrets UI so newline formatting is preserved.
3. **Rotate quarterly.** Regenerate the PAT and update the secret entries; deployments fail fast if credentials drift.
4. **Audit access.** Restrict `Actions` permissions for the repository so only trusted maintainers can edit secrets or run workflows on protected branches.
5. **Tune scheduler alerts.** Provide `OPERATIONS_ALERT_WEBHOOK` and optionally `SCHEDULER_ALERT_THRESHOLD_SECONDS` in the `.env` payload so long-running tasks also raise notifications.

## Runtime injection

- GitHub Actions writes the `PRODUCTION_ENV_FILE` contents to `.env.production`, exports the database credentials for validation, and copies the file to `.env` so migrations run with the same secrets that production uses.
- The deploy workflow hydrates an ephemeral MySQL instance with the exported credentials to ensure schema changes succeed before images are published.
- The Docker publish step authenticates using `REGISTRY_USERNAME` / `REGISTRY_TOKEN` before pushing release tags to `ghcr.io/<owner>/<repo>` and blocks the push if the Trivy scan reports high/critical vulnerabilities.
- Hosts consuming the images should load the same `.env` payload (or environment-specific variations) at container start-up via orchestration tooling (Docker Swarm, Kubernetes secrets, or systemd unit files).

Document any additional environment-specific secrets (for example, payment gateway keys) in your internal runbooks referencing this file so the rotation cadence remains consistent.
