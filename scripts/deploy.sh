#!/usr/bin/env bash
set -euo pipefail

DIR="$(dirname "$(dirname "$(readlink -f "$0")")")"
cd "${DIR}"

PROD=false
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
    echo "  --prod          Build and run in production mode (APP_ENV=prod, no dev dependencies)"
    echo "  --port <port>   Override the nginx port (sets NGINX_PORT)"
    echo "  -h, --help      Show this help message"
    exit 0
  fi
  [[ "${args[$i]}" == "up" ]] && COMMAND="up"
  [[ "${args[$i]}" == "down" ]] && COMMAND="down"
  [[ "${args[$i]}" == "restart" ]] && COMMAND="restart"
  [[ "${args[$i]}" == "--prod" ]] && PROD=true
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

# --- down ---
if [[ "$COMMAND" == "down" ]]; then
  echo "==> Stopping containers"
  $COMPOSE_CMD down
  exit 0
fi

# --- up (no rebuild) ---
if [[ "$COMMAND" == "up" ]]; then
  echo "==> Starting services"
  $COMPOSE_CMD up -d
  exit 0
fi

# --- restart (stop + start, no rebuild) ---
if [[ "$COMMAND" == "restart" ]]; then
  echo "==> Restarting containers"
  $COMPOSE_CMD down
  $COMPOSE_CMD up -d
  exit 0
fi

# --- full deploy (default) ---

echo "==> Stopping containers"
$COMPOSE_CMD down

echo "==> Ensuring external networks exist"
docker network inspect web >/dev/null 2>&1 || docker network create web

echo "==> Bootstrapping content"
if [ ! -f local/content/_site.yaml ]; then
    echo "    local/content/ is empty — seeding from docs/examples/content/"
    cp -r docs/examples/content/. local/content/
else
    echo "    local/content/ already exists — skipping"
fi

echo "==> Bootstrapping translations"
if [ ! -f local/translations/messages.en.yaml ] && [ ! -f local/translations/messages.pl.yaml ]; then
    echo "    local/translations/ is empty — seeding from docs/examples/translations/"
    cp docs/examples/translations/messages.en.yaml local/translations/messages.en.yaml
    cp docs/examples/translations/messages.pl.yaml local/translations/messages.pl.yaml
else
    echo "    local/translations/ already exists — skipping"
fi

echo "==> Bootstrapping nginx config"
if [ ! -f local/docker/nginx/redirects.conf ]; then
    echo "    local/docker/nginx/ is empty — seeding from docs/examples/nginx/"
    mkdir -p local/docker/nginx
    cp docs/examples/nginx/redirects.conf local/docker/nginx/redirects.conf
else
    echo "    local/docker/nginx/redirects.conf already exists — skipping"
fi
if [ ! -f local/docker/nginx/error-pages.conf ]; then
    echo "    seeding local/docker/nginx/error-pages.conf from docs/examples/nginx/"
    mkdir -p local/docker/nginx
    cp docs/examples/nginx/error-pages.conf local/docker/nginx/error-pages.conf
else
    echo "    local/docker/nginx/error-pages.conf already exists — skipping"
fi

echo "==> Bootstrapping local assets"
if [ ! -f local/assets/images/og-default.jpg ]; then
    echo "    local/assets/images/ missing og-default.jpg — seeding from assets/images/"
    mkdir -p local/assets/images
    cp assets/images/og-default.jpg local/assets/images/og-default.jpg
else
    echo "    local/assets/images/og-default.jpg already exists — skipping"
fi

echo "==> Bootstrapping local docs"
if [ ! -f local/docs/EDITOR_GUIDE.md ]; then
    echo "    local/docs/EDITOR_GUIDE.md missing — seeding from docs/examples/docs/"
    mkdir -p local/docs
    cp docs/examples/docs/EDITOR_GUIDE.md local/docs/EDITOR_GUIDE.md
else
    echo "    local/docs/EDITOR_GUIDE.md already exists — skipping"
fi
if [ ! -f local/docs/STYLEGUIDE.md ]; then
    echo "    local/docs/STYLEGUIDE.md missing — seeding from docs/examples/docs/"
    mkdir -p local/docs
    cp docs/examples/docs/STYLEGUIDE.md local/docs/STYLEGUIDE.md
else
    echo "    local/docs/STYLEGUIDE.md already exists — skipping"
fi

echo "==> Building PHP image"
$COMPOSE_CMD build --pull

echo "==> Starting services"
$COMPOSE_CMD up -d

echo "==> Installing dependencies"
$COMPOSE_CMD exec $APP_ENV_FLAG php composer install $COMPOSER_FLAGS

echo "==> Clearing cache"
$COMPOSE_CMD exec $APP_ENV_FLAG php php bin/console cache:clear

echo "==> Removing dart-sass binary (architecture-specific)"
$COMPOSE_CMD exec $APP_ENV_FLAG php rm -rf var/dart-sass

echo "==> Compiling SCSS"
$COMPOSE_CMD exec $APP_ENV_FLAG php php bin/console sass:build

echo "==> Clearing compiled assets"
$COMPOSE_CMD exec $APP_ENV_FLAG php rm -rf public/assets

echo "==> Compiling assets"
$COMPOSE_CMD exec $APP_ENV_FLAG php php bin/console asset-map:compile

echo "==> Rebuilding content and search index"
scripts/rebuild-content.sh $($PROD && echo "--prod")
