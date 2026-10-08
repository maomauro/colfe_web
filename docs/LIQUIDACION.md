# Liquidación quincenal de COLFE

Explica cómo calcula COLFE el pago a cada socio. Está escrito para que lo revise la cooperativa y lo use quien mantenga el sistema. Describe **lo que hace hoy el sistema** (versión del 8 oct 2026); lo acordado que aún no está construido está al final, en «Reglas acordadas pendientes de construir». Los nombres de tablas y columnas están definidos en el [diccionario de datos](DICCIONARIO_DATOS.md).

## Glosario

| Término | Qué significa |
|---|---|
| **Socio** | Productor que entrega leche. Solo los socios `activo` reciben recolección y se liquidan. |
| **Vinculación** | Tipo de relación del socio: `asociado` o `proveedor`. Define qué precio y qué deducibles se le aplican. |
| **Quincena** | Periodo de pago. **1ra:** días 1 al 15. **2da:** día 16 al último del mes. Se identifica por su fecha de cierre (día 15 o último día del mes). |
| **Recolección** | Litros que se le recogen al socio cada día. Nace en `0` y `sin confirmar`; cuando se verifica pasa a `confirmado`. |
| **Producción** | Suma de los litros `confirmado` de un socio en una quincena. |
| **Precio** | Pesos por litro, según la vinculación. Es un precio base (sin bonificaciones). |
| **Deducibles** | Descuentos sobre el pago. **Fedegán:** un porcentaje de los ingresos (contribución parafiscal). **Administración** y **ahorro:** valores fijos en pesos por socio en cada liquidación. |
| **Anticipo** | Dinero adelantado al socio. Está `pendiente`, `aprobado` o `rechazado`; solo el `aprobado` se descuenta. |
| **Pre-liquidación** | Resultado calculado, todavía revisable. |
| **Cierre (liquidación)** | La pre-liquidación confirmada. A partir de ahí el pago se considera definitivo y el panel de inicio la incluye en sus gráficas. |

## Flujo completo

1. **Cada día:** se crean los registros de recolección de todos los socios activos (litros en `0`, `sin confirmar`). Se anotan los litros reales y se confirman.
2. **Al cierre de la quincena (día 15 o último del mes):** el administrador liquida desde el calendario.
3. **El sistema valida** que se pueda liquidar (reglas de la siguiente sección) y calcula una **pre-liquidación** por socio.
4. **El administrador revisa** las pre-liquidaciones y las **confirma**, una por una o todas las de esa fecha. Confirmar las pasa a `liquidacion` (cierre).

## Cuándo se puede liquidar

El sistema se niega a liquidar, con un mensaje claro, si:

- la fecha no es el día 15 ni el último día del mes;
- algún día de la quincena no tiene **ningún** registro confirmado (el mensaje dice cuántos días faltan);
- queda algún registro `sin confirmar` dentro de la quincena.

Si ya existe una liquidación para esa fecha, no hace nada y no la duplica.

## La fórmula, paso a paso

Para cada socio activo con litros confirmados en la quincena:

1. **Litros** = suma de los litros `confirmado` del socio entre el primer y el último día de la quincena.
2. **Precio** = el precio `activo` de su vinculación.
3. **Ingresos** = litros × precio, redondeado a 2 decimales.
4. **Fedegán** = ingresos × (porcentaje de Fedegán ÷ 100), redondeado a 2 decimales.
5. **Administración** y **ahorro** = los valores fijos del deducible `activo` de su vinculación.
6. **Total deducibles** = Fedegán + administración + ahorro.
7. **Anticipos** = suma de los anticipos `aprobado` del socio con fecha dentro de la quincena.
8. **Neto a pagar** = ingresos − total deducibles − anticipos, redondeado a 2 decimales.

La liquidación guarda una **copia** de todo lo usado (precio, deducibles, identificación, vinculación). Así una liquidación ya hecha no cambia si luego se modifica un precio o un deducible.

### Ejemplo con datos reales del demo

