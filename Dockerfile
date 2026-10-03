FROM dunglas/frankenphp:1-php8.4

RUN install-php-extensions pdo_sqlite intl opcache zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY docker/php.ini $PHP_INI_DIR/conf.d/szk.ini

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --no-interaction --prefer-dist

COPY . .
RUN composer dump-autoload --optimize --no-dev \
    && mkdir -p data storage/logs storage/app/public storage/framework/cache/data storage/framework/sessions storage/framework/views \
    && chmod +x docker/start.sh

# The base image checks Caddy's admin port, which php-server disables, so the container
# would stay "unhealthy" and Traefik would never route to it. Check Laravel instead.
HEALTHCHECK --interval=30s --timeout=3s --start-period=20s CMD curl -fs http://localhost/up || exit 1

CMD ["docker/start.sh"]
