#!/usr/bin/env bash
# Local WMS server — canonical port 8100 (Live/PORTS.md).
# Production/Railway: use docker/start-wms.sh (${PORT} from host).
set -eu
cd "$(dirname "$0")/.."

# Prefer APP_PORT, then PORT, then 8100 (not Laravel default 8000)
PORT="${APP_PORT:-${PORT:-8100}}"
HOST="${APP_HOST:-127.0.0.1}"

echo "Enterprise WMS (local)"
echo "  bind: http://${HOST}:${PORT}"
echo "  APP_URL should match (default .env.example: http://127.0.0.1:8100)"
echo "  override: APP_PORT=8100 ./scripts/start-local.sh"
echo ""

exec php artisan serve --host="${HOST}" --port="${PORT}"
