# NutriLnk — Sistema de Gestión de Inventarios Médicos y Alimentarios en Refugios

Aplicación web para la **gestión de inventarios de insumos médicos y alimentos en refugios de emergencia**, desarrollada con Laravel 9 (PHP 8.0+). Proyecto del área de Investigación de Operaciones (EOQ, Newsvendor, CPM) para la cadena logística humanitaria de reabastecimiento entre albergues.

## Características principales

- **Refugios e insumos**: censo poblacional, catálogo maestro de insumos con clasificación ABC y parámetros de inventario.
- **Kardex estricto**: trazabilidad de entradas/salidas con saldo anterior/nuevo y documento de referencia.
- **Lotes FEFO** (*First Expired, First Out*): despacho por fecha de vencimiento con bloqueo de fila (`lockForUpdate`) para evitar sobre-despacho.
- **Transferencias inter-refugios**: máquina de estados estricta (`borrador → aprobada → en_tránsito → recibida / cancelada`) con deducción FEFO al despachar y restauración de stock/lotes al cancelar.
- **Modelos de inventario (I.O.)**: cálculo EOQ, punto de reorden, stock de seguridad y Newsvendor; clasificación ABC.
- **CPM (Ruta Crítica)**: registro de proyectos, actividades, cálculo ES/EF/LS/lf y holgura con grafo interactivo.
- **Reportes PDF** (dompdf) y **bitácora de auditoría** de todas las acciones.
- **Seguridad**: CSRF habilitado, *throttling* de intentos de login y matriz de permisos unificada en Gates (`admin`, `coordinador_refugio`, `almacenista`, `auditor`). *El acceso 2FA/Google Authenticator fue retirado del flujo: el login va directo al panel con contraseña.*
- **Borrado lógico** de refugios/insumos: eliminar ya no destruye en cascada inventarios, lotes ni kardex.

## Requisitos

- PHP 8.0 o superior (`pdo_sqlite`, `mbstring`, `openssl`, `tokenizer`, `xml`)
- Composer
- No requiere servidor de BD: usa **SQLite** (se crea automáticamente)

## Instalación

```bash
git clone https://github.com/USUARIO/hospital-triage.git
cd hospital-triage
composer install
copy .env.example .env
type nul > database\database.sqlite   # Linux/macOS: touch database/database.sqlite
php artisan key:generate
php artisan migrate:fresh --seed      # crea tablas + datos de demostración
php artisan serve                     # http://localhost:8000
```

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

El proyecto incluye suites de verificación propias (sin PHPUnit):

```bash
php tests/test_crud_forms.php            # FormRequests + soft deletes (24 aserciones)
php tests/test_transfer_state_machine.php # estados de transferencia + FEFO (23)
php tests/test_authorization_matrix.php  # matriz de permisos (24)
php tests/test_direct_auth.php           # login directo sin 2FA (4 aserciones)
php tests/test_e2e_login_http.php        # E2E login directo por HTTP (requiere serve)
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