Socio 16 (`asociado`), 1ra quincena de agosto de 2026 (del 1 al 15). Es la liquidación que está guardada en la base de datos y que el sistema calculó; un recálculo independiente da el mismo resultado.

| Paso | Cálculo | Resultado |
|---|---|---|
| Litros confirmados | suma de 15 días | 962,05 |
| Precio (asociado) | | 1.700,00 por litro |
| Ingresos | 962,05 × 1.700 | **1.635.485,00** |
| Fedegán | 1.635.485,00 × 0,75 ÷ 100 | 12.266,14 |
| Administración | valor fijo | 10.000,00 |
| Ahorro | valor fijo | 25.000,00 |
| Total deducibles | 12.266,14 + 10.000 + 25.000 | **47.266,14** |
| Anticipos aprobados | 1 anticipo del 4 de agosto | 84.872,00 |
| **Neto a pagar** | 1.635.485,00 − 47.266,14 − 84.872,00 | **1.503.346,86** |

El detalle día por día de este mismo ejemplo está en [`ejemplos/ejemplo_liquidacion_fija_vs_variable.xlsx`](ejemplos/ejemplo_liquidacion_fija_vs_variable.xlsx).

## Comportamientos que conviene conocer

- **Socio sin precio o sin deducible activo:** si su vinculación no tiene un precio o un deducible `activo`, el socio **se omite sin avisar**. Ya ocurrió con los asociados en el demo y se corrigió con la migración 003. Antes de liquidar conviene revisar que haya uno activo por cada vinculación.
- **Socio inactivo o sin litros confirmados:** no genera producción ni liquidación.
- **Anticipos pendientes o rechazados:** no se descuentan.
- **Anticipo con fecha en una quincena ya liquidada:** no se descuenta en esa quincena (ya no se recalcula) y tampoco pasa a la siguiente.
- **Neto negativo:** si los anticipos y deducibles superan los ingresos, el neto sale negativo y **no se arrastra** a la siguiente quincena. Ver las reglas pendientes.
- **Una liquidación por socio y quincena:** la base de datos lo impide con una restricción única.
- **Cierre sin candado:** ni la base de datos ni el servicio que confirma las liquidaciones impiden cambiar una liquidación ya cerrada (por ejemplo, devolverla a `pre-liquidacion`); solo queda el rastro en la auditoría. Está en el plan, en el bloque 3 de integridad.
- **Reliquidar:** no existe. Para rehacer una quincena habría que borrar sus liquidaciones y su producción a mano.

## Reglas acordadas pendientes de construir

Edgar las definió el 8 oct 2026. Están en el plan de trabajo, Fase 5.2. Hasta que se construyan, el sistema se comporta como se describe arriba.

1. **Precios con vigencia.** El historial de precios llevará fecha de inicio y de fin (vacía = abierto), sin solapes por vinculación. La liquidación usará el precio que rige en las fechas de la quincena.
2. **Liquidación fija o variable.** El administrador elegirá al liquidar:
   - **Fija:** toda la quincena con el precio vigente en la fecha de cierre.
   - **Variable:** cada día con el precio que regía ese día (tramos).

   El tipo se guarda en la liquidación, y mientras esté en pre-liquidación se podrá reliquidar con el otro tipo.
3. **Saldo negativo de anticipos.** Si los anticipos superan el pago de la quincena, el saldo se arrastra como descuento a la siguiente quincena.
4. **Ahorro como garantía.** Se registrará el ahorro de cada socio, con los saldos que ya tenía antes del sistema. Si un socio se retira con anticipos sin cubrir, se descuentan de su ahorro.
5. **Tope de los anticipos.** Se aprobará un anticipo si cabe en el neto estimado de la quincena **o** en el ahorro del socio; basta con cubrir uno de los dos.

## Para revisar con la cooperativa

Conviene que la cooperativa confirme:

- que la fórmula y el ejemplo coinciden con cómo calculan hoy el pago;
- que los valores de administración y ahorro (hoy 10.000 y 25.000 para asociados, 0 para proveedores) y el 0,75 % de Fedegán son los vigentes;
- los saldos de ahorro actuales de cada socio, necesarios para la regla 4.
