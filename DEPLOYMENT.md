# Warehouse Management — Deployment

## Runtime requirements

- PHP 8.1+ (Herd/Laragon OK) with `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`
- Composer 2
- Node 18+ (Vite/Tailwind build)
- MySQL 8 (database `warehouse`)
- Optional: Redis (default local uses file cache/session)

## Local (host PHP + Docker MySQL)

```bash
# from Live monorepo root
docker compose up -d mysql redis

cd warehouse
cp .env.example .env   # or keep existing .env
# DB_HOST=127.0.0.1  DB_DATABASE=warehouse  DB_USERNAME=laravel  DB_PASSWORD=secret
php artisan key:generate
composer install
npm install && npm run build
php artisan migrate --seed
# Local default APP_PORT=8100 (Live/PORTS.md). Production/Railway uses host PORT.
export APP_PORT="${APP_PORT:-8100}"
php artisan serve --host=127.0.0.1 --port="${APP_PORT}"
# APP_URL=http://127.0.0.1:8100
# one-liner: ./scripts/start-local.sh
```

## Docker env note

`.env.docker` uses service hostnames (`DB_HOST=mysql`, `REDIS_HOST=redis`) for in-network containers.
Host-side artisan/serve should use `127.0.0.1` and file drivers for cache/session if Redis is not exposed.

`docker/start-wms.sh` binds `${PORT}` (Railway injects `PORT`; image default `8080`). Local host PHP should use **`APP_PORT=8100`**, not container `8080`.

## Cloudflare Tunnel (demo)

```bash
export APP_PORT="${APP_PORT:-8100}"
php artisan serve --host=127.0.0.1 --port="${APP_PORT}"
cloudflared tunnel --url "http://127.0.0.1:${APP_PORT}"
```

Prefer a named tunnel + hostname for anything beyond a quick demo.

## Production checklist

1. `APP_ENV=production`, `APP_DEBUG=false`
2. Strong `APP_KEY` and rotated demo passwords
3. HTTPS + secure session cookies
4. `php artisan config:cache && php artisan route:cache && php artisan view:cache`
5. Queue worker if QUEUE_CONNECTION is not `sync`
6. Backups for MySQL + `storage/`

## Seeded demo users

See `README.md` — change passwords after first login.
