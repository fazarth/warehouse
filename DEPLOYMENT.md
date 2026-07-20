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
php artisan serve --host=127.0.0.1 --port=8000
```

## Docker env note

`.env.docker` uses service hostnames (`DB_HOST=mysql`, `REDIS_HOST=redis`) for in-network containers.
Host-side artisan/serve should use `127.0.0.1` and file drivers for cache/session if Redis is not exposed.

## Cloudflare Tunnel (demo)

```bash
php artisan serve --host=127.0.0.1 --port=8000
cloudflared tunnel --url http://127.0.0.1:8000
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
