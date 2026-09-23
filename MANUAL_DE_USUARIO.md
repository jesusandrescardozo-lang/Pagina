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
7. **`cpm_projects` y `cpm_activities`**: Modelos de redes de actividades con cálculo automático de Ida (Forward Pass), Vuelta (Backward Pass), holguras y Ruta Crítica para la cadena de reabastecimiento humanitario.
8. **`audit_logs`**: Trazabilidad completa de acciones, IPs, timestamps y cambios de estado.
9. **`users`**: Gestión de accesos con roles: `admin` (Coordinador General), `coordinador_refugio`, `almacenista` y `auditor`.

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

#### E. Método de la Ruta Crítica (CPM - Critical Path Method)
Optimiza la secuencia de actividades de la cadena logística de reabastecimiento en convoy:
- **Paso Adelante (Forward Pass)**:
  $$ES_j = \max_{i \in \text{Pred}(j)}(EF_i), \quad EF_j = ES_j + D_j$$
- **Paso Atrás (Backward Pass)**:
  $$LF_i = \min_{j \in \text{Succ}(i)}(LS_j), \quad LS_i = LF_i - D_i$$
- **Holgura y Ruta Crítica**:
  $$\text{Holgura}_i = LS_i - ES_i = LF_i - EF_i$$
  Las actividades con $\text{Holgura} = 0$ forman la Ruta Crítica (destacadas en rojo en el grafo interactivo con **Vis.js**).

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

6. **Obtener los códigos 2FA de los usuarios demo** (requisito para iniciar sesión):
   ```bash
   php tests/test_check_users.php
   ```
   Imprime el secreto de cada usuario. Ingresarlo en la app **Google Authenticator** del celular (escaneando el QR o escribiendo la clave manualmente).

7. **Iniciar Servidor Local**:
   ```bash
   php artisan serve
   ```
   Abrir en el navegador: `http://localhost:8000`

   > Al iniciar sesión además se solicita el **código de 6 dígitos** que muestra Google Authenticator.

---

### 5. USUARIOS DEMO Y CREDENCIALES DE ACCESO

| Rol Operativo | Correo Electrónico | Contraseña | Alcance de Funciones |
|---|---|---|---|
| **Admin General de Logística** | `admin@refugios.ve` | `Admin1234!` | Acceso total, rebalanceo inter-refugios, simulación I.O. y reportes |
| **Coordinador de Albergue** | `coordinador@refugios.ve` | `Coordinador1234!` | Censo de población, solicitud de suministros y consumos en sitio |
| **Encargada de Almacén** | `almacen@refugios.ve` | `Almacen1234!` | Recepción de donaciones, control de lotes y despacho en convoy |
| **Auditora Humanitaria** | `auditor@refugios.ve` | `Auditor1234!` | Consulta de libros Kardex, bitácora de auditoría y reportes PDF |
