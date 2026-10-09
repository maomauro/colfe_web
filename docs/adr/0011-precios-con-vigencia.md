# ADR 0011: Precios con vigencia, sin solapes

- **Estado:** Aceptada, pendiente de construir (8 oct 2026)
- **Registrada:** 8 oct 2026
- **Decide:** Edgar, con la cooperativa COLFE

## Contexto

El precio de la leche cambia con el tiempo y puede cambiar cualquier día. Es uno para asociados y otro para proveedores. Hoy `tbl_precios` tiene una sola fecha (la de creación) y un estado `activo`, y la liquidación usa el precio activo del momento de liquidar sin mirar fechas: si el precio cambia y luego se liquida una quincena anterior, sale con el precio equivocado.

## Decisión

1. `tbl_precios` pasa a ser un **historial**: cada precio con `fecha_inicio` y `fecha_fin` (vacía = abierto), sin solapes por vinculación, garantizado con un trigger (MySQL no tiene restricciones de rango).
2. La liquidación usa el precio que rige en la **fecha de cierre** de la quincena (liquidación fija; ver el ADR 0012).

## Consecuencias

- Cambios en `tbl_precios` (dos columnas y un trigger) y en `spProcesarLiquidacionQuincenal` (elegir el precio por fecha en lugar de por estado).
- Reemplaza, para los precios, la regla «un solo precio activo» y su trigger.
- Si un precio cambiara en medio de una quincena, toda la quincena se paga con el precio vigente al cierre; es una consecuencia aceptada de la liquidación fija.
- Modelo propuesto en [`../diagramas/er-colfe-objetivo.html`](../diagramas/er-colfe-objetivo.html). Tarea en el plan, Fase 5.2.

## Alternativas consideradas

Pagar cada día con el precio que regía ese día (liquidación variable): se propuso el 8 oct 2026 y COLFE la descartó el 9 oct (ADR 0012). Suponer que los cambios de precio empiezan siempre el día 1 o el 16: se descartó porque el precio puede cambiar cualquier día.
