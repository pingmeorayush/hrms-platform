#!/bin/sh
set -eu

cd /usr/src/app
umask 0002

if [ ! -f .env ] && [ -f .env.example ]; then
  cp .env.example .env
fi

lock_hash_file="node_modules/.package-lock.hash"
current_lock_hash=""
installed_lock_hash=""

if [ -f package-lock.json ]; then
  current_lock_hash="$(sha256sum package-lock.json | awk '{print $1}')"
fi

if [ -f "${lock_hash_file}" ]; then
  installed_lock_hash="$(cat "${lock_hash_file}")"
fi

if [ ! -d node_modules ] || [ -z "$(ls -A node_modules 2>/dev/null)" ] || [ "${current_lock_hash}" != "${installed_lock_hash}" ]; then
  npm install

  if [ -n "${current_lock_hash}" ]; then
    printf '%s' "${current_lock_hash}" > "${lock_hash_file}"
  fi
fi

exec "$@"
