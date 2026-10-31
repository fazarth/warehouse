# WMS local Docker (shared MySQL)

**Date:** 2026-07-23  
**Image:** multi-stage `Dockerfile` (Node assets + PHP 8.2)  
**Compose:** `docker-compose.yml` → container `live-wms` · host **`:8100`**

## Why this first
- Dockerfile + Railway start script already exist  
- DB `warehouse` + user `laravel` already on **live-mysql**  
- Canonical port **8100** (`Live/PORTS.md`)

## Prerequisites
```bash
cd E:/Kerjaan/Code/Live
docker compose -p live-infra -f docker-compose.infra.yml up -d
# network: live-infra · DB warehouse on live-mysql · see docs/DOCKER-INFRA.md
```

Stop host PHP if it holds 8100:
```bash
# find PID listening 8100, then:
# powershell: Stop-Process -Id <pid> -Force
```

## Up
```bash
cd E:/Kerjaan/Code/Live/warehouse
docker compose up -d --build
docker compose ps
docker compose logs -f wms
```

Open: http://127.0.0.1:8100/login  

Migrate/seed run in background via `docker/start-wms.sh` on boot.

## Down
```bash
docker compose down
# does NOT stop live-mysql / live-redis
```

## Notes
- `DB_HOST=live-mysql` (Docker DNS), not `127.0.0.1`
- App still **local/demo** inside container (`APP_DEBUG=true` in compose) — not public prod
- For production Railway, keep existing `docker/start-wms.sh` + platform `PORT`
- Shared Postgres for Talkozy remains separate (`ai_english_coach_db` → future live-postgres multi-DB)
