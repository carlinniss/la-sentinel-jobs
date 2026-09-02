$ErrorActionPreference = 'Stop'

Set-Location -LiteralPath $PSScriptRoot

if (-not (Get-Command docker -ErrorAction SilentlyContinue)) {
    Write-Error 'Docker is not available in this terminal. If Docker Desktop is already closed, work mode is off.'
}

Write-Host 'Stopping LA Sentinel Jobs local dev container...' -ForegroundColor Cyan

docker compose -f docker-compose.dev.yml down

Write-Host 'Work mode is off. You can quit Docker Desktop before gaming or streaming for extra breathing room.' -ForegroundColor Green
