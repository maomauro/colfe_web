# ColfeWeb: plan de trabajo hacia producción

Base: diagnóstico técnico del 29 sep 2026, ampliado con el diagnóstico v2 del 8 oct 2026 (Fases 4 a 6). Marca `[x]` al terminar cada tarea.
Prioridad: **P0** bloqueante · **P1** importante · **P2** deseable.

Decisiones ya tomadas:
- Se usan los datos demo existentes, con fechas 2025-2026, en pruebas y en producción.
- Tras la demostración se limpia la base de producción con un script de reinicio.
- Destino: VPS Contabo, nginx, Docker, subdominio de sitiosapps.com.

---

## Estado actual (8 oct 2026)

**Todo el trabajo de las Fases 0 a 3 está fusionado en `main`** (PR #5 a #15) y el CI corre en cada push: lint, seed y
migraciones en MySQL 8.0, 27 pruebas PHPUnit, pruebas de seguridad (endpoints, autenticación, CSRF, auditoría) y un
recorrido en Chromium. «Publicar imágenes» ya generó las imágenes `app` y `web` en GHCR.

**COLFE está desplegado y publicado en `https://colfe.sitiosapps.com`** (VPS Contabo, Docker, detrás del nginx de PortalCV,
con certificado de origen de Cloudflare en modo *Completo (estricto)*). Edgar verificó HTTPS, login de `admin` y todas las
páginas el 3 oct 2026. Corre con los datos demo.

**Depende de Edgar (nadie más puede hacerlo):**
- [x] Probar `docker compose up -d --build` en su equipo y recorrer la app — hecho el 2 oct 2026 en Windows (Docker 29, Compose v5): los tres contenedores levantan, se crea `admin` con `crear_usuario.php`, el login funciona y se ven todas las páginas.
- [x] **D2:** PortalCV corre en Docker con `portalcv-nginx-prod` en 80/443 y MariaDB 11. COLFE usa su propio MySQL 8.0 y se une a la red de ese nginx (`PROXY_NETWORK`, alias `colfe-web`).
- [x] Programar el respaldo semanal (cron) en el VPS: **decidido** semanal, solo en el VPS (`/srv/sitiosapps/_backups/colfe`), 8 copias. El destino externo (`rclone`) queda opcional para cuando haya datos reales.
- [x] Reorganizar el VPS bajo `/srv/sitiosapps/` (`Curriculum-Vitae-Web/`, `colfe/`, `_backups/`) — hecho el 8 oct 2026; ver `DESPLIEGUE.md`, sección 0.
- [x] VPS: subdominio en Cloudflare y certificado de origen (`colfe.sitiosapps.com`) — hecho el 3 oct 2026.
- [x] Flujo de ramas `feature → develop → main` con `main` y `develop` protegidas (PR y CI obligatorios, «Verificar rama de origen» en `main`, sin saltarse la regla), igual que Curriculum-Vitae-Web — hecho el 8 oct 2026. `develop` es además la rama por defecto del repo.
- [x] Restauración del respaldo de COLFE probada en el VPS el 8 oct 2026 (`probar_restauracion.sh`): conteos idénticos, 4 procedimientos, 1 función y 24 triggers.
- [ ] Antes de la demostración: **revisar precios y deducibles** con la cooperativa (se conservan al limpiar el demo).

---

## Decisiones pendientes

- [x] **D2** ¿Cómo convive con PortalCV en el VPS? **Proxy compartido:** `portalcv-nginx-prod` (contenedor) carga `colfe.conf` y alcanza a `colfe-web` por la red de PortalCV; COLFE usa su propio MySQL 8.0. El bloque vive también en el repositorio de PortalCV (`docker/colfe.conf`).
- [x] **D3** ¿Qué versión de PHP corre en Laragon? **8.1.10** (MySQL 8.0.30). La app soporta 8.1+; las imágenes Docker usan PHP 8.4 y el desarrollo local recomendado es Docker.
- [x] **D4** ¿El repositorio es público? **Sí, es público** y tiene GitHub Pages activo desde `main`. Consecuencias: el seed y los dumps antiguos del historial son datos sintéticos, pero `admin/admin` y `user/12345` estuvieron publicados (ya no existen: la migración 002 los elimina); nunca subir `.env`, claves ni datos reales de socios; los detalles de despliegue de `docs/` y `deploy/` son visibles.
- [ ] **D5** ¿Cómo cubrir del 27 ago al 30 sep 2026? (generar con `spInsertIntoRecoleccion` o cargar desde la app)
- [x] **D1** Datos reales: no se usan por ahora, se sigue con los demo

---

## Sprint 0: Higiene del repositorio

- [x] Subir cambios locales (API móvil, anticipos, dump 2026-09-29) — PR #1
- [x] Script `db/tools/desplazar_fechas.py` y dump `db/seed/colfe_demo_2026.sql` — PR #2
- [x] **P1** Probar la restauración del seed desde cero en MySQL 8.0 — la hace el CI en cada PR (MySQL 8.0.46 real)
- [x] **P1** Crear `.gitignore` (`.env`, `logs/`, `test_*.php`, `test_api_*.php`)
- [x] **P1** Crear `.gitattributes` para normalizar fin de línea
- [x] **P1** Reorganizar `db/`: `schema/` (esquema sin datos), `seed/` (demo 2026), archivar dumps antiguos
- [x] **P1** Corregir `README.md`: importar el esquema vigente y quitar lo que no existe (CSRF, auditoría)
- [x] **P1** `README_TESTS.md` (documentaba tests inexistentes): eliminado; las pruebas reales se explican en `README.md` y `CLAUDE.md`

---

## Fase 0: Reestructura del proyecto (P0, antes de la seguridad)

Detalle y mapa de movimientos (documento histórico; la estructura ya está aplicada): [`DIAGNOSTICO_ESTRUCTURA.md`](DIAGNOSTICO_ESTRUCTURA.md).
Se hace primero para escribir el guard y el bootstrap una sola vez, en su ubicación final.

- [x] Aprobar la estructura objetivo (`public/` + `src/` + `config/` + `storage/`) — aprobada por Edgar al autorizar continuar
- [x] Crear rama y mover con `git mv` (conserva historial): estáticos a `public/vistas/`, código a `src/`
- [x] Mover `ajax/` y `api/` a `public/`; mover `recibo.php` y `reporte_recoleccion.php` a `public/reportes/`
- [x] Mover `config.php` a `config/config.php` y `fpdf` a `src/libs/fpdf/`
- [x] Reemplazar los 20 `require_once $_SERVER["DOCUMENT_ROOT"]."/colfe_web/..."` por rutas con `__DIR__` / `BASE_PATH`
- [x] Crear `src/bootstrap.php` (config, zona horaria, errores, cookies, sesión) y cargarlo desde `public/index.php`
- [x] Actualizar los 4 enlaces a los reportes (`liquidacion.js`, `recoleccion.js`, 2 vistas)
- [x] Eliminar predicción: `prediccion.ajax.php`, `prediccion.php`, `prediccion.js`
- [x] Eliminar `ajax/logs.log` y `test_simple.php`; logs a `storage/logs/`
- [x] Verificar: `php -l` de todos los archivos y arranque con `php -S` sobre `public/`
- [x] Recorrer cada módulo: se hace sobre Docker (ver 2.2); Laragon ya no es el entorno de referencia

**Salida Fase 0:** el sitio funciona igual que antes, solo `public/` es accesible por HTTP.

---

## Fase 1: Seguridad (P0, obligatoria antes de publicar)

### 1.1 Autenticación de endpoints
- [x] Crear `src/auth/guard.php` (`guardSesion()` y `guardToken()`) que responda 401
- [x] **Nuevo (E1):** los módulos de `src/vistas/modulos/` solo se incluyen desde el router; confirmar que ninguno se ejecuta por URL
- [x] **Nuevo (E2):** guard de sesión en `public/reportes/recibo.php` y `reporte_recoleccion.php`
- [x] Incluir el guard en los 9 archivos de `ajax/` (prediccion se eliminó; `inicio.ajax.php` se añadió para el dashboard)
- [x] Incluir el guard en las 6 APIs de `api/` (`apiTotalLiquidacion` no tenía ningún control)
- [x] Revisar `apiCrearRecoleccionesLote.php` y `apiRecoleccionQuincena.php` (control no confirmado)
- [x] Probar con `curl` sin sesión: todo debe devolver 401

### 1.2 Token de la API móvil
- [x] Guardar el token (o JWT firmado) con usuario y expiración
- [x] Validar el token real en `apiValidarToken.php` y `apiSocios.php`

### 1.3 Contraseñas y sesión
- [x] Cambiar a `password_hash()` y `password_verify()`
- [x] Migrar el usuario y eliminar `admin/admin` y `user/12345` (migración 002 + `db/tools/crear_usuario.php`)
- [x] Exigir contraseña nueva y fuerte (quitar la restricción alfanumérica)
- [x] `session_regenerate_id()` al iniciar sesión
- [x] Bloqueo por intentos (usar `MAX_LOGIN_ATTEMPTS`)

### 1.4 Superficie de ataque
- [x] ~~Eliminar `prediccion.ajax.php`~~ (se hace en la Fase 0)
- [x] Confirmar que no queda ningún `test_*.php` ni `shell_exec` en el código
- [x] Restringir CORS (hoy `Access-Control-Allow-Origin: *`)

### 1.5 Configuración
- [x] `config/config.php` lee todo de variables de entorno, sin credenciales por defecto
- [x] `ENVIRONMENT=production` por defecto (sin `display_errors`), aplicado desde el bootstrap (E5)
- [x] Usuario de BD propio con clave fuerte (no `desarrollo/desarrollo`): el código ya no trae credenciales por defecto; **crear el usuario de BD de producción queda para el despliegue (Fase 2)**
- [x] Confirmar que ningún `$item` o `$tabla` interpolado viene de la petición (auditado: todos son literales; sin inyección SQL)

**Salida Fase 1:** los 5 bloqueantes cerrados y probados.

---

## Fase 2: Portabilidad y despliegue (P1)

### 2.1 Código portable
- [x] ~~Rutas con `__DIR__`~~ (se hace en la Fase 0)
- [x] `APP_URL` y `API_URL` por variable de entorno — se eliminaron: ninguna se usaba

### 2.2 Docker local
- [x] `Dockerfile` (php-fpm con `pdo_mysql`) — imagen Debian (iconv //TRANSLIT no funciona en Alpine). **Build sin ejecutar: lo prueba Edgar**
- [x] `docker-compose.yml` (nginx, php-fpm, MySQL 8.0, phpMyAdmin) — `compose config` válido; **`up` sin ejecutar**
- [x] La BD se inicializa sola desde el dump demo — seed + migraciones 001/002 montados en `initdb.d`
- [x] Quitar `DEFINER=root@localhost` de la vista `v_anticipos_completos` — resuelto: el seed 2026 no trae `DEFINER` explícito

### 2.3 nginx
- [x] `root` apuntando a `public/` y `try_files` hacia `index.php?ruta=` (reemplaza `.htaccess`) — verificado con nginx real (rutas, bloqueos, IP real, cabeceras)
- [x] Mantener un `.htaccess` mínimo dentro de `public/` para Laragon/Apache
- [x] `db/`, `src/`, `config/`, `storage/` ya quedan fuera de la raíz web (verificar con `curl`) — verificado: 404

### 2.4 Producción
- [x] `docker-compose.prod.yml` (sin phpMyAdmin, BD sin puerto público, volumen persistente) — ejecutado en el VPS el 3 oct 2026 con `TAG=latest ./deploy/desplegar.sh`
- [x] Subdominio en Cloudflare con HTTPS — `https://colfe.sitiosapps.com`
- [x] Cookie `secure` y cabeceras de seguridad — cookie `Secure` fuera de desarrollo; cabeceras en nginx (HSTS en el vhost de ejemplo)
- [x] `.env` solo en el servidor — `/srv/sitiosapps/colfe/.env` (`chmod 600`, claves generadas con `openssl rand -hex 16`)

### 2.5 Pipeline y respaldo
- [x] GitHub Actions: construir imagen y publicar en GHCR (`latest` y `sha-<commit>`) — workflows `ci.yml` y `publicar.yml`
- [x] Despliegue y rollback por tag — `deploy/desplegar.sh` y workflow manual `desplegar.yml`
- [x] Backup de MySQL — `deploy/backup.sh`, semanal y local por decisión de Edgar (8 copias en `/srv/sitiosapps/_backups/colfe`); copia externa con `rclone` opcional
- [x] Probar la restauración del backup — `deploy/probar_restauracion.sh`, probado: conteos idénticos

### 2.6 Datos hasta hoy
- [ ] Cubrir del 27 ago al 30 sep 2026 según la decisión D5

**Salida Fase 2:** staging en el subdominio con HTTPS y backup restaurable. *Respaldo semanal programado y restauración probada en el VPS.*

---

## Fase 3: Confianza en el negocio (P2, en paralelo al staging)

- [x] Pruebas PHPUnit de liquidación (deducibles, anticipos, precios por quincena) — 27 pruebas / 1.013 aserciones (18 de liquidación y 9 de auditoría), en el CI
- [x] Validar una quincena completa contra un cálculo manual — recálculo independiente de una quincena (2da feb-2025, 107 socios) y de las 4.066 liquidaciones
- [x] Revisar las 27 producciones sin liquidar que quedaron en el demo — causa: deducible de «asociado» con estado NULL (migración 003); eran 1.026 producciones de 27 socios
- [x] CSRF en formularios y ajax — token por sesión + verificación de Origin; los 4 borrados (antes por GET) pasan a POST
- [x] Log de auditoría en liquidaciones y anticipos — migración 004: 16 triggers sobre liquidaciones, anticipos, precios, deducibles, socios y edición de recolección; usuario y origen (web/api/sistema) y valores antes/después en JSON; vista `v_auditoria`. **Falta una pantalla para consultarla** (hoy es por SQL)
- [x] Integridad del modelo, bloque 1 (migración 005): `NOT NULL` en FK y columnas críticas, `UNIQUE` en `tbl_recoleccion(id_socio, fecha)` y `tbl_socios(identificacion)`, `CHECK` de litros, precios, deducibles y anticipos, e índice `tbl_recoleccion(fecha, estado)`. Probada en la base de desarrollo (con copia previa), en una base nueva desde el seed y repetida (idempotente). **Aplicada en el VPS el 8 oct 2026** (carpeta `/srv/sitiosapps/colfe`), con comprobaciones previas en 0 y respaldo antes de aplicar; verificadas las 8 restricciones, el índice, los 24 triggers y las 4.066 liquidaciones, y la web responde 200
- [x] CI con lint y pruebas — `.github/workflows/ci.yml`
- [ ] Probar la app Android contra producción → ver 5.4

---

## Fase 4: Documentación (P1, en paralelo con la Fase 5)

Objetivo: documentación completa, versionada junto al código y alineada con el código real.
Todo en `docs/`, con un índice `docs/README.md`. Tamaño: **S** pequeño · **M** mediano · **L** grande. Responsable: **E** Edgar · **C** Claude.

### 4.1 Diccionario de datos
- [x] **P1 · M · C** `docs/DICCIONARIO_DATOS.md`: por tabla (`tbl_socios`, `tbl_recoleccion`, `tbl_produccion`, `tbl_liquidacion`, `tbl_precios`, `tbl_deducibles`, `tbl_liquidacion_deducible`, `tbl_usuarios`, `tbl_api_tokens`, `tbl_login_intentos`, `tbl_auditoria`): columna, tipo, nulos, valor por defecto, claves y restricciones (`PK`, `FK`, `UNIQUE`, `CHECK`), significado de negocio, valores válidos y migración que la creó
- [x] **P1 · S · C** Incluir la vista `v_auditoria`, procedimientos y funciones, y los 20 triggers (qué dispara cada uno)
- [x] **P1 · S · E** Validar las definiciones de negocio que no se deduzcan del código (se marcan `[por confirmar]`)
- [ ] **P2 · S · C** Migración 006 con `COMMENT` en las columnas, para que el esquema se documente solo
- [ ] **P2 · S · C** Enlazar el diccionario desde el diagrama ER y mostrar columnas clave en el ER
- [ ] **P2 · S · C** Prueba en el CI que falle si una tabla o columna del esquema no aparece en el diccionario

### 4.2 Reglas de negocio de la liquidación
- [x] **P1 · M · C** `docs/LIQUIDACION.md`: glosario (quincena, vinculación, precio, deducibles, cierre) y fórmula paso a paso, con un ejemplo numérico tomado de la quincena validada
- [ ] **P1 · S · E** Revisar el documento con la cooperativa

### 4.3 Contrato de la API móvil
- [x] **P1 · M · C** `docs/API_MOVIL.md` (o `openapi.yaml`): los 6 endpoints, cabeceras, cuerpo, respuestas, códigos de error y vigencia del token (24 h)
- [x] **P1 · S · C** Documentar el cambio de `apiValidarToken` (401 si el token es inválido) y la migración de `?token=` a `Authorization: Bearer`
- [ ] **P2 · S · C** Prueba de contrato en el CI (las respuestas coinciden con el documento)

### 4.4 Decisiones y arquitectura
- [x] **P1 · M · C** `docs/adr/`: ADRs 0001 a 0010 (más 0011 y 0012, con las decisiones del 8 oct sobre precios, liquidación y anticipos) a partir de D1 a D5 y de las decisiones ya tomadas (datos demo, proxy compartido con PortalCV, imagen Debian, tokens en base de datos, CSRF por origen y token, estructura `public/src/config/storage`, respaldo semanal solo local, flujo de ramas)
- [ ] **P2 · M · C** `docs/arquitectura/`: C4 de contexto y de contenedores, y arc42 ligero (secciones 1, 3, 4, 5, 7, 8 y 9)
- [ ] **P2 · S · C** Riesgos y deuda técnica (arc42 sección 11) alimentados por el diagnóstico

### 4.5 Operación y gobierno
- [ ] **P1 · M · C** `docs/OPERACION.md` (runbook): monitoreo, qué hacer ante una caída, rotación de claves y tokens, restauración desde respaldo externo, comandos de diagnóstico
- [ ] **P2 · S · C** `SECURITY.md` (cómo reportar una vulnerabilidad), `CONTRIBUTING.md`, `LICENSE` (Edgar decide la licencia o «propietaria»), `CHANGELOG.md`
- [ ] **P2 · S · C** Definition of Ready y Definition of Done del proyecto (incluye «documentación al día»)

### 4.6 Correcciones
- [ ] **P1 · S · C** README: quitar «Predicciones», corregir la ruta de logs a `storage/logs/`, actualizar fecha y versión, enlazar `docs/README.md`
- [ ] **P2 · S · C** Docblocks en los modelos PHP

**Salida Fase 4:** cada tabla, endpoint, regla de liquidación y decisión está documentado; el índice enlaza todo; el README coincide con el código; el CI avisa si el esquema y el diccionario se separan.

---

## Fase 5: Endurecimiento antes de datos reales (P1)

### 5.1 Seguridad de la aplicación
- [ ] **P1 · M · C** XSS: helper `e()` para toda salida en los 16 archivos de `src/vistas/modulos/`; `.text()` en lugar de `.html()` donde se concatenan datos (12 usos); prueba en el CI que guarde `<script>` en un socio y verifique que se muestra escapado
- [ ] **P1 · S · E+C** Pasar la app Android a `Authorization: Bearer` y retirar `?token=` de la API
- [ ] **P1 · M · E+C** Roles (administrador / consulta): Edgar define permisos, Claude implementa y prueba
- [ ] **P1 · M · C** Pantalla para consultar la auditoría (`v_auditoria`)
- [ ] **P2 · S · C** Agregar `limit_req` al login en nginx (el comentario `DEBUG` de `anticipos.php` desapareció con el módulo)

### 5.2 Datos
- [x] **P1 · L · E+C** Precios con vigencia (decisión de COLFE, 9 oct 2026) — migración 008: `tbl_precios` como historial con `fecha_inicio` y `fecha_fin` (vacía = abierto), sin solapes por vinculación (trigger), sin campo `estado`; `spCrearPrecio` cierra el abierto anterior; la liquidación usa el precio vigente en la fecha de cierre; pantalla de precios con desde, hasta y situación
- [x] **P1 · M · C** Retirar el módulo de anticipos (decisión de COLFE, 9 oct 2026) — migración 006: tabla, pantalla, API interna, vista, procedimiento y triggers; el neto es ingresos − deducibles
- [x] **P1 · L · C** Deducibles uno por fila y retiro del ahorro (decisión de COLFE, 9 oct 2026) — migración 007: `tbl_deducibles` (nombre, porcentaje o valor fijo, por vinculación) y `tbl_liquidacion_deducible` (detalle por liquidación); recibo con un renglón por deducible
- [x] **P1 · S · C** Liquidación solo fija (decisión de COLFE, 9 oct 2026): se descartan la liquidación variable, el arrastre de saldo de anticipos, el ahorro como garantía y el tope de anticipos
- [ ] **P1 · S · C** Integridad, bloque 2: unificar el charset de las tablas viejas a `utf8mb4`. El deducible activo único por nombre y vinculación (007) y los precios sin solape (008) ya los garantizan triggers
- [ ] **P1 · S · E+C** Integridad, bloque 3 (requiere decisión de Edgar): bloquear cambios en liquidaciones cerradas
- [ ] **P1 · M · C** Runner de migraciones con tabla `schema_migrations`, respaldo previo y registro de lo aplicado
- [ ] **P0 · M · E** Validar la liquidación con una quincena real de COLFE

### 5.3 Operación
- [ ] **P0 · M · E+C** Respaldo conjunto fuera del VPS (volcados de `_backups`, `.env`, certificados) y restauración probada desde esa copia
- [ ] **P1 · S · E+C** Monitor externo de `colfe.sitiosapps.com` y `healthcheck` en los contenedores `app` y `web`
- [ ] **P1 · S · E** Cargar los secretos del workflow *Desplegar* (`produccion`: `VPS_HOST`, `VPS_USER`, `VPS_SSH_KEY`, `VPS_KNOWN_HOSTS`)
- [ ] **P1 · S · E** Revisar GitHub Pages (D4): está activo y publica desde `main`; si no es intencional, desactivarlo
- [ ] **P1 · S · E** Aviso de privacidad y política de tratamiento de datos personales con la cooperativa (Ley 1581)

### 5.4 Verificación con la app y con datos
- [ ] **P1 · S · C** Corregir `apiCrearRecoleccionesLote.php` (se retoma cuando la app Android esté terminada; Edgar, 8 oct 2026): hoy falla siempre (`Unknown column 'observaciones'`: el `INSERT` usa columnas que `tbl_recoleccion` no tiene), usa un estado `pendiente` inválido, devuelve el SQL interno al cliente y responde 200 aunque falle. Detectado el 8 oct 2026 al documentar la API (`docs/API_MOVIL.md`).
- [ ] **P1 · S · E** Probar la app Android contra producción (`apiValidarToken` ahora devuelve 401 si el token es inválido; el token es real y vence a las 24 h; las claves son las nuevas)
- [ ] **P1 · S · E** Decidir **D5** (27 ago al 30 sep 2026)
- [ ] **P2 · S · E** Liquidar desde la app la 2da quincena de feb 2025 (pendiente a propósito en el demo) y revisar precios y deducibles con la cooperativa

**Salida Fase 5:** criterios de salida cumplidos para cargar socios reales.

---

## Fase 6: Calidad y mantenimiento (P2, después de la demostración)

- [ ] **P2 · L · C** Frontend a `npm` con versiones fijadas y actualizadas (hallazgo E10: Bootstrap 3.4.1, AdminLTE 2.4.18, DataTables 1.10.19, FullCalendar 3.10.1 y jQuery 3.6.0 están desactualizadas o sin soporte)
- [ ] **P2 · M · C** CI: `composer audit`, Dependabot, CodeQL y escaneo de imágenes (Trivy); acciones de GitHub fijadas por hash
- [ ] **P2 · S · C** Tags SemVer por release además de `sha-<commit>`; el despliegue deja de usar `latest` por defecto
- [ ] **P2 · M · C** Content-Security-Policy compatible con AdminLTE
- [ ] **P2 · M · C** Ampliar pruebas (controladores, modelos, JavaScript) y medir cobertura
- [ ] **P2 · M · C** Métricas DORA básicas (frecuencia de despliegue, tiempo de entrega)

---

## Cierre: demostración y limpieza

- [ ] Checklist de salida cumplido (seguridad, despliegue, backup)
- [ ] Demostración del proyecto
- [x] Backup completo de la base demo antes de limpiar — lo hace `reset_produccion.sh` y se detiene si falla
- [x] `db/reset_produccion.sql`: vacía socios y movimientos, conserva usuario administrador y catálogos (precios, deducibles), reinicia contadores — con `deploy/reset_produccion.sh` (confirmación escrita, respaldo previo, verificación). Probado, incluida la reversión
- [ ] Ejecutar el reinicio en producción y verificar
- [ ] Cargar los socios reales

---

## Criterios de salida (go / no-go)

- [x] Ningún endpoint responde datos sin sesión o token válido — `tests/seguridad/smoke_endpoints.sh`, en el CI
- [x] Contraseñas con hash y `admin/admin` eliminado — migración 002 + `auth_test.sh`
- [x] Solo `public/` es accesible por HTTP; `db/`, `src/`, `config/`, `.git` devuelven 404 — probado con `php -S` y con nginx real; falta confirmarlo en el contenedor
- [x] Ningún reporte ni módulo se abre por URL directa sin sesión — `smoke_endpoints.sh`
- [x] Restauración de BD probada desde el esquema versionado — el CI restaura el seed en MySQL 8.0 en cada PR
- [x] Cálculo de liquidación validado contra una quincena — contra un recálculo independiente (demo). **Falta validarlo con una quincena real de COLFE**
- [x] Backup semanal funcionando y restauración probada — cron semanal (domingo 02:15) programado en el VPS y restauración verificada el 8 oct 2026 (solo local, 8 copias; copia externa opcional)
- [ ] App Android probada contra producción
- [x] Confirmar en producción que `/.git/HEAD` y `/config/config.php` devuelven 404 (**P0**) — verificado el 8 oct 2026 con `curl` contra `https://colfe.sitiosapps.com`: también 404 en `/db/`, `/src/`, `/.env`, `/storage/logs/` y `/docs/`
- [ ] Documentación completa y alineada: diccionario de datos, contrato de la API, reglas de liquidación, ADRs, runbook y README al día (Fase 4)
- [ ] Salida de las vistas escapada: prueba de XSS en el CI en verde (5.1)
- [ ] Roles implementados y probados (5.1)
- [ ] Monitoreo activo y respaldo fuera del VPS con restauración probada (5.3)
- [ ] La app Android usa `Authorization` y funciona contra producción (5.1 y 5.4)
- [ ] Liquidación validada con una quincena real de COLFE (5.2)

Orden sugerido: Fase 5 cumplida → demostración → reinicio de producción → carga de socios reales → Fase 6.
