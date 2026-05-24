#!/usr/bin/env bash

set -euo pipefail

HOST="${DEPLOY_HOST:-}"
USER_NAME="${DEPLOY_USER:-root}"
APP_DIR="${DEPLOY_APP_DIR:-/var/www/ProyectoBasket/BellreguardBasket}"
SSH_OPTS="${DEPLOY_SSH_OPTS:-}"
SKIP_BUILD="${DEPLOY_SKIP_BUILD:-0}"
ARCHIVE_NAME="bellreguardbasket-deploy.tar.gz"
ARCHIVE_PATH="/tmp/${ARCHIVE_NAME}"
REMOTE_ARCHIVE="/tmp/${ARCHIVE_NAME}"

if [[ -z "${HOST}" ]]; then
    echo "Falta DEPLOY_HOST."
    echo "Uso:"
    echo "  DEPLOY_HOST=217.154.179.212 $0"
    exit 1
fi

if ! command -v composer >/dev/null 2>&1; then
    echo "No se ha encontrado composer en esta maquina."
    exit 1
fi

if ! command -v ssh >/dev/null 2>&1 || ! command -v scp >/dev/null 2>&1; then
    echo "Se necesitan ssh y scp para desplegar."
    exit 1
fi

if [[ "${SKIP_BUILD}" != "1" ]]; then
    if ! command -v npm >/dev/null 2>&1; then
        echo "No se ha encontrado npm y DEPLOY_SKIP_BUILD no es 1."
        exit 1
    fi

    npm run build
fi

tar     --exclude='.git'     --exclude='node_modules'     --exclude='storage/logs/*'     --exclude='storage/framework/cache/*'     --exclude='storage/framework/sessions/*'     --exclude='storage/framework/testing/*'     --exclude='storage/framework/views/*'     --exclude='database/database.sqlite'     --exclude='.env'     -czf "${ARCHIVE_PATH}"     -C "$(pwd)" .

scp ${SSH_OPTS} "${ARCHIVE_PATH}" "${USER_NAME}@${HOST}:${REMOTE_ARCHIVE}"

ssh ${SSH_OPTS} "${USER_NAME}@${HOST}" 'bash -s' <<'REMOTE'
set -euo pipefail

APP_DIR="${DEPLOY_APP_DIR:-/var/www/ProyectoBasket/BellreguardBasket}"
STAMP="$(date +%Y%m%d-%H%M%S)"
ARCHIVE_PATH="/tmp/bellreguardbasket-deploy.tar.gz"
ENV_BACKUP_DIR="/root"

test -d "${APP_DIR}"
test -f "${APP_DIR}/artisan"
test -f "${APP_DIR}/.env"

cp "${APP_DIR}/.env" "${ENV_BACKUP_DIR}/BellreguardBasket.env.${STAMP}.bak"
tar xzf "${ARCHIVE_PATH}" -C "${APP_DIR}"

cd "${APP_DIR}"

export COMPOSER_ALLOW_SUPERUSER=1
composer install --no-dev --optimize-autoloader --no-interaction

rm -f public/storage
php artisan storage:link

chown -R www-data:www-data storage bootstrap/cache
chown -h www-data:www-data public/storage || true

php artisan migrate --force
php artisan config:clear
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

rm -f "${ARCHIVE_PATH}"
REMOTE

rm -f "${ARCHIVE_PATH}"

echo "Despliegue completado en ${USER_NAME}@${HOST}:${APP_DIR}"
