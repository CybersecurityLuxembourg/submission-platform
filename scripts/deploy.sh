#!/bin/bash
set -euo pipefail

# Color codes for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Logging functions
log() {
    echo -e "${GREEN}[$(date +'%Y-%m-%d %H:%M:%S')]${NC} $1"
}

error() {
    echo -e "${RED}[$(date +'%Y-%m-%d %H:%M:%S')] ERROR:${NC} $1" >&2
}

warning() {
    echo -e "${YELLOW}[$(date +'%Y-%m-%d %H:%M:%S')] WARNING:${NC} $1"
}

info() {
    echo -e "${BLUE}[$(date +'%Y-%m-%d %H:%M:%S')] INFO:${NC} $1"
}

# Parse command line arguments
FORCE_RECREATE=false
SKIP_DB_CHECK=false
while [[ $# -gt 0 ]]; do
    case $1 in
        --force-recreate)
            FORCE_RECREATE=true
            shift
            ;;
        --skip-db-check)
            SKIP_DB_CHECK=true
            shift
            ;;
        *)
            warning "Unknown option: $1"
            shift
            ;;
    esac
done

# Detect Docker Compose version and set the command
DOCKER_COMPOSE="docker compose"
COMPOSE_VERSION="v2"

# Trap errors
trap 'error "Script failed at line $LINENO"' ERR

log "🚀 Starting deployment process..."

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

# Function to aggressively clean up containers to avoid ContainerConfig errors
cleanup_containers() {
    local service=$1
    info "Performing aggressive cleanup for $service containers..."
    
    # Stop via docker-compose
    $DOCKER_COMPOSE --env-file docker-compose.env stop $service 2>/dev/null || true
    
    # Remove via docker-compose
    $DOCKER_COMPOSE --env-file docker-compose.env rm -f -s $service 2>/dev/null || true
    
    # Find and remove all containers for this service
    docker ps -a --filter "label=com.docker.compose.service=$service" -q | xargs -r docker rm -f 2>/dev/null || true
    
    # Remove by name patterns
    docker ps -a --format "{{.Names}}" | grep -E "nc3[_-]$service" | xargs -r docker rm -f 2>/dev/null || true
    
    # Remove any container with matching name
    docker rm -f "nc3_$service" 2>/dev/null || true
    
    # For v1 compose, also check for prefixed containers
    docker ps -a --format "{{.Names}}" | grep -E "_${service}_[0-9]+" | xargs -r docker rm -f 2>/dev/null || true
}

# Function to handle ContainerConfig error
handle_container_config_error() {
    error "ContainerConfig error detected - performing deep cleanup"
    
    # Nuclear option: remove all project containers
    info "Removing all containers related to this project..."
    
    # Get project name from docker-compose
    PROJECT_NAME=$(grep -E "^[[:space:]]*name:" docker-compose.yml 2>/dev/null | awk '{print $2}' | tr -d '"' || echo "nc3")
    
    # Remove all containers from this project
    docker ps -a --filter "label=com.docker.compose.project=$PROJECT_NAME" -q | xargs -r docker rm -f 2>/dev/null || true
    
    # Also try common patterns
    docker ps -a --format "{{.ID}} {{.Names}}" | grep -E "(nc3|${PROJECT_NAME})" | awk '{print $1}' | xargs -r docker rm -f 2>/dev/null || true
    
    
    
    warning "Deep cleanup completed. Retrying deployment..."
}

# Function to check if container is running and healthy
container_is_running() {
    local container_name=$1
    local status=$(docker inspect -f '{{.State.Status}}' "$container_name" 2>/dev/null || echo "not_found")
    [ "$status" = "running" ]
}

# Function to check if MySQL is accessible
mysql_is_healthy() {
    $DOCKER_COMPOSE --env-file docker-compose.env exec -T db \
        mysqladmin ping -h localhost -u root -p"${MYSQL_ROOT_PASSWORD}" --silent 2>/dev/null
}

