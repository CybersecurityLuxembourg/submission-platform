#!/bin/bash
set -e

echo "🚀 Starting deployment process..."

# Source the environment variables from docker-compose.env
if [ -f docker-compose.env ]; then
    echo "Loading environment variables from docker-compose.env..."
    set -a  # automatically export all variables
    source docker-compose.env
    set +a
    echo "✅ Environment variables loaded"
else
    echo "❌ docker-compose.env file not found"
    exit 1
fi

# Verify required variables are loaded
required_vars=("DB_DATABASE" "DB_USERNAME" "DB_PASSWORD" "MYSQL_ROOT_PASSWORD")
for var in "${required_vars[@]}"; do
    if [ -z "${!var}" ]; then
        echo "❌ Required variable $var is not set"
        exit 1
    else
        echo "✅ Variable $var is set"
    fi
done

# Debug database connection info (without showing passwords)
echo "Database connection details:"
echo "Host: db"
echo "Database: ${DB_DATABASE}"
echo "Username: ${DB_USERNAME}"

# Build Docker images
echo "Building Docker images..."
docker-compose --env-file docker-compose.env build

# Start Docker services
echo "Starting Docker services..."
docker-compose --env-file docker-compose.env up -d

# Simple wait for containers to be ready
echo "Waiting for containers to be ready..."
sleep 10

# Check MySQL container logs
echo "Checking MySQL container logs..."
docker-compose logs db

echo "✅ Database setup completed successfully!"

# Continue with deployment
echo "Running database migrations..."

if ! docker-compose exec -T app php artisan migrate --force; then
    echo "❌ Migration failed. Checking Laravel logs..."
    docker-compose exec -T app cat storage/logs/laravel.log 2>/dev/null || echo "No Laravel logs available"
    exit 1
fi

echo "Clearing caches..."
docker-compose exec -T app php artisan config:clear
docker-compose exec -T app php artisan cache:clear
docker-compose exec -T app php artisan view:clear
docker-compose exec -T app php artisan route:clear

echo "Optimizing application..."
docker-compose exec -T app php artisan config:cache
docker-compose exec -T app php artisan route:cache
docker-compose exec -T app php artisan view:cache

echo "✅ Deployment completed successfully!"