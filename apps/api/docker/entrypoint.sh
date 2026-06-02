#!/bin/sh
set -eu

cd /var/www/html
umask 0002

mkdir -p \
  bootstrap/cache \
  database \
  storage/app \
  storage/framework/cache/data \
  storage/framework/sessions \
  storage/framework/testing \
  storage/logs

touch database/database.sqlite

if [ ! -f .env ] && [ -f .env.example ]; then
  cp .env.example .env
fi

chmod -R ug+rwX bootstrap/cache database storage 2>/dev/null || true

composer_hash_file="vendor/.composer.lock.hash"
current_composer_hash=""
installed_composer_hash=""

if [ -f composer.lock ]; then
  current_composer_hash="$(sha256sum composer.lock | awk '{print $1}')"
fi

if [ -f "${composer_hash_file}" ]; then
  installed_composer_hash="$(cat "${composer_hash_file}")"
fi

if [ ! -f vendor/autoload.php ] || [ "${current_composer_hash}" != "${installed_composer_hash}" ]; then
  composer install --prefer-dist --no-interaction

  if [ -n "${current_composer_hash}" ]; then
    printf '%s' "${current_composer_hash}" > "${composer_hash_file}"
  fi
fi

if ! grep -Eq '^APP_KEY=base64:' .env 2>/dev/null; then
  php artisan key:generate --force --ansi
fi

php artisan optimize:clear --ansi >/dev/null

if [ "${RUN_MIGRATIONS:-1}" = "1" ]; then
  php artisan migrate --force --ansi
fi

exec "$@"
