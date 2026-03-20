@echo off
:: Cloudflare Tunnel Auto-Start Script for Koperasi Digital
:: This script starts a quick tunnel and saves the URL to a file

echo ========================================
echo  Koperasi Digital - Cloudflare Tunnel
echo ========================================
echo.

:: Set variables
set LARAVEL_URL=http://localhost:8000
set LOG_FILE=%~dp0tunnel_url.txt

echo Starting Cloudflare Tunnel...
echo Target: %LARAVEL_URL%
echo.

:: Clear previous URL file
echo Waiting for tunnel URL... > "%LOG_FILE%"

:: Start cloudflared and capture output
cloudflared tunnel --url %LARAVEL_URL% 2>&1 | findstr /C:"trycloudflare.com" > "%LOG_FILE%"

pause
