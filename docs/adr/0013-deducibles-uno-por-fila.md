# ADR 0013: Deducibles uno por fila

- **Estado:** Aceptada y construida (9 oct 2026, migración 007)
- **Registrada:** 9 oct 2026
- **Decide:** Edgar, con la cooperativa COLFE

## Contexto

`tbl_deducibles` tenía tres columnas fijas: `fedegan`, `administracion` y `ahorro`, y una sola fila activa por vinculación. Si COLFE quería aplicar un deducible distinto, no se podía sin cambiar el esquema y el código.

## Decisión

1. `tbl_deducibles` guarda **un deducible por fila**: `nombre`, `tipo_valor` (`porcentaje` o `fijo`) y `valor`, para una `vinculacion`. Un porcentaje va de 0 a 100 y se aplica sobre los ingresos; un valor fijo se descuenta tal cual por liquidación.
2. Al liquidar se aplican **todos los deducibles `activo`** de la vinculación del socio. Un trigger impide dos activos con el mismo nombre para la misma vinculación.
3. `tbl_liquidacion_deducible` (nueva) guarda, por liquidación, cada deducible aplicado con su nombre, tipo, valor y monto al liquidar. `tbl_liquidacion` conserva `total_deducibles` y pierde `id_deducible`, `fedegan`, `administracion` y `ahorro`.
4. El recibo imprime un renglón por cada deducible; la pantalla de Deducibles permite crear, editar, activar y borrar (un deducible ya usado en una liquidación no se borra: se deja `inactivo`).

## Consecuencias

- Se pueden crear deducibles nuevos desde la aplicación, sin cambios de código ni de esquema.
- Los datos existentes se convirtieron: cada fila vieja pasó a «Fedegán» y la administración se separó en «Administración» (solo asociados). Las liquidaciones existentes tienen su detalle.
- Una vinculación sin deducibles activos se liquida sin descuentos; ya no se exige un deducible para liquidar.
- Se corrigió un defecto previo: el formulario de edición también disparaba el borrado porque ambos usaban el mismo campo.
- **Relación con los socios:** no hay llave foránea entre socio y deducible. Los deducibles se aplican por vinculación (todos los `activo` de la vinculación del socio). La tabla `tbl_liquidacion_deducible` es la relación muchos a muchos entre liquidación y deducible y guarda el histórico de lo aplicado; Edgar decidió mantenerla (9 oct 2026) frente a las alternativas de una columna JSON o de guardar solo el total.
- Detalle del modelo en el [diccionario de datos](../DICCIONARIO_DATOS.md).

## Alternativas consideradas

Conservar las columnas fijas y añadir una por cada deducible nuevo: obliga a cambiar el esquema, las pantallas y el recibo cada vez. Guardar el desglose como JSON dentro de `tbl_liquidacion` o solo el total: se descartaron porque el JSON se consulta peor y no tiene integridad referencial, y solo el total pierde el histórico y el renglón por deducible del recibo.
