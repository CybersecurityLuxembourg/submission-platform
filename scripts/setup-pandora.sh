#!/usr/bin/env bash
#
# Host-side helper:  setup-pandora.sh <enabled true|false> <proxy>
set -euo pipefail

PANDORA_ENABLED="${1:-false}"
PROXY="${2:-}"

export http_proxy="$PROXY" https_proxy="$PROXY" HTTP_PROXY="$PROXY" HTTPS_PROXY="$PROXY"

if [[ "$PANDORA_ENABLED" != "true" ]]; then
  echo "→ Pandora disabled – skipping install."
  exit 0
fi

# ────────────────────────────────────────────────────────────
PANDORA_DIR="/var/www/test.applications.nc3.lu/pandora"
# ────────────────────────────────────────────────────────────

echo "→ Cloning / updating Pandora repo…"
if [ ! -d "$PANDORA_DIR/.git" ]; then
  git clone --depth 1 https://github.com/pandora-analysis/pandora.git "$PANDORA_DIR"
else
  git -C "$PANDORA_DIR" pull --ff-only
fi

echo "→ Writing compose override…"
/usr/bin/env cat >"$PANDORA_DIR/docker-compose.override.yml" <<'EOF'
version: "3.9"
services:
  pandora:
    ports: []                  # remove public 6100:6100
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

echo "→ Ensuring common network…"
docker network inspect app_network >/dev/null 2>&1 || docker network create app_network

echo "→ Starting Pandora stack…"
docker compose \
  --profile pandora \
  -f "$PANDORA_DIR/docker-compose.yml" \
  -f "$PANDORA_DIR/docker-compose.override.yml" \
  up -d --pull always --quiet-pull
