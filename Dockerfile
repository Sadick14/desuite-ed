# Stage 1: Build Frontend
FROM node:20-alpine AS frontend

WORKDIR /app

COPY package*.json ./
COPY package-lock.json ./

RUN npm ci --no-audit

COPY resources/ ./resources
COPY vite.config.js ./
COPY tailwind.config.js ./
COPY tsconfig.json ./

RUN npm run build


# Stage 2: Backend & Application
FROM php:8.4-fpm-alpine AS backend

WORKDIR /var/www/html

# Install PHP extensions
RUN apk --no-cache add \
    git \
    curl \
    zip \
    unzip \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    oniguruma-dev \
    libxml2-dev

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql mbstring exif pcntl bcmath opcache

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy app files
COPY composer.json ./
COPY composer.lock ./

RUN composer install --no-ansi --no-dev --optimize-autoloader --no-interaction

COPY --from=frontend /app/public/build ./public/build

COPY . .

# Configure permissions
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Laravel optimization
RUN php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

EXPOSE 9000

CMD ["php-fpm"]
