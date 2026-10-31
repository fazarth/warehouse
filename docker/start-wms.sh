#!/usr/bin/env bash
set -eu

cd /app

if [ -z "${APP_KEY:-}" ]; then
  export APP_KEY="$(php -r 'echo "base64:".base64_encode(random_bytes(32));')"
  echo "Generated ephemeral APP_KEY for this container boot"
fi

# Production/Railway injects PORT (keep host-provided). Image fallback 8080.
# Local host PHP (not this container script): APP_PORT/PORT default 8100 — see Live/PORTS.md
# and scripts/start-local.sh. Do not hardcode 8100 here; leave production-friendly.
PORT="${PORT:-8080}"

php artisan config:clear || true

# Boot HTTP first so healthcheck can pass; migrate/seed in background
(
  sleep 2
  php artisan migrate --force || true
  php artisan db:seed --force || true
  php artisan storage:link || true
) &

echo "Starting WMS on 0.0.0.0:${PORT}"
exec php artisan serve --host=0.0.0.0 --port="${PORT}"
