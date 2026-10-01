# Plataforma Humanitaria — despliegue en Render (runtime Docker)
# Render no ofrece PHP nativo, así que el servicio se construye con esta imagen.
FROM php:8.1-apache

# Extensiones PHP: Laravel 9 + SQLite + dompdf (GD para imágenes de PDF)
RUN apt-get update && apt-get install -y --no-install-recommends \
        libonig-dev libzip-dev libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
        libsqlite3-dev pkg-config unzip git \
    && docker-php-ext-configure gd --with-jpeg --with-freetype \
    && docker-php-ext-install -j"$(nproc)" pdo_sqlite mbstring zip gd \
    && rm -rf /var/lib/apt/lists/*

# Composer 2
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Apache atiende desde public/ y respeta el .htaccess de Laravel (rutas /login, etc.)
RUN a2enmod rewrite \
    && sed -ri -e 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf \
    && sed -ri -e 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# Codigo + dependencias (vendor/, .git y .env quedan fuera por .dockerignore)
COPY . .
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader \
    && touch database/database.sqlite \
    && chown -R www-data:www-data storage bootstrap/cache database \
    && chmod +x docker-start.sh

# El arranque (puerto de Render, APP_KEY y base demo) vive en docker-start.sh
CMD ["sh", "docker-start.sh"]
