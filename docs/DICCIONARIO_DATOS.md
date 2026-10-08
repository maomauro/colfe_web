# Diccionario de datos de COLFE

Describe cada tabla, vista, procedimiento, función y trigger de la base `colfe_db` (MySQL 8.0). Se generó el **8 oct 2026** a partir del esquema en funcionamiento (esquema base `db/schema/colfe_schema.sql` más las migraciones 001 a 005 de `db/migraciones/`), no de documentación anterior. Si cambia el esquema, este documento debe actualizarse en el mismo PR.

**Convenciones**

- **Nulos:** «No» significa que la columna es `NOT NULL`.
- **Clave / restricción:** `PK` llave primaria, `FK` llave foránea, `UNIQUE` valor único. Los `CHECK` y los índices van debajo de cada tabla.
- **Definiciones de negocio:** las que no se deducían del código las confirmó Edgar; ver «Definiciones confirmadas» al final.
- Los valores entre comillas inversas son literales del esquema.

## Mapa de relaciones

Ver el diagrama [`diagramas/er-colfe.html`](diagramas/er-colfe.html). El lado «uno» es el primero:

| Relación | Cardinalidad | Al borrar el padre |
|---|---|---|
| `tbl_socios` → `tbl_anticipos.id_socio` | 1 : N | Se rechaza (`NO ACTION`) |
| `tbl_usuarios` → `tbl_api_tokens.id_usuario` | 1 : N | En cascada |
| `tbl_deducibles` → `tbl_liquidacion.id_deducible` | 1 : N | Se rechaza (`NO ACTION`) |
| `tbl_precios` → `tbl_liquidacion.id_precio` | 1 : N | Se rechaza (`NO ACTION`) |
| `tbl_produccion` → `tbl_liquidacion.id_produccion` | 1 : 1 | Se rechaza (`NO ACTION`) |
| `tbl_socios` → `tbl_liquidacion.id_socio` | 1 : N | Se rechaza (`NO ACTION`) |
| `tbl_socios` → `tbl_produccion.id_socio` | 1 : N | Se rechaza (`NO ACTION`) |
| `tbl_socios` → `tbl_recoleccion.id_socio` | 1 : N | Se rechaza (`NO ACTION`) |

Sin llave foránea, a propósito: `tbl_auditoria.id_usuario` (conserva el historial si se borra el usuario) y `tbl_login_intentos.username` (registra también usuarios que no existen).

## Tablas

### `tbl_socios`: Socios

Productores que entregan leche a la cooperativa. Es la tabla central: casi todo lo demás cuelga de ella.

**Origen:** Esquema base (`db/schema/colfe_schema.sql`); restricciones de la migración 005.

| Columna | Tipo | Nulos | Por defecto | Clave / restricción | Significado | Valores válidos |
|---|---|---|---|---|---|---|
| `id_socio` | `int` | No | — | PK | Identificador interno del socio. | Entero autoincremental. |
| `nombre` | `varchar(50)` | Sí | — | — | Nombres del socio. | Texto, hasta 50. |
| `apellido` | `varchar(50)` | Sí | — | — | Apellidos del socio. | Texto, hasta 50. |
| `identificacion` | `varchar(20)` | No | — | UNIQUE | Cédula o documento de identidad. Identifica de forma única al socio. | Texto, hasta 20; único. |
| `telefono` | `varchar(20)` | Sí | — | — | Teléfono de contacto. | Texto, hasta 20. |
| `direccion` | `varchar(100)` | Sí | — | — | Dirección o predio del socio. | Texto, hasta 100. |
| `vinculacion` | `enum('asociado','proveedor')` | No | — | — | Tipo de relación con la cooperativa. Define qué precio y qué deducible se aplican en la liquidación. | `asociado` o `proveedor`. |
| `fecha_ingreso` | `date` | Sí | — | — | Fecha en que el socio ingresó a la cooperativa. | Fecha. |
| `estado` | `enum('activo','inactivo')` | No | — | — | Si el socio participa en la recolección y la liquidación. | `activo` o `inactivo`. Solo los activos reciben recolección diaria y entran a la liquidación. |

**Restricciones e índices**

