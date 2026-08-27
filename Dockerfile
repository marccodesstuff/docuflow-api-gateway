# DocuFlow API Gateway - Laravel Dockerfile
FROM php:8.2-fpm-alpine AS builder

WORKDIR /app

# Install system dependencies
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libzip-dev \
    zip \
    unzip \
    oniguruma-dev \
    postgresql-dev \
    linux-headers \
    $PHPIZE_DEPS

# Install PHP extensions
RUN docker-php-ext-install \
    pdo_pgsql \
    mbstring \
    zip \
    bcmath \
    pcntl \
    opcache

# Install Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Install gRPC extension
RUN pecl install grpc && docker-php-ext-enable grpc

# Install Protobuf extension
RUN pecl install protobuf && docker-php-ext-enable protobuf

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy composer files
COPY composer.json composer.lock ./

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# Copy application source
COPY . .

# Generate application key and optimize
RUN php artisan key:generate --force && \
    php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache

# Production stage
FROM php:8.2-fpm-alpine

WORKDIR /app

# Install runtime dependencies
RUN apk add --no-cache \
    libpng \
    libzip \
    postgresql-libs \
    oniguruma \
    nginx \
    supervisor

# Install PHP extensions
RUN docker-php-ext-install \
    pdo_pgsql \
    mbstring \
    zip \
    bcmath \
    pcntl \
    opcache

# Enable extensions from builder
COPY --from=builder /usr/local/lib/php/extensions /usr/local/lib/php/extensions
COPY --from=builder /usr/local/etc/php/conf.d /usr/local/etc/php/conf.d

# Create non-root user
RUN addgroup -g 1000 -S appgroup && \
    adduser -u 1000 -S appuser -G appgroup

# Copy application from builder
COPY --from=builder /app /app

# Copy nginx and supervisor configs
COPY docker/nginx/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Create storage directories
RUN mkdir -p /app/storage/framework/{cache,sessions,views} /app/storage/logs /app/bootstrap/cache && \
    chown -R appuser:appgroup /app/storage /app/bootstrap/cache

USER appuser

EXPOSE 80

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]