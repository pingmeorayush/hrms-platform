FROM php:8.4-cli-bookworm

ARG UID=1000
ARG GID=1000

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_HOME=/tmp/composer \
    HOME=/tmp/composer \
    PATH="/var/www/html/vendor/bin:${PATH}"

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        curl \
        git \
        unzip \
        libicu-dev \
        libonig-dev \
        libsqlite3-dev \
        libzip-dev \
    && docker-php-ext-install \
        bcmath \
        intl \
        mbstring \
        pcntl \
        pdo_sqlite \
        zip \
    && mkdir -p /var/www/html/vendor /tmp/composer \
    && chown -R "${UID}:${GID}" /var/www/html /tmp/composer \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
COPY composer.json composer.lock ./
RUN composer install --prefer-dist --no-interaction --no-scripts \
    && chown -R "${UID}:${GID}" /var/www/html /tmp/composer

COPY docker/entrypoint.sh /usr/local/bin/api-entrypoint

RUN chmod +x /usr/local/bin/api-entrypoint

USER ${UID}:${GID}

ENTRYPOINT ["api-entrypoint"]
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