- **UNIQUE** `uk_socios_identificacion` (identificacion).

**Triggers:** `tr_aud_socios_d`, `tr_aud_socios_i`, `tr_aud_socios_u`. Detalle en la sección «Triggers».

### `tbl_recoleccion`: Recolección diaria

Una fila por socio activo y por día, con los litros recogidos. `spCrearEventoRecoleccion` la crea en `0` y `sin confirmar`; después se edita y se confirma.

**Origen:** Esquema base; restricciones de la 005.

| Columna | Tipo | Nulos | Por defecto | Clave / restricción | Significado | Valores válidos |
|---|---|---|---|---|---|---|
| `id_recoleccion` | `int` | No | — | PK | Identificador interno del registro diario. | Entero autoincremental. |
| `id_socio` | `int` | No | — | FK → `tbl_socios.id_socio`, UNIQUE compuesto | Socio que entregó la leche. | FK a `tbl_socios`. |
| `fecha` | `date` | No | — | UNIQUE compuesto | Día de la recolección. | Fecha; un solo registro por socio y fecha. |
| `litros_leche` | `decimal(10,2)` | No | — | — | Litros recogidos al socio ese día. | Decimal ≥ 0. `0` en los registros recién creados. |
| `estado` | `enum('confirmado','sin confirmar')` | No | — | — | Si el dato del día ya fue verificado. Solo lo `confirmado` entra a la producción. | `confirmado` o `sin confirmar`. |

**Restricciones e índices**

- **UNIQUE** `uk_recoleccion_socio_fecha` (id_socio, fecha).
- **FK** `tbl_recoleccion_ibfk_1`: `id_socio` → `tbl_socios.id_socio`, borrado `NO ACTION`.
- **CHECK** `ck_recoleccion_litros`: `(litros_leche >= 0)`.
- **Índice** `idx_recoleccion_fecha_estado` (fecha, estado).

**Triggers:** `before_insert_recoleccion`, `before_update_recoleccion`, `tr_aud_recoleccion_u`. Detalle en la sección «Triggers».

### `tbl_produccion`: Producción por quincena

Resumen de litros confirmados por socio y quincena. Lo escribe el procedimiento de liquidación; la aplicación no la edita.

**Origen:** Esquema base; restricciones de la 005.

| Columna | Tipo | Nulos | Por defecto | Clave / restricción | Significado | Valores válidos |
|---|---|---|---|---|---|---|
| `id_produccion` | `int` | No | — | PK | Identificador interno de la producción de una quincena. | Entero autoincremental. |
| `id_socio` | `int` | No | — | FK → `tbl_socios.id_socio`, UNIQUE compuesto | Socio al que pertenece la producción. | FK a `tbl_socios`. |
| `fecha` | `date` | No | — | UNIQUE compuesto | Fecha de cierre de la quincena (día 15 o último día del mes). | Fecha. Coincide con `fecha_liquidacion`. |
| `quincena` | `enum('1ra','2da')` | No | — | UNIQUE compuesto | Quincena a la que corresponde. | `1ra` (días 1 al 15) o `2da` (día 16 al último del mes). |
| `total_litros` | `decimal(10,2)` | No | — | — | Suma de los litros confirmados del socio en la quincena. | Decimal ≥ 0. |

**Restricciones e índices**

- **UNIQUE** `idx_produccion_unica` (id_socio, fecha, quincena).
- **FK** `fk_produccion_socios`: `id_socio` → `tbl_socios.id_socio`, borrado `NO ACTION`.
- **CHECK** `ck_produccion_litros`: `(total_litros >= 0)`.

### `tbl_precios`: Precios por litro

Precio de la leche por tipo de vinculación. Solo el `activo` se usa al liquidar.

**Origen:** Esquema base; restricciones de la 005.

