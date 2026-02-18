#!/bin/sh
set -e

# Wait for PostgreSQL to be ready
echo "Waiting for database..."
wait-for-it db:5432 --timeout=30 --strict -- echo "Database is up"

# Clear Laravel caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear

# Start Apache
exec "$@"