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

# Install Laravel dependencies
RUN composer install --no-dev --optimize-autoloader

# Create .env file from example
RUN cp .env.example .env

# Generate application key (won’t crash if already exists)
RUN cp .env.example .env
RUN php artisan config:clear || true

# Clear caches to avoid config issues
RUN php artisan config:clear || true
RUN php artisan cache:clear || true

# Ensure required folders exist and set permissions
RUN mkdir -p storage bootstrap/cache
RUN chmod -R 775 storage bootstrap/cache

# Run migrations safely (won’t crash if DB not ready)
RUN php artisan migrate --force || true

# Cache config for performance
RUN php artisan config:cache

# Expose Render port
EXPOSE 10000

# Start Laravel app

CMD php artisan key:generate --force && \
    php artisan migrate --force || true && \
    php artisan serve --host=0.0.0.0 --port=10000