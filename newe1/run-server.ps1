# E1 Site - Server Start Script
# Запуск: правый клик -> "Выполнить с помощью PowerShell"
# Или в терминале: .\run-server.ps1

Write-Host "=== E1 Site Server ===" -ForegroundColor Green

# Добавить npm в PATH
$npmPath = "$env:APPDATA\npm"
if ($env:Path -notlike "*$npmPath*") {
    $env:Path += ";$npmPath"
    Write-Host "Added npm to PATH" -ForegroundColor Yellow
}

# Перейти в папку newe1
$scriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
Set-Location $scriptDir
Write-Host "Working directory: $scriptDir" -ForegroundColor Cyan

# Проверить PM2
$pm2Check = Get-Command pm2 -ErrorAction SilentlyContinue
if (-not $pm2Check) {
    Write-Host "PM2 not found! Installing..." -ForegroundColor Red
    npm install -g pm2
}

# Проверить статус PM2
Write-Host "`nChecking PM2 status..." -ForegroundColor Yellow
$pm2List = pm2 list

# Если newe1 уже запущен - перезапустить, иначе запустить
$isRunning = pm2 describe newe1 2>$null
if ($LASTEXITCODE -eq 0) {
    Write-Host "Restarting newe1..." -ForegroundColor Yellow
    pm2 restart newe1
} else {
    Write-Host "Starting newe1..." -ForegroundColor Yellow

    # Проверить есть ли ecosystem.config.js
    if (Test-Path "ecosystem.config.js") {
        pm2 start ecosystem.config.js
    } else {
        # Запустить напрямую через next
        pm2 start node_modules/next/dist/bin/next --name "newe1" --interpreter node -- start
    }
}

# Показать статус
Write-Host "`n=== Server Status ===" -ForegroundColor Green
pm2 list

# Сохранить конфигурацию
pm2 save

Write-Host "`nServer is running on http://localhost:3000" -ForegroundColor Green
Write-Host "Press any key to exit (server will keep running)..." -ForegroundColor Gray
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
