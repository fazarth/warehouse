@echo off
:: Koperasi Digital - All-in-One Starter
:: Runs: PHP Server + Vite Dev + Cloudflare Tunnel

echo.
echo ========================================
echo   KOPERASI DIGITAL - STARTER
echo ========================================
echo.

cd /d "%~dp0"

echo [1/3] Starting PHP Server...
start "PHP Server" cmd /k "php artisan serve"

echo [2/3] Starting Vite Dev Server...
start "Vite Dev" cmd /k "npm run dev"

echo [3/3] Waiting 5 seconds for servers to start...
timeout /t 5 /nobreak > nul

echo.
echo ========================================
echo   ALL SERVICES STARTED!
echo ========================================
echo.
echo   PHP Server:  http://localhost:8000
echo   Vite Dev:    http://localhost:5173
echo.
echo   Press any key to open browser...
echo ========================================
pause > nul

start http://localhost:8000

echo.
echo Servers are running in separate windows.
echo Close those windows to stop the servers.
echo.
pause
