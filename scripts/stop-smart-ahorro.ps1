$ErrorActionPreference = "SilentlyContinue"
$RootDir = Split-Path -Parent $PSScriptRoot
$PidFile = Join-Path $RootDir ".runtime\pids.json"

function Stop-ByPort([int]$Port) {
    $conns = Get-NetTCPConnection -LocalPort $Port -State Listen -ErrorAction SilentlyContinue
    foreach ($c in $conns) {
        if ($c.OwningProcess -gt 0) {
            & taskkill.exe /PID $c.OwningProcess /T /F 2>&1 | Out-Null
            Write-Host "  -> Puerto $Port liberado (PID $($c.OwningProcess))" -ForegroundColor Green
        }
    }
}

Write-Host "Deteniendo servicios activos de SMART AHORRO..." -ForegroundColor Yellow
if (Test-Path $PidFile) {
    $info = Get-Content $PidFile | ConvertFrom-Json
    if ($info.laravelPid -gt 0) { & taskkill.exe /PID $info.laravelPid /T /F 2>&1 | Out-Null }
    if ($info.springPid  -gt 0) { & taskkill.exe /PID $info.springPid  /T /F 2>&1 | Out-Null }
    Stop-ByPort -Port $info.laravelPort
    Stop-ByPort -Port $info.springPort
    Remove-Item $PidFile -Force
} else {
    Stop-ByPort -Port 8000
    Stop-ByPort -Port 8080
}
Write-Host "Servicios detenidos." -ForegroundColor Green