| Columna | Tipo | Nulos | Por defecto | Clave / restricción | Significado | Valores válidos |
|---|---|---|---|---|---|---|
| `id_precio` | `int` | No | — | PK | Identificador interno del precio. | Entero autoincremental. |
| `vinculacion` | `enum('asociado','proveedor')` | No | — | — | Tipo de socio al que aplica el precio. | `asociado` o `proveedor`. |
| `precio` | `decimal(10,2)` | No | — | — | Precio base pagado por litro de leche, en pesos colombianos (confirmado por Edgar el 8 oct 2026; no incluye bonificaciones). | Decimal > 0. |
| `fecha` | `date` | Sí | — | — | Hoy guarda la fecha en que se registró el precio. **Diseño acordado:** pasará a ser `fecha_inicio`, con una `fecha_fin` (vacía = abierto) y sin solapes por vinculación; ver el plan, Fase 5.2. | Fecha. |
| `estado` | `enum('activo','inactivo')` | No | — | — | Si es el precio vigente. Debe haber a lo sumo uno `activo` por vinculación. | `activo` o `inactivo`. |

**Restricciones e índices**

- **CHECK** `ck_precios_precio`: `(precio > 0)`.

**Triggers:** `before_insert_precios`, `before_update_precios`, `tr_aud_precios_d`, `tr_aud_precios_i`, `tr_aud_precios_u`. Detalle en la sección «Triggers».

### `tbl_deducibles`: Deducibles

Descuentos por tipo de vinculación: porcentaje de Fedegán y valores fijos de administración y ahorro. Solo el `activo` se usa al liquidar.

**Origen:** Esquema base; restricciones de la 005. La migración 003 corrigió el deducible de `asociado`, que había quedado sin estado.

| Columna | Tipo | Nulos | Por defecto | Clave / restricción | Significado | Valores válidos |
|---|---|---|---|---|---|---|
| `id_deducible` | `int` | No | — | PK | Identificador interno del conjunto de deducibles. | Entero autoincremental. |
| `vinculacion` | `enum('asociado','proveedor')` | No | — | — | Tipo de socio al que aplican los deducibles. | `asociado` o `proveedor`. |
| `fedegan` | `decimal(5,2)` | No | — | — | Porcentaje de los ingresos que se descuenta por Fedegán (contribución parafiscal ganadera). Confirmado por Edgar el 8 oct 2026. | Decimal entre 0 y 100 (porcentaje, no fracción). |
| `administracion` | `decimal(10,2)` | No | — | — | Valor fijo en pesos que se descuenta por administración a cada socio en cada liquidación (confirmado por Edgar el 8 oct 2026). | Decimal ≥ 0. |
| `ahorro` | `decimal(10,2)` | No | — | — | Valor fijo en pesos que se descuenta como ahorro a cada socio en cada liquidación (confirmado por Edgar el 8 oct 2026). Hoy no existe un registro del ahorro acumulado de cada socio; ver el plan, Fase 5.2. | Decimal ≥ 0. |
| `fecha` | `date` | Sí | — | — | Fecha en que se registró el conjunto de deducibles. Hoy no define vigencia por rango de fechas. | Fecha. |
| `estado` | `enum('activo','inactivo')` | No | — | — | Si es el conjunto vigente. Debe haber a lo sumo uno `activo` por vinculación. | `activo` o `inactivo`. |

**Restricciones e índices**

- **CHECK** `ck_deducibles_valores`: `((fedegan between 0 and 100) and (administracion >= 0) and (ahorro >= 0))`.

**Triggers:** `before_insert_deducibles`, `before_update_deducibles`, `tr_aud_deducibles_d`, `tr_aud_deducibles_i`, `tr_aud_deducibles_u`. Detalle en la sección «Triggers».

### `tbl_liquidacion`: Liquidación quincenal

Resultado del cálculo por socio y quincena: ingresos, descuentos, anticipos y neto a pagar. Guarda copias (foto histórica) de los datos usados.

**Origen:** Esquema base; restricciones de la 005.

