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
# SQLITE SAFETY (PREVENT MISSING DB FILE)
# =========================================================
RUN touch database/database.sqlite

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
RUN echo '#!/bin/bash\n\
set -e\n\
\n\
echo "Fixing permissions..."\n\
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database || true\n\
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database || true\n\
\n\
echo "Running migrations..."\n\
php artisan migrate --force || true\n\
\n\
echo "Clearing stale caches..."\n\
php artisan config:clear || true\n\
php artisan route:clear || true\n\
\n\
echo "Starting Apache..."\n\
apache2-foreground\n' > /start.sh

RUN chmod +x /start.sh

# =========================================================
# PORT
# =========================================================
EXPOSE 80

# =========================================================
# START COMMAND
# =========================================================
CMD ["/start.sh"]
