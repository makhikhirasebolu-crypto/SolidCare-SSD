FROM php:8.2-cli

# Install system dependencies
FROM php:8.2-cli

RUN apt-get update && apt-get install -y \
    unzip curl git libzip-dev libpq-dev \
    && docker-php-ext-install zip pdo pdo_pgsql

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Copy project files
COPY . .

# Install Laravel dependencies
RUN composer install --no-dev --optimize-autoloader

# IMPORTANT: Do NOT create .env here.
# Render will provide environment variables (including APP_KEY)

# Clear cached config so Laravel reads Render env variables
RUN php artisan config:clear || true
RUN php artisan cache:clear || true

# Ensure required folders exist and permissions are correct
RUN mkdir -p storage bootstrap/cache
RUN chmod -R 775 storage bootstrap/cache

# Expose Render port
EXPOSE 10000

# Start Laravel app
CMD php artisan migrate --force || true && \
    php artisan serve --host=0.0.0.0 --port=10000