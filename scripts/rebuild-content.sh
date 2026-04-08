#!/usr/bin/env bash
set -euo pipefail

DIR="$(dirname "$(dirname "$(readlink -f "$0")")")"
cd "${DIR}"

usage() {
  echo "Usage: ./rebuild-content.sh [options]"
  echo ""
  echo "Options:"
  echo "  --prod    Build in production mode (APP_ENV=prod)"
  echo "  -h, --help  Show this help message"
  exit 0
}

PROD=false
for arg in "$@"; do
  [[ "${arg}" == "-h" || "${arg}" == "--help" ]] && usage
  [[ "${arg}" == "--prod" ]] && PROD=true
done

APP_ENV_FLAG=""
if $PROD; then
    APP_ENV_FLAG="-e APP_ENV=prod -e APP_DEBUG=false"
fi

ENV_LOCAL_FLAG=""
if [[ -f ".env.local" ]]; then
  ENV_LOCAL_FLAG="--env-file .env.local"
fi

echo "==> Bootstrapping content"
if [ ! -f local/content/_site.yaml ]; then
    echo "    local/content/ is empty — seeding from docs/examples/content/"
    cp -r docs/examples/content/. local/content/
else
    echo "    local/content/ already exists — skipping"
fi

echo "==> Building static site"
docker compose --env-file .env $ENV_LOCAL_FLAG exec $APP_ENV_FLAG php php bin/console app:build


echo "==> Copying favicon.ico"
if [ -f local/assets/favicon.ico ]; then
    cp local/assets/favicon.ico public/favicon.ico
elif [ -f assets/images/favicon.ico ]; then
    cp assets/images/favicon.ico public/favicon.ico
fi

echo "==> Building search index"
docker compose --env-file .env $ENV_LOCAL_FLAG exec -e npm_config_cache=/tmp/.npm $APP_ENV_FLAG php npx --yes pagefind --site public/static --output-path public/pagefind

echo "==> Done."
