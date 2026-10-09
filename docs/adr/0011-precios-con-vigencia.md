# ADR 0011: Precios con vigencia, sin solapes

- **Estado:** Aceptada y construida (decidida el 8 oct 2026; migración 008, 9 oct 2026)
- **Registrada:** 8 oct 2026
- **Decide:** Edgar, con la cooperativa COLFE

## Contexto

El precio de la leche cambia con el tiempo y puede cambiar cualquier día. Es uno para asociados y otro para proveedores. Hoy `tbl_precios` tiene una sola fecha (la de creación) y un estado `activo`, y la liquidación usa el precio activo del momento de liquidar sin mirar fechas: si el precio cambia y luego se liquida una quincena anterior, sale con el precio equivocado.

## Decisión

1. `tbl_precios` es un **historial**: cada precio tiene `fecha_inicio` (obligatoria) y `fecha_fin` (vacía = abierto). Se quitan el campo `estado` y la columna `fecha`: manda la vigencia por fechas, y la lista muestra si un precio está Vigente, Futuro o Cerrado.
2. Los rangos de una misma vinculación **no se solapan**, y la fecha de fin no puede ser anterior a la de inicio. Lo garantiza un trigger (MySQL no tiene restricciones de rango), con un `CHECK` como red de seguridad.
3. Al **crear** un precio nuevo, el abierto anterior de esa vinculación se cierra automáticamente el día anterior al inicio del nuevo. Lo hace el procedimiento `spCrearPrecio`, en una transacción.
4. La liquidación usa el precio que rige en la **fecha de cierre** de la quincena (liquidación fija; ver el ADR 0012), y la aplicación se niega a liquidar si una vinculación no tiene precio vigente en esa fecha.

## Consecuencias

- Cambios en `tbl_precios` (dos columnas nuevas, dos retiradas y un trigger), en `spProcesarLiquidacionQuincenal` (elegir el precio por fecha) y en la pantalla de precios (desde, hasta, situación, crear, editar y borrar).
- Si un precio cambia en medio de una quincena, toda la quincena se paga con el precio vigente al cierre: es una consecuencia aceptada de la liquidación fija.
- Un precio ya usado en una liquidación no se puede borrar; se cierra con una fecha de fin.
- La migración 008 falla, sin cambiar nada, si hay precios con estado inactivo: cada uno debe cerrarse antes con una fecha de fin. Los precios existentes quedan abiertos desde su fecha de registro.
- Detalle del modelo en el [diccionario de datos](../DICCIONARIO_DATOS.md).

## Alternativas consideradas

Pagar cada día con el precio que regía ese día (liquidación variable): se propuso el 8 oct 2026 y COLFE la descartó el 9 oct (ADR 0012). Suponer que los cambios de precio empiezan siempre el día 1 o el 16: se descartó porque el precio puede cambiar cualquier día. Mantener el campo `estado` además de las fechas: se descartó porque dos controles pueden contradecirse (un precio «activo» fuera de su rango). Rechazar el precio nuevo si hay uno abierto y pedir cerrarlo a mano: se prefirió cerrarlo automáticamente.
