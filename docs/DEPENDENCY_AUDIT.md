# Dependency & Security Audit — Quick Report

Generated: 2025-11-23

This is a static, repo-side dependency audit created from `composer.json` and `composer.lock`. It is not a replacement for a live vulnerability scanner; run `composer audit` and `composer outdated` in CI or locally to get live results.

Summary — direct composer requires and installed versions

- `php` : ^8.2 (platform requirement)
- `barryvdh/laravel-dompdf` : v3.1.1
- `inertiajs/inertia-laravel` : v2.0.10
- `laravel-notification-channels/twilio` : v4.1.1
- `laravel/cashier` : v16.0.2
- `laravel/framework` : v12.34.0
- `laravel/jetstream` : v5.3.8
- `laravel/sanctum` : v4.2.0
- `laravel/scout` : v10.20.0
- `laravel/socialite` : v5.23.1
- `laravel/tinker` : v2.10.1
- `spatie/laravel-permission` : v6.21.0
- `tightenco/ziggy` : v2.6.0

What I checked (static):
- Extracted installed versions from `composer.lock` and mapped them to the top-level requirements in `composer.json`.
- Inspected presence of common advisories packages (some packages include `roave/security-advisories` in their dev deps).
- Confirmed many packages are recent (2024–2025 releases appear in the lockfile), but a live check is required for CVEs.

Recommended commands (run locally or let CI run them):

1) Show direct outdated packages (useful to plan upgrades):

```bash
composer outdated --direct
```

2) Run Composer's security audit (requires Composer 2.4+):

```bash
composer audit --format=table
```

3) Add the advisory blocker package (optional, for CI hard-fail):

```bash
composer require --dev roave/security-advisories:dev-latest
```

Immediate remediation priorities
- Run `composer audit` and fix any HIGH/CRITICAL advisories. If an advisory affects a transitive dependency, prefer upgrading the direct package that pulls it in.
- Run `composer outdated --direct` and evaluate upgrading the following high-impact packages first: `laravel/framework`, `guzzlehttp/guzzle`, `spatie/laravel-permission`, `laravel/sanctum`, `laravel/jetstream`, `laravel/cashier`.
- Avoid drifting minor Laravel versions across packages; keep `laravel/*` stack consistent (the lockfile shows `laravel/framework` v12.34.0 and other laravel packages are 10/11/12-compatible; confirm compatibility matrix before mass upgrades).
- Pin critical platform packages (e.g., `php` requirement) in CI to prevent accidental installs against unsupported PHP versions.

Security hardening checklist (quick wins)
- Enforce HTTPS and HSTS at the server (Nginx) level.
- Set `SESSION_SECURE_COOKIE=true` and configure cookie domain `.worldbizportal.com` if using subdomains; `SameSite=None` + `Secure` as needed.
- Store all signing keys and secrets in protected vault/CI secrets; do not commit `.env`.
- Add Content-Security-Policy, X-Frame-Options, X-Content-Type-Options headers.
- Add `composer audit` to CI (this repo's GitHub Actions updated to run `composer audit`).
- Schedule a quarterly dependency review and enable Dependabot or similar for PRs.

Next steps I can take (pick any):
- Run `composer audit` in CI and attach results to a GitHub issue/PR. (requires network in CI — already added to CI workflow).
- Add `roave/security-advisories` to `require-dev` and update `composer.json` with an explanation.
- Create a small GitHub Actions job to open an issue when `composer audit` reports vulnerabilities.

If you want me to run the live audit now, I can trigger the CI job (or run the Docker commands locally if you allow me to run them here); otherwise run the commands above and paste the output and I'll produce a prioritized fix plan.

*** End of report
