#!/bin/sh
set -e

cd /app

if [ ! -f .env ]; then
    cp .env.example .env
fi

# Remove any stale cached bootstrap files before Laravel boots.
rm -f bootstrap/cache/*.php

if [ -z "${APP_KEY:-}" ] && ! grep -Eq '^APP_KEY=.+$' .env; then
    php artisan key:generate --force
fi

php artisan config:clear
php artisan route:clear || true
php artisan view:clear || true
php artisan migrate --force || true

exec php artisan serve --host=0.0.0.0 --port="${PORT:-10000}"
