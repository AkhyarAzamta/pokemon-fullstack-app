FROM php:8.3-fpm-alpine

# Install system dependencies
RUN apk update && apk add --no-cache \
    nginx supervisor curl git unzip \
    oniguruma-dev libxml2-dev openssl-dev \
    autoconf g++ make

# Install MongoDB extension
RUN pecl install mongodb && docker-php-ext-enable mongodb

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql mbstring

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .

# Install dependencies dengan ignore platform reqs
RUN composer install --no-dev --optimize-autoloader --ignore-platform-reqs

# Setup storage
RUN mkdir -p storage/framework/views storage/framework/cache storage/framework/sessions storage/logs bootstrap/cache && \
    chown -R www-data:www-data storage bootstrap/cache && \
    chmod -R 775 storage bootstrap/cache

# Clear cache
RUN php artisan config:clear && \
    php artisan cache:clear && \
    php artisan view:clear

COPY ./docker/nginx.conf /etc/nginx/nginx.conf

# Startup script
RUN echo '#!/bin/sh' > /start.sh && \
    echo 'php artisan config:cache' >> /start.sh && \
    echo 'php artisan route:cache' >> /start.sh && \
    echo 'php-fpm -D' >> /start.sh && \
    echo 'nginx -g "daemon off;"' >> /start.sh && \
    chmod +x /start.sh

EXPOSE 8080
CMD ["/start.sh"]