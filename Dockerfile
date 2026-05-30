FROM php:8.3-apache

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip \
    sqlite3 \
    libsqlite3-dev \
    libzip-dev \
    && docker-php-ext-install \
    pdo \
    pdo_sqlite \
    zip

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

RUN composer install --no-dev --optimize-autoloader

RUN chown -R www-data:www-data storage bootstrap/cache

RUN a2enmod rewrite

EXPOSE 80

CMD bash -c "\
php artisan config:cache && \
php artisan route:cache && \
apache2-foreground"
