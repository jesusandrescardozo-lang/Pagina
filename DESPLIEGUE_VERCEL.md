# Despliegue en Vercel

Guía paso a paso para publicar **NUTRILINK** en Vercel.

> ✅ **Despliegue realizado el 4/11/2026 — estado: funcionando.**
>
> - Producción: **<https://hospital-triage-zeta.vercel.app>**
> - Región: `iad1` (Virginia), a ~15 ms de Neon (`us-east-2`)
> - Proyecto Vercel: `jesusandrescardozo-gmailcoms-projects/hospital-triage`
> - GitHub (`Pagina` → `main`) conectado: cada *push* despliega solo
> - Medido en caliente: `/` 0,35 s · `/login` 0,4–1,0 s · `/health` 0,34 s
> - Login de prueba: `admin@refugios.ve` / `Admin1234!`

---

## Por qué Vercel

Tu app hoy habla con Neon (Postgres en `us-east-2`, Ohio) desde Venezuela.
El RTT medido es de **~110–320 ms por consulta**, así que una sola página con
varias consultas tarda 2–4 segundos.

Vercel ejecuta las funciones en **`iad1` (Virginia)**, a ~15 ms de Neon.
Esperado: cada consulta baja de ~350 ms a ~50 ms, y las páginas optimizadas
(pasadas de 20 consultas a 2–8) deberían responder en **menos de 300 ms**.

> Vercel **no tiene runtime PHP oficial**. Se usa el **Container runtime con
> FrankenPHP** (PHP + Caddy), la vía documentada por Vercel:
> <https://vercel.com/kb/guide/laravel-php-with-docker>

---

## Qué se añadió al proyecto

| Archivo | Función |
|---|---|
| `Dockerfile.vercel` | Imagen de producción: FrankenPHP, `pdo_pgsql`, `intl`, `gd`, `bcmath`, OPcache. Genera `package:discover`, `event:cache` y `view:cache` en el build; `route:cache` **al arrancar** (ver §6). |
| `Caddyfile` | Servidor HTTP: sirve `public/` y reescribe todo lo demás a `index.php` en el puerto `PORT` de Vercel. |
| `routes/web.php` → `/health` | Prueba de humo: devuelve `{"status":"ok"}` sin tocar la base de datos. |
| `.dockerignore` | Excluye `tests/`, `docs/`, `.git/`, `*.md` y el `Dockerfile` de Render de la imagen. ⚠️ **buildah en Vercel lo ignora**: la limpieza crítica está en el propio `Dockerfile.vercel` (`rm -f bootstrap/cache/*.php .env`). |
| `docker-start.sh` | **Guardia**: aborta si detecta `VERCEL=1`, para que nunca se ejecute `migrate:fresh --seed` sobre Neon. |
| `.env.example` | Documenta todas las variables que hay que declarar en Vercel. |

Se mantienen **sin tocar** el `Dockerfile` y el `docker-start.sh` de Render, por
si sigues usándolo.

> ⚠️ **No ejecutes** `vercel project update --framework container`.
> Eso haría que Vercel usara el `Dockerfile` **de Render** (el que hace
> `migrate:fresh --seed` y **borraría tus datos**). El nombre `Dockerfile.vercel`
> ya le indica a Vercel cuál usar, y la guardia de `docker-start.sh` es la red
> de seguridad por si acaso.

---

## 0. Requisitos

- Cuenta en <https://vercel.com>
- Node.js (ya lo tienes: v24) y npm
- La CLI de Vercel
- Docker Desktop (**solo** si quieres probar la imagen en local con `vercel dev`)

```bash
npm install -g vercel
vercel --version
```

---

## 1. Variables de entorno

En Vercel **no existe fichero `.env`**: la configuración llega como variables
inyectadas en el contenedor.

Genera la clave de aplicación:

```bash
php artisan key:generate --show
```

Copia el valor (empieza por `base64:`) y declara cada variable:

```bash
vercel env add APP_KEY production
vercel env add APP_URL production
vercel env add DB_PASSWORD production
# ... repite con cada una de la lista
```

**Lista completa** (valores entre `<...>` hay que sustituirlos):

