# Use PHP 8.2 with Apache
FROM php:8.2-apache

# Install Node.js 18.x
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - \
    && apt-get install -y nodejs

# Install system dependencies
RUN apt-get update && apt-get install -y \
    unzip \
    git \
    libpq-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    curl \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    netcat-openbsd \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd

# Install Composer
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

# Enable Apache rewrite module
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy project files
COPY . .

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Install Node.js dependencies and build assets
RUN npm ci \
    && npm run build \
    && rm -rf node_modules

# Create storage directories if they don't exist
RUN mkdir -p storage/logs storage/framework/{cache,sessions,views} bootstrap/cache

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 775 storage bootstrap/cache

# Generate application key (will be overridden by environment variable)
RUN php artisan key:generate --show > /tmp/app-key.txt

# Don't cache config in Docker build - will be done at runtime if needed
# RUN php artisan config:cache && php artisan route:cache

# Create Apache virtual host configuration
RUN echo '<VirtualHost *:80>\n\
    DocumentRoot /var/www/html/public\n\
    <Directory /var/www/html/public>\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>\n\
</VirtualHost>' > /etc/apache2/sites-available/000-default.conf

# Copy startup scripts
COPY docker-start-simple.sh /usr/local/bin/start-simple.sh
RUN chmod +x /usr/local/bin/start-simple.sh

# Expose port
EXPOSE 80

# Start Apache (use simple startup by default)
CMD ["/usr/local/bin/start-simple.sh"]
