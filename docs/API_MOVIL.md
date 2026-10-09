# API móvil de COLFE

Contrato de los 6 endpoints que consume la app Android. Describe **lo que hace hoy la API** (8 oct 2026). Se comprobó llamando a cada endpoint en un entorno local; lo que no coincide con lo esperable está señalado como **Observación**.

## Generalidades

- **Base:** `https://colfe.sitiosapps.com/api/` (local: `http://localhost:8080/api/`).
- **Formato:** JSON en UTF-8. Las peticiones con cuerpo envían `Content-Type: application/json`.
- **Autenticación:** primero `apiLogin.php` entrega un token; luego cada petición lo envía en la cabecera `Authorization: Bearer <token>`.
- **Token:** 64 caracteres hexadecimales, vigente **24 horas** (`API_TOKEN_TTL`, en segundos). En el servidor solo se guarda su hash (`tbl_api_tokens`). Al vencer hay que volver a iniciar sesión.
- **CORS:** la app nativa no lo necesita. El navegador solo puede llamar desde los orígenes de `CORS_ALLOWED_ORIGINS` (vacío por defecto); un `OPTIONS` sin origen permitido responde 403.
- **Auditoría:** los cambios hechos con un token quedan atribuidos a su usuario, con origen `api`.

### Cambio de autenticación (importante para la app)

| Antes | Ahora |
|---|---|
| Cualquier cadena de 64 caracteres hexadecimales se aceptaba como token. | El token debe existir en la base de datos y no estar vencido; si no, **401**. |
| `apiValidarToken` respondía 200 con `Token inválido`. | Responde **401**. |
| Token en la URL: `?token=...`. | Cabecera `Authorization: Bearer <token>`. **`?token=` sigue aceptándose por compatibilidad**, pero está previsto retirarlo (plan, Fase 5.1): ponerlo en la URL lo deja en los registros del servidor. |
| Claves `admin/admin`. | Las claves cambiaron; los usuarios se crean con `db/tools/crear_usuario.php`. |

### Códigos de respuesta

| Código | Cuándo |
|---|---|
| 200 | Petición válida. **También** algunos errores de negocio (ver Observaciones). |
| 400 | Faltan datos requeridos. |
| 401 | Token ausente, inválido o vencido. |
| 405 | Método HTTP no permitido para ese endpoint. |
| 429 | Demasiados intentos de inicio de sesión (bloqueo de 15 minutos). |
| 500 | Error interno. El detalle va al registro del servidor, no a la respuesta. |

Errores con forma `{"status":"error","message":"..."}`.

---

## `POST /api/apiLogin.php` — iniciar sesión

Sin token.

**Cuerpo**
```json
{ "username": "admin", "password": "su-clave" }
```

**200, éxito**
```json
{
  "status": "success",
  "message": "Autenticación exitosa",
  "data": {
    "user_id": 3,
    "username": "admin",
    "nombre": "",
    "rol": "usuario",
    "token": "<64 hex>",
    "timestamp": 1791493101,
    "expires_at": 1791579501
  }
}
```
`timestamp` y `expires_at` son segundos Unix.

**Errores**
- 400 si faltan `username` o `password`.
- **200** con `{"status":"error","message":"Usuario o contraseña incorrectos"}` si las credenciales fallan.
- 429 con el mensaje de bloqueo: 5 fallos por usuario o 20 por IP en 15 minutos.
- 405 si no es POST.

**Observaciones:** `nombre` siempre llega vacío y `rol` siempre vale `usuario`, porque `tbl_usuarios` no tiene esas columnas. Las credenciales incorrectas responden 200 (no 401); la app debe revisar `status`.

---

## `POST /api/apiValidarToken.php` — comprobar un token

Sin cabecera de autenticación: el token va en el cuerpo.

**Cuerpo**
```json
{ "token": "<64 hex>" }
```

**200, token válido**
```json
{
  "status": "success",
  "message": "Token válido",
  "data": { "valid": true, "timestamp": 1791493101, "user_id": 3, "expires_at": 1791496701 }
}
```

**Errores:** 400 `Token requerido` si no se envía; **401** `Token inválido` si no existe o venció; 405 si no es POST.

---

## `GET /api/apiSocios.php` — socios activos

Requiere token. Devuelve solo socios con estado `activo`, ordenados por nombre y apellido.

**Parámetros opcionales (query):** `nombre`, `apellido`, `identificacion` (búsqueda parcial) y `vinculacion` (`asociado` o `proveedor`, exacto). Se combinan con Y.

