# ADR 0011: Precios con vigencia y liquidación fija o variable

- **Estado:** Aceptada, pendiente de construir (8 oct 2026)
- **Registrada:** 8 oct 2026
- **Decide:** Edgar

## Contexto

El precio de la leche cambia con el tiempo y puede cambiar cualquier día, incluso a mitad de una quincena. Es uno para asociados y otro para proveedores. Hoy `tbl_precios` tiene una sola fecha (la de creación) y un estado `activo`, y la liquidación usa el precio activo del momento de liquidar sin mirar fechas: si el precio cambia y luego se liquida una quincena anterior, sale con el precio equivocado.

## Decisión

1. `tbl_precios` pasa a ser un **historial**: cada precio con `fecha_inicio` y `fecha_fin` (vacía = abierto), sin solapes por vinculación, garantizado con un trigger (MySQL no tiene restricciones de rango).
2. El administrador, que es el único perfil que liquida, **elige el tipo de liquidación** en una ventana emergente:
   - **Fija:** toda la quincena con el precio vigente en la fecha de cierre.
   - **Variable:** cada día con el precio que regía ese día, por tramos.
3. El tipo se guarda en `tbl_liquidacion`; el desglose por tramos va en una columna JSON de la misma tabla, y `precio_litro` queda como promedio ponderado (ingresos ÷ litros) para que las pantallas y recibos actuales sigan funcionando. No se crea una tabla nueva.
4. Mientras la liquidación esté en `pre-liquidacion`, se puede reliquidar con el otro tipo; ya cerrada, no.

## Consecuencias

- Cambios en el procedimiento `spProcesarLiquidacionQuincenal`, en `tbl_precios` (dos columnas y un trigger) y en `tbl_liquidacion` (tipo y JSON).
- Reemplaza, para los precios, la regla «un solo precio activo» y su trigger.
- Un ejemplo numérico con una quincena real del demo y un cambio de precio simulado está en [`../ejemplos/ejemplo_liquidacion_fija_vs_variable.xlsx`](../ejemplos/ejemplo_liquidacion_fija_vs_variable.xlsx).
- Tarea en el plan, Fase 5.2.

## Alternativas consideradas

Asumir que los cambios de precio empiezan siempre el día 1 o el 16, para pagar toda la quincena con un solo precio: se descartó porque el precio puede cambiar cualquier día. Una tabla aparte para los tramos: se prefirió una columna JSON para no ampliar el modelo.
