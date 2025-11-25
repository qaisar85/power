# Running the CI audit locally (Windows)

This document explains how to run the repository CI audit locally using the provided Docker PHP CLI image. It requires Docker Desktop for Windows.

Prerequisites
- Docker Desktop for Windows installed and running (WSL2 backend is recommended).
  https://docs.docker.com/desktop/install/windows-install/
- Run steps from the repository root in `cmd.exe`.

Quick run
1. Open `cmd.exe` in the repository root (e.g. `D:\Laravel\Power`).
2. Run the helper script:

```
scripts\run-ci-audit.cmd
```

What the script does
- Builds `docker/php-cli` Docker image defined in `docker/php-cli/Dockerfile`.
- Runs `composer install`, `composer outdated --direct`, `composer audit --format=summary`.
- Runs `php artisan migrate --pretend`.
- PHP-lints files in `database/migrations` and `vendor/autoload.php`.

If you cannot install Docker locally
- Use the GitHub Actions workflow at `.github/workflows/ci.yml` and trigger it from the GitHub UI (Actions → select `ci.yml` → Run workflow).
- Or push an empty commit to trigger the workflow:

```
git commit --allow-empty -m "Trigger CI audit"
git push origin HEAD
```

After running
- Copy/paste the entire terminal output here and I will analyze the results and produce prioritized remediation steps (vulnerable packages, migration errors, broken files, etc.).
