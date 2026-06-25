#!/bin/bash
# Run on production server (cPanel Terminal or SSH):
#   cd /home/r7q5p6bm505j/public_html/d2cpay.co && bash scripts/production-deploy.sh

set -e

APP_DIR="/home/r7q5p6bm505j/public_html/d2cpay.co"
cd "$APP_DIR"

echo "==> Pulling latest code from origin/main..."
git fetch origin
git reset --hard origin/main

echo "==> Clearing Laravel caches..."
php artisan route:clear
php artisan cache:clear
php artisan config:clear

echo "==> Verifying generic callback route..."
php artisan route:list --path=call-back/generic || true

echo "==> Done. Test with:"
echo "    curl -X POST https://d2cpay.co/api/call-back/generic -H 'Content-Type: application/json' -d '{\"status\":\"success\",\"amount\":100}'"
