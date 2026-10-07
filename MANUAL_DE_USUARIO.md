# MANUAL DE USUARIO Y DOCUMENTACIÓN TÉCNICA
## SISTEMA DE GESTIÓN Y OPTIMIZACIÓN DE INVENTARIOS DE INSUMOS MÉDICOS Y ALIMENTOS EN REFUGIOS
### Cátedra: Investigación de Operaciones (I.O. / IDO)

---

### 1. INTRODUCCIÓN Y PROPÓSITO HUMANITARIO
El sistema **REFUGIO-IO** tiene por propósito la planificación, monitoreo y optimización cuantitativa de la cadena de suministro y stock de insumos médicos vitales y alimentos de primera necesidad en refugios y albergues temporales ante situaciones de catástrofe o emergencia humanitaria.

A través de modelos matemáticos clásicos y avanzados de **Investigación de Operaciones (I.O.)**, el sistema previene el desabastecimiento de alimentos y medicamentos esenciales en refugios saturados, minimiza el costo total de inventario y elimina el desperdicio de productos perecederos mediante reglas de despacho **FEFO** (First Expired, First Out).

---

### 2. MODELO ENTIDAD-RELACIÓN (ER) Y ARQUITECTURA DE DATOS

El sistema cuenta con las siguientes entidades principales en su esquema relacional:

1. **`shelters` (Refugios / Albergues)**: Centros de recepción comunitaria con registro de capacidad máxima, población total albergada desglosada por grupos vulnerables (adultos, niños/lactantes, adultos mayores, pacientes con atención médica prioritaria), estado operativo y responsable.
2. **`supplies` (Insumos Médicos y Alimentos)**: Catálogo maestro con parámetros cuantitativos de I.O., clasificación ABC, tasa de consumo per cápita/día, costos de ordenar ($S$), costos de mantenimiento ($H$), lead time ($L$) y costos Newsvendor ($C_u, C_o$).
3. **`shelter_inventories`**: Registro consolidado de existencias de cada insumo en cada refugio, niveles de stock mínimo, máximo y punto de reorden (ROP).
4. **`inventory_batches` (Lotes y Vencimientos)**: Trazabilidad por número de lote, fecha de ingreso y fecha de caducidad con aplicación automática del criterio **FEFO**.
5. **`inventory_movements` (Kardex Inmutable)**: Registro riguroso de cada entrada (donaciones, compras, transferencias entrantes) y salida (consumo diario de población, transferencias salientes, mermas por vencimiento) con stock anterior y resultante.
6. **`supply_transfers` (Rebalanceo Logístico Inter-Refugios)**: Órdenes de transferencia recomendadas por algoritmos heurísticos entre refugios donantes (con superávit) y refugios receptores (con déficit crítico).
7. **`audit_logs`**: Trazabilidad completa de acciones, IPs, timestamps y cambios de estado.
8. **`users`**: Gestión de accesos con roles: `admin` (Coordinador General), `coordinador_refugio`, `almacenista` y `auditor`.

---

### 3. MODELOS MATEMÁTICOS DE INVESTIGACIÓN DE OPERACIONES (I.O.)

#### A. Modelo EOQ (Cantidad Económica de Pedido / Wilson)
Diseñado para suministros estándar no perecederos o de demanda regular. Minimiza la suma anual de costos de emisión de órdenes y costos de posesión:

$$\text{EOQ} = Q^* = \sqrt{\frac{2 D S}{H}}$$

- **Punto de Reorden (ROP)**:
  $$\text{ROP} = (d \cdot L) + SS = \left(\frac{D}{365} \cdot L\right) + SS$$
- **Número óptimo de pedidos al año**: $N = \frac{D}{Q^*}$
- **Tiempo entre pedidos**: $T = \left(\frac{Q^*}{D}\right) \times 365 \text{ días}$
- **Costo Total Anual de Inventario**:
  $$\text{TC} = \left(\frac{D}{Q^*}\right) S + \left(\frac{Q^*}{2}\right) H + (D \cdot C)$$

#### B. Modelo Newsvendor / Período Único (Insumos Perecederos y Kits de Crisis)
Aplicado a insumos críticos con vida útil corta o fórmulas infantiles perecederas, donde la demanda en crisis es probabilística $D \sim \mathcal{N}(\mu, \sigma)$:
- **Costo de Subestimación / Escasez ($C_u$)**: Costo o impacto humanitario por la falta de una ración o kit médico vital ante una emergencia.
- **Costo de Sobreestimación / Desecho ($C_o$)**: Pérdida económica por producto vencido no consumido.
- **Razón Crítica (Nivel de Servicio Óptimo)**:
  $$\text{CR} = \frac{C_u}{C_u + C_o}$$
- **Cantidad Óptima a Mantener**:
  $$Q^* = \mu + Z \cdot \sigma, \quad \text{donde } \Phi(Z) = \text{CR}$$

#### C. Matriz de Días de Autonomía y Runway Humanitario
$$\text{Días de Autonomía} = \frac{\text{Stock Disponible}}{\text{Población Albergada} \times \text{Consumo Diario Per Cápita}}$$
- 🔴 **Crítico ($< 2$ días)**: Alerta roja de desabastecimiento inminente.
- 🟡 **Precaución ($2 - 5$ días)**: Reabastecimiento requerido a corto plazo.
- 🟢 **Óptimo ($> 5$ días)**: Cobertura humanitaria garantizada.

