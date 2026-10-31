@echo off
:: Enterprise WMS - local starter (APP_PORT default 8100; see Live/PORTS.md)
:: Runs: PHP Server + Vite Dev

echo.
echo ========================================
echo   ENTERPRISE WMS - STARTER
echo ========================================
echo.

cd /d "%~dp0"

if not defined APP_PORT set APP_PORT=8100
if not defined PORT set PORT=%APP_PORT%

echo [1/3] Starting PHP Server on port %APP_PORT%...
start "PHP Server" cmd /k "php artisan serve --host=127.0.0.1 --port=%APP_PORT%"

echo [2/3] Starting Vite Dev Server...
start "Vite Dev" cmd /k "npm run dev"

echo [3/3] Waiting 5 seconds for servers to start...
timeout /t 5 /nobreak > nul

echo.
echo ========================================
echo   ALL SERVICES STARTED!
echo ========================================
echo.
echo   PHP Server:  http://127.0.0.1:%APP_PORT%
echo   Vite Dev:    http://localhost:5173
echo.
echo   Press any key to open browser...
echo ========================================
pause > nul

start http://127.0.0.1:%APP_PORT%

echo.
echo Servers are running in separate windows.
echo Close those windows to stop the servers.
echo.
pause
