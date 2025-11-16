# ============================
# 1) Backend Laravel Build
# ============================
FROM php:8.3-fpm-alpine AS backend-build

RUN apk update && apk add --no-cache \
    nginx supervisor curl git unzip \
    oniguruma-dev libxml2-dev openssl-dev \
    autoconf g++ make

RUN pecl install mongodb && docker-php-ext-enable mongodb
RUN docker-php-ext-install pdo pdo_mysql mbstring

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY backend/ .

RUN composer install --no-dev --optimize-autoloader --ignore-platform-reqs

RUN mkdir -p storage/framework/views storage/framework/cache storage/framework/sessions storage/logs bootstrap/cache && \
    chown -R www-data:www-data storage bootstrap/cache && \
    chmod -R 775 storage bootstrap/cache

RUN php artisan config:clear && php artisan cache:clear && php artisan view:clear

# ============================
# 2) Frontend Vue Build
# ============================
FROM node:20-alpine AS frontend-build

WORKDIR /app
COPY frontend/ .
RUN npm install && npm run build

# ============================
# 3) FINAL IMAGE (FE + BE)
# ============================
FROM php:8.3-fpm-alpine

RUN apk update && apk add --no-cache \
    nginx supervisor curl openssl-dev \
    autoconf make g++ oniguruma-dev libxml2-dev

# Install MongoDB extension
RUN pecl install mongodb \
    && docker-php-ext-enable mongodb \
    && apk del autoconf make g++  # remove build deps

RUN docker-php-ext-install pdo pdo_mysql mbstring

# Copy backend
COPY --from=backend-build /var/www/html /var/www/html

# Copy frontend
COPY --from=frontend-build /app/dist /usr/share/nginx/html

# Copy config files
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisord.conf
COPY docker/www.conf /usr/local/etc/php-fpm.d/www.conf

RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