| Variable | Valor |
|---|---|
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_KEY` | `base64:...` (salida de `key:generate --show`) |
| `APP_URL` | `https://<tu-proyecto>.vercel.app` |
| `APP_TIMEZONE` | `America/Caracas` |
| `LOG_CHANNEL` | `stderr` |
| `LOG_LEVEL` | `error` |
| `DB_CONNECTION` | `pgsql` |
| `DB_HOST` | `ep-snowy-lab-b47a57pb.c-6.us-east-2.aws.neon.tech` |
| `DB_PORT` | `5432` |
| `DB_DATABASE` | `neondb` |
| `DB_USERNAME` | `neondb_owner` |
| `DB_PASSWORD` | *(la de Neon)* |
| `DB_SSLMODE` | `require` |
| `DB_ENDPOINT_ID` | `ep-snowy-lab-b47a57pb` |
| `DB_PERSISTENT` | `true` |
| `DB_QUERY_LOG` | `false` |
| `SESSION_DRIVER` | `database` |
| `CACHE_STORE` | `database` |
| `QUEUE_CONNECTION` | `sync` |
| `APP_MAINTENANCE_DRIVER` | `database` |

Notas:

- **No uses el host con `-pooler`** de Neon: rompe las transacciones
  multi-sentencia (`SQLSTATE 25P02`).
- `SESSION_DRIVER` y `CACHE_STORE` **deben ser `database`**: el disco del
  contenedor es efímero y no se comparte entre instancias. Las tablas
  `sessions` y `cache` ya existen en Neon.
- `LOG_CHANNEL=stderr`: Vercel captura `stdout`/`stderr`; no hay disco para
  ficheros de log.
- `QUEUE_CONNECTION=sync`: la app no despacha trabajos de cola ni guarda
  ficheros en disco, así que no necesita colas ni Blob.

---

## 2. Base de datos

Las **12 migraciones ya están aplicadas** en Neon, así que no hay nada que
correr ahora. Cuando cambies el esquema en el futuro, ejecuta (desde tu
máquina, apuntando a Neon):

```bash
php artisan migrate --force
```

**Nunca** se ejecuta `migrate` al arrancar el contenedor.

Revisa también Neon:

1. **IP Allow**: si está activo en el proyecto, las IPs salientes de Vercel
   quedarían bloqueadas. Déjalo desactivado (o añade las de Vercel).
2. **Compute**: confirma que el endpoint está en *Auto-suspend* (se activa solo).

---

## 3. Desplegar

```bash
cd "C:\Users\jesus\OneDrive\Escritorio\Proyecto de IDO\hospital-triage"

vercel login
vercel deploy --prod --yes
```

La CLI imprime la URL al final. Producción actual:

```
https://hospital-triage-zeta.vercel.app
```

Verifica:

```bash
curl https://hospital-triage-zeta.vercel.app/health
# esperado: {"status":"ok","time":"..."}
```

Abre `/login` en el navegador y entra con:

```
admin@refugios.ve / Admin1234!
```

**Variables ya declaradas** (25 en total, en *Production*, *Preview* y
*Development*): usa la tabla del paso 1 con
`vercel env add NOMBRE --environment production --value VALOR --force`
(o el script `set-env-vercel.ps1` para hacerlo en bloque).
`APP_URL=https://hospital-triage-zeta.vercel.app` está configurado en los
tres entornos.

---

## 4. (Opcional) Despliegue desde Git

✅ **Ya conectado**: el repositorio `Pagina` (GitHub) está enlazado al
proyecto de Vercel con la rama `main` como producción. Cada `git push origin
main` crea un despliegue automático.

```bash
git push origin main
```

`Dockerfile.vercel` tiene prioridad sobre la detección de framework de Laravel,
así que Vercel construirá el contenedor y no intentará ejecutar `php artisan`
en su build de Node.

---

## 5. Verificación y medición

Una vez en producción, comprueba la mejora real:

```bash
# 1. Sanidad
curl -i https://<tu-proyecto>.vercel.app/health

# 2. Tiempos de respuesta (con sesión iniciada en el navegador)
```

En local mediste (contra Neon desde Venezuela):

| Página | Antes | Después de optimizar |
|---|---|---|
| `/` | 3038 ms | 43 ms |
| `/login` | 1549 ms | 29 ms |
| `/shelters` | 3198 ms | 46 ms |
| `/supplies` | 3005 ms | 64 ms |
| `/shelters/5` (fría) | 7802 ms / 22 consultas | 3892 ms / 8 consultas |
| `/shelters/5` (caché) | — | 681 ms / 2 consultas |

