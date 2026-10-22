# NUTRILINK — Sistema de Gestión de Inventarios Médicos y Alimentarios en Refugios

Aplicación web para la **gestión de inventarios de insumos médicos y alimentos en refugios de emergencia**, desarrollada con Laravel 9 (PHP 8.0+). Proyecto del área de Investigación de Operaciones (EOQ, Newsvendor) para la cadena logística humanitaria de reabastecimiento entre albergues.

## Características principales

- **Refugios e insumos**: censo poblacional, catálogo maestro de insumos con clasificación ABC y parámetros de inventario.
- **Kardex estricto**: trazabilidad de entradas/salidas con saldo anterior/nuevo y documento de referencia.
- **Lotes FEFO** (*First Expired, First Out*): despacho por fecha de vencimiento con bloqueo de fila (`lockForUpdate`) para evitar sobre-despacho.
- **Transferencias inter-refugios**: máquina de estados estricta (`borrador → aprobada → en_tránsito → recibida / cancelada`) con deducción FEFO al despachar y restauración de stock/lotes al cancelar.
- **Modelos de inventario (I.O.)**: cálculo EOQ, punto de reorden, stock de seguridad y Newsvendor; clasificación ABC.
- **Reportes PDF** (dompdf) y **bitácora de auditoría** de todas las acciones.
- **Seguridad**: CSRF habilitado, *throttling* de intentos de login, registro de cuentas restringido a dominios de correo institucionales (`REGISTRO_DOMINIOS`) y matriz de permisos unificada en Gates (`admin`, `coordinador_refugio`, `almacenista`, `auditor`). *El acceso 2FA/Google Authenticator fue retirado del flujo: el login va directo al panel con contraseña.*
- **Borrado lógico** de refugios/insumos: eliminar ya no destruye en cascada inventarios, lotes ni kardex.

## Requisitos

- PHP 8.0 o superior (`pdo_pgsql` o `pdo_sqlite`, `mbstring`, `openssl`, `tokenizer`, `xml`)
- Composer
- Base de datos: **SQLite** (desarrollo local, sin servidor) o **PostgreSQL/Neon** (producción). El driver se elige en `.env` (`DB_CONNECTION`).

## Instalación

```bash
git clone https://github.com/USUARIO/hospital-triage.git
cd hospital-triage
composer install
copy .env.example .env
type nul > database\database.sqlite   # Linux/macOS: touch database/database.sqlite
php artisan key:generate
```

Configura la base de datos en `.env`:

- **Desarrollo local (SQLite, sin servidor):**
  ```dotenv
  DB_CONNECTION=sqlite
  DB_DATABASE=database/database.sqlite
  ```
- **Producción (Neon / PostgreSQL):** usa la cadena de conexión del panel de Neon en `DATABASE_URL` y revisa el bloque `# ── Base de datos: Neon` de `.env.example`.

```bash
php artisan migrate:fresh --seed      # crea tablas + datos de demostración
php artisan serve                     # http://localhost:8000
```

### Variable `REGISTRO_DOMINIOS`

Controla el auto-registro de cuentas de empleado desde la pantalla de login:

```dotenv
REGISTRO_DOMINIOS=refugios.ve,refugios.org   # lista separada por comas
```

- Solo se aceptan correos de esos dominios; el enlace "Crear cuenta de empleado" se oculta si la lista está vacía.
- Dejarla vacía (`REGISTRO_DOMINIOS=`) **desactiva el registro** por completo.
- Las cuentas creadas quedan con rol `operador` (solo lectura) hasta que un administrador asigne un rol con permisos.


> Detalle completo en [`MANUAL_DE_USUARIO.md`](MANUAL_DE_USUARIO.md).

## Usuarios de demostración

| Rol | Correo | Contraseña |
|---|---|---|
| Admin General | `admin@refugios.ve` | `Admin1234!` |
| Coordinador de Albergue | `coordinador@refugios.ve` | `Coordinador1234!` |
| Encargada de Almacén | `almacen@refugios.ve` | `Almacen1234!` |
| Auditora Humanitaria | `auditor@refugios.ve` | `Auditor1234!` |

> Acceso directo: solo se requiere contraseña (el paso 2FA/Google Authenticator fue retirado del flujo).

## Pruebas

