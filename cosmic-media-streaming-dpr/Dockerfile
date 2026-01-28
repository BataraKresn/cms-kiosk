# Multi-stage build for production
FROM php:8.3-fpm

# Set working directory
WORKDIR /var/www

# Install system dependencies, PHP extensions, Node.js, and FFmpeg
RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libwebp-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libicu-dev \
    ffmpeg \
    nginx \
    supervisor \
    ca-certificates \
    gnupg \
    && docker-php-ext-configure intl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip intl opcache \
    && pecl install redis \
    && docker-php-ext-enable redis opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Node.js 20.x using the official setup script
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy application files
COPY --chown=www-data:www-data . /var/www

# Install PHP dependencies for production (skip scripts to avoid S3 config errors during build)
RUN composer install --optimize-autoloader --no-dev --no-interaction --no-progress --prefer-dist --no-scripts

# Install Node dependencies (including devDependencies for build) and build assets
RUN npm ci \
    && npm run build \
    && rm -rf node_modules \
    && npm ci --only=production \
    && npm cache clean --force

# Set PHP configurations for file uploads and production
RUN echo "file_uploads = On\n" \
         "memory_limit = 2048M\n" \
         "upload_max_filesize = 2048M\n" \
         "post_max_size = 2048M\n" \
         "max_execution_time = 300\n" \
         "max_input_time = 300\n" \
         "expose_php = Off\n" \
    >> /usr/local/etc/php/conf.d/uploads.ini

# OPcache optimizations for production
RUN echo "opcache.enable=1\n" \
         "opcache.memory_consumption=256\n" \
         "opcache.interned_strings_buffer=16\n" \
         "opcache.max_accelerated_files=20000\n" \
         "opcache.revalidate_freq=60\n" \
         "opcache.fast_shutdown=1\n" \
         "opcache.validate_timestamps=0\n" \
         "opcache.enable_cli=0\n" \
    >> /usr/local/etc/php/conf.d/opcache.ini

# PHP-FPM configuration - optimized for 4GB RAM
RUN echo "[www]\n" \
         "pm = dynamic\n" \
         "pm.max_children = 75\n" \
         "pm.start_servers = 10\n" \
         "pm.min_spare_servers = 5\n" \
         "pm.max_spare_servers = 20\n" \
         "pm.max_requests = 1000\n" \
         "pm.process_idle_timeout = 10s\n" \
         "request_terminate_timeout = 30s\n" \
    >> /usr/local/etc/php-fpm.d/zz-docker.conf

# Configure Nginx
COPY docker/nginx/default.conf /etc/nginx/sites-available/default
RUN ln -sf /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default

# Configure Supervisor
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Create necessary directories and set permissions
RUN mkdir -p /var/www/storage/app/public \
             /var/www/storage/framework/{sessions,views,cache} \
             /var/www/storage/logs \
             /var/www/bootstrap/cache \
    && chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# NOTE: Do NOT cache config/routes/views here!
# Caching must be done at deployment time with proper environment variables
# Pre-caching in build causes environment mismatch issues

# Health check script
COPY docker/healthcheck.sh /usr/local/bin/healthcheck
RUN chmod +x /usr/local/bin/healthcheck

# Expose port 80 for Nginx
EXPOSE 80

# Use supervisor to manage PHP-FPM and Nginx
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
