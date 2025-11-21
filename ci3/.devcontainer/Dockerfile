FROM php:8.3-apache

USER root

# Install system dependencies
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        curl \
        zip \
        unzip \
        default-mysql-client \
        libpng-dev \
        libjpeg-dev \
        libfreetype6-dev \
        libonig-dev \
        libxml2-dev \
        libzip-dev \
        libicu-dev \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mysqli \
        intl \
        zip \
        gd \
        opcache \
        mbstring \
        exif \
        pcntl \
        bcmath

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY web/apache.conf /etc/apache2/sites-available/000-default.conf
COPY php/conf.d/shulelabs.ini /usr/local/etc/php/conf.d/shulelabs.ini

RUN a2enmod rewrite \
    && echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Create vscode user for compatibility with same UID as host
RUN groupadd --gid 1000 vscode \
    && useradd --uid 1000 --gid vscode --shell /bin/bash --create-home vscode \
    && usermod -aG www-data vscode

# Set proper permissions
RUN chown -R vscode:vscode /var/www/html

WORKDIR /workspace/shulelabs

EXPOSE 80

CMD ["apache2ctl", "-D", "FOREGROUND"]