| Columna | Tipo | Nulos | Por defecto | Clave / restricción | Significado | Valores válidos |
|---|---|---|---|---|---|---|
| `id_liquidacion` | `int` | No | — | PK | Identificador interno de la liquidación de un socio en una quincena. | Entero autoincremental. |
| `id_produccion` | `int` | No | — | FK → `tbl_produccion.id_produccion` | Producción de la quincena que se liquida. | FK a `tbl_produccion`; una liquidación por producción. |
| `id_deducible` | `int` | No | — | FK → `tbl_deducibles.id_deducible` | Deducibles aplicados (los vigentes al liquidar). | FK a `tbl_deducibles`. |
| `id_precio` | `int` | No | — | FK → `tbl_precios.id_precio` | Precio aplicado (el vigente al liquidar). | FK a `tbl_precios`. |
| `id_socio` | `int` | No | — | FK → `tbl_socios.id_socio`, UNIQUE compuesto | Socio liquidado. | FK a `tbl_socios`. |
| `vinculacion` | `enum('asociado','proveedor')` | Sí | — | — | Foto de la vinculación del socio al liquidar. Conserva el histórico si luego cambia. | `asociado` o `proveedor`. |
| `quincena` | `enum('1ra','2da')` | No | — | UNIQUE compuesto | Quincena liquidada. | `1ra` o `2da`. |
| `identificacion` | `varchar(20)` | Sí | — | — | Foto de la identificación del socio al liquidar. | Texto, hasta 20. |
| `total_litros` | `decimal(10,2)` | Sí | — | — | Litros liquidados (copia de la producción). | Decimal ≥ 0. |
| `precio_litro` | `decimal(10,2)` | Sí | — | — | Precio por litro aplicado (copia del precio vigente). | Decimal. |
| `total_ingresos` | `decimal(15,2)` | Sí | — | — | Ingresos brutos de la quincena = `total_litros` × `precio_litro`. | Decimal ≥ 0, redondeado a 2 decimales. |
| `fedegan` | `decimal(10,2)` | Sí | — | — | Valor en pesos descontado por Fedegán = `total_ingresos` × (porcentaje fedegán / 100). | Decimal, redondeado a 2 decimales. |
| `administracion` | `decimal(10,2)` | Sí | — | — | Valor fijo descontado por administración (copia del deducible). | Decimal. |
| `ahorro` | `decimal(10,2)` | Sí | — | — | Valor fijo descontado por ahorro (copia del deducible). | Decimal. |
| `total_deducibles` | `decimal(15,2)` | Sí | — | — | Suma de los tres descuentos = `fedegan` + `administracion` + `ahorro`. | Decimal ≥ 0. |
| `total_anticipos` | `decimal(15,2)` | Sí | — | — | Suma de los anticipos `aprobado` del socio con fecha dentro de la quincena. | Decimal ≥ 0. |
| `neto_a_pagar` | `decimal(15,2)` | Sí | — | — | Valor a pagar al socio = `total_ingresos` − `total_deducibles` − `total_anticipos`. | Decimal. Puede resultar negativo si los descuentos superan los ingresos (la base no lo impide). |
| `estado` | `enum('pre-liquidacion','liquidacion')` | No | — | — | Etapa de la liquidación. El cálculo nace en `pre-liquidacion`; al confirmarla pasa a `liquidacion` (cierre). El panel de inicio solo grafica las `liquidacion`. | `pre-liquidacion` o `liquidacion`. |
| `fecha_liquidacion` | `date` | No | — | UNIQUE compuesto | Fecha de cierre de la quincena (día 15 o último día del mes). | Fecha; única por socio y quincena. |

**Restricciones e índices**

- **UNIQUE** `idx_liquidacion_unica` (id_socio, fecha_liquidacion, quincena).
- **FK** `fk_liquidacion_deducible`: `id_deducible` → `tbl_deducibles.id_deducible`, borrado `NO ACTION`.
- **FK** `fk_liquidacion_precio`: `id_precio` → `tbl_precios.id_precio`, borrado `NO ACTION`.
- **FK** `fk_liquidacion_produccion`: `id_produccion` → `tbl_produccion.id_produccion`, borrado `NO ACTION`.
- **FK** `fk_liquidacion_socios`: `id_socio` → `tbl_socios.id_socio`, borrado `NO ACTION`.
- **CHECK** `ck_liquidacion_montos`: `((total_litros >= 0) and (total_ingresos >= 0) and (total_deducibles >= 0) and (total_anticipos >= 0))`.

