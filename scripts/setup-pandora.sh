#!/usr/bin/env bash
#
# setup-pandora.sh <enabled true|false> [proxy]
#
# Starts the Pandora overlay if the first argument is "true".
# Works with either:
#   • docker-compose  (v1 or v2 wrapper)
#   • docker compose  (v2 plugin)
# by relying on the COMPOSE_FILE env-var instead of -f flags.
# --------------------------------------------------------------------

set -euo pipefail

PANDORA_ENABLED="${1:-false}"
PROXY="${2:-${PROXY:-}}"
# Derive project root from this script location (one directory up)
PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

if [[ "$PANDORA_ENABLED" != "true" ]]; then
  echo "→ Pandora disabled – skipping install."
  exit 0
fi

echo "→ Ensuring Docker network…"
docker network inspect app_network >/dev/null 2>&1 \
  || docker network create app_network

# Export proxy env for compose/docker if provided
if [[ -n "$PROXY" ]]; then
  export HTTP_PROXY="$PROXY"
  export HTTPS_PROXY="$PROXY"
  export http_proxy="$PROXY"
  export https_proxy="$PROXY"
fi

# --------------------------------------------------------------------
# Compose files: base stack + Pandora overlay (separated by “:” on Linux/macOS)
export COMPOSE_FILE="$PROJECT_ROOT/docker-compose.yml:$PROJECT_ROOT/docker/pandora/pandora.yml"
# --------------------------------------------------------------------

# Choose whichever compose command exists
if command -v docker-compose >/dev/null 2>&1; then
  COMPOSE='docker-compose'          # v1 or v2 wrapper binary
else
  COMPOSE='docker compose'          # v2 plugin syntax
fi

echo "→ Pulling images & starting Pandora stack…"
$COMPOSE up -d --pull always        # identical for both v1 & v2
