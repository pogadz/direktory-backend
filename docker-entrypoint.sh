#!/bin/sh
set -e

# Wait for PostgreSQL to be ready
echo "Waiting for database..."
wait-for-it db:5432 --timeout=30 --strict -- echo "Database is up"

# Clear config/route caches
php artisan config:clear
php artisan route:clear

# Run migrations
php artisan migrate --force

# Clear application cache
php artisan cache:clear

# Start Apache
exec "$@"