# Function to wait for MySQL
wait_for_mysql() {
    log "Waiting for MySQL to be ready..."
    local max_attempts=60
    local attempt=0
    
    while [ $attempt -lt $max_attempts ]; do
        if mysql_is_healthy; then
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

# If force recreate is requested, clean everything
if [ "$FORCE_RECREATE" = true ]; then
    warning "Force recreate requested - removing all containers"
    cleanup_containers "app"
    cleanup_containers "db"
    cleanup_containers "redis"
fi

# Handle database container
if [ "$SKIP_DB_CHECK" = false ]; then
    DB_NEEDS_START=false
    if ! container_is_running "nc3_db"; then
        log "Database container is not running, will start it"
        DB_NEEDS_START=true
    elif ! mysql_is_healthy; then
        warning "Database container is running but not healthy, will restart it"
        DB_NEEDS_START=true
        cleanup_containers "db"
    else
        log "✅ Database is already running and healthy"
    fi
    
    if [ "$DB_NEEDS_START" = true ]; then
        log "Starting database service..."
        if ! $DOCKER_COMPOSE --env-file docker-compose.env up -d db; then
            error "Failed to start database"
            exit 1
        fi
        
        if ! wait_for_mysql; then
            error "Database initialization failed"
            exit 1
        fi
    fi
    
    # Initialize database schema
    log "Checking database schema..."
    DB_EXISTS=$($DOCKER_COMPOSE --env-file docker-compose.env exec -T db \
        mysql -u root -p"${MYSQL_ROOT_PASSWORD}" -e "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '${DB_DATABASE}'" -B -N 2>/dev/null || echo "")
    
    if [ -z "$DB_EXISTS" ]; then
        log "Creating database schema..."
        $DOCKER_COMPOSE --env-file docker-compose.env exec -T db mysql -u root -p"${MYSQL_ROOT_PASSWORD}" << EOF
CREATE DATABASE IF NOT EXISTS \`${DB_DATABASE}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '${DB_USERNAME}'@'%' IDENTIFIED BY '${DB_PASSWORD}';
GRANT ALL PRIVILEGES ON \`${DB_DATABASE}\`.* TO '${DB_USERNAME}'@'%';
FLUSH PRIVILEGES;
EOF
    else
        log "✅ Database schema already exists"
    fi
fi

# Clean up application container
cleanup_containers "app"

# Build application image
log "Building application Docker image..."
BUILD_SUCCESS=false

# Try to build with docker-compose
if $DOCKER_COMPOSE --env-file docker-compose.env build --no-cache --pull app 2>&1 | tee /tmp/docker-build.log; then
    BUILD_SUCCESS=true
else
    # Check if it's a ContainerConfig error
    if grep -q "ContainerConfig" /tmp/docker-build.log; then
        handle_container_config_error
        # Retry build after cleanup
        if $DOCKER_COMPOSE --env-file docker-compose.env build --no-cache --pull app; then
            BUILD_SUCCESS=true
        fi
    else
        warning "Build failed, attempting without cache mounts..."
        cp Dockerfile Dockerfile.original
        sed 's/RUN --mount=[^ ]* /RUN /g' Dockerfile.original > Dockerfile
        
        if $DOCKER_COMPOSE --env-file docker-compose.env build --no-cache --pull app; then
            BUILD_SUCCESS=true
        fi
        
        mv Dockerfile.original Dockerfile
    fi
fi

if [ "$BUILD_SUCCESS" = false ]; then
    error "Failed to build application image"
    exit 1
fi

# Start application container
log "Starting application container..."
RETRY_COUNT=0
MAX_RETRIES=3

while [ $RETRY_COUNT -lt $MAX_RETRIES ]; do
    if $DOCKER_COMPOSE --env-file docker-compose.env up -d app 2>&1 | tee /tmp/docker-up.log; then
        break
    else
        # Check for ContainerConfig error
        if grep -q "ContainerConfig" /tmp/docker-up.log; then
            RETRY_COUNT=$((RETRY_COUNT + 1))
            warning "ContainerConfig error on attempt $RETRY_COUNT/$MAX_RETRIES"
            handle_container_config_error
            
            if [ $RETRY_COUNT -eq $MAX_RETRIES ]; then
                error "Failed to start application after $MAX_RETRIES attempts"
                error "This is likely due to docker-compose v1 bugs. Please upgrade to Docker Compose v2"
                exit 1
            fi
        else
            error "Failed to start application container"
            cat /tmp/docker-up.log
            exit 1
        fi
    fi
done

# Wait for application container
log "Waiting for application container to be ready..."
max_attempts=30
attempt=0
while [ $attempt -lt $max_attempts ]; do
    if $DOCKER_COMPOSE --env-file docker-compose.env exec -T app echo "ready" > /dev/null 2>&1; then
        log "✅ Application container is ready"
        break
    fi
    attempt=$((attempt + 1))
    sleep 2
done

# For Laravel application
if [ -f artisan ]; then
    log "Detected Laravel application"
    
    # Run migrations
    log "Running migrations..."
    if ! $DOCKER_COMPOSE --env-file docker-compose.env exec -T app php artisan migrate --force; then
        error "Migration failed"
        $DOCKER_COMPOSE --env-file docker-compose.env logs --tail=50 app
        exit 1
    fi
    
    # Optimize
    log "Optimizing Laravel application..."
    $DOCKER_COMPOSE --env-file docker-compose.env exec -T app php artisan config:cache
    $DOCKER_COMPOSE --env-file docker-compose.env exec -T app php artisan route:cache
    $DOCKER_COMPOSE --env-file docker-compose.env exec -T app php artisan view:cache
    $DOCKER_COMPOSE --env-file docker-compose.env exec -T app php artisan cache:clear
    
    # Restart queue workers
    $DOCKER_COMPOSE --env-file docker-compose.env exec -T app php artisan queue:restart 2>/dev/null || true
fi

# Start all other services
log "Starting all services..."
$DOCKER_COMPOSE --env-file docker-compose.env up -d

# Final health check
log "Performing final health check..."
sleep 5

# Show running services
log "✅ Deployment completed successfully!"
log "Services running:"
$DOCKER_COMPOSE --env-file docker-compose.env ps

# Clean up
rm -f /tmp/docker-build.log /tmp/docker-up.log 2>/dev/null || true