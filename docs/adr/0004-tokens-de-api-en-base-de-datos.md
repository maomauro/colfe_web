# ADR 0004: Tokens de la API móvil guardados como hash en la base de datos

- **Estado:** Aceptada
- **Registrada:** 8 oct 2026
- **Decide:** Edgar

## Contexto

La API móvil aceptaba cualquier cadena de 64 caracteres hexadecimales como token válido. Hacía falta un token real, con vencimiento y atribuible a un usuario.

## Decisión

`apiLogin` genera un token aleatorio de 64 caracteres hexadecimales (`random_bytes(32)`) y guarda solo su hash SHA-256 en `tbl_api_tokens`, con `expira_en` (24 h por defecto, `API_TOKEN_TTL`) y `ultimo_uso`. Cada petición valida el token contra esa tabla (`guardToken`). Los vencidos se borran de forma oportunista. Los cambios hechos con un token quedan atribuidos a su usuario en la auditoría, con origen `api`.

## Consecuencias

- Un token filtrado se puede revocar borrando su fila; la base de datos nunca contiene tokens en claro.
- Cada petición hace una consulta a la base de datos.
- La app debe renovar el token (nuevo login) cuando vence o recibe un 401.
- Pendiente: retirar `?token=` de la URL y aceptar solo `Authorization: Bearer` (plan, Fase 5.1). Contrato en [`../API_MOVIL.md`](../API_MOVIL.md).

## Alternativas consideradas

Token firmado sin estado (HMAC o JWT): no necesita consultar la base de datos, pero no se puede revocar antes de que venza.
