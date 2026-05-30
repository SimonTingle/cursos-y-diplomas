FROM php:8.3-apache

# ----------------------------------------------------
# SYSTEM DEPENDENCIES
# ----------------------------------------------------
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

# ----------------------------------------------------
# COMPOSER
# ----------------------------------------------------
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# ----------------------------------------------------
# APACHE CONFIG (CRITICAL FOR LARAVEL)
# ----------------------------------------------------
RUN a2enmod rewrite

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/000-default.conf \
 && sed -ri -e 's!/var/www/!/var/www/html/public!g' /etc/apache2/apache2.conf

# Prevent Apache warning
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# ----------------------------------------------------
# WORKDIR
# ----------------------------------------------------
WORKDIR /var/www/html

# ----------------------------------------------------
# COPY APPLICATION
# ----------------------------------------------------
COPY . .

# ----------------------------------------------------
# INSTALL PHP DEPENDENCIES
# ----------------------------------------------------
RUN composer install --no-dev --optimize-autoloader --no-interaction

# ----------------------------------------------------
# INSTALL FRONTEND BUILD
# ----------------------------------------------------
RUN npm install && npm run build

# ----------------------------------------------------
# SQLITE SETUP
# ----------------------------------------------------
RUN mkdir -p database \
    && touch database/database.sqlite

# ----------------------------------------------------
# PERMISSIONS (CRITICAL FIX FOR YOUR ERROR)
# ----------------------------------------------------
RUN mkdir -p storage/logs bootstrap/cache \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache database

# ----------------------------------------------------
# LARAVEL SAFE CACHE (BUILD TIME ONLY)
# ----------------------------------------------------
RUN php artisan optimize:clear || true

# OPTIONAL (DO NOT FAIL BUILD IF MISSING ENV)
RUN php artisan storage:link || true

# ----------------------------------------------------
# EXPOSE APACHE
# ----------------------------------------------------
EXPOSE 80

# ----------------------------------------------------
# STARTUP
# ----------------------------------------------------
CMD bash -c "\
php artisan migrate --force || true && \
php artisan config:cache || true && \
php artisan route:cache || true && \
apache2-foreground"
