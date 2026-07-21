#!/usr/bin/env bash
set -euo pipefail

cd /app

if [ -z "${APP_KEY:-}" ]; then
  export APP_KEY="$(php -r 'echo "base64:".base64_encode(random_bytes(32));')"
  echo "Generated ephemeral APP_KEY for this container boot"
fi

php artisan config:clear || true
php artisan migrate --force || true
php artisan db:seed --force || true
php artisan storage:link || true

PORT="${PORT:-8000}"
exec php artisan serve --host=0.0.0.0 --port="$PORT"
