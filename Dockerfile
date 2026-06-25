FROM php:8.2-apache

# Enable mod_rewrite for Laravel routing
RUN a2enmod rewrite headers

# System dependencies
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libpng-dev \
    libjpeg-dev \
    libzip-dev \
    libonig-dev \
    libicu-dev \
    zip unzip curl git \
    && curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && rm -rf /var/lib/apt/lists/*

# PHP extensions (pdo_pgsql for Neon, intl for Filament)
RUN docker-php-ext-install pdo pdo_pgsql pgsql mbstring bcmath gd zip pcntl opcache intl

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set Apache document root to Laravel public/
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf \
    && sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

WORKDIR /var/www/html

# Copy app
COPY . .

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Build frontend assets
RUN npm ci && npm run build

# Storage permissions
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 80

CMD bash -c "php artisan migrate --force && php artisan storage:link --force && php artisan config:cache && php artisan route:cache && apache2-foreground"
