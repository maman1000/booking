FROM php:8.4-fpm-alpine

RUN apk add --no-cache \
    git zip unzip libzip-dev libpng-dev libjpeg-turbo-dev freetype-dev \
    oniguruma-dev postgresql-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd pdo pdo_mysql pdo_pgsql mbstring zip opcache

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY composer.json ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-plugins --no-scripts --ignore-platform-req=php

COPY . .
RUN php artisan config:cache

# EXPOSE 8000

# CMD php artisan serve --host=0.0.0.0 --port=8000

EXPOSE ${PORT:-8000}
CMD php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
