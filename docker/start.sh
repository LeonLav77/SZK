#!/bin/sh
set -e

touch "$DB_DATABASE"
php artisan migrate --force
php artisan storage:link --force > /dev/null 2>&1 || true
php artisan optimize

# With DOMAIN set, FrankenPHP (Caddy) gets an HTTPS certificate automatically.
if [ -n "$DOMAIN" ]; then
    exec frankenphp php-server --root public --domain "$DOMAIN"
else
    exec frankenphp php-server --root public --listen :80
fi