**Triggers:** `tr_aud_liquidacion_d`, `tr_aud_liquidacion_i`, `tr_aud_liquidacion_u`. Detalle en la sección «Triggers».

### `tbl_anticipos`: Anticipos

Adelantos de dinero a los socios, que se descuentan de la liquidación de la quincena en que caen si están aprobados.

**Origen:** Esquema base (con el trigger `tr_anticipos_before_insert`); restricciones de la 005.

| Columna | Tipo | Nulos | Por defecto | Clave / restricción | Significado | Valores válidos |
|---|---|---|---|---|---|---|
| `id_anticipo` | `int` | No | — | PK | Identificador interno del anticipo. | Entero autoincremental. |
| `id_socio` | `int` | No | — | FK → `tbl_socios.id_socio` | Socio que recibe el anticipo. | FK a `tbl_socios`. |
| `monto` | `decimal(10,2)` | No | — | — | Valor del anticipo en pesos. | Decimal > 0. |
| `fecha_anticipo` | `date` | No | — | — | Fecha del anticipo. Define en qué quincena se descuenta. | Fecha. |
| `estado` | `enum('pendiente','aprobado','rechazado')` | No | `pendiente` | — | Si el anticipo se descuenta. Solo los `aprobado` restan en la liquidación. | `pendiente` (por defecto), `aprobado` o `rechazado`. |
| `observaciones` | `text` | Sí | — | — | Notas libres sobre el anticipo. | Texto. |
| `fecha_registro` | `timestamp` | No | `CURRENT_TIMESTAMP` | — | Momento en que se registró el anticipo (lo fija un trigger). | Fecha y hora. |
| `usuario_registro` | `varchar(50)` | Sí | — | — | Quién lo registró. La aplicación escribe `admin`; si llega vacío, el trigger guarda el usuario de MySQL (`USER()`). No es una FK a `tbl_usuarios` (pendiente: bloque 3 de integridad). | Texto, hasta 50. |

**Restricciones e índices**

- **FK** `fk_anticipos_socios`: `id_socio` → `tbl_socios.id_socio`, borrado `NO ACTION`.
- **CHECK** `ck_anticipos_monto`: `(monto > 0)`.
- **Índice** `idx_estado` (estado).
- **Índice** `idx_estado_fecha` (estado, fecha_anticipo).
- **Índice** `idx_fecha_anticipo` (fecha_anticipo).
- **Índice** `idx_socio_fecha` (id_socio, fecha_anticipo).

**Triggers:** `tr_anticipos_before_insert`, `tr_aud_anticipos_d`, `tr_aud_anticipos_i`, `tr_aud_anticipos_u`. Detalle en la sección «Triggers».

### `tbl_usuarios`: Usuarios de la aplicación

Cuentas que inician sesión en la web y en la API móvil. Se crean con `db/tools/crear_usuario.php`; el seed no trae usuarios.

**Origen:** Esquema base; la migración 002 eliminó los usuarios demo `admin/admin` y `user/12345`.

| Columna | Tipo | Nulos | Por defecto | Clave / restricción | Significado | Valores válidos |
|---|---|---|---|---|---|---|
| `id` | `int` | No | — | PK | Identificador interno del usuario de la aplicación. | Entero autoincremental. |
| `username` | `varchar(50)` | No | — | UNIQUE | Nombre de usuario para iniciar sesión. | Texto, hasta 50; único. |
| `password` | `varchar(255)` | No | — | — | Hash de la contraseña (`password_hash` de PHP). Nunca se guarda en claro. | Texto, hasta 255. |
| `created_at` | `timestamp` | No | `CURRENT_TIMESTAMP` | — | Momento de creación del usuario. | Fecha y hora. |

**Restricciones e índices**

- **UNIQUE** `username` (username).

**Triggers:** `before_insert_usuario`. Detalle en la sección «Triggers».

### `tbl_api_tokens`: Tokens de la API móvil

Tokens de acceso de la app Android. Solo se guarda el hash.

**Origen:** Migración 001.

