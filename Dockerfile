# Stage 1: Build assets with Node.js
FROM node:20-alpine AS node-builder
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY resources/ ./resources/
COPY vite.config.js ./
RUN npm run build

# Stage 2: Install PHP dependencies with Composer
FROM composer:2 AS composer-builder
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress
COPY . .

# Stage 3: Production image
FROM php:8.3-fpm-alpine
WORKDIR /var/www/html

# Install system dependencies
RUN apk update && apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    bash \
    icu-dev

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd && \
                                                                       docker-php-ext-configure intl && \
                                                                       docker-php-ext-install intl

# Configure opcache
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

# Copy application files
COPY --from=composer-builder /app /var/www/html
COPY --from=node-builder /app/dist /var/www/html/public/build

# Set permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Expose port and start PHP-FPM
EXPOSE 9000
CMD ["php-fpm"]
