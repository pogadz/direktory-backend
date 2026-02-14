#!/bin/bash

# Laravel Docker Local Development Script

set -e

echo "🐳 Laravel Docker Local Setup"
echo "=============================="

# Check if .env exists
if [ ! -f .env ]; then
    echo "📝 Creating .env file from .env.example..."
    cp .env.example .env
    echo "✅ .env file created"
else
    echo "✅ .env file already exists"
fi

# Start containers
echo ""
echo "🚀 Starting Docker containers..."
docker-compose up -d

# Wait for database
echo ""
echo "⏳ Waiting for database to be ready..."
sleep 5

# Install dependencies
echo ""
echo "📦 Installing Composer dependencies..."
docker-compose exec app composer install

# Generate key
echo ""
echo "🔑 Generating application key..."
docker-compose exec app php artisan key:generate

# Run migrations
echo ""
echo "🗄️  Running migrations..."
docker-compose exec app php artisan migrate

# Seed database
echo ""
read -p "Do you want to seed the database? (y/n) " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    echo "🌱 Seeding database..."
    docker-compose exec app php artisan db:seed
fi

# Set permissions
echo ""
echo "🔒 Setting permissions..."
docker-compose exec app chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
docker-compose exec app chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

echo ""
echo "✅ Setup complete!"
echo ""
echo "📍 Your application is running at:"
echo "   http://localhost:8000"
echo ""
echo "🔧 Useful commands:"
echo "   docker-compose logs -f          # View logs"
echo "   docker-compose exec app bash    # Access container"
echo "   docker-compose down             # Stop containers"
echo "   docker-compose down -v          # Stop and remove volumes"
echo ""