**Medido en Vercel (`iad1`, en caliente, 4 peticiones):**

| Ruta | Código | Tiempo |
|---|---|---|
| `/health` | 200 | 0,34 – 0,54 s |
| `/login` | 200 | 0,43 – 1,05 s |
| `/` (sin sesión → redirect) | 302 | 0,33 – 0,36 s |
| `/dashboard` (con sesión) | 200 | ~0,4 – 0,6 s |
| `/shelters` (con sesión) | 200 | ~0,9 s |
| `/supplies` (con sesión) | 200 | ~1,0 s |

El primer request tras el reposo de la instancia tarda más (arranque en
frío, ~2–3 s).

---

## 6. Solución de problemas

| Síntoma | Causa probable | Solución |
|---|---|---|
| **502** al abrir cualquier ruta | Error de arranque de FrankenPHP o Caddyfile inválido | *Deployments → Logs* (stdout/stderr del contenedor) |
| **502** + log `docker-start.sh: entorno Vercel detectado` | Vercel está usando el `Dockerfile` de Render | Comprueba que `Dockerfile.vercel` está en la raíz; **no** uses `vercel project update --framework container` |
| **500** con error de conexión a base de datos | IP Allow de Neon activo, o variable mal declarada | Revisa IP Allow y las variables `DB_*` |
| **500** `No application encryption key` | Falta `APP_KEY` | `vercel env add APP_KEY production` y vuelve a desplegar |
| **500** `Class "NunoMaduro\Collision\...CollisionServiceProvider" not found` | buildah **ignora `.dockerignore`**: entró `bootstrap/cache/services.php` local (con paquetes DEV) a la imagen | El `Dockerfile.vercel` ya ejecuta `rm -f bootstrap/cache/*.php .env` antes de las cachés. Si reaparece, revisa que ese paso siga ahí |
| **500** `InvalidSignatureException: Your serialized closure might have been modified` | `route:cache` se generó en el build, donde **no existe la `APP_KEY` real** (está en Vercel): las closures de las rutas se firman con una clave vacía y no coinciden en runtime | `route:cache` se ejecuta **al arrancar el contenedor** (CMD del `Dockerfile.vercel`), cuando la clave ya está disponible. No lo añadas al build |
| **500** + log `Target class [translator] does not exist` | Síntoma secundario: la página de error 500 no puede renderizarse | Ignora este error y busca la **primera** excepción del request en `vercel logs` |
| **404/401 con página de Vercel** en producción | *Deployment Protection → Vercel Authentication* activo | API: `PATCH /v9/projects/{id}` con `{"ssoProtection":{"deploymentType":"preview"}}` (protege solo previews, deja producción pública). En el dashboard: *Settings → Deployment Protection* |
| Se cae la sesión entre peticiones | `SESSION_DRIVER` no es `database` | Corrige la variable y redeploya |
| Errores de plantilla / vistas vacías | Caché de vistas desactualizada | El build ya ejecuta `view:cache`; reintenta el deploy |
| Los logs no aparecen | `LOG_CHANNEL` no es `stderr` | Corrige la variable |

Para ver los logs de la última ejecución:

```bash
vercel logs <url-del-deployment>
# Filtra solo las líneas de una petición concreta:
#   vercel logs <url> | Select-String '<requestId>'
```

Consejo: `php.ini-production` trae `display_errors=Off`, que **oculta** los
errores de artisan durante el build. El `Dockerfile.vercel` lo activa solo
durante el paso de cachés (`zz-build-debug.ini`) y lo quita después.

---

## 7. Límites a tener en cuenta

- **Container runtime está en beta** en todos los planes; se cobra por *Active
  CPU* (solo cuando el código corre, no durante la espera de I/O).
- Las instancias **se apagan a los 5 minutos** sin tráfico (30 s en preview).
  El primer request tras el reposo tarda más (arranque en frío).
- **No hay IPs salientes fijas** ni *Secure Compute*: si algún servicio externo
  requiere allow-list de IP, no funcionará desde Vercel.
- No programes *cron* ni colas dentro del contenedor: se detienen con la
  instancia. Usa [Vercel Cron Jobs](https://vercel.com/docs/cron-jobs) si lo
  necesitas.
- El fichero `public/_t.php` (que contenía credenciales de Neon) y los scripts
  de diagnóstico de la raíz fueron **eliminados** antes de este despliegue.
