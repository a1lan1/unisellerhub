# --- STAGE 1: PHP Base ---
FROM php:8.4-fpm-alpine AS base

# Install system utilities and dependencies for PHP extensions
RUN apk add --no-cache \
    $PHPIZE_DEPS \
    libzip-dev \
    postgresql-dev \
    postgresql-client \
    git \
    libpq \
    python3 \
    zip \
    unzip \
    make \
    g++ \
    oniguruma-dev \
    libxml2-dev \
    supervisor \
    linux-headers \
    nodejs \
    yarn \
    openssl-dev

# Install the PHP extensions
RUN docker-php-ext-install -j$(nproc) \
    pcntl \
    exif \
    sockets \
    pdo_pgsql \
    zip \
    mbstring \
    bcmath \
    dom \
    xml

# Install Redis extension
RUN if ! php -m | grep -q 'redis'; then pecl install redis && docker-php-ext-enable redis; fi

# Install GD extension
RUN apk add --no-cache libpng-dev libjpeg-turbo-dev freetype-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd

# Install Imagick extension
RUN apk add --no-cache imagemagick imagemagick-dev \
    && if ! php -m | grep -q 'imagick'; then pecl install imagick && docker-php-ext-enable imagick; fi

# Install Intl extension
RUN apk add --no-cache icu-dev \
    && docker-php-ext-install intl \
    && apk del icu-dev \
    && apk add --no-cache icu-libs

# Install Composer globally
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install RoadRunner for Octane
COPY --from=spiralscout/roadrunner:2025.1 /usr/bin/rr /usr/bin/rr

# --- STAGE 2: Development Image ---
FROM base AS local

# Install Xdebug for debugging in development
RUN if ! php -m | grep -q 'xdebug'; then pecl install xdebug && docker-php-ext-enable xdebug; fi

# Copy entrypoint script
COPY /docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

WORKDIR /var/www/html

# Expose the port for local development
EXPOSE 8585

# Use entrypoint
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

# Command for local development
CMD ["php", "artisan", "octane:start", "--server=roadrunner", "--watch", "--host=0.0.0.0", "--port=8585"]
