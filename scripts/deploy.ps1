<#
Deploy script (PowerShell)
Usage:
  .\scripts\deploy.ps1 -Environment staging -Branch staging -Remote origin -RunSeed:$false

This script performs a safe deploy workflow:
 - fetch & checkout branch
 - install deps (composer, npm) and build assets
 - put app in maintenance
 - create DB backup placeholder (you must run Oracle backup manually or configure here)
 - run migrations and optional seeders
 - clear caches and bring app up

IMPORTANT: This script includes placeholders for Oracle backups; run backups manually if needed.
#>

param(
    [Parameter(Mandatory=$true)][ValidateSet('staging','production')]
    [string]$Environment,

    [Parameter(Mandatory=$false)][string]$Branch = 'main',
    [Parameter(Mandatory=$false)][string]$Remote = 'origin',
    [Parameter(Mandatory=$false)][bool]$RunSeed = $false
)

function ExitOnError($code, $msg) {
    if ($code -ne 0) {
        Write-Error $msg
        exit $code
    }
}

Write-Host "Starting deploy: env=$Environment branch=$Branch" -ForegroundColor Cyan

Write-Host "Pulling code..."
git fetch $Remote
ExitOnError($LASTEXITCODE, 'git fetch failed')
git checkout $Branch
ExitOnError($LASTEXITCODE, 'git checkout failed')
git pull $Remote $Branch
ExitOnError($LASTEXITCODE, 'git pull failed')

Write-Host "Installing PHP dependencies (composer)..."
composer install --no-dev --optimize-autoloader
ExitOnError($LASTEXITCODE, 'composer install failed')

if (Test-Path package.json) {
    Write-Host "Installing JS dependencies and building assets..."
    npm ci
    ExitOnError($LASTEXITCODE, 'npm ci failed')
    npm run build
    ExitOnError($LASTEXITCODE, 'npm run build failed')
}

Write-Host "Entering maintenance mode..."
php artisan down --message="Maintenance: deploying $Branch ($Environment)"
ExitOnError($LASTEXITCODE, 'artisan down failed')

Write-Host "*** DB BACKUP: IMPORTANT ***" -ForegroundColor Yellow
Write-Host "This script does NOT perform the Oracle backup automatically. Please run your Oracle backup (expdp or DB snapshot) now, then press Enter to continue."
Write-Host "Example expdp (adjust): expdp system/Pass@//dbhost:1521/XEPDB1 schemas=YOUR_SCHEMA directory=DATA_PUMP_DIR dumpfile=roles_backup_%U.dmp logfile=roles_backup.log"
Read-Host "Press Enter after completing the backup"

Write-Host "Running migrations..."
php artisan migrate --force
ExitOnError($LASTEXITCODE, 'artisan migrate failed')

if ($RunSeed) {
    Write-Host "Running seeders (BankRolesSeeder)..."
    php artisan db:seed --class=BankRolesSeeder --force
    ExitOnError($LASTEXITCODE, 'db:seed failed')
}

Write-Host "Clearing and caching config/routes/views..."
php artisan view:clear
php artisan cache:clear
php artisan config:cache
php artisan route:cache

Write-Host "Bringing app UP..."
php artisan up

Write-Host "Deployment finished. Please run smoke tests and verify the application." -ForegroundColor Green

# Optional smoke tests
Write-Host "Optional: run basic smoke tests (artisan test)" -ForegroundColor Yellow
Write-Host "Command: php artisan test --filter=SmokeTest" -ForegroundColor Yellow

Exit 0
