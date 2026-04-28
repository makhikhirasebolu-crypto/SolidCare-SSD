FROM php:8.2-cli

# Install system dependencies (FIXED)
RUN apt-get update && apt-get install -y \
    unzip curl git libzip-dev libsqlite3-dev \
    && docker-php-ext-install zip pdo pdo_sqlite

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .

# Install Laravel dependencies
RUN composer install --no-dev --optimize-autoloader

# Setup database
RUN mkdir -p database && touch database/database.sqlite

# Laravel setup
RUN php artisan key:generate
RUN php artisan config:cache

# Fix permissions
RUN chmod -R 775 storage bootstrap/cache

EXPOSE 10000

CMD php artisan serve --host=0.0.0.0 --port=10000