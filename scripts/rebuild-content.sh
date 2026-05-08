#!/usr/bin/env bash
set -euo pipefail

DIR="$(dirname "$(dirname "$(readlink -f "$0")")")"
cd "${DIR}"

usage() {
  echo "Usage: ./rebuild-content.sh [options]"
  echo ""
  echo "Options:"
  echo "  --bare    Seed bare content (no demo translations/assets)"
  echo "  --demo    Seed demo content (default)"
  echo "  --prod    Build in production mode (APP_ENV=prod)"
  echo "  -h, --help  Show this help message"
  exit 0
}

PROD=false
THEME="demo"
for arg in "$@"; do
  [[ "${arg}" == "-h" || "${arg}" == "--help" ]] && usage
  [[ "${arg}" == "--prod" ]] && PROD=true
  [[ "${arg}" == "--bare" ]] && THEME="bare"
  [[ "${arg}" == "--demo" ]] && THEME="demo"
done

APP_ENV_FLAG=""
if $PROD; then
    APP_ENV_FLAG="-e APP_ENV=prod -e APP_DEBUG=false"
fi

ENV_LOCAL_FLAG=""
if [[ -f ".env.local" ]]; then
  ENV_LOCAL_FLAG="--env-file .env.local"
fi

echo "==> Bootstrapping content (theme: ${THEME})"
if [ ! -f local/content/_site.yaml ]; then
    if [[ "$THEME" == "bare" ]]; then
        echo "    local/content/ is empty — seeding from docs/bare/content/"
        cp -r docs/bare/content/. local/content/
    else
        echo "    local/content/ is empty — seeding from docs/demo/content/"
        cp -r docs/demo/content/. local/content/
    fi
else
    echo "    local/content/ already exists — skipping"
fi

echo "==> Building static site"
docker compose --env-file .env $ENV_LOCAL_FLAG run --rm $APP_ENV_FLAG php php bin/console cache:clear
docker compose --env-file .env $ENV_LOCAL_FLAG run --rm $APP_ENV_FLAG php php bin/console app:build


echo "==> Copying favicon.ico"
if [ -f local/assets/favicon.ico ]; then
    cp local/assets/favicon.ico public/favicon.ico
elif [ -f assets/images/favicon.ico ]; then
    cp assets/images/favicon.ico public/favicon.ico
fi

echo "==> Building search index"
rm -rf public/pagefind
# Pinned to pagefind@1.5.0 — later versions ship a jemalloc-linked ARM64 binary
# that crashes on 16K-page hosts (e.g. Raspberry Pi 5) with
# "<jemalloc>: Unsupported system page size".
# Tracking: https://github.com/Pagefind/pagefind/issues/1147
docker compose --env-file .env $ENV_LOCAL_FLAG run --rm -e npm_config_cache=/tmp/.npm $APP_ENV_FLAG php npx --yes pagefind@1.5.0 --site public/static --output-path public/pagefind

echo "==> Done."
