# ADR 0005: Protección CSRF con token por sesión y verificación del origen

- **Estado:** Aceptada
- **Registrada:** 8 oct 2026
- **Decide:** Edgar

## Contexto

La interfaz web modifica datos por AJAX y formularios con sesión de cookie, lo que la expone a peticiones falsificadas desde otros sitios. Además, cuatro borrados se hacían con peticiones GET.

## Decisión

Toda petición que no sea GET, HEAD u OPTIONS debe traer un token CSRF válido (por sesión, en el campo `csrf_token` o la cabecera `X-CSRF-Token`) y un `Origin` (o `Referer`) del mismo host. La plantilla añade la cabecera a todo `$.ajax`. Los cuatro borrados pasaron a POST. La API móvil queda fuera de esta regla porque se autentica con token en `Authorization`, no con cookie.

## Consecuencias

- Un formulario o llamada AJAX nuevos deben incluir el token; si no, el servidor responde con error.
- Las pruebas del CI cubren CSRF (`tests/seguridad/csrf_test.sh`).
- La verificación de origen exige que el nginx pase bien el host y el protocolo.

## Alternativas consideradas

Solo verificar el origen, o solo el token: cada una por separado deja huecos que la combinación cubre.
