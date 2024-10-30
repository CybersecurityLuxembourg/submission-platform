#!/bin/bash
set -e

echo "🚀 Starting deployment process..."

# Install/update PHP dependencies
docker-compose exec -T app composer install --optimize-autoloader --no-dev

# Install Node dependencies and build assets
docker-compose run --rm node npm ci
docker-compose run --rm node npm run build

# Clear and cache Laravel config
docker-compose exec -T app php artisan config:clear
docker-compose exec -T app php artisan config:cache

# Run migrations (if any)
docker-compose exec -T app php artisan migrate --force

# Cache routes and views
docker-compose exec -T app php artisan route:cache
docker-compose exec -T app php artisan view:cache

# Optimize Laravel
docker-compose exec -T app php artisan optimize

# Restart containers to apply changes
docker-compose up -d --build app

echo "✅ Deployment completed successfully!"
