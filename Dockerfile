# Gunakan PHP 8.3 FPM Alpine yang ringan dan aman
FROM php:8.3-fpm-alpine

# Set working directory
WORKDIR /var/www/html

# Install system dependencies dan library yang diperlukan
RUN apk update && apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    oniguruma-dev \
    libzip-dev \
    icu-dev \
    linux-headers \
    netcat-openbsd

# Install ekstensi PHP yang dibutuhkan Laravel
RUN docker-php-ext-install \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    intl \
    opcache

# Salin binary Composer dari official image
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Salin source code project ke container
COPY . /var/www/html

# Buat direktori storage dan cache jika belum ada, serta atur izin akses (permissions)
RUN mkdir -p /var/www/html/storage/framework/cache \
    /var/www/html/storage/framework/sessions \
    /var/www/html/storage/framework/views \
    /var/www/html/storage/logs \
    /var/www/html/bootstrap/cache \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Install dependencies composer untuk environment production/container
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Expose port PHP-FPM
EXPOSE 9000

# Jalankan service PHP-FPM
CMD ["php-fpm"]
