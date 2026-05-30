FROM php:8.3-apache

# -------------------------
# System dependencies
# -------------------------
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

# -------------------------
# Composer
# -------------------------
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# -------------------------
# Apache config (CRITICAL for Laravel)
# -------------------------
RUN a2enmod rewrite

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/000-default.conf

RUN sed -ri -e 's!/var/www/!/var/www/html/public!g' /etc/apache2/apache2.conf

# -------------------------
# App setup
# -------------------------
WORKDIR /var/www/html

COPY . .

# -------------------------
# Install PHP deps
# -------------------------
RUN composer install --no-dev --optimize-autoloader --no-interaction

# -------------------------
# Frontend build (Vite)
# -------------------------
RUN npm install && npm run build

# -------------------------
# SQLite safety
# -------------------------
RUN mkdir -p database \
    && touch database/database.sqlite

# -------------------------
# Permissions (critical in CapRover)
# -------------------------
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache database

# -------------------------
# Laravel optimization (safe order)
# -------------------------
RUN php artisan config:clear || true
RUN php artisan cache:clear || true
RUN php artisan view:clear || true

RUN php artisan storage:link || true

# -------------------------
# Expose Apache
# -------------------------
EXPOSE 80

# -------------------------
# Startup (safe for CapRover)
# -------------------------
CMD bash -c "\
php artisan config:cache || true && \
php artisan route:cache || true && \
php artisan view:cache || true && \
apache2-foreground"
