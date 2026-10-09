# ADR 0006: Estructura de carpetas con una sola raíz web (`public/`)

- **Estado:** Aceptada
- **Registrada:** 8 oct 2026
- **Decide:** Edgar

## Contexto

Al inicio toda la carpeta del proyecto era la raíz web, así que `db/`, `config.php`, los controladores y los registros quedaban descargables salvo que el servidor lo impidiera. Los módulos de `vistas/modulos/` se podían abrir por URL y saltarse el inicio de sesión.

## Decisión

Solo `public/` es accesible por HTTP: `index.php`, `ajax/`, `api/`, `reportes/` y los estáticos de `vistas/`. Fuera de la raíz web quedan `src/` (código, plantillas, `bootstrap.php` y guards), `config/`, `storage/logs/`, `db/`, `tests/`, `docker/` y `deploy/`. Las URLs públicas no cambian, así que el JavaScript y las vistas no se reescribieron. Un solo `src/bootstrap.php` y un solo guard (`src/auth/guard.php`); las rutas usan `__DIR__`, nunca `DOCUMENT_ROOT`. Las plantillas de los módulos solo se incluyen desde el router.

## Consecuencias

- La protección de `db/`, `src/` y `config/` no depende de reglas del servidor web: están fuera de la raíz.
- Verificado en producción: `/.git/HEAD`, `/config/config.php`, `/db/`, `/src/`, `/.env`, `/storage/logs/` y `/docs/` devuelven 404.
- Antes de esta decisión se reorganizó con `git mv` para conservar el historial. El mapa de movimientos está en [`../DIAGNOSTICO_ESTRUCTURA.md`](../DIAGNOSTICO_ESTRUCTURA.md) (documento histórico).

## Alternativas consideradas

Dejar la raíz actual y bloquear carpetas con reglas de nginx y `.htaccess`: frágil, porque una regla olvidada expone el código.
