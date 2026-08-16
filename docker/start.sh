#!/bin/sh
set -e

if [ ! -f /var/www/html/.env ]; then
    cp /var/www/html/.env.example /var/www/html/.env
    php artisan key:generate --force
fi

php artisan migrate --force

if [ "${RUN_DB_SEED:-false}" = "true" ]; then
    php artisan db:seed --force
fi

php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link

php-fpm -D
nginx -g 'daemon off;'
