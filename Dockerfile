# syntax=docker/dockerfile:1
# Radio ISDB API — Laravel 13 + Filament v4, served by Apache + mod_php.
FROM php:8.3-apache

# --- System libraries for the PHP extensions we need --------------------------
RUN apt-get update && apt-get install -y --no-install-recommends \
        libpq-dev libzip-dev libicu-dev libpng-dev libjpeg62-turbo-dev \
        libfreetype6-dev unzip git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" pdo_pgsql pgsql gd zip intl bcmath exif opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# --- Composer --------------------------------------------------------------
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# --- Apache: serve Laravel's public/, allow .htaccess rewrites -----------
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
COPY docker/000-default.conf /etc/apache2/sites-available/000-default.conf
RUN a2enmod rewrite headers

WORKDIR /var/www/html

# --- PHP dependencies (cached layer) ---------------------------------------
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction

# --- Application code -----------------------------------------------------
COPY . .
# Classmap only — artisan scripts (package:discover, filament:assets) run at
# startup in the entrypoint, once the real environment is present.
RUN composer dump-autoload --optimize --no-dev --no-scripts --no-interaction \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R ug+rwX storage bootstrap/cache

# Render injects $PORT at runtime; entrypoint points Apache at it.
ENV PORT=8080
EXPOSE 8080

COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["apache2-foreground"]
