#!/bin/bash

# Write .env from Render environment variables
cat > /var/www/html/.env <<EOF
APP_NAME=${APP_NAME:-ResQLink}
APP_ENV=${APP_ENV:-production}
APP_KEY=${APP_KEY}
APP_DEBUG=${APP_DEBUG:-false}
APP_URL=${APP_URL:-http://localhost}

LOG_CHANNEL=${LOG_CHANNEL:-stderr}

DB_CONNECTION=${DB_CONNECTION:-pgsql}
DB_HOST=${DB_HOST}
DB_PORT=${DB_PORT:-5432}
DB_DATABASE=${DB_DATABASE}
DB_USERNAME=${DB_USERNAME}
DB_PASSWORD=${DB_PASSWORD}
DB_SSLMODE=${DB_SSLMODE:-require}

CACHE_DRIVER=${CACHE_DRIVER:-file}
SESSION_DRIVER=${SESSION_DRIVER:-cookie}
SESSION_LIFETIME=${SESSION_LIFETIME:-120}
QUEUE_CONNECTION=${QUEUE_CONNECTION:-sync}
EOF

# Clear any cached config so fresh .env is used
php artisan config:clear

# Generate APP_KEY if not set
if [ -z "$APP_KEY" ]; then
    php artisan key:generate --force
fi

# Drop all tables and re-run migrations cleanly
php artisan migrate:fresh --force

# Storage link
php artisan storage:link --force 2>/dev/null || true

# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Render sets PORT env var — configure Apache to listen on it
PORT="${PORT:-80}"
sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

# Start Apache
exec apache2-foreground
