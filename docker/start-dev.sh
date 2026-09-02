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

set_env() {
    key="$1"
    value="$2"

    if grep -q "^${key}=" .env; then
        sed -i "s#^${key}=.*#${key}=${value}#" .env
    else
        printf '\n%s=%s\n' "$key" "$value" >> .env
    fi
}

set_env APP_ENV local
set_env APP_DEBUG true
set_env DB_CONNECTION mysql
set_env DB_HOST db
set_env DB_PORT 3306
set_env DB_DATABASE openclassify
set_env DB_USERNAME openclassify
set_env DB_PASSWORD secret
set_env CACHE_STORE database
set_env SESSION_DRIVER database
set_env QUEUE_CONNECTION database
set_env BROADCAST_CONNECTION null
set_env FILESYSTEM_DISK public
set_env MEDIA_DISK public
set_env LOCAL_MEDIA_DISK public
set_env LIVEWIRE_TEMPORARY_FILE_UPLOAD_DISK local

composer install
npm install

php artisan optimize:clear || true

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
