#!/usr/bin/env bash

set -euo pipefail

BRANCH="${BRANCH:-develop}"
APP_SERVICE="${APP_SERVICE:-app}"
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
