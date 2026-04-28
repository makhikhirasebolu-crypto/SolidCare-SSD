FROM php:8.2-cli

# Install system dependencies required by Laravel and MySQL.
RUN apt-get update && apt-get install -y \
    curl \
    default-mysql-client \
    git \
    libzip-dev \
    unzip \
    && docker-php-ext-install pdo pdo_mysql zip \
    && rm -rf /var/lib/apt/lists/*

# Install Composer.
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copy the application and install PHP dependencies.
COPY . .
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# Copy the container startup script.
COPY docker/start.sh /usr/local/bin/start-container

# Ensure Laravel can write runtime files.
RUN mkdir -p storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache \
    && chmod +x /usr/local/bin/start-container

EXPOSE 10000

CMD ["start-container"]
