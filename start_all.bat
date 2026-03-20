@echo off
:: Koperasi Digital - Full Starter with Tunnel
:: Runs: PHP Server + Vite Dev + Cloudflare Tunnel

echo.
echo ========================================
echo   KOPERASI DIGITAL - FULL STARTER
echo   (with Cloudflare Tunnel)
echo ========================================
echo.

cd /d "%~dp0"

echo [1/4] Starting PHP Server...
start "PHP Server" cmd /k "php artisan serve"

echo [2/4] Starting Vite Dev Server...
start "Vite Dev" cmd /k "npm run dev"

echo [3/4] Waiting 5 seconds for servers to start...
timeout /t 5 /nobreak > nul

echo [4/4] Starting Cloudflare Tunnel...
start "Cloudflare Tunnel" cmd /k "cloudflared tunnel --url http://localhost:8000"

echo.
echo ========================================
echo   ALL SERVICES STARTED!
echo ========================================
echo.
echo   Local:    http://localhost:8000
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

start http://localhost:8000

pause
