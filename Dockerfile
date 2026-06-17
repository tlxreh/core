# syntax=docker/dockerfile:1

##
# Cachet (core) demo / test image.
#
# cachethq/core is a Laravel *package*, not a standalone application. This image
# builds the Workbench/Testbench app that ships with the package and serves it
# with PHP's built-in server via `testbench serve`. It is meant for local
# testing and demos of the package — the production image lives with the
# standalone Cachet application.
##
FROM php:8.3-cli-bookworm

# System libraries needed to compile the PHP extensions and build assets.
RUN apt-get update && apt-get install -y --no-install-recommends \
        git unzip libzip-dev libicu-dev libpng-dev libonig-dev \
    && rm -rf /var/lib/apt/lists/*

# PHP extensions (copied installer — no network bootstrap script).
COPY --from=mlocati/php-extension-installer:latest /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions pdo_sqlite intl bcmath gd zip exif pcntl

# Composer and the Node toolchain, copied from their official images.
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
COPY --from=node:20-bookworm /usr/local/bin/node /usr/local/bin/node
COPY --from=node:20-bookworm /usr/local/lib/node_modules /usr/local/lib/node_modules
RUN ln -s /usr/local/lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm

WORKDIR /app

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    MAIL_MAILER=log

# Install dependencies first to maximise layer caching. The package does not
# commit composer.lock (library convention), so resolve like CI does.
COPY composer.json ./
RUN composer update --no-interaction --prefer-dist --no-scripts --no-autoloader

COPY package.json package-lock.json ./
RUN npm ci

# Copy the rest of the source, then finish autoloading, build assets, and
# prepare the Testbench app (publishes assets, creates + seeds a SQLite DB).
COPY . .
RUN composer dump-autoload --optimize \
    && npm run build \
    && composer build \
    && chmod +x docker/entrypoint.sh

EXPOSE 8000

ENTRYPOINT ["/app/docker/entrypoint.sh"]
CMD ["php", "vendor/bin/testbench", "serve", "--host=0.0.0.0", "--port=8000"]
