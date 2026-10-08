# ADR 0007: Respaldo semanal de la base de datos, solo en el VPS por ahora

- **Estado:** Aceptada, con revisión obligatoria antes de datos reales
- **Registrada:** 8 oct 2026
- **Decide:** Edgar

## Contexto

Hacen falta respaldos de la base de datos de COLFE. Hoy los datos son demo, así que perderlos no es grave.

## Decisión

`deploy/backup.sh` vuelca la base (tablas, procedimientos, funciones, triggers y eventos), quita los `DEFINER`, valida el archivo (gzip íntegro y cierre normal del volcado), guarda un `.sha256` y rota los respaldos locales. Se ejecuta **cada domingo a las 2:15 a. m.** con cron y deja **8 copias** (56 días) en `/srv/sitiosapps/_backups/colfe`. La copia fuera del VPS con `rclone` (`BACKUP_REMOTE`) queda opcional. La restauración se probó en el VPS el 8 oct 2026 (`deploy/probar_restauracion.sh`): conteos idénticos, 4 procedimientos, 1 función y 24 triggers.

## Consecuencias

- Todas las copias están en el mismo servidor que la base: protegen contra un borrado accidental, no contra la pérdida del VPS.
- Antes de cargar datos reales hay que tener un respaldo conjunto fuera del VPS con restauración probada (plan, Fase 5.3, prioridad P0).
- Copiar la carpeta `/srv/sitiosapps` no respalda las bases de datos, que viven en volúmenes de Docker: el respaldo conjunto debe incluir los volcados de `_backups`.

## Alternativas consideradas

Respaldo diario o copia externa desde el inicio: se pospuso porque los datos son demo.
