FROM php:8.2-cli

RUN apt-get update && apt-get install -y \
    libzip-dev libpq-dev zip unzip curl git \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql pgsql

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

RUN composer install --no-dev --optimize-autoloader

CMD php artisan serve --host=0.0.0.0 --port=$PORT
