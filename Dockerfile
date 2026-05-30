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
    && docker-php-ext-install \
    pdo \
    pdo_sqlite \
    zip

# -------------------------
# Composer
# -------------------------
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# -------------------------
# Enable Apache rewrite
# -------------------------
RUN a2enmod rewrite

# -------------------------
# FORCE correct Laravel public root (IMPORTANT)
# -------------------------
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf

# -------------------------
# App setup
# -------------------------
WORKDIR /var/www/html
COPY . .

# -------------------------
# Install PHP dependencies
# -------------------------
RUN composer install --no-dev --optimize-autoloader --no-interaction

# -------------------------
# Install Node properly (fixes Vite issues)
# -------------------------
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

RUN npm install
RUN npm run build

# -------------------------
# SQLite safety
# -------------------------
RUN mkdir -p /var/www/html/database \
    && touch /var/www/html/database/database.sqlite \
    && chown -R www-data:www-data /var/www/html/database
# -------------------------
# Permissions (CapRover safe)
# -------------------------

# -------------------------
# DO NOT cache Laravel during build (important fix)
# -------------------------
# (removed config/route/view cache intentionally)

# -------------------------
# Expose Apache
# -------------------------
EXPOSE 80

# -------------------------
# Runtime startup ONLY
# -------------------------
CMD ["apache2-foreground"]
