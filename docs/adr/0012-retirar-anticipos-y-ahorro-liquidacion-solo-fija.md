# ADR 0012: Retirar anticipos y ahorro; liquidación solo fija

- **Estado:** Aceptada y construida (9 oct 2026, migraciones 006 y 007)
- **Registrada:** 9 oct 2026
- **Decide:** Edgar, con la cooperativa COLFE

## Contexto

El 8 oct 2026 se había diseñado, con Edgar, el arrastre del saldo negativo de anticipos, un registro de ahorro como garantía, un tope de anticipos y una liquidación variable (cada día con su precio). El 9 oct 2026 COLFE, el cliente, cambió esas definiciones.

## Decisión

1. **Se retira el módulo de anticipos** completo: tabla `tbl_anticipos`, su vista, su procedimiento, sus triggers, la pantalla y el descuento en la liquidación (migración 006). El neto pasa a ser ingresos − deducibles.
2. **Se retira el ahorro:** ni descuento de ahorro ni registro de ahorros (migración 007). Quedan los deducibles Fedegán y Administración (ADR 0013).
3. **Solo liquidación fija:** toda la quincena con el precio vigente en la fecha de cierre. Se descartan la liquidación variable, el desglose por tramos y la reliquidación con otro tipo.
4. **Se descartan** el arrastre del saldo negativo, el ahorro como garantía y el tope de anticipos, que dependían de los puntos anteriores.

## Consecuencias

- Las migraciones 006 y 007 son **destructivas**: borran los datos de anticipos (23 en el demo) y de ahorro, y recalculan las liquidaciones existentes (16 que descontaban anticipos y las de los asociados, que ya no descuentan ahorro). Hay que hacer un respaldo antes; para volver atrás hay que restaurarlo.
- La auditoría conserva el historial de lo que se registró de los anticipos.
- Un neto negativo ya solo puede ocurrir si los deducibles superan los ingresos.
- Quedan sin efecto las tareas del plan sobre anticipos, ahorro y liquidación variable (Fase 5.2).

## Alternativas consideradas

Mantener lo diseñado el 8 oct (arrastre, ahorro y variable): se descartó por decisión del cliente. Retirar solo lo nuevo y dejar el módulo de anticipos existente: no era lo que pidió COLFE.