| Columna | Tipo | Nulos | Por defecto | Clave / restricción | Significado | Valores válidos |
|---|---|---|---|---|---|---|
| `id_token` | `int` | No | — | PK | Identificador interno del token. | Entero autoincremental. |
| `id_usuario` | `int` | No | — | FK → `tbl_usuarios.id` | Usuario dueño del token. | FK a `tbl_usuarios`; se borra en cascada con el usuario. |
| `token_hash` | `char(64)` | No | — | UNIQUE | Hash SHA-256 (hex) del token. El token en claro solo lo conoce la app móvil. | 64 caracteres hexadecimales; único. |
| `expira_en` | `datetime` | No | — | — | Momento en que el token deja de ser válido. | Fecha y hora. Por defecto 24 h después de emitirse (`API_TOKEN_TTL`, en segundos, en `config/config.php`). |
| `creado_en` | `timestamp` | No | `CURRENT_TIMESTAMP` | — | Momento de emisión. | Fecha y hora. |
| `ultimo_uso` | `datetime` | Sí | — | — | Último momento en que el token se validó. | Fecha y hora; nulo si nunca se usó. |

**Restricciones e índices**

- **UNIQUE** `uk_token_hash` (token_hash).
- **FK** `fk_api_tokens_usuario`: `id_usuario` → `tbl_usuarios.id`, borrado `CASCADE`.
- **Índice** `idx_expira_en` (expira_en).

### `tbl_login_intentos`: Intentos de inicio de sesión fallidos

Registro para el bloqueo por intentos (por usuario y por IP).

**Origen:** Migración 002.

| Columna | Tipo | Nulos | Por defecto | Clave / restricción | Significado | Valores válidos |
|---|---|---|---|---|---|---|
| `id_intento` | `int` | No | — | PK | Identificador interno del intento fallido. | Entero autoincremental. |
| `username` | `varchar(50)` | No | — | — | Usuario con el que se intentó entrar (exista o no). | Texto, hasta 50. |
| `ip` | `varchar(45)` | No | — | — | Dirección IP de origen. | IPv4 o IPv6, hasta 45 caracteres. |
| `creado_en` | `timestamp` | No | `CURRENT_TIMESTAMP` | — | Momento del intento fallido. | Fecha y hora. La aplicación borra los de más de un día. |

**Restricciones e índices**

- **Índice** `idx_intentos_ip` (ip, creado_en).
- **Índice** `idx_intentos_usuario` (username, creado_en).

### `tbl_auditoria`: Auditoría de cambios

Historial de INSERT, UPDATE y DELETE de las tablas de negocio, con el usuario, el origen y los valores antes y después. La llenan los triggers `tr_aud_*`.

**Origen:** Migración 004.

| Columna | Tipo | Nulos | Por defecto | Clave / restricción | Significado | Valores válidos |
|---|---|---|---|---|---|---|
| `id_auditoria` | `bigint` | No | — | PK | Identificador interno del evento. | Entero grande autoincremental. |
| `fecha` | `timestamp` | No | `CURRENT_TIMESTAMP` | — | Momento del cambio. | Fecha y hora. |
| `tabla` | `varchar(40)` | No | — | — | Tabla donde ocurrió el cambio. | `tbl_liquidacion`, `tbl_anticipos`, `tbl_precios`, `tbl_deducibles`, `tbl_socios` o `tbl_recoleccion`. |
| `accion` | `enum('INSERT','UPDATE','DELETE')` | No | — | — | Tipo de cambio. | `INSERT`, `UPDATE` o `DELETE` (en `tbl_recoleccion` solo `UPDATE`). |
| `id_registro` | `varchar(40)` | No | — | — | Clave primaria de la fila afectada, como texto. | Texto, hasta 40. |
| `id_usuario` | `int` | Sí | — | — | Usuario de la aplicación que hizo el cambio. Nulo si fue el sistema. Sin FK, para conservar el historial si se borra el usuario. | Entero o nulo. |
| `origen` | `varchar(10)` | No | `sistema` | — | Desde dónde se hizo el cambio. | `web`, `api` o `sistema` (por defecto). |
| `datos_antes` | `json` | Sí | — | — | Fila completa antes del cambio, en JSON. | JSON; nulo en `INSERT`. |
| `datos_despues` | `json` | Sí | — | — | Fila completa después del cambio, en JSON. | JSON; nulo en `DELETE`. |

