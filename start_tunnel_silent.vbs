' Silent Cloudflare tunnel for Enterprise WMS (local APP_PORT default 8100)
Set WshShell = CreateObject("WScript.Shell")
port = WshShell.ExpandEnvironmentStrings("%APP_PORT%")
If port = "%APP_PORT%" Or port = "" Then port = "8100"
WshShell.Run "cloudflared tunnel --url http://127.0.0.1:" & port, 0, False
