# syntax=docker/dockerfile:1

# ---------------------------------------------------------------------------
# Stage 1 — build front-end assets with Vite
# ---------------------------------------------------------------------------
FROM node:22-alpine AS assets
WORKDIR /app
COPY package.json package-lock.json vite.config.js tailwind.config.js postcss.config.js ./
RUN npm ci
COPY resources ./resources
RUN npm run build

# ---------------------------------------------------------------------------
# Stage 2 — PHP 8.4 application image (php-fpm)
# ---------------------------------------------------------------------------
FROM php:8.4-fpm-bookworm AS app

# System libraries + PHP extensions required by Laravel, Cashier (bcmath) and Redis.
RUN apt-get update && apt-get install -y --no-install-recommends \
        git unzip libicu-dev libzip-dev libpng-dev libonig-dev default-mysql-client \
    && docker-php-ext-configure intl \
    && docker-php-ext-install -j"$(nproc)" bcmath intl zip gd pdo_mysql opcache \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Install PHP dependencies first (better layer caching).
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction

# Application source + compiled assets.
COPY . .
COPY --from=assets /app/public/build ./public/build

RUN composer dump-autoload --optimize --no-dev --classmap-authoritative \
    && cp docker/php/php.ini "$PHP_INI_DIR/conf.d/99-leadrecover.ini" \
    && chown -R www-data:www-data storage bootstrap/cache

COPY docker/entrypoint.sh /usr/local/bin/entrypoint
RUN chmod +x /usr/local/bin/entrypoint

EXPOSE 9000
ENTRYPOINT ["entrypoint"]
CMD ["php-fpm"]
