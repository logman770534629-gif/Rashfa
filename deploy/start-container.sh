#!/bin/sh
set -eu

export APP_URL="${APP_URL:-https://${VERCEL_URL:-}}"
export LARAVEL_STORAGE_PATH=/tmp/rashfa-storage
export VIEW_COMPILED_PATH=/app/storage/framework/views
export APP_CONFIG_CACHE=/tmp/rashfa-config.php

mkdir -p "$LARAVEL_STORAGE_PATH/framework/cache/data" \
    "$LARAVEL_STORAGE_PATH/framework/sessions" \
    "$LARAVEL_STORAGE_PATH/framework/views" \
    "$LARAVEL_STORAGE_PATH/logs" \
    "$XDG_CONFIG_HOME" "$XDG_DATA_HOME"

php /app/deploy/validate-environment.php
if [ -n "${MYSQL_SSL_CA_BASE64:-}" ]; then
    export MYSQL_ATTR_SSL_CA=/tmp/rashfa-mysql-ca.pem
fi

# No migrations, seeders, admin creation or configuration caching at startup.
exec "$@"
