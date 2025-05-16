#!/usr/bin/env bash
set -euo pipefail
PANDORA_ENABLED="${1:-false}"
PROJECT_ROOT="/var/www/test.applications.nc3.lu"
if [[ "$PANDORA_ENABLED" != "true" ]]; then
  echo "→ Pandora disabled – skipping install."
  exit 0
fi

docker network inspect app_network >/dev/null 2>&1 || docker network create app_network

docker compose \
  -f "$PROJECT_ROOT/docker-compose.yml" \
  -f "$PROJECT_ROOT/docker/pandora/pandora.yml" \
  up -d --pull always --quiet-pull

  