#!/usr/bin/env bash
#
# Host-side helper.  Usage:
#   ./scripts/setup-pandora.sh <pandora_enabled true|false> <proxy>
set -euo pipefail

PANDORA_ENABLED="${1:-false}"
PROXY="${2:-}"

export http_proxy="$PROXY" https_proxy="$PROXY" HTTP_PROXY="$PROXY" HTTPS_PROXY="$PROXY"

if [[ "$PANDORA_ENABLED" != "true" ]]; then
  echo "→ Pandora disabled – nothing to do."
  exit 0
fi

echo "→ Cloning/updating Pandora…"
if [ ! -d /opt/pandora/.git ]; then
  git clone --depth 1 https://github.com/pandora-analysis/pandora.git /opt/pandora
else
  git -C /opt/pandora pull --ff-only
fi

echo "→ Writing compose override…"
/usr/bin/env cat >/opt/pandora/docker-compose.override.yml <<'EOF'
version: "3.9"
services:
  pandora:
    ports: []        # remove upstream 6100:6100
    expose: ["6100"]
    networks: [app_network]
  redis:
    networks: [app_network]
  kvrocks:
    networks: [app_network]
  clamav:
    networks: [app_network]

networks:
  app_network:
    external: true
EOF

echo "→ Creating network (if absent)…"
docker network inspect app_network >/dev/null 2>&1 || docker network create app_network

echo "→ Starting Pandora stack…"
docker compose \
  --profile pandora \
  -f /opt/pandora/docker-compose.yml \
  -f /opt/pandora/docker-compose.override.yml \
  up -d --pull always --quiet-pull
