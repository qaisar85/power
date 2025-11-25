FROM php:8.3-cli

# Install system deps commonly needed for Laravel dev (adjust as needed)
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    unzip \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libicu-dev \
    libpq-dev \
    && docker-php-ext-install pcntl pdo pdo_mysql zip gd intl

WORKDIR /app

# Copy in our clean php.ini to override auto_prepend_file in CLI
COPY php.ini /usr/local/etc/php/php.ini

# default command is a shell so CI runner can run composer/artisan
CMD ["bash"]
