FROM php:8.4-fpm-alpine

RUN apk add --no-cache \
    $PHPIZE_DEPS \
    nginx \
    nodejs \
    npm \
    git \
    curl \
    zip \
    unzip \
    libpng-dev \
    libjpeg-turbo-dev \
    libwebp-dev \
    freetype-dev \
    oniguruma-dev \
    libxml2-dev \
    icu-dev \
    libzip-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd opcache intl zip \
    && pecl install redis \
    && docker-php-ext-enable redis

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock* ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

COPY . .

RUN composer dump-autoload --optimize \
    && npm install \
    && npm run build

COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/php-upload.ini /usr/local/etc/php/conf.d/uploads.ini
COPY docker/start.sh /start.sh
RUN chmod +x /start.sh

RUN mkdir -p /var/lib/nginx/tmp/client_body /var/lib/nginx/tmp/fastcgi /run/nginx \
    && chown -R www-data:www-data storage bootstrap/cache /var/lib/nginx /run/nginx \
    && chmod -R 775 storage bootstrap/cache /var/lib/nginx /run/nginx

EXPOSE 80

CMD ["/start.sh"]
