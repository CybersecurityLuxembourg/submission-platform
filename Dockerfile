FROM node:20-alpine AS node-builder
ARG PROXY
ENV http_proxy=$PROXY \
    HTTP_PROXY=$PROXY \
    https_proxy=$PROXY \
    HTTPS_PROXY=$PROXY

# Configure npm to use proxy
RUN npm config set proxy $PROXY \
    && npm config set https-proxy $PROXY \
    && npm config set registry https://registry.npmjs.org/
# Set working directory
WORKDIR /app

# Add build essentials
RUN apk add --no-cache python3 make g++
# Copy package files
COPY package.json package-lock.json ./

# Copy all necessary config files
COPY resources/ ./resources/
COPY vite.config.js ./
COPY postcss.config.js ./
COPY tailwind.config.js ./

RUN set -eux; \
    npm install --prefer-offline --no-audit --no-progress

# Build assets with explicit env
RUN NODE_ENV=production npm run build


# Stage 2: Install PHP dependencies with Composer
FROM composer:2 AS composer-builder
ARG PROXY
ENV http_proxy=$PROXY \
    HTTP_PROXY=$PROXY \
    https_proxy=$PROXY \
    HTTPS_PROXY=$PROXY

WORKDIR /app

# Install required PHP extensions
RUN apk add --no-cache \
        icu-dev \
    && docker-php-ext-configure intl \
    && docker-php-ext-install intl

# Copy composer files first
COPY composer.json composer.lock ./

# Copy the rest of the application before installing
COPY . .

# Install dependencies without running scripts
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress --no-scripts

# Now run the post-install scripts
RUN composer run-script post-autoload-dump

# Stage 3: Production image
FROM php:8.3-fpm-alpine
ARG PROXY
ENV http_proxy=$PROXY \
    HTTP_PROXY=$PROXY \
    https_proxy=$PROXY \
    HTTPS_PROXY=$PROXY

WORKDIR /var/www/html

# Install system dependencies
RUN apk update && apk add --no-cache \
    git \
    curl \
    libpng-dev \
    oniguruma-dev \
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
COPY --from=node-builder /app/public/build /var/www/html/public/build

RUN mkdir -p /var/www/html/storage/framework/{sessions,views,cache} \
    && mkdir -p /var/www/html/storage/logs \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

VOLUME /var/www/html/storage
# Expose port and start PHP-FPM
EXPOSE 9000
CMD ["php-fpm"]
