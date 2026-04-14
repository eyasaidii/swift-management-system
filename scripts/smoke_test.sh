#!/usr/bin/env bash
set -euo pipefail

BASE_URL=${1:-http://localhost:8000}

check() {
  local path="$1"
  local code
  code=$(curl -s -o /dev/null -w "%{http_code}" "$BASE_URL$path")
  if [[ "$code" != "200" ]]; then
    echo "[FAIL] $path returned $code"
    return 1
  fi
  echo "[OK] $path returned 200"
}

echo "Running smoke tests against $BASE_URL"
check "/login"
check "/dashboard"

echo "Smoke tests passed"
