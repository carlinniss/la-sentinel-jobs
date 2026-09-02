$ErrorActionPreference = 'Stop'

Set-Location -LiteralPath $PSScriptRoot

if (-not (Get-Command docker -ErrorAction SilentlyContinue)) {
    Write-Error 'Docker is not available in this terminal. Start Docker Desktop, then run this script again.'
}

Write-Host 'Starting LA Sentinel Jobs local dev container...' -ForegroundColor Cyan

git pull origin bmo-poc
docker compose -f docker-compose.dev.yml up --build
