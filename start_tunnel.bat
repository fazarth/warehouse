@echo off
:: Cloudflare Tunnel for Enterprise WMS (local APP_PORT default 8100)

echo ========================================
echo  Enterprise WMS - Cloudflare Tunnel
echo ========================================
echo.

if not defined APP_PORT set APP_PORT=8100
if not defined PORT set PORT=%APP_PORT%

set LARAVEL_URL=http://127.0.0.1:%APP_PORT%
set LOG_FILE=%~dp0tunnel_url.txt

echo Starting Cloudflare Tunnel...
echo Target: %LARAVEL_URL%
echo.

echo Waiting for tunnel URL... > "%LOG_FILE%"

cloudflared tunnel --url %LARAVEL_URL% 2>&1 | findstr /C:"trycloudflare.com" > "%LOG_FILE%"

pause
