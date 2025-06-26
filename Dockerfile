# Stage 1: PHP Dependencies
FROM composer:2 AS composer-builder
ARG PROXY
ENV http_proxy=$PROXY \
    HTTP_PROXY=$PROXY \
    https_proxy=$PROXY \
    HTTPS_PROXY=$PROXY

WORKDIR /app

# Install PHP extensions first (better caching)
RUN apk add --no-cache \
        icu-dev \
        libzip-dev \
    && docker-php-ext-configure intl \
    && docker-php-ext-install intl zip

# Copy composer files for better layer caching
COPY composer.json composer.lock ./

# Download dependencies without installing
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress --no-scripts --no-autoloader

# Copy application code
COPY . .

# Generate optimized autoloader
RUN composer dump-autoload --optimize --no-dev --classmap-authoritative

# Stage 2: Node Dependencies and Build
FROM node:20-alpine AS node-builder
ARG PROXY
ENV http_proxy=$PROXY \
    HTTP_PROXY=$PROXY \
    https_proxy=$PROXY \
    HTTPS_PROXY=$PROXY

WORKDIR /app

# Install build dependencies
RUN apk add --no-cache python3 make g++ git

# Configure npm
RUN npm config set proxy $PROXY \
    && npm config set https-proxy $PROXY \
    && npm config set registry https://registry.npmjs.org/

# Copy package files for better caching
COPY package.json package-lock.json ./

# Install dependencies
RUN npm ci --prefer-offline --no-audit --no-fund

# Copy source files
COPY resources/ ./resources/
COPY vite.config.js postcss.config.js tailwind.config.js ./
COPY public/ ./public/

# Build production assets
RUN NODE_ENV=production npm run build

# Stage 3: Production PHP Runtime
FROM php:8.3-fpm-alpine AS runtime
ARG PROXY
ENV http_proxy=$PROXY \
    HTTP_PROXY=$PROXY \
    https_proxy=$PROXY \
    HTTPS_PROXY=$PROXY

# Install runtime dependencies in one layer
RUN apk update && apk add --no-cache \
    curl \
    libpng-dev \
    oniguruma-dev \
    libxml2-dev \
    libzip-dev \
    icu-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    supervisor \
    nginx \
    bash \
    shadow \
    fcgi \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        intl \
        zip \
        opcache \
        sockets \
    && rm -rf /var/cache/apk/*

# Configure PHP
COPY docker/php/php.ini /usr/local/etc/php/php.ini
COPY docker/php/www.conf /usr/local/etc/php-fpm.d/www.conf
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

# Create non-root user
RUN addgroup -g 1000 -S laravel && \
    adduser -u 1000 -S laravel -G laravel

# Set working directory
WORKDIR /var/www/html

# Copy application from builder stages
COPY --from=composer-builder --chown=laravel:laravel /app /var/www/html
COPY --from=node-builder --chown=laravel:laravel /app/public/build /var/www/html/public/build

# Create required directories with proper permissions
RUN mkdir -p storage/framework/{sessions,views,cache} \
    && mkdir -p storage/logs \
    && mkdir -p bootstrap/cache \
    && chown -R laravel:laravel storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Copy supervisor configuration
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Health check script
COPY docker/health-check.sh /usr/local/bin/health-check
RUN chmod +x /usr/local/bin/health-check

# Switch to non-root user
USER laravel

# Expose port
EXPOSE 9000

# Health check
HEALTHCHECK --interval=10s --timeout=3s --start-period=10s --retries=3 \
    CMD /usr/local/bin/health-check || exit 1

# Start services
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]