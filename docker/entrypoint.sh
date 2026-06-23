#!/usr/bin/env bash
set -e

# Wait for the database to accept connections (best-effort).
if [ -n "$DB_HOST" ]; then
    echo "Waiting for database at ${DB_HOST}:${DB_PORT:-3306}..."
    until php -r "exit(@fsockopen(getenv('DB_HOST'), (int)(getenv('DB_PORT') ?: 3306)) ? 0 : 1);" 2>/dev/null; do
        sleep 2
    done
fi

# Ensure a storage symlink and cache framework config for performance.
php artisan storage:link --force >/dev/null 2>&1 || true

if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
    echo "Running database migrations..."
    php artisan migrate --force
fi

# (Re)build optimized caches. Safe to run on every boot.
php artisan optimize >/dev/null 2>&1 || true

exec "$@"