#### D. Algoritmo Heurístico de Rebalanceo Inter-Refugios
El algoritmo analiza continuamente la red:
1. Detecta refugios con déficit inminente ($< 2.5$ días de autonomía).
2. Identifica refugios con excedentes ($> 6.0$ días de autonomía para el mismo insumo).
3. Calcula la cantidad óptima a transferir para nivelar la cobertura del receptor a un mínimo seguro (4 días) sin desproteger al refugio donante, generando la orden de transferencia sin incurrir en compras externas.

---

### 4. GUÍA DE PUESTA EN MARCHA (DESPLIEGUE)

1. **Requisitos de Entorno**:
   - PHP 8.0 o superior (con extensiones `pdo_sqlite`, `mbstring`, `openssl`, `tokenizer`, `xml`).
   - Composer.
   - (Opcional) XAMPP u otro servidor, solo si se desea usar MySQL en lugar de SQLite.

2. **Ubicación del Proyecto**:
   ```
   cd "c:\Users\jesus\OneDrive\Escritorio\Proyecto de IDO\hospital-triage"
   ```

3. **Instalar dependencias** (solo la primera vez o tras clonar el repositorio):
   ```bash
   composer install
   ```

4. **Configuración de Variables de Entorno (`.env`)**:
   Si el archivo `.env` no existe, copiar el de ejemplo:
   ```bash
   copy .env.example .env
   ```
   El proyecto usa **SQLite** (no requiere instalar servidor de base de datos):
   ```env
   DB_CONNECTION=sqlite
   DB_DATABASE=database/database.sqlite
   ```
   Crear el archivo de base de datos si no existe:
   ```bash
   type nul > database\database.sqlite
   ```
   Generar la llave de cifrado de la aplicación:
   ```bash
   php artisan key:generate
   ```

5. **Ejecutar Migraciones y Poblado de Datos (Seeders)**:
   ```bash
   php artisan migrate:fresh --seed
   ```
   > ⚠️ `migrate:fresh` **borra y regenera** toda la base de datos. Ejecutarlo solo la primera vez o cuando se quieran restaurar los datos de demostración.

6. **Iniciar Servidor Local**:
   ```bash
   php artisan serve
   ```
   Abrir en el navegador: `http://localhost:8000`

   > Acceso directo con correo y contraseña (el paso 2FA/Google Authenticator fue retirado del flujo de inicio de sesión).

---

### 5. USUARIOS DEMO Y CREDENCIALES DE ACCESO

| Rol Operativo | Correo Electrónico | Contraseña | Alcance de Funciones |
|---|---|---|---|
| **Admin General de Logística** | `admin@refugios.ve` | `Admin1234!` | Acceso total, rebalanceo inter-refugios, simulación I.O. y reportes |
| **Coordinador de Albergue** | `coordinador@refugios.ve` | `Coordinador1234!` | Censo de población, solicitud de suministros y consumos en sitio |
| **Encargada de Almacén** | `almacen@refugios.ve` | `Almacen1234!` | Recepción de donaciones, control de lotes y despacho en convoy |
| **Auditora Humanitaria** | `auditor@refugios.ve` | `Auditor1234!` | Consulta de libros Kardex, bitácora de auditoría y reportes PDF |

> En la pantalla de inicio de sesión, la barra **«Rápido»** permite rellenar cada cuenta de demostración con un solo clic. El campo contraseña se deja vacío por seguridad: se escribe una sola vez.

### 6. VERIFICACIÓN AUTOMÁTICA (PRUEBAS DE CORRECCIÓN DE CÓDIGO)

El sistema incluye una suite propia de verificación (sin PHPUnit) que demuestra que la aplicación **se ejecuta sin errores**:

```bash
php tests/run_all.php          # suite completa → 16 correctas · 0 fallos · 0 omitidas
php tests/run_all.php --quick  # versiones rápidas
```

Pruebas de corrección de código (garantizan el arranque limpio):

| Suite | Qué comprueba |
|---|---|
| `tests/diagnostico.php` | Entorno, `.env`, `APP_KEY`, extensiones y base de datos |
| `tests/test_integrity.php` | Sintaxis de todo el código, los 13 archivos `config/`, migraciones de caché/trabajos, caché/log/rutas y ausencia de depuración (`dd()`, `APP_DEBUG=true`) |
| `tests/test_http_smoke.php` | Las 55 rutas responden y respetan la matriz de permisos de los 4 roles + invitado |
| `tests/test_pages.php` | Las páginas clave renderizan y las credenciales demo coinciden con el seeder |

Complementos: `test_crud_forms.php`, `test_transfer_state_machine.php`, `test_authorization_matrix.php`, `test_role_permissions.php`, `test_direct_auth.php`, `test_shelter_switching.php`, `test_google2fa_system.php`, `test_totp_algo.php`, `test_ido.php`, `test_full_verification.php` y las suites E2E por HTTP (`test_http_flow.php`, `test_e2e_login_http.php`), que se omiten automáticamente si no hay servidor activo.

### 7. INTERFAZ, PALETA Y TEMAS

- **Paleta oficial:** neutros sobrios de base carbón (`#121519` / `#171b21`) con **ámbar suave** (`#e6a86c`, `#d89656`, `#c88340`) como único acento de marca. Sin gradientes corporativos, sin halos ni puntos decorativos; radios contenidos (8/12/16 px) y sombras sutiles.
- **Estados semánticos:** verde `#10b981` (ok), ámbar `#e6a86c` (alerta), rosa `#f43f5e` (peligro).
- **Temas:** el sistema recuerda la preferencia del usuario (`Modo Oscuro` / `Modo Claro`) en `localStorage` y se aplica sin parpadeo al cargar; el tema por defecto es **oscuro**.
- **Accesibilidad y fluidez:** transiciones cortas (0.2 s) y soporte de `prefers-reduced-motion` para usuarios sensible al movimiento.