El proyecto incluye suites de verificación propias (sin PHPUnit). **Todo se ejecuta con un único comando:**

```bash
php tests/run_all.php            # suite completa (20 suites)
php tests/run_all.php --quick    # solo las pruebas rápidas
php tests/run_all.php --verbose  # salida detallada de cada aserción
```

### Pruebas de corrección de código

Verifican que la aplicación arranca sin errores y que no quedan fallos estructurales:

| Suite | Qué comprueba |
|---|---|
| `tests/diagnostico.php` | Integridad general: `.env`, `APP_KEY`, extensiones PHP, base de datos y rutas |
| `tests/test_integrity.php` | Sintaxis de todo el código (`php -l`), los 13 archivos `config/`, `php artisan`, caché, log, RateLimiter, esquema de tablas y ausencia de `dd()`/`var_dump()`/`APP_DEBUG=true` |
| `tests/test_http_smoke.php` | Recorrido HTTP de las 55 rutas × 5 roles (invitado, admin, coordinador, almacén, auditor) + matriz de permisos |
| `tests/test_smoke_rapido.php` | Renderizado sin errores (200) de las páginas principales autenticadas y públicas |
| `tests/test_pages.php` | Renderizado de páginas clave, credenciales demo del login y coherencia con el seeder |

### Pruebas de lógica de negocio

```bash
php tests/test_crud_forms.php             # FormRequests + soft deletes
php tests/test_validations.php            # validaciones de negocio (Kardex, transferencias, throttle)
php tests/test_registro.php               # registro institucional (dominios permitidos)
php tests/test_registro_login.php         # flujo completo registro -> login -> dashboard
php tests/test_cache_version.php          # invalidación de caché analítica por versión
php tests/test_transfer_state_machine.php # estados de transferencia + FEFO
php tests/test_authorization_matrix.php   # matriz de permisos
php tests/test_role_permissions.php       # permisos por rol
php tests/test_direct_auth.php            # login directo sin 2FA
php tests/test_shelter_switching.php      # aislamiento por refugio
php tests/test_google2fa_system.php       # sistema 2FA
php tests/test_totp_algo.php              # algoritmo TOTP RFC 6238
php tests/test_ido.php                    # métricas de IDO
php tests/test_full_verification.php      # contenido de dashboard/login
```

### Pruebas end-to-end (HTTP)

Requieren el servidor local (`php artisan serve`); si no está activo se marcan como **omitidas**:

```bash
php tests/test_http_flow.php       # login + recorrido completo por HTTP
php tests/test_e2e_login_http.php  # login E2E de los 4 roles por HTTP
```

## Stack tecnológico

- **Backend**: Laravel 9 · PHP 8.0 · SQLite
- **Seguridad**: bacon/bacon-qr-code (QR local), TOTP RFC 6238
- **Frontend**: Blade + Tailwind (CDN) + Chart.js / Vis.js

## Despliegue en Render

El repositorio incluye `Dockerfile`, `docker-start.sh` y `.dockerignore` (Render no ofrece PHP nativo, así que usa runtime Docker):

1. En [dashboard.render.com](https://dashboard.render.com) → **New → Web Service** → pestaña *Git Provider* → conecta tu cuenta de GitHub y selecciona este repositorio.
2. *Language*: **Docker** (Render detecta el `Dockerfile`; no llenes Build/Start Command).
3. *Branch*: `main` → región **Virginia** → plan **Free**.
4. En *Advanced* → *Health Check Path*: `/login` → **Create Web Service**.

Al arrancar, `docker-start.sh` genera la `APP_KEY`, ejecuta `php artisan migrate:fresh --force --seed` (datos demo) y arranca Apache escuchando en el puerto de Render. La SQLite es efímera: **cada deploy o reinicio vuelve al estado demo** (si necesitas datos persistentes, hay que añadir un *Persistent Disk* o PostgreSQL de Render).

## Notas de mantenimiento

- `composer audit`: las 21 advisories restantes pertenecen a Laravel 9 / dompdf 2 / symfony 6.0, ramas **EOL sin parche para PHP 8.0**; se resuelven únicamente con el salto a PHP 8.2+ y Laravel 12.
- La base de datos (`database/*.sqlite`), el `.env` y `vendor/` **nunca** se suben al repositorio (reglas en `.gitignore`).

## Licencia

MIT