**200**
```json
{
  "status": "success",
  "data": [
    {
      "id_socio": 16, "nombre": "Daniela", "apellido": "Martinez",
      "identificacion": "27181884", "telefono": "308227884",
      "direccion": "Calle 42 #79-34", "vinculacion": "asociado",
      "fecha_ingreso": "2026-05-02", "estado": "activo"
    }
  ],
  "total": 1
}
```

**Errores:** 401 sin token válido; 405 si no es GET. Sin coincidencias devuelve `"data": []` y `"total": 0`.

---

## `GET /api/apiRecoleccionQuincena.php` — recolección de la quincena actual

Requiere token. La quincena se calcula con la fecha del servidor: del día 1 al 15, o del 16 al último día del mes. Solo incluye socios activos, ordenada por fecha descendente.

**200**
```json
{
  "status": "success",
  "data": {
    "quincena": "Primera",
    "fecha_inicio": "2026-10-01",
    "fecha_fin": "2026-10-15",
    "fecha_actual": "2026-10-08",
    "recolecciones": [
      {
        "id_recoleccion": 0, "id_socio": 0, "fecha": "2026-10-07",
        "litros_leche": "0.00", "estado": "confirmado",
        "nombre": "", "apellido": "", "identificacion": "",
        "telefono": "", "direccion": "", "vinculacion": "asociado"
      }
    ],
    "estadisticas": {
      "total_litros": 0, "total_socios": 0, "total_recolecciones": 0,
      "confirmadas": 0, "pendientes": 0
    }
  }
}
```
Los valores del ejemplo son ilustrativos. `estado` es `confirmado` o `sin confirmar`; `pendientes` cuenta todo lo que no esté confirmado. Si aún no hay registros en la quincena, `recolecciones` es `[]`.

**Errores:** 401, 405 (si no es GET).

---

## `POST /api/apiCrearRecoleccionesLote.php` — sincronizar recolecciones en lote

Requiere token.

**Cuerpo**
```json
{
  "recolecciones": [
    { "id_socio": 16, "fecha": "2026-10-08", "litros_leche": 62.5, "estado": "confirmado" }
  ]
}
```
Cada elemento exige `id_socio`, `fecha` (`AAAA-MM-DD`) y `litros_leche` mayor que 0; los elementos incompletos se descartan en silencio. `estado` es opcional.

**Errores:** 400 si falta el campo `recolecciones`; 401; 405 si no es POST; **200** con `No hay recolecciones válidas para procesar` si todos los elementos se descartan.

> **Observación grave: este endpoint no funciona hoy.** Prueba del 8 oct 2026: cualquier lote válido responde `{"status":"error","message":"Error en la transacción: SQLSTATE[42S22]: Column not found: 1054 Unknown column 'observaciones' in 'field list'"}`. El `INSERT` usa las columnas `observaciones` y `created_at`, que `tbl_recoleccion` no tiene (solo tiene `id_recoleccion`, `id_socio`, `fecha`, `litros_leche`, `estado`). Además:
>
> - el estado por defecto del código es `pendiente`, que no es un valor válido (los válidos son `confirmado` y `sin confirmar`);
> - el mensaje de error revela el SQL interno al cliente;
> - responde 200 aunque falle.
>
> Si la app Android sincroniza por aquí, hoy no está guardando nada. Corrección pendiente en el plan (Fase 5).

---

## `GET /api/apiTotalLiquidacion.php` — totales de liquidaciones cerradas

Requiere token. Devuelve, por vinculación, quincena y fecha de cierre, la suma de litros y de neto a pagar de las liquidaciones **cerradas** (`estado = liquidacion`), ordenadas por fecha.

**200** (sin el envoltorio `status`: es un arreglo directo)
```json
[
  { "vinculacion": "asociado", "quincena": "1ra", "fecha_liquidacion": "2025-01-15",
    "total_litros": "23466.97", "total_neto": "38649645.15" }
]
```
Los totales llegan como **texto**, no como números.

**Errores:** 401, 405 (si no es GET).

---

## Para la app Android

1. Guardar el `token` y `expires_at` que devuelve el login; renovar el token (nuevo login) cuando venza o ante un 401.
2. Enviar siempre `Authorization: Bearer <token>`; dejar de usar `?token=`.
3. No suponer que 200 significa éxito: revisar `status`.
4. Tratar `total_litros` y `total_neto` de `apiTotalLiquidacion` como texto que hay que convertir.
5. No usar `apiCrearRecoleccionesLote` hasta que se corrija.
