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

CACHE_DRIVER=${CACHE_DRIVER:-file}
SESSION_DRIVER=${SESSION_DRIVER:-cookie}
SESSION_LIFETIME=${SESSION_LIFETIME:-120}
QUEUE_CONNECTION=${QUEUE_CONNECTION:-sync}
EOF

php artisan config:clear

if [ -z "$APP_KEY" ]; then
    php artisan key:generate --force
fi

# Use direct (non-pooler) Neon endpoint for DDL migrations
DB_HOST_DIRECT=$(echo "${DB_HOST}" | sed 's/-pooler\././g')
DB_HOST="${DB_HOST_DIRECT}" php artisan migrate:fresh --force

php artisan storage:link --force 2>/dev/null || true
php artisan config:cache
php artisan route:cache

# Render uses a non-standard PORT — rewrite the VirtualHost with the correct port
PORT="${PORT:-80}"
sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
cat > /etc/apache2/sites-available/000-default.conf <<APACHECONF
<VirtualHost *:${PORT}>
    ServerName localhost
    DocumentRoot /var/www/html/public
    <Directory /var/www/html/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    ErrorLog \${APACHE_LOG_DIR}/error.log
    CustomLog \${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
APACHECONF

exec apache2-foreground
