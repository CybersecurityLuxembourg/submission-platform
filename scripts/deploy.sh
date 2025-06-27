#!/bin/bash
set -euo pipefail

# Color codes for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Logging function
log() {
    echo -e "${GREEN}[$(date +'%Y-%m-%d %H:%M:%S')]${NC} $1"
}

error() {
    echo -e "${RED}[$(date +'%Y-%m-%d %H:%M:%S')] ERROR:${NC} $1" >&2
}

warning() {
    echo -e "${YELLOW}[$(date +'%Y-%m-%d %H:%M:%S')] WARNING:${NC} $1"
}

# Detect Docker Compose version and set the command
if command -v docker compose &> /dev/null; then
    DOCKER_COMPOSE="docker compose"
    log "Using Docker Compose v2"
elif command -v docker-compose &> /dev/null; then
    DOCKER_COMPOSE="docker-compose"
    log "Using Docker Compose v1"
else
    error "Docker Compose is not installed"
    exit 1
fi

# Trap errors
trap 'error "Script failed at line $LINENO"' ERR

log "🚀 Starting enhanced deployment process..."

# Enable BuildKit
export DOCKER_BUILDKIT=1
export COMPOSE_DOCKER_CLI_BUILD=1

# Verify environment files exist
if [ ! -f docker-compose.env ]; then
    error "docker-compose.env file not found"
    exit 1
fi

if [ ! -f .env ]; then
    error ".env file not found"
    exit 1
fi

# Load and verify environment variables
log "Loading environment variables..."
set -a
source docker-compose.env
set +a

# Verify required variables
required_vars=("DB_DATABASE" "DB_USERNAME" "DB_PASSWORD" "MYSQL_ROOT_PASSWORD")
for var in "${required_vars[@]}"; do
    if [ -z "${!var:-}" ]; then
        error "Required variable $var is not set"
        exit 1
    fi
done
log "✅ All required environment variables are set"

# Docker health check
if ! docker info > /dev/null 2>&1; then
    error "Docker daemon is not running"
    exit 1
fi

# Stop existing containers gracefully
log "Stopping existing containers..."
$DOCKER_COMPOSE --env-file docker-compose.env down --timeout 30 || true

# Clean up containers and volumes to avoid ContainerConfig errors
log "Cleaning up old containers and orphaned volumes..."
docker rm -f nc3_app nc3_db 2>/dev/null || true
docker volume prune -f 2>/dev/null || true
docker image prune -f --filter "label=project=nc3" 2>/dev/null || true

# Remove any problematic container metadata
docker system prune -f --volumes 2>/dev/null || true

# Build with BuildKit
log "Building Docker images with BuildKit..."
if ! DOCKER_BUILDKIT=1 $DOCKER_COMPOSE --env-file docker-compose.env build --no-cache --pull; then
    error "Docker build failed"
    
    # Fallback: Try building without mount cache
    warning "Attempting build without cache mounts..."
    
    # Create a modified Dockerfile without mount options
    sed 's/RUN --mount=[^ ]* /RUN /g' Dockerfile > Dockerfile.no-cache
    mv Dockerfile Dockerfile.original
    mv Dockerfile.no-cache Dockerfile
    
    if ! $DOCKER_COMPOSE --env-file docker-compose.env build --no-cache --pull; then
        mv Dockerfile.original Dockerfile
        error "Build failed even without cache mounts"
        exit 1
    fi
    
    mv Dockerfile.original Dockerfile
fi

# Start services (without --no-recreate to avoid ContainerConfig issues)
log "Starting Docker services..."
$DOCKER_COMPOSE --env-file docker-compose.env up -d db

# Wait for database to be ready before starting app
wait_for_mysql() {
    log "Waiting for MySQL to be ready..."
    local max_attempts=60
    local attempt=0
    
    while [ $attempt -lt $max_attempts ]; do
        if $DOCKER_COMPOSE --env-file docker-compose.env exec -T db \
            mysqladmin ping -h localhost -u root -p"${MYSQL_ROOT_PASSWORD}" --silent 2>/dev/null; then
            log "✅ MySQL is ready!"
            return 0
        fi
        
        attempt=$((attempt + 1))
        if [ $((attempt % 10)) -eq 0 ]; then
            log "Still waiting for MySQL... (${attempt}/${max_attempts})"
        fi
        sleep 2
    done
    
    error "MySQL failed to become ready after ${max_attempts} attempts"
    $DOCKER_COMPOSE --env-file docker-compose.env logs --tail=50 db
    return 1
}

# Wait for database
if ! wait_for_mysql; then
    error "Database initialization failed"
    exit 1
fi

# Initialize database schema
log "Initializing database schema..."
$DOCKER_COMPOSE --env-file docker-compose.env exec -T db mysql -u root -p"${MYSQL_ROOT_PASSWORD}" << EOF
CREATE DATABASE IF NOT EXISTS \`${DB_DATABASE}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '${DB_USERNAME}'@'%' IDENTIFIED BY '${DB_PASSWORD}';
GRANT ALL PRIVILEGES ON \`${DB_DATABASE}\`.* TO '${DB_USERNAME}'@'%';
FLUSH PRIVILEGES;
EOF

# Verify database connection
log "Verifying database connection..."
if ! $DOCKER_COMPOSE --env-file docker-compose.env exec -T db \
    mysql -u"${DB_USERNAME}" -p"${DB_PASSWORD}" -e "SELECT 1" "${DB_DATABASE}" > /dev/null 2>&1; then
    error "Failed to connect to database with application user"
    exit 1
fi

# Now start the app container
log "Starting application container..."
$DOCKER_COMPOSE --env-file docker-compose.env up -d app

# Wait for application container to be ready
log "Waiting for application container..."
max_attempts=30
attempt=0
while [ $attempt -lt $max_attempts ]; do
    if $DOCKER_COMPOSE --env-file docker-compose.env exec -T app echo "ready" > /dev/null 2>&1; then
        break
    fi
    attempt=$((attempt + 1))
    sleep 2
done

# For Laravel application
if [ -f artisan ]; then
    log "Running Laravel migrations..."
    if ! $DOCKER_COMPOSE --env-file docker-compose.env exec -T app php artisan migrate --force; then
        error "Migration failed"
        $DOCKER_COMPOSE --env-file docker-compose.env logs --tail=50 app
        exit 1
    fi
    
    log "Optimizing Laravel application..."
    $DOCKER_COMPOSE --env-file docker-compose.env exec -T app php artisan config:cache
    $DOCKER_COMPOSE --env-file docker-compose.env exec -T app php artisan route:cache
    $DOCKER_COMPOSE --env-file docker-compose.env exec -T app php artisan view:cache
fi

# Health check
log "Performing health check..."
if $DOCKER_COMPOSE --env-file docker-compose.env ps | grep -E "(Exit|unhealthy)"; then
    error "Some containers are not healthy"
    $DOCKER_COMPOSE --env-file docker-compose.env ps
    $DOCKER_COMPOSE --env-file docker-compose.env logs --tail=50
    exit 1
fi

log "✅ Deployment completed successfully!"
log "Services running:"
$DOCKER_COMPOSE --env-file docker-compose.env ps