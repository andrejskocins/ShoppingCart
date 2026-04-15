FROM php:8.3-cli

# System deps + PHP extensions needed by Laravel/Postgres
RUN apt-get update && apt-get install -y \
    git unzip curl libpq-dev libzip-dev nodejs npm \
    && docker-php-ext-install pdo pdo_pgsql zip \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Install PHP deps first (better layer caching)
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist --no-scripts

# Install frontend deps and build assets
COPY package.json package-lock.json* ./
RUN if [ -f package-lock.json ]; then npm ci; else npm install; fi
COPY . .
RUN composer dump-autoload --optimize
RUN php artisan package:discover --ansi
RUN npm run build

# Laravel runtime prep
RUN php artisan storage:link || true

EXPOSE 10000
CMD ["sh", "-c", "php artisan optimize:clear; php artisan migrate --force --seed; php artisan serve --host=0.0.0.0 --port=${PORT:-10000}"]