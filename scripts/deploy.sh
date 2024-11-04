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
# Start Docker services
echo "Starting Docker services..."
docker-compose --env-file docker-compose.env up -d

wait_for_mysql() {
    echo "Waiting for MySQL to be ready..."
    max_attempts=30
    counter=0

    # Cleanup any existing mysqladmin processes first
    if pgrep mysqladmin >/dev/null; then
        echo "Cleaning up existing mysqladmin processes..."
        sudo killall -9 mysqladmin || true
    fi

    while [ $counter -lt $max_attempts ]; do
        # Use timeout to prevent hanging
        if timeout 10s docker-compose exec -T db bash -c '
            mysqladmin ping -h localhost \
            -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" \
            --connect_timeout=5 --silent' &>/dev/null
        then
            echo "✅ MySQL is ready!"
            return 0
        fi

        counter=$((counter+1))
        echo "Attempt $counter/$max_attempts: MySQL not ready yet..."

        if [ $counter -eq $max_attempts ]; then
            echo "❌ MySQL failed to become ready. Checking container status..."
            docker-compose logs db
            return 1
        fi

        sleep 5
    done

    return 1
}

# Add this function to check MySQL connection more safely
check_mysql_connection() {
    timeout 10s docker-compose exec -T db bash -c \
        'mysql -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" \
        -e "SELECT 1;" >/dev/null 2>&1'
    return $?
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


## Initialize database
#echo "Setting up database..."
#if ! initialize_database; then
#    echo "❌ Failed to initialize database"
#    echo "MySQL logs:"
#    docker-compose logs db
#    exit 1
#fi
#
## Verify database connection using non-root user
#echo "Verifying database connection..."
#if ! docker-compose exec db sh -c 'mysql -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" -e "SELECT 1" "$MYSQL_DATABASE"' &> /dev/null; then
#    echo "❌ Failed to connect to database with application user"
#    exit 1
#fi

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
