FROM php:8.2-fpm-alpine

# Set working directory
WORKDIR /app

# Install dependencies and tools
RUN apk add --no-cache \
    nginx \
    supervisor \
    sqlite \
    sqlite-dev \
    git \
    zip \
    unzip \
    curl \
    libpng-dev \
    libzip-dev \
    oniguruma-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    nodejs \
    npm \
    && rm -rf /var/cache/apk/*

# Configure and install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install -j$(nproc) \
    pdo_mysql \
    pdo_sqlite \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy application files
COPY . /app

# Set up Nginx
COPY docker/nginx.conf /etc/nginx/http.d/default.conf

# Set up Supervisor
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Set up entrypoint script
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# Make SQLite database directory
RUN mkdir -p /app/database
RUN touch /app/database/database.sqlite
RUN chown -R www-data:www-data /app/database

# Create a volume for database persistence
VOLUME /app/database

# Set proper permissions
RUN chown -R www-data:www-data /app/storage /app/bootstrap/cache

# Install dependencies with no interaction
RUN composer install --optimize-autoloader --no-dev --no-interaction

# Install npm dependencies and build assets
RUN if [ -f package.json ]; then \
        npm install && \
        npm run build; \
    fi

# Optimize for production
RUN php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

# Expose port
EXPOSE 80

# Start services
ENTRYPOINT ["/entrypoint.sh"]
