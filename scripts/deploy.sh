#!/bin/bash
set -e

echo "🚀 Starting deployment process..."
if ! docker-compose ps | grep -q 'app'; then
    echo "App container is not running. Starting containers..."
    docker-compose up -d
fi
# Install/update PHP dependencies
docker-compose exec -T app composer install --optimize-autoloader --no-dev

# Clear and cache Laravel config
docker-compose exec -T app php artisan config:clear
docker-compose exec -T app php artisan config:cache

# Run migrations
docker-compose exec -T app php artisan migrate --force

# Cache routes and views
docker-compose exec -T app php artisan route:cache
docker-compose exec -T app php artisan view:cache

# Optimize Laravel
docker-compose exec -T app php artisan optimize

echo "✅ Deployment completed successfully!"
