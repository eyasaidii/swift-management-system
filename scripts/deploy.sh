#!/usr/bin/env bash
set -euo pipefail

usage() {
  cat <<EOF
Usage: $0 -e <staging|production> [-b branch] [-r remote] [--seed]

Example:
  ./scripts/deploy.sh -e staging -b staging -r origin --seed
EOF
}

ENVIRONMENT=""
BRANCH="main"
REMOTE="origin"
RUN_SEED=0

while [[ $# -gt 0 ]]; do
  case "$1" in
    -e|--env) ENVIRONMENT="$2"; shift 2;;
    -b|--branch) BRANCH="$2"; shift 2;;
    -r|--remote) REMOTE="$2"; shift 2;;
    --seed) RUN_SEED=1; shift 1;;
    -h|--help) usage; exit 0;;
    *) echo "Unknown arg: $1"; usage; exit 1;;
  esac
done

if [[ "$ENVIRONMENT" != "staging" && "$ENVIRONMENT" != "production" ]]; then
  echo "Environment must be 'staging' or 'production'"
  usage
  exit 1
fi

echo "Starting deploy: env=$ENVIRONMENT branch=$BRANCH"

echo "Fetching code..."
git fetch "$REMOTE"
git checkout "$BRANCH"
git pull "$REMOTE" "$BRANCH"

echo "Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader

if [[ -f package.json ]]; then
  echo "Installing JS dependencies and building assets..."
  npm ci
  npm run build
fi

echo "Entering maintenance mode..."
php artisan down --message="Maintenance: deploying $BRANCH ($ENVIRONMENT)"

echo "*** DB BACKUP: IMPORTANT ***"
echo "This script does NOT perform Oracle backups automatically. Run your Oracle backup now (expdp or snapshot), then press Enter to continue."
echo "Example expdp (adjust): expdp system/Pass@//dbhost:1521/XEPDB1 schemas=YOUR_SCHEMA directory=DATA_PUMP_DIR dumpfile=roles_backup_%U.dmp logfile=roles_backup.log"
read -r -p "Press Enter after completing the backup"

echo "Running migrations..."
php artisan migrate --force

if [[ $RUN_SEED -eq 1 ]]; then
  echo "Running seeders (BankRolesSeeder)..."
  php artisan db:seed --class=BankRolesSeeder --force
fi

echo "Clearing and caching config/routes/views..."
php artisan view:clear
php artisan cache:clear
php artisan config:cache
php artisan route:cache

echo "Bringing app UP..."
php artisan up

echo "Deployment finished. Run smoke tests and verify the application." 
echo "Optional: php artisan test --filter=SmokeTest"

exit 0
