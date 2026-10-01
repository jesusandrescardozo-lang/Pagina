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

# Apache: DocumentRoot en public/ con rewrite INLINE en el VirtualHost.
# Se escribe el vhost completo (sin sed) para no depender de AllowOverride
# ni de que el .htaccess se interprete: /login, /dashboard, etc. van a index.php.
RUN a2enmod rewrite \
    && printf '%s\n' \
       '<VirtualHost *:80>' \
       '    ServerAdmin webmaster@localhost' \
       '    DocumentRoot /var/www/html/public' \
       '' \
       '    <Directory /var/www/html/public>' \
       '        Options -Indexes +FollowSymLinks' \
       '        AllowOverride All' \
       '        Require all granted' \
       '' \
       '        RewriteEngine On' \
       '        RewriteCond %{HTTP:Authorization} .' \
       '        RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]' \
       '        RewriteCond %{REQUEST_FILENAME} !-d' \
       '        RewriteCond %{REQUEST_FILENAME} !-f' \
       '        RewriteRule ^ index.php [L]' \
       '    </Directory>' \
       '' \
       '    ErrorLog ${APACHE_LOG_DIR}/error.log' \
       '    CustomLog ${APACHE_LOG_DIR}/access.log combined' \
       '</VirtualHost>' \
       > /etc/apache2/sites-available/000-default.conf

# Codigo + dependencias (vendor/, .git y .env quedan fuera por .dockerignore)
COPY . .

# Diagnostico obligatorio: si falta index.php/.htaccess o la config de Apache,
# el build FALLA aqui (en vez de un 404 misterioso en runtime).
RUN test -f public/index.php \
    && test -f public/.htaccess \
    && test -f docker-start.sh \
    && echo "=== Diagnostico Apache ===" \
    && grep -n "DocumentRoot\|<Directory\|RewriteEngine" /etc/apache2/sites-available/000-default.conf \
    && apache2ctl -M 2>/dev/null | grep "rewrite_module" \
    && apache2ctl -M 2>/dev/null | grep "php_module" \
    && echo "=== public/ ===" \
    && ls -la public/
# storage/ no esta rastreada en git (solo contiene artefactos ignorados),
# asi que se crea la estructura completa aqui, con permisos para Apache.
RUN mkdir -p storage/app storage/framework/cache/data storage/framework/sessions \
        storage/framework/views storage/framework/testing storage/logs \
    && composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader \
    && touch database/database.sqlite \
    && chown -R www-data:www-data storage bootstrap/cache database \
    && chmod +x docker-start.sh

# El arranque (puerto de Render, APP_KEY y base demo) vive en docker-start.sh
CMD ["sh", "docker-start.sh"]
