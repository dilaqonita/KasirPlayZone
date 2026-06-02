FROM php:8.2-fpm

# Install system dependencies & PHP extensions
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libssl-dev \
    pkg-config \
    && pecl install mongodb \
    && docker-php-ext-enable mongodb \
    && docker-php-ext-install gd mbstring exif pcntl bcmath opcache

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Copy existing application directory contents
COPY . .

# Install PHP dependencies bypassing platform checks
RUN composer install --no-interaction --optimize-autoloader --ignore-platform-reqs

# Expose port and start php server
EXPOSE 8080
CMD php artisan config:clear && php artisan view:clear && php artisan route:clear && php artisan serve --host=0.0.0.0 --port=8080