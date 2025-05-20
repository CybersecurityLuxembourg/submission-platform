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

# Build Docker images
echo "Building Docker images..."
docker-compose --env-file docker-compose.env build

# Start Docker services
echo "Starting Docker services..."
docker-compose --env-file docker-compose.env up -d

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

# Function to wait for MySQL to be ready
wait_for_mysql() {
    echo "Waiting for MySQL to be ready..."
    # Simple cleanup of existing mysqladmin processes
    killall mysqladmin 2>/dev/null || true
    max_attempts=30
    counter=0

    while [ $counter -lt $max_attempts ]; do
        if docker-compose exec db sh -c 'mysqladmin ping -h localhost -u root -p"$MYSQL_ROOT_PASSWORD"' &> /dev/null; then
            echo "✅ MySQL is ready!"
            return 0
        fi
        counter=$((counter+1))
        echo "Attempt $counter/$max_attempts: MySQL not ready yet..."
        sleep 5
    done

    echo "❌ MySQL failed to become ready"
    return 1
}

# Function to initialize database
initialize_database() {
    echo "Initializing database..."
    docker-compose exec -T db sh -c 'mysql -p"$MYSQL_ROOT_PASSWORD"' << EOF
CREATE DATABASE IF NOT EXISTS \`$DB_DATABASE\`;
CREATE USER IF NOT EXISTS '$DB_USERNAME'@'%' IDENTIFIED BY '$DB_PASSWORD';
GRANT ALL PRIVILEGES ON \`$DB_DATABASE\`.* TO '$DB_USERNAME'@'%';
FLUSH PRIVILEGES;
EOF
}

# Check MySQL container logs
echo "Checking MySQL container logs..."
docker-compose logs db

# Wait for MySQL to be ready
if ! wait_for_mysql; then
    echo "❌ MySQL failed to become ready"
    docker-compose logs db
    exit 1
fi

# Initialize database
echo "Setting up database..."
if ! initialize_database; then
    echo "❌ Failed to initialize database"
    echo "MySQL logs:"
    docker-compose logs db
    exit 1
fi

echo "✅ Database setup completed successfully!"

# Continue with deployment
echo "Running database migrations..."

if ! docker-compose exec -T app php artisan migrate --force; then
    echo "❌ Migration failed. Checking Laravel logs..."
    docker-compose exec -T app cat storage/logs/laravel.log
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
