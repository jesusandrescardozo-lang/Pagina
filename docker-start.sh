#!/usr/bin/env sh
# Arranque del servicio en Render: puerto, APP_KEY y base de datos demo.
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

# 4) Apache en primer plano (Render exige que el proceso principal escuche el puerto)
exec apache2-foreground
