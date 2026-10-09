# Liquidación quincenal de COLFE

Explica cómo calcula COLFE el pago a cada socio. Está escrito para que lo revise la cooperativa y lo use quien mantenga el sistema. Describe **lo que hace el sistema hoy** (versión del 9 oct 2026); lo acordado que aún no está construido está al final. Los nombres de tablas y columnas están definidos en el [diccionario de datos](DICCIONARIO_DATOS.md).

## Glosario

| Término | Qué significa |
|---|---|
| **Socio** | Productor que entrega leche. Solo los socios `activo` reciben recolección y se liquidan. |
| **Vinculación** | Tipo de relación del socio: `asociado` o `proveedor`. Define qué precio y qué deducibles se le aplican. |
| **Quincena** | Periodo de pago. **1ra:** días 1 al 15. **2da:** día 16 al último del mes. Se identifica por su fecha de cierre (día 15 o último día del mes). |
| **Recolección** | Litros que se le recogen al socio cada día. Nace en `0` y `sin confirmar`; cuando se verifica pasa a `confirmado`. |
| **Producción** | Suma de los litros `confirmado` de un socio en una quincena. |
| **Precio** | Pesos por litro, según la vinculación. Es un precio base (sin bonificaciones). |
| **Deducible** | Un descuento sobre el pago. Cada deducible tiene un nombre, una vinculación a la que aplica y un valor, que puede ser un **porcentaje** de los ingresos o un **valor fijo** en pesos por liquidación. Hoy hay dos: **Fedegán** (porcentaje, contribución parafiscal, para todos) y **Administración** (valor fijo, solo para asociados). Se pueden crear más sin cambiar el sistema. |
| **Pre-liquidación** | Resultado calculado, todavía revisable. |
| **Cierre (liquidación)** | La pre-liquidación confirmada. A partir de ahí el pago se considera definitivo y el panel de inicio la incluye en sus gráficas. |

## Flujo completo

1. **Cada día:** se crean los registros de recolección de todos los socios activos (litros en `0`, `sin confirmar`). Se anotan los litros reales y se confirman.
2. **Al cierre de la quincena (día 15 o último del mes):** el administrador liquida desde el calendario.
3. **El sistema valida** que se pueda liquidar (reglas de la siguiente sección) y calcula una **pre-liquidación** por socio.
4. **El administrador revisa** las pre-liquidaciones y las **confirma**, una por una o todas las de esa fecha. Confirmar las pasa a `liquidacion` (cierre).
5. **El recibo** (PDF) muestra, por socio, los litros, el precio, los ingresos, **un renglón por cada deducible aplicado**, el total de deducibles y el neto.

## Cuándo se puede liquidar

El sistema se niega a liquidar, con un mensaje claro, si:

- alguna vinculación con socios activos no tiene un **precio** `activo` (el mensaje dice cuál);
- la fecha no es el día 15 ni el último día del mes;
- algún día de la quincena no tiene **ningún** registro confirmado (el mensaje dice cuántos días faltan);
- queda algún registro `sin confirmar` dentro de la quincena.

Si ya existe una liquidación para esa fecha, no hace nada y no la duplica.

## La fórmula, paso a paso

Para cada socio activo con litros confirmados en la quincena:

1. **Litros** = suma de los litros `confirmado` del socio entre el primer y el último día de la quincena.
2. **Precio** = el precio `activo` de su vinculación. La liquidación es **fija**: toda la quincena se paga con ese precio.
3. **Ingresos** = litros × precio, redondeado a 2 decimales.
4. **Deducibles:** se aplican **todos los deducibles `activo`** de su vinculación, uno a uno:
   - si es un **porcentaje**: ingresos × porcentaje ÷ 100, redondeado a 2 decimales;
   - si es un **valor fijo**: ese valor.
5. **Total deducibles** = la suma de los montos de cada deducible.
6. **Neto a pagar** = ingresos − total deducibles, redondeado a 2 decimales.

La liquidación guarda una **copia** de todo lo usado (precio, identificación, vinculación y, por cada deducible, su nombre, tipo, valor y monto). Así una liquidación ya hecha no cambia si luego se modifica un precio o un deducible.

### Ejemplo con datos reales del demo

Socio 16 (`asociado`), 1ra quincena de agosto de 2026 (del 1 al 15). Es la liquidación que está guardada en la base de datos.

| Paso | Cálculo | Resultado |
|---|---|---|
| Litros confirmados | suma de 15 días | 962,05 |
| Precio (asociado) | | 1.700,00 por litro |
| Ingresos | 962,05 × 1.700 | **1.635.485,00** |
| Deducible «Fedegán» (porcentaje, 0,75 %) | 1.635.485,00 × 0,75 ÷ 100 | 12.266,14 |
| Deducible «Administración» (valor fijo) | | 10.000,00 |
| Total deducibles | 12.266,14 + 10.000,00 | **22.266,14** |
| **Neto a pagar** | 1.635.485,00 − 22.266,14 | **1.613.218,86** |

Para un **proveedor** solo se aplica Fedegán, porque «Administración» está definido solo para asociados.

## Comportamientos que conviene conocer

- **Socio sin precio activo:** si su vinculación no tiene un precio `activo`, el socio se omitiría sin avisar; por eso la aplicación lo comprueba antes de liquidar y se niega.
- **Vinculación sin deducibles activos:** se liquida sin descuentos.
- **Socio inactivo o sin litros confirmados:** no genera producción ni liquidación.
- **Neto negativo:** si los deducibles superan los ingresos, el neto sale negativo; la base de datos no lo impide.
- **Una liquidación por socio y quincena:** la base de datos lo impide con una restricción única.
- **Cierre sin candado:** ni la base de datos ni el servicio que confirma las liquidaciones impiden cambiar una liquidación ya cerrada (por ejemplo, devolverla a `pre-liquidacion`); solo queda el rastro en la auditoría. Está en el plan, en el bloque 3 de integridad.
- **Reliquidar:** no existe. Para rehacer una quincena habría que borrar sus liquidaciones y su producción a mano.
- **Deducibles ya usados:** un deducible que aparece en alguna liquidación no se puede borrar; se deja `inactivo` para que deje de aplicarse.

## Decisiones de COLFE del 9 oct 2026

Cambiaron el cálculo y ya están construidas (migraciones 006 y 007):

- **Sin anticipos:** se retiró el módulo y el descuento. Ya no hay nada que restar de la quincena fuera de los deducibles.
- **Sin ahorro:** se retiró el descuento de ahorro y el registro de ahorros.
- **Solo liquidación fija:** no existe la liquidación variable (cada día con su precio).
- **Deducibles uno por fila:** se pueden crear deducibles nuevos desde la pantalla de Deducibles, y el recibo los lista.

## Regla acordada pendiente de construir

**Precios con vigencia.** El historial de precios llevará fecha de inicio y de fin (vacía = abierto), sin solapes por vinculación. La liquidación usará el precio que rige en la fecha de cierre de la quincena. Hoy usa el precio `activo` del momento de liquidar sin mirar fechas: si el precio cambia y luego se liquida una quincena anterior, saldría con el precio equivocado. Está en el plan de trabajo, Fase 5.2.

## Para revisar con la cooperativa

Conviene que la cooperativa confirme:

- que la fórmula y el ejemplo coinciden con cómo calculan hoy el pago;
- que los valores vigentes son el 0,75 % de Fedegán para todos y 10.000 de administración solo para asociados.
