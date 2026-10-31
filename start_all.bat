@echo off
:: Enterprise WMS - full starter with tunnel (APP_PORT default 8100)
:: Runs: PHP Server + Vite Dev + Cloudflare Tunnel

echo.
echo ========================================
echo   ENTERPRISE WMS - FULL STARTER
echo   (with Cloudflare Tunnel)
echo ========================================
echo.

cd /d "%~dp0"

if not defined APP_PORT set APP_PORT=8100
if not defined PORT set PORT=%APP_PORT%

echo [1/4] Starting PHP Server on port %APP_PORT%...
start "PHP Server" cmd /k "php artisan serve --host=127.0.0.1 --port=%APP_PORT%"

echo [2/4] Starting Vite Dev Server...
start "Vite Dev" cmd /k "npm run dev"

echo [3/4] Waiting 5 seconds for servers to start...
timeout /t 5 /nobreak > nul

echo [4/4] Starting Cloudflare Tunnel...
start "Cloudflare Tunnel" cmd /k "cloudflared tunnel --url http://127.0.0.1:%APP_PORT%"

echo.
echo ========================================
echo   ALL SERVICES STARTED!
echo ========================================
echo.
echo   Local:    http://127.0.0.1:%APP_PORT%
echo   Vite:     http://localhost:5173
echo   Tunnel:   Check Cloudflare Tunnel window for URL
echo.
echo   3 windows opened:
echo   - PHP Server
echo   - Vite Dev
echo   - Cloudflare Tunnel (shows public URL)
echo.
echo ========================================
echo.

start http://127.0.0.1:%APP_PORT%

pause
