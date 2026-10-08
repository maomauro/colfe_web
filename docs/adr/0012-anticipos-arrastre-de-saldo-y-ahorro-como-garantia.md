# ADR 0012: Anticipos: arrastre de saldo negativo, ahorro como garantía y tope

- **Estado:** Aceptada, pendiente de construir (8 oct 2026)
- **Registrada:** 8 oct 2026
- **Decide:** Edgar

## Contexto

Un socio puede tener cuantos anticipos aprobados quiera. Si superan el pago de la quincena, el neto sale negativo; nada lo impide y el saldo no pasa a la siguiente quincena. Además, el ahorro solo existe como un descuento fijo en cada liquidación: no hay un registro del ahorro acumulado de cada socio.

## Decisión

1. **Arrastre:** si los anticipos superan el pago de la quincena, el saldo negativo se descuenta de la siguiente quincena. Propuesta: una columna de saldo previo en `tbl_liquidacion`.
2. **Ahorro como garantía:** se registra el ahorro de cada socio en una tabla nueva, `tbl_ahorros`, de movimientos (estructura por definir; con los saldos que ya tenía antes del sistema, que debe aportar la cooperativa). Sirve de respaldo en el caso extremo en que el socio se retire con anticipos sin cubrir: se descuentan de su ahorro.
3. **Tope de los anticipos:** se aprueba un anticipo si cabe en el neto estimado de la quincena **o** en el ahorro del socio; basta con cubrir uno de los dos.
4. **Trazabilidad de los anticipos:** `tbl_anticipos` se conserva y gana `id_liquidacion` (la liquidación que lo descontó) e `id_usuario` (llave foránea a `tbl_usuarios`, en lugar del texto `usuario_registro`). Edgar descartó un registro único de movimientos por socio; la huella técnica de los cambios sigue en `tbl_auditoria`.

## Consecuencias

- Hace falta una tabla nueva para el ahorro (`tbl_ahorros`) y una columna de saldo previo en la liquidación.
- Modelo propuesto en [`../diagramas/er-colfe-objetivo.html`](../diagramas/er-colfe-objetivo.html) (llega con el PR del diccionario de datos).
- La cooperativa debe entregar los saldos de ahorro actuales para cargar el saldo inicial.
- La aprobación de un anticipo pasa a validar el tope.
- Tareas en el plan, Fase 5.2. Mientras tanto, el comportamiento actual está descrito en [`../LIQUIDACION.md`](../LIQUIDACION.md).

## Alternativas consideradas

No hacer nada y que el administrador lo controle a mano; bloquear los anticipos que dejarían un neto negativo (se eligió el arrastre); o un registro único de movimientos por socio que absorba anticipos y ahorro (Edgar lo descartó para no ampliar el modelo).
