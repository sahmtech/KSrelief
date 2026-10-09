#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")/.."

echo "==> Removing stale Vite hot file (if any)"
rm -f public/hot

echo "==> Installing PHP dependencies"
export COMPOSER_CACHE_DIR="${COMPOSER_CACHE_DIR:-/tmp/composer-cache}"
mkdir -p "$COMPOSER_CACHE_DIR"
composer install --no-dev --optimize-autoloader

if ! grep -q '^APP_KEY=base64:' .env 2>/dev/null; then
    echo "==> Generating APP_KEY"
    php artisan key:generate --force
fi

echo "==> Linking storage"
php artisan storage:link || true

echo "==> Running migrations"
php artisan migrate --force

echo "==> Seeding essentials (roles, settings, super admin)"
php artisan db:seed --force

echo "==> Caching for production"
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "==> Fixing permissions"
chmod -R 775 storage bootstrap/cache

echo "==> Done. Login: superadmin@ksrelife.com / ks123456relife"
echo ""
echo "IMPORTANT: Cloudways Webroot MUST be: public_html/public"
echo "Verify assets: https://YOUR-DOMAIN/build/assets/app-DREMItda.js"
