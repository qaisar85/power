@echo off
rem Helper script to run the repo CI audit locally via Docker on Windows (cmd.exe)
rem Usage: run from the repository root: scripts\run-ci-audit.cmd

echo Building PHP CLI image...
docker build -f docker/php-cli/Dockerfile -t power-php-cli:ci .
if errorlevel 1 (
  echo ERROR: docker build failed. Ensure Docker Desktop is installed and running.
  exit /b 1
)

echo Running CI audit inside container...
docker run --rm -v "%cd%":/app -w /app power-php-cli:ci bash -lc "set -e; echo '== Composer install =='; composer install --no-interaction --prefer-dist || true; echo '== Composer outdated (direct) =='; composer outdated --direct || true; echo '== Composer audit (summary) =='; composer audit --format=summary || true; echo '== PHP version =='; php -v; echo '== Artisan migrate --pretend =='; php artisan migrate --pretend || true; echo '== PHP lint for migrations =='; for f in database/migrations/*.php; do echo '--- Lint:' \"$f\"; php -l \"$f\" || true; done; echo '== PHP lint for vendor/autoload =='; php -l vendor/autoload.php || true;"

echo Done. Paste the output here and I'll analyze the results.
