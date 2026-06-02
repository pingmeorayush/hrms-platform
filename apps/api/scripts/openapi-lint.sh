#!/usr/bin/env sh

set -eu

ROOT_DIR=$(CDPATH= cd -- "$(dirname "$0")/.." && pwd)
LOCAL_REDOCLY="$ROOT_DIR/node_modules/.bin/redocly"
CACHED_REDOCLY=""
SPEC_FILES=$(find "$ROOT_DIR/openapi" -maxdepth 1 -name '*.yaml' | sort)

if [ -z "$SPEC_FILES" ]; then
  echo "No OpenAPI contract files were found under $ROOT_DIR/openapi." >&2
  exit 1
fi

if [ -x "$LOCAL_REDOCLY" ]; then
  CACHED_REDOCLY="$LOCAL_REDOCLY"
elif [ -d "$HOME/.npm/_npx" ]; then
  CACHED_REDOCLY=$(find "$HOME/.npm/_npx" -maxdepth 4 -path '*/node_modules/.bin/redocly' | head -n 1 || true)
fi

if [ -n "$CACHED_REDOCLY" ] && [ -x "$CACHED_REDOCLY" ]; then
  # shellcheck disable=SC2086
  exec "$CACHED_REDOCLY" lint $SPEC_FILES
fi

cd "$ROOT_DIR"
# shellcheck disable=SC2086
exec npx -y @redocly/cli lint $SPEC_FILES
