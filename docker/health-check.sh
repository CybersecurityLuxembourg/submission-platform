#!/bin/sh

# Health check script for Laravel application
# Following Docker and Kubernetes best practices

set -e

# Configuration
HEALTH_ENDPOINT="${HEALTH_ENDPOINT:-http://localhost:8000/livez}"
TIMEOUT="${HEALTH_TIMEOUT:-5}"
USER_AGENT="Docker-Health-Check/1.0"

# Function to check if the service is healthy
check_health() {
    # Use curl with timeout and proper error handling
    curl --fail \
         --silent \
         --show-error \
         --max-time "$TIMEOUT" \
         --user-agent "$USER_AGENT" \
         --header "Accept: application/json" \
         "$HEALTH_ENDPOINT" > /dev/null 2>&1
}

# Main health check logic
main() {
    # Check if curl is available
    if ! command -v curl >/dev/null 2>&1; then
        echo "ERROR: curl not found. Please install curl for health checks."
        exit 1
    fi

    # Attempt health check
    if check_health; then
        echo "Health check passed"
        exit 0
    else
        echo "Health check failed"
        exit 1
    fi
}

# Run the health check
main "$@" 