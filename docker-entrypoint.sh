#!/bin/bash
set -e

echo "Starting Laravel application..."

# Wait for database to be ready (if using external database)
if [ -n "$DB_HOST" ]; then
    echo "Waiting for database connection..."
    while ! nc -z $DB_HOST ${DB_PORT:-5432} 2>/dev/null; do
        echo "Database is unavailable - sleeping"
        sleep 1
    done
    echo "Database is up!"
fi

# Generate application key if not set
if [ -z "$APP_KEY" ]; then
    echo "Generating application key..."
    php artisan key:generate --force
fi

# Run migrations
echo "Running migrations..."
php artisan migrate --force

# Cache configuration for better performance
echo "Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set proper permissions
echo "Setting permissions..."
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

echo "Laravel application is ready!"

# Execute the main command
exec "$@"
