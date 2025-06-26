# Health Checks

This application implements comprehensive health checks following Docker and Kubernetes best practices. The health check system provides different levels of monitoring to ensure reliable container orchestration and service monitoring.

## Overview

The health check system consists of:

1. **Artisan Command** (`health:check`) - CLI health checks with multiple modes
2. **HTTP Endpoints** - RESTful health check endpoints for container orchestration
3. **Docker Integration** - Native Docker health check support
4. **Kubernetes Probes** - Startup, liveness, and readiness probe support

## Health Check Types

### Liveness Probe (`/livez`)
**Purpose**: Determines if the application should be restarted  
**Checks**: Minimal application functionality only  
**Behavior**: Only fails if the application itself is broken

```bash
# CLI
php artisan health:check --type=liveness

# HTTP
curl http://localhost:8000/livez
```

### Readiness Probe (`/readyz`)
**Purpose**: Determines if the application is ready to serve traffic  
**Checks**: Application + core dependencies (database, cache, storage)  
**Behavior**: Can fail when dependencies are temporarily unavailable

```bash
# CLI
php artisan health:check --type=readiness

# HTTP
curl http://localhost:8000/readyz
```

### Startup Probe (`/startup`)
**Purpose**: Checks if the application has finished starting up  
**Checks**: Same as readiness probe  
**Behavior**: Used during container startup phase

```bash
# HTTP
curl http://localhost:8000/startup
```

### Basic Health Check (`/healthz`)
**Purpose**: General health monitoring  
**Checks**: Application + database + cache  
**Behavior**: Lightweight monitoring for general health

```bash
# CLI (default)
php artisan health:check

# HTTP
curl http://localhost:8000/healthz
```

### Full Health Check
**Purpose**: Comprehensive monitoring including optional services  
**Checks**: All services including Redis, queues, external services  
**Behavior**: For detailed monitoring and debugging

```bash
# CLI
php artisan health:check --type=full

# HTTP
curl http://localhost:8000/healthz?type=full
```

## Artisan Command Usage

### Basic Usage
```bash
php artisan health:check
```

### Options
```bash
# Specify check type
php artisan health:check --type=liveness
php artisan health:check --type=readiness
php artisan health:check --type=basic
php artisan health:check --type=full

# JSON output
php artisan health:check --json

# Custom timeout
php artisan health:check --timeout=30
```

### Exit Codes
- `0`: All checks passed (healthy)
- `1`: One or more checks failed (unhealthy)

## HTTP Endpoints

| Endpoint | Purpose | Kubernetes Probe |
|----------|---------|------------------|
| `/livez` | Liveness check | livenessProbe |
| `/readyz` | Readiness check | readinessProbe |
| `/startup` | Startup check | startupProbe |
| `/healthz` | General health | Custom monitoring |
| `/up` | Laravel default | Basic check |

### Response Format
```json
{
  "status": "healthy",
  "timestamp": "2024-01-15T10:30:00Z",
  "type": "readiness",
  "checks": [
    {
      "service": "Application",
      "status": "success",
      "message": "Laravel application running",
      "response_time": 1.5,
      "timestamp": "2024-01-15T10:30:00Z"
    }
  ],
  "summary": {
    "success": 4,
    "warning": 1,
    "error": 0
  }
}
```

### HTTP Status Codes
- `200`: Healthy
- `503`: Unhealthy (Service Unavailable)

## Docker Integration

### Health Check Script
The application includes a Docker health check script at `docker/health-check.sh`:

```dockerfile
HEALTHCHECK --interval=10s --timeout=3s --start-period=10s --retries=3 \
    CMD /usr/local/bin/health-check || exit 1
```

### Environment Variables
```bash
# Health check endpoint (default: http://localhost:8000/livez)
HEALTH_ENDPOINT=http://localhost:8000/livez

# Health check timeout (default: 5 seconds)
HEALTH_TIMEOUT=5
```

## Kubernetes Configuration

### Recommended Probe Configuration
```yaml
startupProbe:
  httpGet:
    path: /startup
    port: 8000
  initialDelaySeconds: 10
  periodSeconds: 10
  timeoutSeconds: 5
  failureThreshold: 30  # 5 minutes max startup time

livenessProbe:
  httpGet:
    path: /livez
    port: 8000
  initialDelaySeconds: 30
  periodSeconds: 30
  timeoutSeconds: 5
  failureThreshold: 3

readinessProbe:
  httpGet:
    path: /readyz
    port: 8000
  initialDelaySeconds: 5
  periodSeconds: 10
  timeoutSeconds: 5
  failureThreshold: 3
```

## Service Checks

### Application
- ✅ Laravel configuration loaded
- ✅ Framework bootstrap successful

### Database
- ✅ Connection test (`SELECT 1`)
- ✅ Migration status (full mode only)
- ✅ Response time measurement

### Cache
- ✅ Read/write test with unique keys
- ✅ Automatic cleanup of test data

### Redis (Optional)
- ⚠️ Connection test (warning only if fails)
- ✅ Memory usage reporting (full mode)

### Storage
- ✅ File system read/write test
- ✅ Disk usage monitoring (full mode)
- ✅ Automatic cleanup of test files

### Queue System
- ✅ Stuck job detection
- ✅ Failed job count monitoring
- ⚠️ Non-fatal failures

### External Services
- ✅ Mail server connectivity
- ⚠️ Graceful handling of unavailable services

## Best Practices

### 1. Separation of Concerns
- **Liveness**: Only check if the app itself is working
- **Readiness**: Check app + required dependencies
- **Monitoring**: Check everything including optional services

### 2. Timeout Management
- Set appropriate timeouts for each check type
- Use shorter timeouts for liveness checks
- Allow longer timeouts for dependency checks

### 3. Error Handling
- Make optional services non-fatal (Redis, external APIs)
- Use warning status for degraded but functional states
- Fail fast for critical dependencies

### 4. Resource Efficiency
- Keep liveness checks lightweight
- Avoid expensive operations in frequently-called probes
- Use unique test data to prevent conflicts

### 5. Monitoring Integration
- Use different endpoints for different purposes
- Provide detailed JSON responses for monitoring systems
- Include response time metrics for performance monitoring

## Troubleshooting

### Common Issues

1. **Health checks timing out**
   - Increase timeout values
   - Check database connection pool settings
   - Monitor system resources

2. **Frequent container restarts**
   - Review liveness probe configuration
   - Ensure liveness checks aren't too strict
   - Check for dependency issues in liveness checks

3. **Service marked as not ready**
   - Check readiness probe logs
   - Verify database connectivity
   - Check cache configuration

### Debugging Commands

```bash
# Check specific service
php artisan health:check --type=full --json | jq '.checks[] | select(.service=="Database")'

# Monitor health check performance
while true; do
  time curl -s http://localhost:8000/readyz > /dev/null
  sleep 1
done

# View detailed error information
php artisan health:check --type=full
```

## Configuration

The health check system can be configured via environment variables:

```env
# Database connection timeout
DB_TIMEOUT=10

# Cache timeout
CACHE_TIMEOUT=5

# Redis connection timeout  
REDIS_TIMEOUT=3

# Health check intervals (for Docker)
HEALTH_ENDPOINT=http://localhost:8000/livez
HEALTH_TIMEOUT=5
```

For more advanced configuration, modify the `HealthCheck` command class or create custom health check classes. 