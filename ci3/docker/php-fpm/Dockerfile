FROM php:8.3-fpm-bookworm

ENV COMPOSER_ALLOW_SUPERUSER=1

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        unzip \
        gosu \
        libzip-dev \
        libpng-dev \
        libicu-dev \
        libxml2-dev \
        libjpeg62-turbo-dev \
        libfreetype6-dev \
        libwebp-dev \
        default-mysql-client \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j"$(nproc)" \
        bcmath \
        gd \
        intl \
        mysqli \
        pcntl \
        pdo_mysql \
        exif \
        zip \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

RUN mkdir -p /var/log/php \
    && chown www-data:www-data /var/log/php

WORKDIR /var/www/html

COPY docker/php-fpm/php.ini /usr/local/etc/php/conf.d/shulelabs.ini

COPY docker/php-fpm/entrypoint.sh /usr/local/bin/docker-entrypoint.sh

RUN chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]

CMD ["php-fpm"]