**Restricciones e índices**

- **Índice** `idx_aud_fecha` (fecha).
- **Índice** `idx_aud_registro` (tabla, id_registro).
- **Índice** `idx_aud_usuario` (id_usuario).


## Vistas

| Vista | Qué muestra | Origen | Quién la usa |
|---|---|---|---|
| `v_anticipos_completos` | Cada anticipo con los datos del socio (`nombre_socio`, `identificacion`, `telefono`, `vinculacion`), solo de socios `activo`, del más reciente al más antiguo por `fecha_registro`. | Esquema base | No la consume la aplicación hoy (el código consulta `tbl_anticipos` directamente). |
| `v_auditoria` | `tbl_auditoria` con el `username` del usuario (`LEFT JOIN` a `tbl_usuarios`). Conserva los eventos de usuarios ya borrados, con `username` nulo. | Migración 004 | Consulta manual por SQL; falta una pantalla (pendiente en el plan). |

## Procedimientos y funciones

| Nombre | Parámetros | Qué hace | Uso |
|---|---|---|---|
| `spCrearEventoRecoleccion` | `p_nombre_evento VARCHAR(50)`, `p_fecha DATE` | Solo actúa si el evento es `recoleccion`. Si no hay registros para esa fecha, crea uno por cada socio `activo`, con `litros_leche = 0` y estado `sin confirmar`. Devuelve `resultado` (`TRUE` creado o ya existía, `FALSE` evento distinto). | Producción: lo llama la pantalla de calendario (`src/modelos/calendario.modelo.php`). |
| `spProcesarLiquidacionQuincenal` | `p_evento VARCHAR(50)`, `p_fecha_liquidacion DATE` | Solo actúa si el evento es `liquidacion`. Si ya hay liquidación en esa fecha, no hace nada. Exige que la fecha sea día 15 o último del mes; que cada día de la quincena tenga al menos un registro `confirmado`; y que no queden registros `sin confirmar`. Si cumple, en una transacción: (1) inserta en `tbl_produccion` la suma de litros confirmados por socio `activo`; (2) inserta en `tbl_liquidacion`, en estado `pre-liquidacion`, el cálculo con el precio y el deducible `activo` de su vinculación y los anticipos `aprobado` de la quincena. Si algo falla, revierte. Los errores de validación salen como `SQLSTATE 45000` con un mensaje en español. | Producción: lo llama la pantalla de calendario. **Nota:** si una vinculación no tiene precio o deducible `activo`, sus socios se omiten sin aviso (por eso existe la migración 003). |
| `sp_total_anticipos_socio` | `p_id_socio INT`, `p_fecha_inicio DATE`, `p_fecha_fin DATE` | Devuelve los totales de anticipos de un socio en un rango, separados en `total_aprobado`, `total_pendiente` y `total_rechazado`. | Consulta manual; la aplicación no la usa hoy. |
| `spInsertIntoRecoleccion` | ninguno | Recorre los días del 2025-01-01 al 2026-08-26 (fechas fijas en el código), inserta recolección `confirmado` para todos los socios con litros aleatorios y llama a la liquidación los días 15 y último de cada mes. | **Solo demo.** No ejecutar en producción: sus fechas están fijas. |
| `generar_litros_leche` (función) | `fecha DATE`, `id_socio INT` → `decimal(10,2)` | Devuelve litros simulados: base de 50 a 70 según el socio, más una variación aleatoria por temporada, con mínimo de 30. | **Solo demo.** Usa `RAND()`, así que no es repetible. |

## Triggers

Son 24. Los de validación dan un error `SQLSTATE 45000` con un mensaje en español; los de auditoría escriben en `tbl_auditoria` con el usuario y el origen que la aplicación deja en las variables de sesión `@colfe_usuario` y `@colfe_origen` (`src/modelos/conexion.php`).

