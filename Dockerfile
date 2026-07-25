FROM php:8.3-fpm-alpine

RUN apk add --no-cache \
    postgresql-dev \
    linux-headers \
    && docker-php-ext-install -j$(nproc) \
        pdo_pgsql \
        pgsql \
        pcntl

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

COPY . .

RUN composer install --no-dev --prefer-dist \
    && php artisan storage:link \
    && chmod -R 775 storage bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 9000

CMD ["php-fpm"]
