# ColfeWeb: plan de trabajo hacia producción

Base: diagnóstico técnico del 29 sep 2026. Marca `[x]` al terminar cada tarea.
Prioridad: **P0** bloqueante · **P1** importante · **P2** deseable.

Decisiones ya tomadas:
- Se usan los datos demo existentes, con fechas 2025-2026, en pruebas y en producción.
- Tras la demostración se limpia la base de producción con un script de reinicio.
- Destino: VPS Contabo, nginx, Docker, subdominio de sitiosapps.com.

---

## Decisiones pendientes

- [ ] **D2** ¿Cómo convive con PortalCV en el VPS? (proxy compartido o puerto interno detrás del nginx existente)
- [ ] **D3** ¿Qué versión de PHP corre en Laragon? (define la imagen php-fpm)
- [ ] **D4** ¿El repositorio es público? (hoy contiene `admin/admin` en el dump)
- [ ] **D5** ¿Cómo cubrir del 27 ago al 30 sep 2026? (generar con `spInsertIntoRecoleccion` o cargar desde la app)
- [x] **D1** Datos reales: no se usan por ahora, se sigue con los demo

---

## Sprint 0: Higiene del repositorio

- [x] Subir cambios locales (API móvil, anticipos, dump 2026-09-29) — PR #1
- [x] Script `db/tools/desplazar_fechas.py` y dump `db/colfe_db_demo_2026.sql` — PR #2
- [ ] **P1** Probar la restauración de `colfe_db_demo_2026.sql` desde cero en MySQL 8.0
- [x] **P1** Crear `.gitignore` (`.env`, `logs/`, `test_*.php`, `test_api_*.php`)
- [x] **P1** Crear `.gitattributes` para normalizar fin de línea
- [x] **P1** Reorganizar `db/`: `schema/` (esquema sin datos), `seed/` (demo 2026), archivar dumps antiguos
- [x] **P1** Corregir `README.md`: importar el esquema vigente y quitar lo que no existe (CSRF, auditoría)
- [x] **P1** Corregir `README_TESTS.md` (documenta tests inexistentes)

---

## Fase 0: Reestructura del proyecto (P0, antes de la seguridad)

Detalle y mapa de movimientos: [`DIAGNOSTICO_ESTRUCTURA.md`](DIAGNOSTICO_ESTRUCTURA.md).
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
- [ ] **Pendiente de Edgar:** recorrer cada módulo en Laragon (socios, calendario, recolección, producción, deducibles, precios, anticipos, liquidación, recibos)

**Salida Fase 0:** el sitio funciona igual que antes, solo `public/` es accesible por HTTP.

---

## Fase 1: Seguridad (P0, obligatoria antes de publicar)

### 1.1 Autenticación de endpoints
- [x] Crear `src/auth/guard.php` (`guardSesion()` y `guardToken()`) que responda 401
- [x] **Nuevo (E1):** los módulos de `src/vistas/modulos/` solo se incluyen desde el router; confirmar que ninguno se ejecuta por URL
- [x] **Nuevo (E2):** guard de sesión en `public/reportes/recibo.php` y `reporte_recoleccion.php`
- [x] Incluir el guard en los 8 archivos de `ajax/` (prediccion se eliminó)
- [x] Incluir el guard en las 6 APIs de `api/` (`apiTotalLiquidacion` no tenía ningún control)
- [x] Revisar `apiCrearRecoleccionesLote.php` y `apiRecoleccionQuincena.php` (control no confirmado)
- [x] Probar con `curl` sin sesión: todo debe devolver 401

### 1.2 Token de la API móvil
- [x] Guardar el token (o JWT firmado) con usuario y expiración
- [x] Validar el token real en `apiValidarToken.php` y `apiSocios.php`
- [ ] **Pendiente de Edgar:** probar la app Android contra el cambio. Cambia solo `apiValidarToken` (ahora 401 si el token es inválido) y el token pasa a ser real

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
- [ ] `APP_URL` y `API_URL` por variable de entorno

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
- [x] `docker-compose.prod.yml` (sin phpMyAdmin, BD sin puerto público, volumen persistente) — `compose config` válido; **sin ejecutar en el VPS**
- [ ] Subdominio en Cloudflare con HTTPS
- [ ] Cookie `secure` y cabeceras de seguridad
- [ ] `.env` solo en el servidor

### 2.5 Pipeline y respaldo
- [x] GitHub Actions: construir imagen y publicar en GHCR (`latest` y `sha-<commit>`) — workflows `ci.yml` y `publicar.yml`
- [x] Despliegue y rollback por tag — `deploy/desplegar.sh` y workflow manual `desplegar.yml`
- [x] Backup diario de MySQL a un destino fuera del VPS — `deploy/backup.sh` (rclone). **Falta que Edgar elija el destino externo y configure `rclone`**
- [x] Probar la restauración del backup — `deploy/probar_restauracion.sh`, probado: conteos idénticos

### 2.6 Datos hasta hoy
- [ ] Cubrir del 27 ago al 30 sep 2026 según la decisión D5

**Salida Fase 2:** staging en el subdominio con HTTPS y backup restaurable.

---

## Fase 3: Confianza en el negocio (P2, en paralelo al staging)

- [x] Pruebas PHPUnit de liquidación (deducibles, anticipos, precios por quincena) — 18 pruebas / 988 aserciones, en el CI
- [x] Validar una quincena completa contra un cálculo manual — recálculo independiente de una quincena (2da feb-2025, 107 socios) y de las 4.066 liquidaciones
- [x] Revisar las 27 producciones sin liquidar que quedaron en el demo — causa: deducible de «asociado» con estado NULL (migración 003); eran 1.026 producciones de 27 socios
- [ ] Liquidar desde la app la 2da quincena de feb 2025 (queda pendiente a propósito en el demo; las pruebas ya verificaron que el procedimiento la calcula bien)
- [ ] CSRF en formularios y ajax
- [ ] Log de auditoría en liquidaciones y anticipos
- [ ] Roles de usuario (administrador / consulta)
- [ ] CI con lint y pruebas, y métricas DORA básicas
- [ ] Probar la app Android contra el servidor de producción

---

## Cierre: demostración y limpieza

- [ ] Checklist de salida cumplido (seguridad, despliegue, backup)
- [ ] Demostración del proyecto
- [ ] Backup completo de la base demo antes de limpiar
- [ ] `db/reset_produccion.sql`: vacía socios y movimientos, conserva usuario administrador y catálogos (precios, deducibles), reinicia contadores
- [ ] Ejecutar el reinicio en producción y verificar
- [ ] Cargar los socios reales

---

## Criterios de salida (go / no-go)

- [ ] Ningún endpoint responde datos sin sesión o token válido
- [ ] Contraseñas con hash y `admin/admin` eliminado
- [ ] Solo `public/` es accesible por HTTP; `db/`, `src/`, `config/`, `.git` devuelven 404
- [ ] Ningún reporte ni módulo se abre por URL directa sin sesión
- [ ] Restauración de BD probada desde el esquema versionado
- [ ] Cálculo de liquidación validado contra una quincena
- [ ] Backup diario funcionando y restauración probada
- [ ] App Android probada contra producción
