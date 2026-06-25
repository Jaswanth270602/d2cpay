# Start ngrok for local QPC Payin 9 webhook testing.
# Usage: powershell -ExecutionPolicy Bypass -File scripts/start-ngrok-qpc.ps1
# Requires: ngrok installed + authtoken in %LOCALAPPDATA%\ngrok\ngrok.yml

$ErrorActionPreference = "Stop"
$port = if ($env:QPC_LOCAL_PORT) { $env:QPC_LOCAL_PORT } else { 8000 }
$projectRoot = Split-Path -Parent $PSScriptRoot
$envFile = Join-Path $projectRoot ".env"

# Refresh PATH so ngrok is found after winget install
$env:Path = [System.Environment]::GetEnvironmentVariable("Path", "Machine") + ";" + [System.Environment]::GetEnvironmentVariable("Path", "User")

function Get-NgrokPublicUrl {
    $api = Invoke-RestMethod -Uri "http://127.0.0.1:4040/api/tunnels" -ErrorAction SilentlyContinue
    foreach ($tunnel in $api.tunnels) {
        if ($tunnel.public_url -like "https://*") {
            return $tunnel.public_url.TrimEnd("/")
        }
    }
    return $null
}

$existing = Get-NgrokPublicUrl
if (-not $existing) {
    Write-Host "Starting ngrok on port $port ..."
    Start-Process -FilePath "ngrok" -ArgumentList "http", $port, "--log=stdout" -WindowStyle Minimized | Out-Null
    $deadline = (Get-Date).AddSeconds(20)
    do {
        Start-Sleep -Seconds 1
        $existing = Get-NgrokPublicUrl
    } while (-not $existing -and (Get-Date) -lt $deadline)
}

if (-not $existing) {
    Write-Error "Could not start ngrok. Run 'ngrok update' then 'ngrok http $port' manually."
}

Write-Host "Ngrok URL: $existing"

if (Test-Path $envFile) {
    $content = Get-Content $envFile -Raw
    if ($content -match "(?m)^QPC_PUBLIC_URL=.*$") {
        $content = $content -replace "(?m)^QPC_PUBLIC_URL=.*$", "QPC_PUBLIC_URL=$existing"
    } else {
        $content += "`nQPC_PUBLIC_URL=$existing`n"
    }
    Set-Content -Path $envFile -Value $content.TrimEnd() + "`n" -NoNewline
    Write-Host "Updated .env QPC_PUBLIC_URL"
}

Push-Location $projectRoot
php artisan config:clear | Out-Null
Pop-Location

Write-Host ""
Write-Host "QPC Payin callback:  $existing/api/call-back/qpc-payin"
Write-Host "QPC Payout callback: $existing/api/call-back/qpc-payout"
Write-Host "Payin 9 web UI:        $existing/agent/add-money/v9/welcome"
Write-Host ""
        Write-Host "Keep ngrok running. Create a NEW payin order so QPC gets the ngrok callback URL."
        Write-Host ""
        Write-Host "If callbacks still use 127.0.0.1, clear shell override:"
        Write-Host "  Remove-Item Env:QPC_PUBLIC_URL -ErrorAction SilentlyContinue"
        Write-Host "  php artisan config:clear"
        Write-Host "  Restart: php artisan serve"
