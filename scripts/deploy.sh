#!/usr/bin/env bash

set -euo pipefail

BRANCH="${BRANCH:-develop}"
APP_SERVICE="${APP_SERVICE:-app}"
FRONTEND_DIR="${FRONTEND_DIR:-frontend}"
DEPLOY_FRONTEND="${DEPLOY_FRONTEND:-1}"
FRONTEND_RESTART_CMD="${FRONTEND_RESTART_CMD:-pm2 restart easyrp-frontend}"
PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

echo "==> EasyRP deploy started"
echo "==> Project: ${PROJECT_ROOT}"
echo "==> Branch: ${BRANCH}"

cd "${PROJECT_ROOT}"

if ! git rev-parse --is-inside-work-tree >/dev/null 2>&1; then
  echo "Error: not inside a git repository."
  exit 1
fi

CURRENT_BRANCH="$(git rev-parse --abbrev-ref HEAD)"
if [[ "${CURRENT_BRANCH}" != "${BRANCH}" ]]; then
  echo "==> Switching branch ${CURRENT_BRANCH} -> ${BRANCH}"
  git fetch origin "${BRANCH}"
  git checkout "${BRANCH}"
fi

echo "==> Pulling latest changes from origin/${BRANCH}"
git pull --ff-only origin "${BRANCH}"

echo "==> Building and restarting Docker services"
docker compose up -d --build

echo "==> Reloading nginx upstream mapping"
docker compose restart nginx

if [[ "${DEPLOY_FRONTEND}" == "1" ]]; then
  FRONTEND_PATH="${PROJECT_ROOT}/${FRONTEND_DIR}"
  if [[ -d "${FRONTEND_PATH}" ]]; then
    echo "==> Installing frontend dependencies"
    (cd "${FRONTEND_PATH}" && bun install)

    echo "==> Building frontend"
    (cd "${FRONTEND_PATH}" && bun run build)

    if [[ -n "${FRONTEND_RESTART_CMD}" ]]; then
      echo "==> Restarting frontend process"
      bash -lc "${FRONTEND_RESTART_CMD}"
    else
      echo "==> FRONTEND_RESTART_CMD not set, skipping frontend process restart"
    fi
  else
    echo "==> Frontend directory '${FRONTEND_PATH}' not found, skipping frontend deploy"
  fi
else
  echo "==> Skipping frontend deploy (DEPLOY_FRONTEND=${DEPLOY_FRONTEND})"
fi

read -r -p "Run database migrations now? (y/N): " RUN_MIGRATIONS
if [[ "${RUN_MIGRATIONS}" =~ ^[Yy]$ ]]; then
  echo "==> Running migrations"
  docker compose exec "${APP_SERVICE}" php artisan migrate --force
else
  echo "==> Skipping migrations"
fi

echo "==> Clearing and warming Laravel caches"
docker compose exec "${APP_SERVICE}" php artisan optimize:clear
docker compose exec "${APP_SERVICE}" php artisan optimize

echo "==> Deploy complete"
