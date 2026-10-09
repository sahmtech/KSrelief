#!/usr/bin/env bash
# Run on Cloudways when you see: HasApiTokens not found / Sanctum not found
set -euo pipefail

cd "$(dirname "$0")/.."

export COMPOSER_CACHE_DIR="${COMPOSER_CACHE_DIR:-/tmp/composer-cache}"
export COMPOSER_HOME="${COMPOSER_HOME:-/tmp/composer-home}"
mkdir -p "$COMPOSER_CACHE_DIR" "$COMPOSER_HOME"

echo "==> Clearing Laravel bootstrap cache"
rm -f bootstrap/cache/*.php
rm -f public/hot

if ! grep -q '"name": "laravel/sanctum"' composer.lock 2>/dev/null; then
    echo "ERROR: composer.lock on server is outdated (no laravel/sanctum)."
    echo "Upload a fresh esghaa-deploy.zip or git pull latest composer.lock"
    exit 1
fi

echo "==> composer install (this installs vendor/, including Sanctum)"
composer install --no-dev --optimize-autoloader --no-interaction

if [ ! -f vendor/laravel/sanctum/src/HasApiTokens.php ]; then
    echo "==> Sanctum still missing — forcing install"
    composer require laravel/sanctum:^4.3 --no-dev --optimize-autoloader --no-interaction
fi

if [ ! -f vendor/laravel/sanctum/src/HasApiTokens.php ]; then
    echo "FATAL: laravel/sanctum could not be installed. Send composer output to support."
    exit 1
fi

echo "==> Sanctum OK: $(composer show laravel/sanctum --no-ansi 2>/dev/null | head -1 || true)"

php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "==> Done. Reload the site in your browser."
