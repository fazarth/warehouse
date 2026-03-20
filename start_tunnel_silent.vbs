' Koperasi Digital - Silent Cloudflare Tunnel Starter
' This script runs cloudflared tunnel silently at Windows startup

Set WshShell = CreateObject("WScript.Shell")

' Run cloudflared tunnel in hidden window
WshShell.Run "cloudflared tunnel --url http://localhost:8000", 0, False

' Optional: Log startup
Set fso = CreateObject("Scripting.FileSystemObject")
Set logFile = fso.OpenTextFile("C:\laragon\www\koperasi\tunnel_startup.log", 8, True)
logFile.WriteLine Now & " - Cloudflare Tunnel Started"
logFile.Close
