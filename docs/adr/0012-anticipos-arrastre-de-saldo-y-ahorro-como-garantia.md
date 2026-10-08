# ADR 0012: Anticipos: arrastre de saldo negativo, ahorro como garantía y tope

- **Estado:** Aceptada, pendiente de construir (8 oct 2026)
- **Registrada:** 8 oct 2026
- **Decide:** Edgar

## Contexto

Un socio puede tener cuantos anticipos aprobados quiera. Si superan el pago de la quincena, el neto sale negativo; nada lo impide y el saldo no pasa a la siguiente quincena. Además, el ahorro solo existe como un descuento fijo en cada liquidación: no hay un registro del ahorro acumulado de cada socio.

## Decisión

1. **Arrastre:** si los anticipos superan el pago de la quincena, el saldo negativo se descuenta de la siguiente quincena.
2. **Ahorro como garantía:** se registra el ahorro de cada socio (movimientos, con los saldos que ya tenía antes del sistema, que debe aportar la cooperativa). Sirve de respaldo en el caso extremo en que el socio se retire con anticipos sin cubrir: se descuentan de su ahorro.
3. **Tope de los anticipos:** se aprueba un anticipo si cabe en el neto estimado de la quincena **o** en el ahorro del socio; basta con cubrir uno de los dos.

## Consecuencias

- Hace falta una estructura nueva para el ahorro y un mecanismo de arrastre.
- La cooperativa debe entregar los saldos de ahorro actuales para cargar el saldo inicial.
- La aprobación de un anticipo pasa a validar el tope.
- Tareas en el plan, Fase 5.2. Mientras tanto, el comportamiento actual está descrito en [`../LIQUIDACION.md`](../LIQUIDACION.md).

## Alternativas consideradas

No hacer nada y que el administrador lo controle a mano; o bloquear los anticipos que dejarían un neto negativo. Se eligió el arrastre.
