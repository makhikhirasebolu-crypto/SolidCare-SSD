FROM php:8.2-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    unzip curl git libzip-dev default-mysql-client \
    && docker-php-ext-install zip pdo pdo_mysql

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Copy project files
COPY . .

# Install dependencies
RUN composer install --no-dev --optimize-autoloader

# Ensure Laravel folders exist
RUN mkdir -p storage bootstrap/cache

# Set permissions
RUN chmod -R 775 storage bootstrap/cache

# Clear and cache config properly
RUN php artisan config:clear || true
RUN php artisan cache:clear || true

# Generate app key safely
RUN php artisan key:generate || true

# Run migrations (safe for production)
RUN php artisan migrate --force || true

# Cache config for performance
RUN php artisan config:cache

# Expose port
EXPOSE 10000

# Start Laravel
CMD php artisan serve --host=0.0.0.0 --port=10000