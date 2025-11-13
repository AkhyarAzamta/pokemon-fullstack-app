# ---- Build stage ----
FROM composer:2 AS builder
WORKDIR /app
COPY . .
RUN composer install --no-dev --optimize-autoloader
RUN php artisan config:cache && php artisan route:cache && php artisan view:cache

# ---- Production stage ----
FROM php:8.3-fpm-alpine

# Install system dependencies
RUN apk add --no-cache nginx supervisor curl git zip unzip libpng-dev oniguruma-dev libxml2-dev bash

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd

# Copy Laravel app
WORKDIR /var/www/html
COPY --from=builder /app .

# Copy Nginx and Supervisor configs
COPY ./docker/nginx.conf /etc/nginx/nginx.conf
COPY ./docker/supervisord.conf /etc/supervisord.conf

# Set permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 8080
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
