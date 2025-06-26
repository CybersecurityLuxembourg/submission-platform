#!/bin/bash
set -e

# Color output
RED='\033[0;31m'
GREEN='\033[0;32m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

log() {
    echo -e "${BLUE}[$(date +'%Y-%m-%d %H:%M:%S')]${NC} $1"
}

error() {
    echo -e "${RED}[ERROR]${NC} $1" >&2
}

success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

# Deployment configuration
DEPLOYMENT_ID=$(date +%s)
HEALTH_CHECK_RETRIES=30
HEALTH_CHECK_INTERVAL=5

log "🚀 Starting deployment process (ID: $DEPLOYMENT_ID)..."

# Load environment variables
if [ -f docker-compose.env ]; then
    log "Loading environment variables..."
    set -a
    source docker-compose.env
    set +a
    success "Environment variables loaded"
else
    error "docker-compose.env file not found"
    exit 1
fi

# Verify required variables
required_vars=("DB_DATABASE" "DB_USERNAME" "DB_PASSWORD" "MYSQL_ROOT_PASSWORD")
for var in "${required_vars[@]}"; do
    if [ -z "${!var}" ]; then
        error "Required variable $var is not set"
        exit 1
    fi
done

# Function to check if a service is healthy
check_service_health() {
    local service=$1
    local max_attempts=$2
    local interval=$3
    local attempt=0
    
    log "Checking health of $service..."
    
    while [ $attempt -lt $max_attempts ]; do
        if docker-compose exec -T $service sh -c 'exit 0' &> /dev/null; then
            if [ "$service" = "db" ]; then
                if docker-compose exec -T db sh -c 'mysqladmin ping -h localhost -u root -p"$MYSQL_ROOT_PASSWORD"' &> /dev/null; then
                    success "$service is healthy!"
                    return 0
                fi
            elif [ "$service" = "app" ]; then
                if docker-compose exec -T app php artisan --version &> /dev/null; then
                    success "$service is healthy!"
                    return 0
                fi
            fi
        fi
        
        attempt=$((attempt+1))
        log "Attempt $attempt/$max_attempts: $service not ready yet..."
        sleep $interval
    done
    
    error "$service failed health check"
    return 1
}

# Function to run database migrations with rollback capability
run_migrations() {
    log "Running database migrations..."
    
    # First, check migration status
    if ! docker-compose exec -T app php artisan migrate:status; then
        error "Failed to check migration status"
        return 1
    fi
    
    # Run migrations with --step flag for easier rollback
    if ! docker-compose exec -T app php artisan migrate --force --step; then
        error "Migration failed"
        return 1
    fi
    
    success "Migrations completed successfully"
    return 0
}

# Function to perform health checks on the application
app_health_check() {
    log "Performing application health checks..."
    
    # Check Laravel configuration
    if ! docker-compose exec -T app php artisan config:cache; then
        error "Configuration cache failed"
        return 1
    fi
    
    # Check database connectivity
    if ! docker-compose exec -T app php artisan db:show; then
        error "Database connectivity check failed"
        return 1
    fi
    
    # Check if app can serve requests (you should add a /health endpoint)
    # if ! curl -f http://localhost:9000/health; then
    #     error "HTTP health check failed"
    #     return 1
    # fi
    
    success "All health checks passed"
    return 0
}

# Main deployment process
main() {
    # Build new images
    log "Building Docker images..."
    if ! docker-compose build --parallel; then
        error "Docker build failed"
        exit 1
    fi
    
    # Start database if not running
    if ! docker-compose ps | grep -q "nc3_db.*Up"; then
        log "Starting database service..."
        docker-compose up -d db
        check_service_health "db" $HEALTH_CHECK_RETRIES $HEALTH_CHECK_INTERVAL || exit 1
    fi
    
    # Initialize database if needed
    log "Checking database initialization..."
    if ! docker-compose exec -T db sh -c 'mysql -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" -e "SELECT 1" "$MYSQL_DATABASE"' &> /dev/null 2>&1; then
        log "Initializing database..."
        docker-compose exec -T db sh -c 'mysql -u root -p"$MYSQL_ROOT_PASSWORD"' << EOF
CREATE DATABASE IF NOT EXISTS \`$DB_DATABASE\`;
CREATE USER IF NOT EXISTS '$DB_USERNAME'@'%' IDENTIFIED BY '$DB_PASSWORD';
GRANT ALL PRIVILEGES ON \`$DB_DATABASE\`.* TO '$DB_USERNAME'@'%';
FLUSH PRIVILEGES;
EOF
        success "Database initialized"
    fi
    
    # Blue-Green deployment for app service
    log "Starting new application container..."
    
    # Scale up to 2 instances (old + new)
    docker-compose up -d --scale app=2 --no-recreate app
    
    # Wait for new container to be healthy
    sleep 10  # Give new container time to start
    check_service_health "app" $HEALTH_CHECK_RETRIES $HEALTH_CHECK_INTERVAL || {
        error "New application container failed to start"
        docker-compose up -d --scale app=1 app
        exit 1
    }
    
    # Run migrations on new container
    if ! run_migrations; then
        error "Migrations failed, rolling back..."
        docker-compose up -d --scale app=1 app
        exit 1
    fi
    
    # Perform health checks
    if ! app_health_check; then
        error "Health checks failed, rolling back..."
        docker-compose exec -T app php artisan migrate:rollback --force
        docker-compose up -d --scale app=1 app
        exit 1
    fi
    
    # Clear and warm up caches
    log "Optimizing application..."
    docker-compose exec -T app php artisan config:cache
    docker-compose exec -T app php artisan route:cache
    docker-compose exec -T app php artisan view:cache
    docker-compose exec -T app php artisan event:cache
    
    # If using queue workers, restart them
    # docker-compose exec -T app php artisan queue:restart
    
    # Remove old container
    log "Removing old application container..."
    docker-compose up -d --scale app=1 --no-recreate app
    
    # Clean up unused images
    docker image prune -f
    
    # Log deployment completion
    success "Deployment $DEPLOYMENT_ID completed successfully!"
    
    # Send notification (example: Slack, email, etc.)
    # notify_deployment_success $DEPLOYMENT_ID
}

# Run main deployment
main