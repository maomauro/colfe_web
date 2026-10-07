# ColfeWeb: guía para Claude (VS Code, web y cualquier sesión)

Portal de liquidación lechera de la cooperativa COLFE. PHP + MySQL 8.0, MVC propio en español,
AdminLTE + jQuery + DataTables. Destino: VPS Contabo, nginx, Docker, subdominio de sitiosapps.com.

## Antes de empezar cualquier tarea
1. `git fetch origin` y partir de `develop` actualizado.
2. Leer `docs/PLAN_TRABAJO.md` (fuente de verdad del avance) y `docs/DIAGNOSTICO_ESTRUCTURA.md`.
3. Si hay otra sesión trabajando (otro chat o VS Code), no tocar su rama ni sus archivos.

## Fuente de verdad
- **Qué falta por hacer:** `docs/PLAN_TRABAJO.md`. Cada PR marca las casillas que completa.
- **Estructura objetivo:** `docs/DIAGNOSTICO_ESTRUCTURA.md` (`public/` + `src/` + `config/`).
- **Decisiones tomadas:** sección «Decisiones» del plan. No reabrirlas sin que Edgar lo pida.
- El chat no es memoria: si algo se decide, se escribe en estos archivos.

## Ramas y PR
- Flujo: **feature → `develop` → `main`**. Ramas permanentes: `main` (producción, siempre estable) y `develop` (integración).
  Ambas están protegidas: solo se cambian por PR, con el CI en verde y las conversaciones resueltas.
- Una rama por tarea o grupo corto de tareas: `fase-N/tema` (ej. `fase-1/seguridad-endpoints`,
  `fase-2/docker`), siempre desde `develop` actualizado. El PR de la feature va **contra `develop`**.
- Cuando `develop` está listo para publicar, se abre un PR `develop` → `main`. Un PR hacia `main` desde
  cualquier otra rama falla el check «Verificar rama de origen» (`.github/workflows/enforce-develop-to-main.yml`).
- Publicar imágenes (`publicar.yml`) corre en cada fusión a `main`; el despliegue al VPS sigue siendo manual (`desplegar.yml`).
- Commits en español, descriptivos, un cambio lógico por commit.
- Al terminar: marcar casillas en `docs/PLAN_TRABAJO.md` dentro del mismo PR.
- Etiquetas al cerrar hitos: `v0.1-seguridad`, `v0.2-staging`, `v1.0-produccion`.
- No fusionar ni borrar ramas ajenas; no reescribir historial compartido.

## Reglas del proyecto
- Los datos son **demo** (fechas 2025-2026), se usan en pruebas y producción. Tras la
  demostración se limpian con `db/reset_produccion.sql`. No cargar datos reales antes.
- Nunca versionar secretos, `.env` ni credenciales. El seed ya no deja usuarios: se crean con `db/tools/crear_usuario.php`.
- Las URLs públicas (`vistas/...`, `ajax/...`, `api/...`) no cambian al reestructurar.
- Seguridad antes de publicar: ver Fase 1 del plan (guard de sesión y token, hash de claves).
- Cambios mecánicos primero (mover/renombrar con `git mv`), lógica después.
- La app Android consume `api/`: avisar a Edgar antes de cambiar contratos de la API.

## Verificación antes de abrir un PR
- `php -l` sobre todos los `.php` modificados.
- Arranque local: `php -S localhost:8080 -t public docker/php-dev-router.php` (con `ENVIRONMENT=development` y las `DB_*`) o `docker compose up -d --build`.
- Pruebas (el CI las ejecuta en MySQL 8.0, pero conviene correrlas antes):
  - `vendor/bin/phpunit` (tras `composer install`): liquidación y auditoría. **Escriben en la base**: usar una desechable.
  - `tests/seguridad/*.sh` (endpoints, autenticación, CSRF, auditoría) con `BASE_URL`, `APP_USER`, `APP_PASS`.
  - `tests/navegador/recorrido.mjs` (Playwright): login, módulos sin errores de JS y borrado con confirmación.
- Lo que toque JavaScript o formularios **debe probarse en navegador**: las pruebas con `curl` no ven errores de JS (así se coló un bucle de login).
- Si el sandbox no tiene MySQL/Docker, decirlo en el PR; el CI cubre MySQL 8.0 y el build de las imágenes.
- No incluir `vendor/` ni `.phpunit.cache/` en un commit (están en `.gitignore`; con `git add` explícito y no `-A` en ramas viejas).

## Fusión de PR apilados
Si una rama se apoya en otra, **fusionar siempre contra `develop`** (o redirigir la base a `develop` antes). Fusionar un PR
contra su rama base deja el contenido varado fuera de `develop` y de `main`, aunque GitHub lo muestre como «merged».

## Idioma y estilo
- Respuestas, commits y documentación en español, tono profesional y cercano.
- Primero un borrador para iterar; no asumir que la primera versión es la final.
