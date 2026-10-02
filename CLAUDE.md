# ColfeWeb: guía para Claude (VS Code, web y cualquier sesión)

Portal de liquidación lechera de la cooperativa COLFE. PHP + MySQL 8.0, MVC propio en español,
AdminLTE + jQuery + DataTables. Destino: VPS Contabo, nginx, Docker, subdominio de sitiosapps.com.

## Antes de empezar cualquier tarea
1. `git fetch origin` y partir de `main` actualizado.
2. Leer `docs/PLAN_TRABAJO.md` (fuente de verdad del avance) y `docs/DIAGNOSTICO_ESTRUCTURA.md`.
3. Si hay otra sesión trabajando (otro chat o VS Code), no tocar su rama ni sus archivos.

## Fuente de verdad
- **Qué falta por hacer:** `docs/PLAN_TRABAJO.md`. Cada PR marca las casillas que completa.
- **Estructura objetivo:** `docs/DIAGNOSTICO_ESTRUCTURA.md` (`public/` + `src/` + `config/`).
- **Decisiones tomadas:** sección «Decisiones» del plan. No reabrirlas sin que Edgar lo pida.
- El chat no es memoria: si algo se decide, se escribe en estos archivos.

## Ramas y PR
- `main` siempre estable; solo se cambia por PR.
- Una rama por tarea o grupo corto de tareas: `fase-N/tema` (ej. `fase-0/reestructura`,
  `fase-1/seguridad-endpoints`, `fase-2/docker`), siempre desde `main` actualizado.
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

## Verificación mínima antes de abrir un PR
- `php -l` sobre todos los `.php` modificados.
- Arranque local: `php -S localhost:8080 -t public docker/php-dev-router.php`.
- Recorrido manual del módulo tocado; el proyecto aún no tiene pruebas automáticas.
- Sin MySQL no se prueba la lógica de liquidación: decirlo en el PR si no se pudo probar.

## Idioma y estilo
- Respuestas, commits y documentación en español, tono profesional y cercano.
- Primero un borrador para iterar; no asumir que la primera versión es la final.
