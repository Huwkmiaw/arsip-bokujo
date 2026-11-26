FROM node:22-bookworm-slim AS assets

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund

COPY resources ./resources
COPY public ./public
COPY vite.config.js ./
RUN npm run build

FROM dunglas/frankenphp:1-php8.4-bookworm AS app

RUN apt-get update \
    && apt-get install -y --no-install-recommends ca-certificates \
    && rm -rf /var/lib/apt/lists/* \
    && install-php-extensions pdo_mysql intl zip

WORKDIR /app

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --no-scripts \
    --optimize-autoloader \
    --prefer-dist

COPY . .
COPY --from=assets /app/public/build ./public/build

RUN mkdir -p \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        bootstrap/cache \
    && composer dump-autoload --no-dev --classmap-authoritative \
    && php artisan storage:link \
    && chown -R www-data:www-data storage bootstrap/cache

ENV PORT=80 \
    APP_ENV=production \
    APP_DEBUG=false \
    LOG_CHANNEL=stderr \
    SESSION_DRIVER=database \
    CACHE_STORE=database \
    QUEUE_CONNECTION=sync

USER www-data

CMD ["frankenphp", "run", "--config", "/etc/frankenphp/Caddyfile"]
