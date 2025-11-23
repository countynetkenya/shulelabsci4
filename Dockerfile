# ShuleLabs CI4 - Production Docker Image
FROM php:8.3-fpm-alpine

LABEL maintainer="ShuleLabs Team"
LABEL version="2.0.0"
LABEL description="ShuleLabs School Management System - CodeIgniter 4"

# Install system dependencies
RUN apk add --no-cache \
    bash \
    curl \
    git \
    zip \
    unzip \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    icu-dev \
    oniguruma-dev \
    nginx \
    supervisor \
    mysql-client \
    sqlite

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_mysql \
        mysqli \
        gd \
        zip \
        intl \
        mbstring \
        bcmath \
        opcache

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . /var/www/html

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/writable \
    && chmod -R 755 /var/www/html/public

# Copy nginx configuration
COPY deployment/nginx/nginx.conf /etc/nginx/nginx.conf
COPY deployment/nginx/default.conf /etc/nginx/http.d/default.conf

# Copy supervisor configuration
COPY deployment/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# PHP configuration
RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && echo "upload_max_filesize = 50M" >> "$PHP_INI_DIR/php.ini" \
    && echo "post_max_size = 50M" >> "$PHP_INI_DIR/php.ini" \
    && echo "memory_limit = 256M" >> "$PHP_INI_DIR/php.ini" \
    && echo "max_execution_time = 300" >> "$PHP_INI_DIR/php.ini"

# Expose port 80
EXPOSE 80

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD curl -f http://localhost/health || exit 1

# Start supervisor (manages nginx + php-fpm)
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
