#!/usr/bin/env bash
set -euo pipefail

DIR="$(dirname "$(dirname "$(readlink -f "$0")")")"
cd "${DIR}"

PROD=false
THEME="demo"
COMMAND=""
PORT=""
args=("$@")
for ((i=0; i<${#args[@]}; i++)); do
  if [[ "${args[$i]}" == "-h" || "${args[$i]}" == "--help" ]]; then
    echo "Usage: ./deploy.sh [COMMAND] [OPTIONS]"
    echo ""
    echo "Commands:"
    echo "  up              Start containers (no rebuild)"
    echo "  down            Stop containers and exit"
    echo "  restart         Stop and start containers (no rebuild)"
    echo "  (none)          Full deploy: stop, build, start, compile, rebuild content"
    echo ""
    echo "Options:"
    echo "  --bare          Seed bare wireframe content (no demo templates/assets)"
    echo "  --demo          Seed full demo content + templates + assets (default)"
    echo "  --prod          Build and run in production mode (APP_ENV=prod, no dev dependencies)"
    echo "  --port <port>   Override the nginx port (sets NGINX_PORT)"
    echo "  -h, --help      Show this help message"
    exit 0
  fi
  [[ "${args[$i]}" == "up" ]] && COMMAND="up"
  [[ "${args[$i]}" == "down" ]] && COMMAND="down"
  [[ "${args[$i]}" == "restart" ]] && COMMAND="restart"
  [[ "${args[$i]}" == "--prod" ]] && PROD=true
  [[ "${args[$i]}" == "--bare" ]] && THEME="bare"
  [[ "${args[$i]}" == "--demo" ]] && THEME="demo"
  if [[ "${args[$i]}" == "--port" ]]; then
    PORT="${args[$i+1]:-}"
  fi
done

COMPOSER_FLAGS="--optimize-autoloader --no-scripts --no-interaction"
if $PROD; then
  COMPOSER_FLAGS="--no-dev $COMPOSER_FLAGS"
fi

APP_ENV_FLAG=""
if $PROD; then
  APP_ENV_FLAG="-e APP_ENV=prod -e APP_DEBUG=false"
fi

if [[ -n "$PORT" ]]; then
  export NGINX_PORT="$PORT"
fi

ENV_LOCAL_FLAG=""
if [[ -f ".env.local" ]]; then
  ENV_LOCAL_FLAG="--env-file .env.local"
fi

COMPOSE_CMD="docker compose --env-file .env $ENV_LOCAL_FLAG"

RUNTIME_PROFILE=""
if [[ "${RUNTIME_PHP_ENABLED:-true}" == "true" ]]; then
    RUNTIME_PROFILE="--profile runtime"
fi

# --- down ---
if [[ "$COMMAND" == "down" ]]; then
  echo "==> Stopping containers"
  $COMPOSE_CMD down
  exit 0
fi

# --- up (no rebuild) ---
if [[ "$COMMAND" == "up" ]]; then
  echo "==> Starting services"
    $COMPOSE_CMD $RUNTIME_PROFILE up -d
  exit 0
fi

# --- restart (stop + start, no rebuild) ---
if [[ "$COMMAND" == "restart" ]]; then
  echo "==> Restarting containers"
  $COMPOSE_CMD down
  $COMPOSE_CMD $RUNTIME_PROFILE up -d
  exit 0
fi

# --- full deploy (default) ---

echo "==> Stopping containers"
$COMPOSE_CMD down

echo "==> Ensuring external networks exist"
docker network inspect web >/dev/null 2>&1 || docker network create web

# ── Helpers ──────────────────────────────────────────────────────────

dir_is_empty() {
    local dir="$1"
    [ -d "$dir" ] || return 0
    local non_gitkeep
    non_gitkeep="$(find "$dir" -type f ! -name '.gitkeep' 2>/dev/null)"
    [ -z "$non_gitkeep" ]
}

local_has_content() {
    local subdirs=(content translations assets docs src templates docker)
    for sub in "${subdirs[@]}"; do
        if ! dir_is_empty "local/$sub"; then
            return 0
        fi
    done
    return 1
}

# ── Seed logic ───────────────────────────────────────────────────────

SEED_SOURCE="docs/${THEME}"

if [ ! -d local ]; then
    echo "==> local/ does not exist — creating and seeding from ${SEED_SOURCE}/"
    mkdir -p local
    cp -r "${SEED_SOURCE}/." local/
elif local_has_content; then
    TIMESTAMP="$(date '+%Y-%m-%d-%H%M%S')"
    echo "==> local/ has content — backing up to local-${TIMESTAMP}/"
    mv local "local-${TIMESTAMP}"
    mkdir -p local
    cp -r "${SEED_SOURCE}/." local/
else
    echo "==> local/ exists but is empty — seeding from ${SEED_SOURCE}/"
    cp -r "${SEED_SOURCE}/." local/
fi

if [ ! -f local/assets/images/og-default.jpg ]; then
    mkdir -p local/assets/images
    cp assets/images/og-default.jpg local/assets/images/og-default.jpg
fi

echo "==> Building PHP image"
$COMPOSE_CMD build --pull php

echo "==> Starting services"
$COMPOSE_CMD $RUNTIME_PROFILE up -d

echo "==> Installing dependencies"
$COMPOSE_CMD run --rm $APP_ENV_FLAG php composer install $COMPOSER_FLAGS

echo "==> Clearing cache"
$COMPOSE_CMD run --rm $APP_ENV_FLAG php php bin/console cache:clear

echo "==> Removing dart-sass binary (architecture-specific)"
$COMPOSE_CMD run --rm $APP_ENV_FLAG php rm -rf var/dart-sass

echo "==> Compiling SCSS"
$COMPOSE_CMD run --rm $APP_ENV_FLAG php php bin/console sass:build

echo "==> Clearing compiled assets"
$COMPOSE_CMD run --rm $APP_ENV_FLAG php rm -rf public/assets

echo "==> Compiling assets"
$COMPOSE_CMD run --rm $APP_ENV_FLAG php php bin/console asset-map:compile

echo "==> Rebuilding content and search index"
scripts/rebuild-content.sh $($PROD && echo "--prod") $( [[ "$THEME" == "bare" ]] && echo "--bare" )
