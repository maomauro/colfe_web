# ADR 0001: Datos demo en pruebas y producción hasta la demostración

- **Estado:** Aceptada (decisión D1)
- **Registrada:** 8 oct 2026
- **Decide:** Edgar

## Contexto

Todavía no se cargan datos reales de la cooperativa. El proyecto necesita datos para probar y para la demostración.

## Decisión

Se usan datos **demo** sintéticos (`db/seed/colfe_demo_2026.sql`, con fechas de 2025-01-01 a 2026-08-26) en las pruebas y también en producción. Después de la demostración se limpia la base de producción con `db/reset_produccion.sql` (vía `deploy/reset_produccion.sh`): vacía socios y movimientos y conserva el usuario administrador y los catálogos (precios y deducibles).

## Consecuencias

- No se cargan datos reales antes de la demostración ni antes de terminar la Fase 5 (aviso de privacidad, respaldo externo, validación con una quincena real).
- El repositorio puede ser público sin exponer datos de personas (ver ADR 0010).
- El reinicio hace un respaldo previo, pide confirmación escrita y se detiene si el respaldo falla.
- Sigue abierta **D5**: cómo cubrir del 27 ago al 30 sep 2026 (generar datos o cargarlos desde la app).

## Alternativas consideradas

Cargar datos reales desde el inicio: se descartó por el riesgo de exponerlos mientras el sistema se endurece.
