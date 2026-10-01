#!/usr/bin/env sh
# Arranque del servicio en Render: puerto, APP_KEY, base de datos y Apache.
set -e

# 1) Puerto asignado por Render (10000 por defecto)
sed -ri "s/^Listen .*/Listen ${PORT:-10000}/" /etc/apache2/ports.conf

# 2) .env minimo solo con APP_KEY si no existe (clave distinta por instancia).
#    El resto de la configuracion llega como variables de entorno de Render.
if [ ! -f .env ]; then
  printf 'APP_KEY=base64:%s\n' "$(head -c 32 /dev/urandom | base64 | tr -d '\n')" > .env
fi

# 3) Base de datos demo: SQLite efemera, se reconstruye en cada arranque
#    (cada deploy/reinicio vuelve al estado sembrado de demostracion)
php artisan migrate:fresh --force --seed

# 4) Vhost garantizado: DocumentRoot public/ + rewrite a index.php.
#    Se reescribe el fichero canonico y se REHABILITA el sitio con symlink
#    limpio, para no depender de como venga configurada la imagen
#    (si sites-enabled trae una copia en vez de enlace, se sustituye).
cat > /etc/apache2/sites-available/000-default.conf <<'EOF'
<VirtualHost *:80>
    ServerAdmin webmaster@localhost
    DocumentRoot /var/www/html/public

    <Directory /var/www/html/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted

        RewriteEngine On
        RewriteCond %{HTTP:Authorization} .
        RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
        RewriteCond %{REQUEST_FILENAME} !-d
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteRule ^ index.php [L]
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
EOF
rm -f /etc/apache2/sites-enabled/*
a2ensite 000-default

# 5) Diagnostico visible en los logs de Render (ver DocumentRoot real cargado)
{
  echo "[diag] sites-enabled:"
  ls -la /etc/apache2/sites-enabled/
  echo "[diag] vhost que carga Apache:"
  apache2ctl -S
  echo "[diag] modulos:"
  apache2ctl -M | grep -E "rewrite_module|php_module"
  echo "[diag] index.php:"
  ls -la /var/www/html/public/index.php
} || true

# 6) Comprobacion de sintaxis obligatoria (aborta el arranque si el vhost esta mal)
apache2ctl configtest

# 7) Apache en primer plano (Render exige que el proceso principal escuche el puerto)
exec apache2-foreground