| Trigger | Tabla | Momento | Qué hace |
|---|---|---|---|
| `before_insert_recoleccion` | `tbl_recoleccion` | antes de INSERT | Rechaza un segundo registro del mismo socio y fecha («Ya existe un registro para este socio en esta fecha»). La restricción `uk_recoleccion_socio_fecha` lo respalda. |
| `before_update_recoleccion` | `tbl_recoleccion` | antes de UPDATE | Igual, al cambiar socio o fecha. |
| `before_insert_precios` | `tbl_precios` | antes de INSERT | Rechaza un precio `activo` si ya hay otro `activo` para la misma vinculación. |
| `before_update_precios` | `tbl_precios` | antes de UPDATE | Igual, al activar o cambiar la vinculación. |
| `before_insert_deducibles` | `tbl_deducibles` | antes de INSERT | Rechaza un conjunto `activo` si ya hay otro `activo` para la misma vinculación. |
| `before_update_deducibles` | `tbl_deducibles` | antes de UPDATE | Igual, al activar o cambiar la vinculación. |
| `before_insert_usuario` | `tbl_usuarios` | antes de INSERT | Rechaza un `username` repetido («El nombre de usuario ya está registrado»). La restricción `UNIQUE` lo respalda. |
| `tr_anticipos_before_insert` | `tbl_anticipos` | antes de INSERT | Fija `fecha_registro = NOW()` y, si `usuario_registro` viene vacío, lo llena con el usuario de MySQL (`USER()`). |
| `tr_aud_liquidacion_i`, `_u`, `_d` | `tbl_liquidacion` | después de INSERT, UPDATE y DELETE | Registran el cambio en `tbl_auditoria`. El UPDATE solo registra si algún valor cambió. |
| `tr_aud_anticipos_i`, `_u`, `_d` | `tbl_anticipos` | después de INSERT, UPDATE y DELETE | Ídem. |
| `tr_aud_precios_i`, `_u`, `_d` | `tbl_precios` | después de INSERT, UPDATE y DELETE | Ídem. |
| `tr_aud_deducibles_i`, `_u`, `_d` | `tbl_deducibles` | después de INSERT, UPDATE y DELETE | Ídem. |
| `tr_aud_socios_i`, `_u`, `_d` | `tbl_socios` | después de INSERT, UPDATE y DELETE | Ídem. |
| `tr_aud_recoleccion_u` | `tbl_recoleccion` | después de UPDATE | Registra solo las ediciones (no cada alta diaria, que serían miles de eventos). |

## Definiciones confirmadas

Edgar confirmó el 8 oct 2026 las definiciones que el código no permitía afirmar. No queda ninguna marca `[por confirmar]`.

- **Precio:** base, en pesos por litro, con historial por fechas.
- **`administracion` y `ahorro`:** valores fijos por socio en cada liquidación.
- **`fedegan`:** porcentaje sobre los ingresos, correspondiente a una contribución parafiscal.

## Reglas de negocio acordadas y aún no implementadas

El esquema actual no las cumple todavía. Están en el plan (Fase 5.2) y se documentarán en `LIQUIDACION.md` (4.2):

- **Precios con vigencia:** `tbl_precios` como historial con `fecha_inicio` y `fecha_fin` (vacía = abierto), sin solapes por vinculación.
- **Liquidación fija o variable:** el administrador elige al liquidar. *Fija:* toda la quincena con el precio vigente en la fecha de cierre. *Variable:* cada día con el precio que regía ese día. Se guarda en `tbl_liquidacion`, con el desglose por tramos en una columna JSON; se puede reliquidar con el otro tipo mientras la liquidación no esté cerrada. Ejemplo numérico: [`ejemplos/ejemplo_liquidacion_fija_vs_variable.xlsx`](ejemplos/ejemplo_liquidacion_fija_vs_variable.xlsx).
- **Saldo negativo de anticipos:** si los anticipos superan el pago de la quincena, el saldo se arrastra como descuento a la siguiente quincena. Hoy el neto puede salir negativo y no se arrastra.
- **Ahorro como garantía:** un registro del ahorro de cada socio (con los saldos que ya tenía antes del sistema), para descontar de él los anticipos pendientes si el socio se retira.
- **Tope de los anticipos:** se aprueba un anticipo si cabe en el neto estimado de la quincena **o** en el ahorro del socio; basta con cubrir uno de los dos.
