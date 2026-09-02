#!/bin/sh
set -e

APP_DIR="/workspaces/la-sentinel-jobs"

if [ ! -d "$APP_DIR" ]; then
    APP_DIR="/var/www/html"
fi

cd "$APP_DIR"

if [ ! -f .env ]; then
    cp .env.example .env
fi

composer install
npm install

if ! grep -q '^APP_KEY=' .env; then
    printf '\nAPP_KEY=\n' >> .env
fi

if ! grep -q '^APP_KEY=base64:' .env; then
    php artisan key:generate --force
fi

php artisan migrate --force
php artisan db:seed --force
php artisan storage:link || true

php artisan queue:listen --tries=1 --timeout=0 &
npm run dev -- --host 0.0.0.0 &

exec php artisan serve --host=0.0.0.0 --port=8000
