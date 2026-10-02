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
- [ ] **P1** Crear `.gitignore` (`.env`, `logs/`, `test_*.php`, `test_api_*.php`)
- [ ] **P1** Crear `.gitattributes` para normalizar fin de línea
- [ ] **P1** Archivar los dumps antiguos (`colfe_db.sql`, `colfe_db_20250717.sql`) y dejar uno solo como base
- [ ] **P1** Corregir `README.md`: importar el esquema vigente y quitar lo que no existe (CSRF, auditoría)
- [ ] **P1** Corregir `README_TESTS.md` (documenta tests inexistentes)

---

## Fase 1: Seguridad (P0, obligatoria antes de publicar)

### 1.1 Autenticación de endpoints
- [ ] Crear guard común (sesión para `ajax/`, token para `api/`) que responda 401
- [ ] Incluir el guard en los 10 archivos de `ajax/`
- [ ] Incluir el guard en las 6 APIs de `api/`
- [ ] Revisar `apiCrearRecoleccionesLote.php` y `apiRecoleccionQuincena.php` (control no confirmado)
- [ ] Probar con `curl` sin sesión: todo debe devolver 401

### 1.2 Token de la API móvil
- [ ] Guardar el token (o JWT firmado) con usuario y expiración
- [ ] Validar el token real en `apiValidarToken.php` y `apiSocios.php`
- [ ] Probar la app Android contra el cambio (rompe hasta actualizar la app)

### 1.3 Contraseñas y sesión
- [ ] Cambiar a `password_hash()` y `password_verify()`
- [ ] Migrar el usuario y eliminar `admin/admin` y `user/12345`
- [ ] Exigir contraseña nueva y fuerte (quitar la restricción alfanumérica)
- [ ] `session_regenerate_id()` al iniciar sesión
- [ ] Bloqueo por intentos (usar `MAX_LOGIN_ATTEMPTS`)

### 1.4 Superficie de ataque
- [ ] Eliminar `ajax/prediccion.ajax.php` (ejecuta `shell_exec`)
- [ ] Eliminar los `test_*.php` de la raíz web
- [ ] Restringir CORS (hoy `Access-Control-Allow-Origin: *`)

### 1.5 Configuración
- [ ] `config.php` lee todo de variables de entorno, sin credenciales por defecto
- [ ] `ENVIRONMENT=production` por defecto (sin `display_errors`)
- [ ] Usuario de BD propio con clave fuerte (no `desarrollo/desarrollo`)
- [ ] Confirmar que ningún `$item` o `$tabla` interpolado viene de la petición

**Salida Fase 1:** los 5 bloqueantes cerrados y probados.

---

## Fase 2: Portabilidad y despliegue (P1)

### 2.1 Código portable
- [ ] Reemplazar `$_SERVER["DOCUMENT_ROOT"]."/colfe_web/..."` por `__DIR__` en `usuarios.controlador.php`
- [ ] `APP_URL` y `API_URL` por variable de entorno

### 2.2 Docker local
- [ ] `Dockerfile` (php-fpm con `pdo_mysql`)
- [ ] `docker-compose.yml` (nginx, php-fpm, MySQL 8.0, phpMyAdmin)
- [ ] La BD se inicializa sola desde el dump demo
- [ ] Quitar `DEFINER=root@localhost` de la vista `v_anticipos_completos`

### 2.3 nginx
- [ ] `try_files` hacia `index.php?ruta=` (reemplaza `.htaccess`)
- [ ] Bloquear `db/`, `logs/`, `.git`, `test_*`

### 2.4 Producción
- [ ] `docker-compose.prod.yml` (sin phpMyAdmin, BD sin puerto público, volumen persistente)
- [ ] Subdominio en Cloudflare con HTTPS
- [ ] Cookie `secure` y cabeceras de seguridad
- [ ] `.env` solo en el servidor

### 2.5 Pipeline y respaldo
- [ ] GitHub Actions: construir imagen y publicar en GHCR (`latest` y `sha-<commit>`)
- [ ] Despliegue y rollback por tag
- [ ] Backup diario de MySQL a un destino fuera del VPS
- [ ] Probar la restauración del backup

### 2.6 Datos hasta hoy
- [ ] Cubrir del 27 ago al 30 sep 2026 según la decisión D5

**Salida Fase 2:** staging en el subdominio con HTTPS y backup restaurable.

---

## Fase 3: Confianza en el negocio (P2, en paralelo al staging)

- [ ] Pruebas PHPUnit de liquidación (deducibles, anticipos, precios por quincena)
- [ ] Validar una quincena completa contra un cálculo manual
- [ ] Revisar las 27 producciones sin liquidar que quedaron en el demo
- [ ] Liquidar desde la app la 2da quincena de feb 2025 (quedó sin liquidar a propósito)
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
- [ ] Sin `db/`, `test_*.php`, `logs/` ni `.git` accesibles por HTTP
- [ ] Restauración de BD probada desde el esquema versionado
- [ ] Cálculo de liquidación validado contra una quincena
- [ ] Backup diario funcionando y restauración probada
- [ ] App Android probada contra producción
