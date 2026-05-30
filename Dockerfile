# =========================================================
# BASE IMAGE
# =========================================================
FROM php:8.3-apache

# =========================================================
# SYSTEM DEPENDENCIES
# =========================================================
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip \
    curl \
    sqlite3 \
    libsqlite3-dev \
    libzip-dev \
    nodejs \
    npm \
    && docker-php-ext-install \
        pdo \
        pdo_sqlite \
        zip

# =========================================================
# COMPOSER
# =========================================================
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# =========================================================
# APACHE CONFIG (Laravel public folder)
# =========================================================
RUN a2enmod rewrite

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/000-default.conf \
 && sed -ri -e 's!/var/www/!/var/www/html/public!g' /etc/apache2/apache2.conf

# FIX: Apache server name warning
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# =========================================================
# WORKDIR
# =========================================================
WORKDIR /var/www/html

# =========================================================
# COPY APPLICATION
# =========================================================
COPY . .

# =========================================================
# INSTALL PHP DEPENDENCIES
# =========================================================
RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction \
    --no-progress

# =========================================================
# FRONTEND BUILD (Vite)
# =========================================================
RUN npm install && npm run build

# =========================================================
# LARAVEL REQUIRED DIRECTORIES (CRITICAL FAILSAFE)
# =========================================================
RUN mkdir -p storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache \
    database

# =========================================================
# SQLITE SAFETY — store DB inside the persistent storage volume
# =========================================================
# NOTE: /var/www/html/database is NOT persistent on CapRover (and mounting it
# would mask migrations/factories/seeders). The real DB lives under storage/,
# which IS a persistent CapRover volume.
RUN mkdir -p storage/app/database \
 && touch storage/app/database/database.sqlite \
 && touch database/database.sqlite

# =========================================================
# PERMISSIONS (FIXES 500 + LOG FAILURES)
# =========================================================
RUN chown -R www-data:www-data /var/www/html \
 && chmod -R 775 storage bootstrap/cache database

# =========================================================
# LARAVEL OPTIMIZATION (SAFE ORDER)
# =========================================================
RUN php artisan config:clear || true
RUN php artisan route:clear || true
RUN php artisan view:clear || true
RUN php artisan cache:clear || true

RUN php artisan storage:link || true

# =========================================================
# IMPORTANT: DO NOT CACHE DURING BUILD (CapRover SAFE)
# =========================================================
# OLD (REMOVED SAFETY ISSUE):
# RUN php artisan config:cache
# RUN php artisan route:cache
# RUN php artisan view:cache

# =========================================================
# STARTUP SCRIPT (FAILSAFE RUNTIME BOOT)
# =========================================================
RUN printf '%s\n' \
'#!/bin/bash' \
'set -e' \
'' \
'echo "[boot] Ensuring persistent SQLite path exists..."' \
'mkdir -p /var/www/html/storage/app/database' \
'touch /var/www/html/storage/app/database/database.sqlite' \
'' \
'echo "[boot] Fixing permissions..."' \
'chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache || true' \
'chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache || true' \
'' \
'echo "[boot] Clearing stale caches..."' \
'php artisan config:clear || true' \
'php artisan route:clear || true' \
'php artisan view:clear || true' \
'' \
'echo "[boot] Running migrations..."' \
'php artisan migrate --force || echo "[boot] migrate failed (continuing)"' \
'' \
'echo "[boot] Seeding admin (idempotent)..."' \
'php artisan db:seed --class=AdminUserSeeder --force || true' \
'' \
'echo "[boot] Streaming Laravel log to stdout..."' \
'touch /var/www/html/storage/logs/laravel.log' \
'chown www-data:www-data /var/www/html/storage/logs/laravel.log || true' \
'tail -F /var/www/html/storage/logs/laravel.log &' \
'' \
'echo "[boot] Starting Apache..."' \
'exec apache2-foreground' \
> /start.sh

RUN chmod +x /start.sh

# =========================================================
# PORT
# =========================================================
EXPOSE 80

# =========================================================
# START COMMAND
# =========================================================
CMD ["/start.sh"]
