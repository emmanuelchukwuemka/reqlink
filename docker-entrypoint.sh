#!/bin/bash
set -e

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

CACHE_DRIVER=${CACHE_DRIVER:-database}
SESSION_DRIVER=${SESSION_DRIVER:-database}
QUEUE_CONNECTION=${QUEUE_CONNECTION:-database}
EOF

# Clear any cached config so fresh .env is used
php artisan config:clear

# Run migrations
php artisan migrate --force

# Storage link
php artisan storage:link --force 2>/dev/null || true

# Start Apache
apache2-foreground
